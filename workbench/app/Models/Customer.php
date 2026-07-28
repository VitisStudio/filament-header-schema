<?php

namespace Workbench\App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Model
{
    protected $guarded = [];

    protected $casts = [
        'is_priority' => 'boolean',
        'joined_at' => 'date',
    ];

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    /**
     * A locally generated avatar, so the demo renders identically with or
     * without a network connection.
     */
    protected function avatar(): Attribute
    {
        return Attribute::get(function (): string {
            $initials = collect(explode(' ', $this->name))
                ->take(2)
                ->map(fn (string $word): string => mb_strtoupper(mb_substr($word, 0, 1)))
                ->implode('');

            $hue = crc32($this->name) % 360;

            $svg = <<<SVG
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 96 96">
                    <rect width="96" height="96" fill="hsl({$hue} 62% 46%)"/>
                    <text x="48" y="49" fill="#fff" font-family="system-ui, sans-serif"
                          font-size="38" font-weight="600" text-anchor="middle"
                          dominant-baseline="central">{$initials}</text>
                </svg>
                SVG;

            return 'data:image/svg+xml;base64,'.base64_encode($svg);
        });
    }

    protected function lifetimeValue(): Attribute
    {
        return Attribute::get(fn (): float => (float) $this->orders()->sum('total'));
    }
}
