<?php

namespace VitisStudio\FilamentHeaderSchema;

use Filament\Support\Facades\FilamentView;
use Filament\View\PanelsRenderHook;
use Illuminate\Support\HtmlString;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use VitisStudio\FilamentHeaderSchema\Commands\MakeHeaderSchemaCommand;

class FilamentHeaderSchemaServiceProvider extends PackageServiceProvider
{
    protected static ?string $css = null;

    public function configurePackage(Package $package): void
    {
        $package
            ->name('filament-header-schema')
            ->hasViews()
            ->hasCommand(MakeHeaderSchemaCommand::class);
    }

    public function packageBooted(): void
    {
        FilamentView::registerRenderHook(
            PanelsRenderHook::STYLES_AFTER,
            fn (): HtmlString => new HtmlString('<style>'.static::css().'</style>'),
        );
    }

    /**
     * The package's styles are hand-written CSS built entirely on Filament's own
     * design tokens, so there is no build step and no asset to publish. Inlining
     * them means a custom theme picks up the user's colours and spacing for free.
     */
    protected static function css(): string
    {
        return static::$css ??= trim((string) file_get_contents(
            __DIR__.'/../resources/css/filament-header-schema.css',
        ));
    }
}
