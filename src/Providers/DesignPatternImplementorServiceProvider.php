<?php

namespace AnthonyBrindley\DesignPatternImplementor\Providers;

use AnthonyBrindley\DesignPatternImplementor\Commands\ImplementAdaptorPatternCommand;
use AnthonyBrindley\DesignPatternImplementor\Commands\ImplementDecoratorPatternCommand;
use AnthonyBrindley\DesignPatternImplementor\Commands\ImplementFactoryPatternCommand;
use AnthonyBrindley\DesignPatternImplementor\Commands\ImplementObserverPatternCommand;
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
                ImplementStrategyPatternCommand::class,
                ImplementObserverPatternCommand::class,
                ImplementFactoryPatternCommand::class,
                ImplementAdaptorPatternCommand::class,
                ImplementDecoratorPatternCommand::class
            ]);

    }
}
