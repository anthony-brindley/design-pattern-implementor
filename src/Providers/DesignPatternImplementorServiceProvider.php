<?php

namespace Anthonybrindley\DesignPatternImplementor\Providers;

use Anthonybrindley\DesignPatternImplementor\Commands\ImplementStrategyPatternCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class DesignPatternImplementorServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package) : void
    {
        dd('gerererer');
        $package
            ->name('design-pattern-implementor')
            ->hasConfigFile()
            ->hasCommands([
                ImplementStrategyPatternCommand::class
            ]);
    }
}
