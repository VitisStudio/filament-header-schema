<?php

namespace VitisStudio\FilamentHeaderSchema\Tests;

use Illuminate\Database\Schema\Blueprint;
use Orchestra\Testbench\TestCase as Orchestra;
use VitisStudio\FilamentHeaderSchema\FilamentHeaderSchemaServiceProvider;
use VitisStudio\FilamentHeaderSchema\Tests\Fixtures\AdminPanelProvider;

class TestCase extends Orchestra
{
    /**
     * Filament, Livewire and this package are all auto-discovered. Listing them
     * in `getPackageProviders()` as well registers each one twice, which resets
     * Livewire's data store mid-render and breaks every component test.
     */
    protected $enablesPackageDiscoveries = true;

    protected function setUp(): void
    {
        parent::setUp();

        $this->app['db']->connection()->getSchemaBuilder()->create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('reference');
            $table->string('status')->default('paid');
            $table->string('customer_name')->nullable();
            $table->timestamps();
        });
    }

    /**
     * @return array<int, class-string>
     */
    protected function getPackageProviders($app): array
    {
        // Discovery covers Filament and Livewire. It does not cover the package
        // under test — that is the root package, not an installed one — so it
        // is registered explicitly, along with the test panel.
        return [
            FilamentHeaderSchemaServiceProvider::class,
            AdminPanelProvider::class,
        ];
    }

    public function getEnvironmentSetUp($app): void
    {
        config()->set('app.key', 'base64:'.base64_encode(random_bytes(32)));
        config()->set('database.default', 'testing');
        config()->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);

        $app['view']->addNamespace('filament-header-schema-tests', __DIR__.'/Fixtures/views');
    }
}
