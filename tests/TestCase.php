<?php

namespace AnthonyBrindley\DesignPatternImplementor\Tests;

use AnthonyBrindley\DesignPatternImplementor\Providers\DesignPatternImplementorServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    

    protected function getPackageProviders($app)
    {
        return [
            DesignPatternImplementorServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app)
    {
        // perform environment setup
    }
}
