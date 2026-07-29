<?php

namespace VitisStudio\FilamentHeaderSchema\Components\Concerns;

use Closure;
use Filament\Support\Enums\VerticalAlignment;

/**
 * Per-slot overrides for the cross-axis alignment the section sets on all three
 * slots at once.
 *
 * Filament's own `HasVerticalAlignment` covers the section, but a header often
 * wants one slot to break ranks — an avatar pinned to the top of a header whose
 * heading and badges are centered, say:
 *
 *     ->leadingVerticallyAlignStart()
 *
 * A slot left alone follows the section, so only the exceptions need naming.
 * Each slot also takes its alignment as the second argument of the method that
 * fills it, which keeps the two readable as one statement:
 *
 *     ->leading($avatar, VerticalAlignment::Start)
 */
trait HasSlotVerticalAlignment
{
    protected VerticalAlignment | string | Closure | null $leadingVerticalAlignment = null;

    protected VerticalAlignment | string | Closure | null $mainVerticalAlignment = null;

    protected VerticalAlignment | string | Closure | null $trailingVerticalAlignment = null;

    public function leadingVerticalAlignment(VerticalAlignment | string | Closure | null $alignment): static
    {
        $this->leadingVerticalAlignment = $alignment;

        return $this;
    }

    public function leadingVerticallyAlignStart(bool | Closure $condition = true): static
    {
        return $this->leadingVerticalAlignment(fn (): ?VerticalAlignment => $this->evaluate($condition) ? VerticalAlignment::Start : null);
    }

    public function leadingVerticallyAlignCenter(bool | Closure $condition = true): static
    {
        return $this->leadingVerticalAlignment(fn (): ?VerticalAlignment => $this->evaluate($condition) ? VerticalAlignment::Center : null);
    }

    public function leadingVerticallyAlignEnd(bool | Closure $condition = true): static
    {
        return $this->leadingVerticalAlignment(fn (): ?VerticalAlignment => $this->evaluate($condition) ? VerticalAlignment::End : null);
    }

    public function mainVerticalAlignment(VerticalAlignment | string | Closure | null $alignment): static
    {
        $this->mainVerticalAlignment = $alignment;

        return $this;
    }

    public function mainVerticallyAlignStart(bool | Closure $condition = true): static
    {
        return $this->mainVerticalAlignment(fn (): ?VerticalAlignment => $this->evaluate($condition) ? VerticalAlignment::Start : null);
    }

    public function mainVerticallyAlignCenter(bool | Closure $condition = true): static
    {
        return $this->mainVerticalAlignment(fn (): ?VerticalAlignment => $this->evaluate($condition) ? VerticalAlignment::Center : null);
    }

    public function mainVerticallyAlignEnd(bool | Closure $condition = true): static
    {
        return $this->mainVerticalAlignment(fn (): ?VerticalAlignment => $this->evaluate($condition) ? VerticalAlignment::End : null);
    }

    public function trailingVerticalAlignment(VerticalAlignment | string | Closure | null $alignment): static
    {
        $this->trailingVerticalAlignment = $alignment;

        return $this;
    }

    public function trailingVerticallyAlignStart(bool | Closure $condition = true): static
    {
        return $this->trailingVerticalAlignment(fn (): ?VerticalAlignment => $this->evaluate($condition) ? VerticalAlignment::Start : null);
    }

    public function trailingVerticallyAlignCenter(bool | Closure $condition = true): static
    {
        return $this->trailingVerticalAlignment(fn (): ?VerticalAlignment => $this->evaluate($condition) ? VerticalAlignment::Center : null);
    }

    public function trailingVerticallyAlignEnd(bool | Closure $condition = true): static
    {
        return $this->trailingVerticalAlignment(fn (): ?VerticalAlignment => $this->evaluate($condition) ? VerticalAlignment::End : null);
    }

    public function getLeadingVerticalAlignment(): VerticalAlignment | string | null
    {
        return $this->normalizeVerticalAlignment($this->evaluate($this->leadingVerticalAlignment));
    }

    public function getMainVerticalAlignment(): VerticalAlignment | string | null
    {
        return $this->normalizeVerticalAlignment($this->evaluate($this->mainVerticalAlignment));
    }

    public function getTrailingVerticalAlignment(): VerticalAlignment | string | null
    {
        return $this->normalizeVerticalAlignment($this->evaluate($this->trailingVerticalAlignment));
    }

    /**
     * A string that names one of Filament's alignments becomes the enum case;
     * anything else is passed through as the custom class a theme meant it to be.
     */
    protected function normalizeVerticalAlignment(mixed $alignment): VerticalAlignment | string | null
    {
        if (! is_string($alignment)) {
            return ($alignment instanceof VerticalAlignment) ? $alignment : null;
        }

        return VerticalAlignment::tryFrom($alignment) ?? $alignment;
    }

    /**
     * The class an alignment renders as, shared by the section and its slots.
     */
    protected function getVerticalAlignmentClass(VerticalAlignment | string | null $alignment): ?string
    {
        $alignment = $this->normalizeVerticalAlignment($alignment);

        if ($alignment instanceof VerticalAlignment) {
            return "fi-vertical-align-{$alignment->value}";
        }

        return $alignment;
    }
}
