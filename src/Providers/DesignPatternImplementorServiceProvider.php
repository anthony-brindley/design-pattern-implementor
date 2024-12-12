<?php

namespace AnthonyBrindley\DesignPatternImplementor\Providers;

use AnthonyBrindley\DesignPatternImplementor\Commands\ImplementAdaptorPatternCommand;
use AnthonyBrindley\DesignPatternImplementor\Commands\ImplementDecoratorPatternCommand;
use AnthonyBrindley\DesignPatternImplementor\Commands\ImplementFacadePatternCommand;
use AnthonyBrindley\DesignPatternImplementor\Commands\ImplementFactoryPatternCommand;
use AnthonyBrindley\DesignPatternImplementor\Commands\ImplementObserverPatternCommand;
use AnthonyBrindley\DesignPatternImplementor\Commands\ImplementProxyPatternCommand;
use AnthonyBrindley\DesignPatternImplementor\Commands\ImplementStatePatternCommand;
use AnthonyBrindley\DesignPatternImplementor\Commands\ImplementStrategyPatternCommand;
use AnthonyBrindley\DesignPatternImplementor\Commands\ImplementVisitorPatternCommand;
use AnthonyBrindley\DesignPatternImplementor\Commands\PromptsExplorerCommand;
use AnthonyBrindley\DesignPatternImplementor\Services\MethodGenerator\ClassGeneratorService;
use AnthonyBrindley\DesignPatternImplementor\Services\MethodGenerator\MethodGeneratorService;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class DesignPatternImplementorServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package) : void
    {
        $package
            ->name('design-pattern-implementor')
            ->hasConfigFile()
            ->hasTranslations()
            
            ->hasCommands(self::$commandsToRegister);

    }

    protected static array $commandsToRegister = [
        ImplementStrategyPatternCommand::class,
        ImplementObserverPatternCommand::class,
        ImplementFactoryPatternCommand::class,
        ImplementAdaptorPatternCommand::class,
        ImplementDecoratorPatternCommand::class,
        ImplementProxyPatternCommand::class,
        ImplementStatePatternCommand::class,
        ImplementFacadePatternCommand::class,
        ImplementVisitorPatternCommand::class,
    ];

    public static function getCommandNames(): array
    {
        $names = [];
        foreach(self::$commandsToRegister as $command)
        {
            $names[] = (app($command))->getName();
        }

        return $names;
    }

    public function packageRegistered()
    {
        $this->app->singleton(MethodGeneratorService::class);
    }
}
