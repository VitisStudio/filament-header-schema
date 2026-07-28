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
use Filament\Support\Enums\FontWeight;
use Filament\Support\Enums\IconPosition;
use Filament\Support\Enums\IconSize;
use Filament\Support\Enums\Size;
use Filament\Support\Enums\TextSize;
use Filament\Support\View\ComponentAttributeBag as FilamentComponentAttributeBag;

use function Filament\Support\generate_icon_html;

/**
 * The muted supporting line that sits under a {@see Heading}.
 *
 * Reads its state the same way any infolist entry does — `Subheading::make('email')`
 * resolves `email` on the header schema's record. Pass a literal or a closure
 * through `->state()` on pages that have no record:
 *
 *     Subheading::make('count')->state(fn ($livewire) => $livewire->getModel()::count().' orders')
 */
class Subheading extends Entry implements HasEmbeddedView
{
    use HasColor;
    use HasIcon;
    use HasIconPosition;
    use HasIconSize;
    use HasWeight;

    protected TextSize | Size | string | Closure | null $size = null;

    /**
     * Accepts any of `Heading::SIZES`, or a `TextSize`/`Size` case. Defaults to
     * `lg`, matching Filament's native page subheading.
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
     * Subheadings render bare, for the same reason headings do.
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

        $icon = $this->getIcon();
        $iconPosition = $this->getIconPosition();
        $iconSize = $this->getIconSize();
        $weight = $this->getWeight();
        $size = $this->getSize();

        if (filled($iconSize) && ! $iconSize instanceof IconSize) {
            $iconSize = IconSize::tryFrom($iconSize) ?? $iconSize;
        }

        $attributes = (new FilamentComponentAttributeBag)
            ->color(TextComponent::class, $this->getColor())
            ->class([
                'fi-hs-subheading',
                filled($size) ? "fi-hs-size-{$size}" : null,
                ($weight instanceof FontWeight) ? "fi-font-{$weight->value}" : $weight,
            ])
            ->merge($this->getExtraAttributes(), escape: false);

        ob_start(); ?>

        <p <?= $attributes->toHtml() ?>>
            <?php if ($iconPosition === IconPosition::Before && filled($icon)) { ?>
                <?= generate_icon_html($icon, size: $iconSize instanceof IconSize ? $iconSize : null)?->toHtml() ?>
            <?php } ?>

            <?= e($state) ?>

            <?php if ($iconPosition === IconPosition::After && filled($icon)) { ?>
                <?= generate_icon_html($icon, size: $iconSize instanceof IconSize ? $iconSize : null)?->toHtml() ?>
            <?php } ?>
        </p>

        <?php return ob_get_clean();
    }
}
