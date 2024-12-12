<?php

namespace AnthonyBrindley\DesignPatternImplementor\Tests\Unit;

use AnthonyBrindley\DesignPatternImplementor\Providers\DesignPatternImplementorServiceProvider;
use Illuminate\Support\Facades\Artisan;

test('the package commands are registered with the application', function(){
    $commandNames = DesignPatternImplementorServiceProvider::getCommandNames();
    $registeredArtisanCommands = Artisan::all();

    foreach($commandNames as $name)
    {
        $this->assertArrayHasKey($name, $registeredArtisanCommands, "The command {$name} is not registered.");
    }

});