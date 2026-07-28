<?php

namespace VitisStudio\FilamentHeaderSchema\Concerns;

use Filament\Facades\Filament;
use Filament\Schemas\Schema;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Model;

/**
 * Lets a Filament page build its header from a schema instead of a Blade view.
 *
 * Define `headerSchema(Schema $schema): Schema` on the page to opt in. Without
 * that method — or when it returns a schema with no components — the page falls
 * back to Filament's native heading and subheading. Defining `getHeader()` on
 * the page itself still wins over this trait, so taking the header over
 * completely works exactly as it did before.
 */
trait HasHeaderSchema
{
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
