<?php

namespace VitisStudio\FilamentHeaderSchema\Commands\FileGenerators;

use Filament\Schemas\Schema;
use Filament\Support\Commands\FileGenerators\ClassGenerator;
use Nette\PhpGenerator\ClassType;
use Nette\PhpGenerator\Method;
use VitisStudio\FilamentHeaderSchema\Components\HeaderSection;
use VitisStudio\FilamentHeaderSchema\Components\Heading;

/**
 * Mirrors Filament's own ResourceFormSchemaClassGenerator, so a generated
 * header schema sits next to the resource's form and infolist and reads the
 * same way.
 */
class HeaderSchemaClassGenerator extends ClassGenerator
{
    final public function __construct(
        protected string $fqn,
        protected ?string $recordTitleAttribute,
    ) {}

    public function getNamespace(): string
    {
        return $this->extractNamespace($this->getFqn());
    }

    /**
     * @return array<string>
     */
    public function getImports(): array
    {
        return [
            Schema::class,
            HeaderSection::class,
            Heading::class,
        ];
    }

    public function getBasename(): string
    {
        return class_basename($this->getFqn());
    }

    protected function addMethodsToClass(ClassType $class): void
    {
        $this->addConfigureMethodToClass($class);
    }

    protected function addConfigureMethodToClass(ClassType $class): void
    {
        $headerSection = $this->simplifyFqn(HeaderSection::class);
        $heading = $this->simplifyFqn(Heading::class);

        // The resource's record title attribute is the closest thing to a known
        // heading. Falling back to the key keeps the generated class runnable —
        // an `Entry` needs a name — while reading obviously like a placeholder.
        $headingAttribute = $this->getRecordTitleAttribute() ?? 'id';

        // One header schema serves every page of the resource, and a list page
        // has no record to read the attribute from. Defaulting to the page's own
        // heading means opting a page in never leaves it with no title at all.
        $method = $class->addMethod('configure')
            ->setPublic()
            ->setStatic()
            ->setReturnType(Schema::class)
            ->setBody(<<<PHP
                return \$schema
                    ->components([
                        {$headerSection}::make([
                            {$heading}::make('{$headingAttribute}')
                                ->default(fn (\$livewire) => \$livewire->getHeading()),
                            //
                        ]),
                    ]);
                PHP);
        $method->addParameter('schema')
            ->setType(Schema::class);

        $this->configureConfigureMethod($method);
    }

    protected function configureConfigureMethod(Method $method): void {}

    public function getFqn(): string
    {
        return $this->fqn;
    }

    public function getRecordTitleAttribute(): ?string
    {
        return $this->recordTitleAttribute;
    }
}
