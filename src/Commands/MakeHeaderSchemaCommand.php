<?php

namespace VitisStudio\FilamentHeaderSchema\Commands;

use Filament\Resources\Pages\PageRegistration;
use Filament\Support\Commands\Concerns\CanAskForResource;
use Filament\Support\Commands\Concerns\CanManipulateFiles;
use Filament\Support\Commands\Concerns\HasCluster;
use Filament\Support\Commands\Concerns\HasPanel;
use Filament\Support\Commands\Concerns\HasResourcesLocation;
use Filament\Support\Commands\Exceptions\FailureCommandOutput;
use Illuminate\Console\Command;
use ReflectionClass;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use VitisStudio\FilamentHeaderSchema\Commands\Concerns\CanApplyTraitToClass;
use VitisStudio\FilamentHeaderSchema\Commands\FileGenerators\HeaderSchemaClassGenerator;
use VitisStudio\FilamentHeaderSchema\Concerns\HasHeaderSchema;

use function Laravel\Prompts\multiselect;

#[AsCommand(name: 'make:filament-header-schema', aliases: [
    'filament:header-schema',
])]
class MakeHeaderSchemaCommand extends Command
{
    use CanApplyTraitToClass;
    use CanAskForResource;
    use CanManipulateFiles;
    use HasCluster;
    use HasPanel;
    use HasResourcesLocation;

    protected $description = 'Create a header schema class for a Filament resource, and apply the trait to its pages';

    protected $name = 'make:filament-header-schema';

    /**
     * @var array<string>
     */
    protected $aliases = [
        'filament:header-schema',
    ];

    /**
     * @var class-string
     */
    protected string $resourceFqn;

    /**
     * @var class-string
     */
    protected string $fqn;

    protected string $path;

    /**
     * @return array<InputArgument>
     */
    protected function getArguments(): array
    {
        return [
            new InputArgument(
                name: 'resource',
                mode: InputArgument::OPTIONAL,
                description: 'The resource to create the header schema for',
            ),
        ];
    }

    /**
     * @return array<InputOption>
     */
    protected function getOptions(): array
    {
        return [
            new InputOption(
                name: 'cluster',
                shortcut: 'C',
                mode: InputOption::VALUE_OPTIONAL,
                description: 'The cluster that the resource belongs to',
            ),
            new InputOption(
                name: 'panel',
                mode: InputOption::VALUE_REQUIRED,
                description: 'The panel to create the header schema in',
            ),
            new InputOption(
                name: 'resource-namespace',
                mode: InputOption::VALUE_OPTIONAL,
                description: 'The namespace of the resource class, such as ['.app()->getNamespace().'Filament\\Resources]',
            ),
            new InputOption(
                name: 'page',
                mode: InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
                description: 'The page route keys to apply the trait to, skipping the prompt',
            ),
            new InputOption(
                name: 'no-pages',
                mode: InputOption::VALUE_NONE,
                description: 'Only generate the schema class, leaving the pages untouched',
            ),
            new InputOption(
                name: 'force',
                shortcut: 'F',
                mode: InputOption::VALUE_NONE,
                description: 'Overwrite the contents of the files if they already exist',
            ),
        ];
    }

    public function handle(): int
    {
        try {
            $this->configurePanel(question: 'Which panel would you like to create this header schema in?');
            $this->configureResource();
            $this->configureLocation();

            $this->createHeaderSchema();
            $this->applyTraitToPages();
        } catch (FailureCommandOutput) {
            return static::FAILURE;
        }

        $this->components->info("Header schema [{$this->fqn}] created successfully.");

        return static::SUCCESS;
    }

    protected function configureResource(): void
    {
        $this->configureClusterFqn(
            initialQuestion: 'Is the resource in a cluster?',
            question: 'Which cluster is the resource in?',
        );

        if (filled($this->clusterFqn)) {
            $this->configureClusterResourcesLocation();
        } else {
            $this->configureResourcesLocation(question: 'Which namespace is the resource in?');
        }

        $this->resourceFqn = $this->askForResource(
            question: 'Which resource would you like to create this header schema for?',
            initialResource: $this->argument('resource'),
        );
    }

    protected function configureLocation(): void
    {
        $modelBasename = class_basename($this->resourceFqn::getModel());

        $namespace = (string) str($this->resourceFqn)->beforeLast('\\');

        $this->fqn = "{$namespace}\\Schemas\\{$modelBasename}Header";

        $this->path = (string) str((new ReflectionClass($this->resourceFqn))->getFileName())
            ->beforeLast(DIRECTORY_SEPARATOR)
            ->append(DIRECTORY_SEPARATOR, 'Schemas', DIRECTORY_SEPARATOR, "{$modelBasename}Header.php");
    }

    protected function createHeaderSchema(): void
    {
        if (! $this->option('force') && $this->checkForCollision($this->path)) {
            throw new FailureCommandOutput;
        }

        $this->writeFile($this->path, app(HeaderSchemaClassGenerator::class, [
            'fqn' => $this->fqn,
            'recordTitleAttribute' => $this->resourceFqn::getRecordTitleAttribute(),
        ]));
    }

    /**
     * The trait resolves the generated class by convention, so a page only has
     * to apply the trait to pick it up.
     */
    protected function applyTraitToPages(): void
    {
        if ($this->option('no-pages')) {
            $this->components->info('Apply the ['.class_basename(HasHeaderSchema::class).'] trait to a page to use the header schema.');

            return;
        }

        $pages = $this->getResourcePages();

        if (! $pages) {
            $this->components->warn("No pages were found on [{$this->resourceFqn}]. Apply the [".class_basename(HasHeaderSchema::class).'] trait yourself to use the header schema.');

            return;
        }

        $selected = $this->option('page') ?: multiselect(
            label: 'Which pages should use the header schema?',
            options: array_map(
                fn (string $pageFqn): string => class_basename($pageFqn),
                $pages,
            ),
            default: array_keys(array_filter(
                $pages,
                fn (string $routeKey): bool => in_array($routeKey, ['index', 'view', 'edit'], strict: true),
                ARRAY_FILTER_USE_KEY,
            )),
            hint: 'Use the space bar to select pages. Pages you skip keep their native heading.',
        );

        foreach ($selected as $routeKey) {
            if (! array_key_exists($routeKey, $pages)) {
                $this->components->warn("Page [{$routeKey}] is not registered on [{$this->resourceFqn}], skipping.");

                continue;
            }

            $this->applyTraitToPage($pages[$routeKey]);
        }
    }

    /**
     * @param  class-string  $pageFqn
     */
    protected function applyTraitToPage(string $pageFqn): void
    {
        $basename = class_basename($pageFqn);

        $path = (new ReflectionClass($pageFqn))->getFileName();

        if ($path === false || ! $this->fileExists($path)) {
            $this->components->warn("Could not locate the file for [{$basename}], skipping.");

            return;
        }

        if (in_array(HasHeaderSchema::class, class_uses_recursive($pageFqn), strict: true)) {
            $this->components->info("[{$basename}] already uses the trait.");

            return;
        }

        if (! $this->applyTraitToClassFile($path, HasHeaderSchema::class)) {
            $this->components->info("[{$basename}] already uses the trait.");

            return;
        }

        $this->components->info("Applied the trait to [{$basename}].");
    }

    /**
     * @return array<string, class-string> Page classes, keyed by route key.
     */
    protected function getResourcePages(): array
    {
        $pages = [];

        foreach ($this->resourceFqn::getPages() as $routeKey => $registration) {
            if (! $registration instanceof PageRegistration) {
                continue;
            }

            $pageFqn = $registration->getPage();

            // Pages that live in the vendor directory are not ours to edit.
            $path = (new ReflectionClass($pageFqn))->getFileName();

            if ($path === false || str($path)->startsWith(base_path('vendor'))) {
                continue;
            }

            $pages[$routeKey] = $pageFqn;
        }

        return $pages;
    }
}
