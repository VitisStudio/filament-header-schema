<?php

namespace VitisStudio\FilamentHeaderSchema\Components;

use Closure;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Schema;
use Filament\Support\Components\Contracts\HasEmbeddedView;
use Filament\Support\Concerns\HasFromBreakpoint;
use Filament\Support\Concerns\HasVerticalAlignment;
use Filament\Support\Enums\VerticalAlignment;
use Filament\Support\View\ComponentAttributeBag as FilamentComponentAttributeBag;
use Illuminate\Contracts\Support\Htmlable;

/**
 * The flexbox layout a rich page header usually wants, without writing a Blade
 * view for it: something small on the leading edge, the heading and its
 * supporting content in the middle, and metadata pushed to the trailing edge.
 *
 *     HeaderSection::make([
 *         Heading::make('name'),
 *         Flex::make([
 *             TextEntry::make('status')->badge()->hiddenLabel(),
 *             TextEntry::make('reference')->hiddenLabel(),
 *         ]),
 *         Subheading::make('summary'),
 *     ])
 *         ->leading(ImageEntry::make('avatar_url')->circular()->hiddenLabel())
 *         ->trailing(TextEntry::make('total')->money()->hiddenLabel())
 *
 * The three slots sit in a column on small screens and become a row from the
 * breakpoint given to `->from()`, which defaults to `sm`.
 */
class HeaderSection extends Component implements HasEmbeddedView
{
    use HasFromBreakpoint;
    use HasVerticalAlignment;

    public const LEADING_SCHEMA_KEY = 'leading';

    public const TRAILING_SCHEMA_KEY = 'trailing';

    /**
     * @param  array<Component | Action | ActionGroup | string | Htmlable> | Closure  $schema
     */
    final public function __construct(array | Closure $schema = [])
    {
        $this->schema($schema);
    }

    /**
     * @param  array<Component | Action | ActionGroup | string | Htmlable> | Closure  $schema
     */
    public static function make(array | Closure $schema = []): static
    {
        $static = app(static::class, ['schema' => $schema]);
        $static->configure();

        return $static;
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->from('sm');
        $this->verticallyAlignCenter();
    }

    /**
     * @param  array<Component | Action | ActionGroup | string | Htmlable> | Schema | Component | Action | ActionGroup | string | Htmlable | Closure | null  $components
     */
    public function leading(array | Schema | Component | Action | ActionGroup | string | Htmlable | Closure | null $components): static
    {
        $this->childComponents($components, static::LEADING_SCHEMA_KEY);

        return $this;
    }

    /**
     * @param  array<Component | Action | ActionGroup | string | Htmlable> | Schema | Component | Action | ActionGroup | string | Htmlable | Closure | null  $components
     */
    public function trailing(array | Schema | Component | Action | ActionGroup | string | Htmlable | Closure | null $components): static
    {
        $this->childComponents($components, static::TRAILING_SCHEMA_KEY);

        return $this;
    }

    public function toEmbeddedHtml(): string
    {
        $verticalAlignment = $this->getVerticalAlignment();

        if (! $verticalAlignment instanceof VerticalAlignment) {
            $verticalAlignment = filled($verticalAlignment)
                ? (VerticalAlignment::tryFrom($verticalAlignment) ?? $verticalAlignment)
                : null;
        }

        $attributes = (new FilamentComponentAttributeBag)
            ->merge($this->getExtraAttributes(), escape: false)
            ->class([
                'fi-hs-section',
                'fi-dense' => $this->isDense(),
                'fi-from-'.($this->getFromBreakpoint() ?? 'default'),
                ($verticalAlignment instanceof VerticalAlignment) ? "fi-vertical-align-{$verticalAlignment->value}" : $verticalAlignment,
            ]);

        // Slots are rendered before the buffer opens so a throwing child
        // component does not leave a dangling output buffer behind.
        $leading = $this->getChildSchema(static::LEADING_SCHEMA_KEY)?->toHtml();
        $main = $this->getChildSchema()->toHtml();
        $trailing = $this->getChildSchema(static::TRAILING_SCHEMA_KEY)?->toHtml();

        ob_start(); ?>

        <div <?= $attributes->toHtml() ?>>
            <?php if (filled($leading)) { ?>
                <div class="fi-hs-section-leading">
                    <?= $leading ?>
                </div>
            <?php } ?>

            <div class="fi-hs-section-main">
                <?= $main ?>
            </div>

            <?php if (filled($trailing)) { ?>
                <div class="fi-hs-section-trailing">
                    <?= $trailing ?>
                </div>
            <?php } ?>
        </div>

        <?php return ob_get_clean();
    }
}
