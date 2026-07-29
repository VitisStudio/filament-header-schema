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
use VitisStudio\FilamentHeaderSchema\Components\Concerns\HasSlotVerticalAlignment;

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
 *
 * Filament's `->grow()` controls the main slot. It grows by default, taking the
 * leftover width, which is what holds the trailing slot against the far edge.
 * `->grow(false)` lets it size to its content so the slots pack together:
 *
 *     ->grow()       [avatar][ heading ..................... ][ total ]
 *     ->grow(false)  [avatar][ heading ][ total ]
 *
 * `->verticallyAlignStart()` and friends align all three slots against each
 * other. A single slot breaks from that either through the matching
 * `->leadingVerticallyAlign*()`, `->mainVerticallyAlign*()` and
 * `->trailingVerticallyAlign*()`, or inline as the second argument of the
 * method that fills it:
 *
 *     HeaderSection::make([...], VerticalAlignment::Start)
 *         ->leading($avatar, VerticalAlignment::Start)
 *         ->trailing($total, VerticalAlignment::End)
 */
class HeaderSection extends Component implements HasEmbeddedView
{
    use HasFromBreakpoint;
    use HasSlotVerticalAlignment;
    use HasVerticalAlignment;

    public const LEADING_SCHEMA_KEY = 'leading';

    public const TRAILING_SCHEMA_KEY = 'trailing';

    /**
     * @param  array<Component | Action | ActionGroup | string | Htmlable> | Closure  $schema
     */
    final public function __construct(array | Closure $schema = [], VerticalAlignment | string | Closure | null $verticalAlignment = null)
    {
        $this->schema($schema);

        if (filled($verticalAlignment)) {
            $this->mainVerticalAlignment($verticalAlignment);
        }
    }

    /**
     * The second argument aligns the main slot, the same way `leading()` and
     * `trailing()` align theirs. It is the slot's own cross-axis alignment, not
     * the section's — `->verticallyAlign*()` still sets that.
     *
     * @param  array<Component | Action | ActionGroup | string | Htmlable> | Closure  $schema
     */
    public static function make(array | Closure $schema = [], VerticalAlignment | string | Closure | null $verticalAlignment = null): static
    {
        $static = app(static::class, ['schema' => $schema, 'verticalAlignment' => $verticalAlignment]);
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
    public function leading(array | Schema | Component | Action | ActionGroup | string | Htmlable | Closure | null $components, VerticalAlignment | string | Closure | null $verticalAlignment = null): static
    {
        $this->childComponents($components, static::LEADING_SCHEMA_KEY);

        if (filled($verticalAlignment)) {
            $this->leadingVerticalAlignment($verticalAlignment);
        }

        return $this;
    }

    /**
     * @param  array<Component | Action | ActionGroup | string | Htmlable> | Schema | Component | Action | ActionGroup | string | Htmlable | Closure | null  $components
     */
    public function trailing(array | Schema | Component | Action | ActionGroup | string | Htmlable | Closure | null $components, VerticalAlignment | string | Closure | null $verticalAlignment = null): static
    {
        $this->childComponents($components, static::TRAILING_SCHEMA_KEY);

        if (filled($verticalAlignment)) {
            $this->trailingVerticalAlignment($verticalAlignment);
        }

        return $this;
    }

    public function toEmbeddedHtml(): string
    {
        $attributes = (new FilamentComponentAttributeBag)
            ->merge($this->getExtraAttributes(), escape: false)
            ->class(array_filter([
                'fi-hs-section',
                'fi-dense' => $this->isDense(),
                'fi-from-'.($this->getFromBreakpoint() ?? 'default'),
                $this->getVerticalAlignmentClass($this->getVerticalAlignment()),
            ]));

        // Filament's own `grow()`, read here for the main slot: growing takes
        // the leftover width and pushes the trailing slot to the far edge,
        // while `->grow(false)` lets the main slot hug its content so the three
        // slots pack together.
        $mainAttributes = (new FilamentComponentAttributeBag)
            ->class(array_filter([
                'fi-hs-section-main',
                'fi-growable' => $this->canGrow(),
                $this->getVerticalAlignmentClass($this->getMainVerticalAlignment()),
            ]));

        // A slot with no alignment of its own inherits the section's, since
        // `align-self` is only emitted when one was asked for.
        $leadingAttributes = (new FilamentComponentAttributeBag)
            ->class(array_filter([
                'fi-hs-section-leading',
                $this->getVerticalAlignmentClass($this->getLeadingVerticalAlignment()),
            ]));

        $trailingAttributes = (new FilamentComponentAttributeBag)
            ->class(array_filter([
                'fi-hs-section-trailing',
                $this->getVerticalAlignmentClass($this->getTrailingVerticalAlignment()),
            ]));

        // Slots are rendered before the buffer opens so a throwing child
        // component does not leave a dangling output buffer behind.
        $leading = $this->getChildSchema(static::LEADING_SCHEMA_KEY)?->toHtml();
        $main = $this->getChildSchema()->toHtml();
        $trailing = $this->getChildSchema(static::TRAILING_SCHEMA_KEY)?->toHtml();

        ob_start(); ?>

        <div <?= $attributes->toHtml() ?>>
            <?php if (filled($leading)) { ?>
                <div <?= $leadingAttributes->toHtml() ?>>
                    <?= $leading ?>
                </div>
            <?php } ?>

            <div <?= $mainAttributes->toHtml() ?>>
                <?= $main ?>
            </div>

            <?php if (filled($trailing)) { ?>
                <div <?= $trailingAttributes->toHtml() ?>>
                    <?= $trailing ?>
                </div>
            <?php } ?>
        </div>

        <?php return ob_get_clean();
    }
}
