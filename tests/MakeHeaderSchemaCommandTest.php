<?php

use Illuminate\Support\Facades\File;
use Illuminate\Testing\PendingCommand;
use VitisStudio\FilamentHeaderSchema\Tests\Fixtures\Resources\Bare\BareOrderResource;
use VitisStudio\FilamentHeaderSchema\Tests\Fixtures\Resources\Bare\NoTitleOrderResource;

beforeEach(function () {
    $this->schemasDirectory = __DIR__.'/Fixtures/Resources/Bare/Schemas';
    $this->pagesDirectory = __DIR__.'/Fixtures/Resources/Bare/Pages';

    // The command writes next to the resource it is given, so the fixture pages
    // are snapshotted and restored rather than mocked.
    $this->pageBackups = collect(File::files($this->pagesDirectory))
        ->mapWithKeys(fn ($file): array => [$file->getPathname() => File::get($file->getPathname())])
        ->all();
});

afterEach(function () {
    File::deleteDirectory($this->schemasDirectory);

    foreach ($this->pageBackups as $path => $contents) {
        File::put($path, $contents);
    }
});

function headerSchemaPath(): string
{
    return __DIR__.'/Fixtures/Resources/Bare/Schemas/OrderHeader.php';
}

function makeHeaderSchema(array $arguments = []): PendingCommand
{
    return test()->artisan('make:filament-header-schema', [
        'resource' => BareOrderResource::class,
        '--panel' => 'admin',
        ...$arguments,
    ]);
}

it('generates a header schema in the resource Schemas directory', function () {
    makeHeaderSchema(['--no-pages' => true])->assertSuccessful();

    expect(headerSchemaPath())->toBeFile();

    expect(File::get(headerSchemaPath()))
        ->toContain('namespace VitisStudio\FilamentHeaderSchema\Tests\Fixtures\Resources\Bare\Schemas;')
        ->toContain('class OrderHeader')
        ->toContain('public static function configure(Schema $schema): Schema')
        ->toContain('HeaderSection::make(')
        ->toContain('Heading::make(');
});

it('seeds the heading with the resource record title attribute', function () {
    makeHeaderSchema(['--no-pages' => true])->assertSuccessful();

    expect(File::get(headerSchemaPath()))->toContain("Heading::make('reference')");
});

it('falls back to the key when the resource has no record title attribute', function () {
    test()->artisan('make:filament-header-schema', [
        'resource' => NoTitleOrderResource::class,
        '--panel' => 'admin',
        '--no-pages' => true,
    ])->assertSuccessful();

    expect(File::get(headerSchemaPath()))->toContain("Heading::make('id')");
});

it('defaults the heading to the page heading so a list page never loses its title', function () {
    makeHeaderSchema(['--no-pages' => true])->assertSuccessful();

    expect(File::get(headerSchemaPath()))
        ->toContain('->default(fn ($livewire) => $livewire->getHeading())');
});

it('applies the trait to the pages that were selected', function () {
    $path = $this->pagesDirectory.'/ViewBareOrder.php';

    expect(File::get($path))->not->toContain('use HasHeaderSchema;');

    makeHeaderSchema(['--page' => ['view']])->assertSuccessful();

    expect(File::get($path))
        ->toContain('use VitisStudio\FilamentHeaderSchema\Concerns\HasHeaderSchema;')
        ->toContain("\n    use HasHeaderSchema;\n");
});

it('applies the trait to several pages at once', function () {
    makeHeaderSchema(['--page' => ['index', 'view', 'edit']])->assertSuccessful();

    foreach (['ListBareOrders', 'ViewBareOrder', 'EditBareOrder'] as $page) {
        expect(File::get($this->pagesDirectory."/{$page}.php"))
            ->toContain('use HasHeaderSchema;');
    }
});

it('leaves pages that were not selected alone', function () {
    $path = $this->pagesDirectory.'/ListBareOrders.php';
    $before = File::get($path);

    makeHeaderSchema(['--page' => ['view']])->assertSuccessful();

    expect(File::get($path))->toBe($before);
});

it('leaves every page alone when told not to touch them', function () {
    $before = collect(File::files($this->pagesDirectory))
        ->mapWithKeys(fn ($file): array => [$file->getPathname() => File::get($file->getPathname())]);

    makeHeaderSchema(['--no-pages' => true])->assertSuccessful();

    foreach ($before as $path => $contents) {
        expect(File::get($path))->toBe($contents);
    }
});

it('is idempotent on a page that already uses the trait', function () {
    $path = $this->pagesDirectory.'/ViewBareOrder.php';

    makeHeaderSchema(['--page' => ['view']])->assertSuccessful();
    $afterFirstRun = File::get($path);

    makeHeaderSchema(['--page' => ['view'], '--force' => true])->assertSuccessful();

    expect(File::get($path))->toBe($afterFirstRun)
        ->and(substr_count($afterFirstRun, 'use HasHeaderSchema;'))->toBe(1);
});

it('preserves the rest of the page file when applying the trait', function () {
    makeHeaderSchema(['--page' => ['view']])->assertSuccessful();

    expect(File::get($this->pagesDirectory.'/ViewBareOrder.php'))
        ->toContain('A docblock the command must leave intact.')
        ->toContain('public function getHeading(): string')
        ->toContain("return 'Native heading';")
        ->toContain('protected static string $resource = BareOrderResource::class;');
});

it('slots the trait in among traits the class already uses', function () {
    makeHeaderSchema(['--page' => ['edit']])->assertSuccessful();

    $contents = File::get($this->pagesDirectory.'/EditBareOrder.php');

    expect($contents)
        ->toContain('use Conditionable;')
        ->toContain('use HasHeaderSchema;')
        ->toContain('protected function getHeaderActions(): array');

    // Both trait statements sit together at the top of the class body, not
    // scattered through it.
    expect(preg_match('/\{\s*\n(\s*use\s+[^;]+;\s*\n){2}/', $contents))->toBe(1);
});

it('inserts the import in alphabetical order', function () {
    makeHeaderSchema(['--page' => ['view']])->assertSuccessful();

    $imports = collect(explode("\n", File::get($this->pagesDirectory.'/ViewBareOrder.php')))
        ->filter(fn (string $line): bool => str_starts_with($line, 'use '))
        ->values()
        ->all();

    expect($imports)->toEqual(collect($imports)->sort(SORT_NATURAL | SORT_FLAG_CASE)->values()->all());
});

it('leaves the generated page files valid PHP', function () {
    makeHeaderSchema(['--page' => ['index', 'view', 'edit']])->assertSuccessful();

    foreach (File::files($this->pagesDirectory) as $file) {
        $output = [];
        $status = 0;
        exec('php -l '.escapeshellarg($file->getPathname()).' 2>&1', $output, $status);

        expect($status)->toBe(0, implode("\n", $output));
    }

    exec('php -l '.escapeshellarg(headerSchemaPath()).' 2>&1', $output, $status);
    expect($status)->toBe(0, implode("\n", $output ?? []));
});

it('warns about a page route key the resource does not register', function () {
    makeHeaderSchema(['--page' => ['nonsense']])
        ->expectsOutputToContain('is not registered')
        ->assertSuccessful();
});
