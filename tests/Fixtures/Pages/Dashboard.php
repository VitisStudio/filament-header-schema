<?php

namespace VitisStudio\FilamentHeaderSchema\Tests\Fixtures\Pages;

use Filament\Pages\Page;
use VitisStudio\FilamentHeaderSchema\Concerns\HasHeaderSchema;

/**
 * A custom page, not a resource page. It has no `getResource()`, so the trait
 * has nothing to resolve a conventional header schema from and the page keeps
 * its native heading.
 */
class Dashboard extends Page
{
    use HasHeaderSchema;

    protected string $view = 'filament-header-schema-tests::custom-page';
}
