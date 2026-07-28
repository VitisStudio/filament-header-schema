<?php

namespace VitisStudio\FilamentHeaderSchema\Concerns;

use Filament\Facades\Filament;
use Filament\Schemas\Schema;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Model;

/**
 * Lets a Filament page build its header from a schema instead of a Blade view.
 *
 * By default the trait looks for a header schema class in the resource's
 * `Schemas` directory, the same way `form()` and `infolist()` resolve to
 * `OrderForm` and `OrderInfolist` — `make:filament-header-schema` generates it.
 * Declaring `headerSchema(Schema $schema): Schema` on the page overrides that
 * lookup.
 *
 * With neither in place — or when the schema resolves to no components — the
 * page falls back to Filament's native heading and subheading. Defining
 * `getHeader()` on the page itself still wins over this trait, so taking the
 * header over completely works exactly as it did before.
 */
trait HasHeaderSchema
{
    /**
     * Header schema classes already resolved for a page class, including the
     * misses, which are stored as `null`.
     *
     * @var array<class-string, ?class-string>
     */
    protected static array $cachedHeaderSchemaClasses = [];

    /**
     * Resolves the schema class the resource's `Schemas` directory is expected
     * to hold. Override `headerSchema()` on the page to build one inline
     * instead.
     */
    public function headerSchema(Schema $schema): Schema
    {
        $headerSchemaClass = static::getHeaderSchemaClass();

        if (blank($headerSchemaClass)) {
            // An empty schema reads as "no header schema" to `hasHeaderSchema()`,
            // which is what sends the page back to its native heading.
            return $schema;
        }

        return $headerSchemaClass::configure($schema);
    }

    /**
     * The conventional location of this page's header schema:
     * `App\Filament\Resources\Orders\Schemas\OrderHeader`, alongside the
     * `OrderForm` and `OrderInfolist` that Filament generates.
     *
     * @return ?class-string
     */
    public static function getHeaderSchemaClass(): ?string
    {
        return static::$cachedHeaderSchemaClasses[static::class] ??= (function (): ?string {
            // The trait works on any Filament page, but only resource pages know
            // which resource — and so which `Schemas` directory — to look in.
            // Custom pages have no `getResource()` and fall back to their native
            // heading unless they declare `headerSchema()` themselves.
            if (! method_exists(static::class, 'getResource')) {
                return null;
            }

            $resource = static::getResource();

            if (! class_exists($resource)) {
                return null;
            }

            $model = $resource::getModel();

            $fqn = (string) str($resource)
                ->beforeLast('\\')
                ->append('\\Schemas\\')
                ->append(class_basename($model))
                ->append('Header');

            return (class_exists($fqn) && method_exists($fqn, 'configure')) ? $fqn : null;
        })();
    }

    /**
     * Applied to the header schema before `headerSchema()` runs. Filament
     * resolves this by convention from the `headerSchema` schema name.
     */
    public function defaultHeaderSchema(Schema $schema): Schema
    {
        if (! $schema->hasCustomColumns()) {
            $schema->columns(1);
        }

        // A schema's default gap is built for page content and reads as loose
        // in a header, where Filament's own heading and subheading sit 0.5rem
        // apart. `->dense(false)` restores the roomier spacing.
        return $schema
            ->dense()
            ->record($this->getHeaderSchemaRecord());
    }

    /**
     * The record the header schema reads its state from. View and Edit pages
     * bind to the record being viewed or edited; List pages have no record, so
     * components there resolve through closures instead.
     *
     * @return Model|array<string, mixed>|null
     */
    public function getHeaderSchemaRecord(): Model | array | null
    {
        // `$record` is a non-nullable typed property on record pages, so it is
        // uninitialized until the page mounts. `isset()` covers that as well as
        // pages that have no record property at all.
        if (! isset($this->record)) {
            return null;
        }

        return method_exists($this, 'getRecord') ? $this->getRecord() : $this->record;
    }

    public function hasHeaderSchema(): bool
    {
        return (bool) count($this->getSchema('headerSchema')?->getComponents() ?? []);
    }

    public function getHeader(): ?View
    {
        if (! $this->hasHeaderSchema()) {
            return parent::getHeader();
        }

        return view('filament-header-schema::header', [
            'actions' => $this->getCachedHeaderActions(),
            'actionsAlignment' => $this->getHeaderActionsAlignment(),
            'breadcrumbs' => Filament::hasBreadcrumbs() ? $this->getBreadcrumbs() : [],
            'page' => $this,
            'schema' => $this->getSchema('headerSchema'),
        ]);
    }
}
