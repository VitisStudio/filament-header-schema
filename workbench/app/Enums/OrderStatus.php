<?php

namespace Workbench\App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;
use Filament\Support\Icons\Heroicon;

enum OrderStatus: string implements HasColor, HasIcon, HasLabel
{
    case Draft = 'draft';

    case Pending = 'pending';

    case Paid = 'paid';

    case Refunded = 'refunded';

    public function getLabel(): string
    {
        return ucfirst($this->value);
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Draft => 'gray',
            self::Pending => 'warning',
            self::Paid => 'success',
            self::Refunded => 'danger',
        };
    }

    public function getIcon(): Heroicon
    {
        return match ($this) {
            self::Draft => Heroicon::OutlinedPencilSquare,
            self::Pending => Heroicon::OutlinedClock,
            self::Paid => Heroicon::OutlinedCheckCircle,
            self::Refunded => Heroicon::OutlinedArrowUturnLeft,
        };
    }
}
