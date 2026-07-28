<?php

namespace VitisStudio\FilamentHeaderSchema\Components;

use Closure;
use Filament\Infolists\Components\Entry;
use Filament\Schemas\View\Components\TextComponent;
use Filament\Support\Components\Contracts\HasEmbeddedView;
use Filament\Support\Concerns\HasColor;
use Filament\Support\Concerns\HasIcon;
use Filament\Support\Concerns\HasIconPosition;
use Filament\Support\Concerns\HasIconSize;
use Filament\Support\Concerns\HasWeight;
use Filament\Support\Enums\Alignment;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Enums\IconPosition;
use Filament\Support\Enums\IconSize;
use Filament\Support\Enums\Size;
use Filament\Support\Enums\TextSize;
use Filament\Support\View\ComponentAttributeBag as FilamentComponentAttributeBag;
use InvalidArgumentException;

use function Filament\Support\generate_icon_html;

/**
 * A real heading element for a page header.
 *
 * Reads its state the same way any infolist entry does — `Heading::make('name')`
 * resolves `name` on the header schema's record. Pass a literal through
 * `->state()` on pages that have no record:
 *
 *     Heading::make('title')->state('Orders')
 */
class Heading extends Entry implements HasEmbeddedView
{
    use HasColor;
    use HasIcon;
    use HasIconPosition;
    use HasIconSize;
    use HasWeight;

    /**
     * Font sizes the CSS understands, from `->size()`. Anything else is passed
     * through as a raw class so a custom theme can define its own.
     */
    public const SIZES = ['xs', 'sm', 'md', 'lg', 'xl', '2xl', '3xl'];

    protected int | Closure $level = 1;

    protected TextSize | Size | string | Closure | null $size = null;

    public function level(int | Closure $level): static
    {
        if (is_int($level)) {
            static::validateLevel($level);
        }

        $this->level = $level;

        return $this;
    }

    /**
     * @return int<1, 6>
     */
    public function getLevel(): int
    {
        return static::validateLevel((int) $this->evaluate($this->level));
    }

    /**
     * @return int<1, 6>
     */
    protected static function validateLevel(int $level): int
    {
        if ($level < 1 || $level > 6) {
            throw new InvalidArgumentException("Heading level [{$level}] is out of range: headings must be between 1 and 6.");
        }

        return $level;
    }

    /**
     * Accepts any of `Heading::SIZES`, or a `TextSize`/`Size` case. Leave it
     * unset to use the default size for the heading's level.
     */
    public function size(TextSize | Size | string | Closure | null $size): static
    {
        $this->size = $size;

        return $this;
    }

    public function getSize(): ?string
    {
        $size = $this->evaluate($this->size);

        if (blank($size)) {
            return null;
        }

        return ($size instanceof TextSize || $size instanceof Size) ? $size->value : $size;
    }

    /**
     * Headings render bare. The entry wrapper exists to lay out a label beside
     * its value, which is not what a heading is.
     *
     * @internal
     */
    public function wrapEmbeddedHtml(string $html): string
    {
        return $html;
    }

    public function toEmbeddedHtml(): string
    {
        $state = $this->getState();

        if (is_array($state)) {
            $state = implode(', ', $state);
        }

        if (blank($state)) {
            $state = $this->getPlaceholder();
        }

        if (blank($state)) {
            return '';
        }

        $level = $this->getLevel();
        $tag = "h{$level}";

        $icon = $this->getIcon();
        $iconPosition = $this->getIconPosition();
        $iconSize = $this->getIconSize();
        $weight = $this->getWeight();
        $size = $this->getSize();
        $alignment = $this->getAlignment();

        if (filled($iconSize) && ! $iconSize instanceof IconSize) {
            $iconSize = IconSize::tryFrom($iconSize) ?? $iconSize;
        }

        if (! $alignment instanceof Alignment) {
            $alignment = filled($alignment) ? (Alignment::tryFrom($alignment) ?? $alignment) : null;
        }

        $attributes = (new FilamentComponentAttributeBag)
            ->color(TextComponent::class, $this->getColor())
            ->class([
                'fi-hs-heading',
                "fi-hs-heading-level-{$level}",
                'fi-hs-has-icon' => filled($icon),
                filled($size) ? "fi-hs-size-{$size}" : null,
                ($weight instanceof FontWeight) ? "fi-font-{$weight->value}" : $weight,
                ($alignment instanceof Alignment) ? "fi-align-{$alignment->value}" : $alignment,
            ])
            ->merge($this->getExtraAttributes(), escape: false);

        ob_start(); ?>

        <<?= $tag ?> <?= $attributes->toHtml() ?>>
            <?php if ($iconPosition === IconPosition::Before && filled($icon)) { ?>
                <?= generate_icon_html($icon, size: $iconSize instanceof IconSize ? $iconSize : null)?->toHtml() ?>
            <?php } ?>

            <?= e($state) ?>

            <?php if ($iconPosition === IconPosition::After && filled($icon)) { ?>
                <?= generate_icon_html($icon, size: $iconSize instanceof IconSize ? $iconSize : null)?->toHtml() ?>
            <?php } ?>
        </<?= $tag ?>>

        <?php return ob_get_clean();
    }
}
