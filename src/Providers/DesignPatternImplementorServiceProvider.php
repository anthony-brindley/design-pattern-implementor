<?php

namespace AnthonyBrindley\DesignPatternImplementor\Providers;

use AnthonyBrindley\DesignPatternImplementor\Commands\ImplementStrategyPatternCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class DesignPatternImplementorServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package) : void
    {
        $package
            ->name('design-pattern-implementor')
            ->hasConfigFile()
            ->hasCommands([
                ImplementStrategyPatternCommand::class
            ]);

    }
}
