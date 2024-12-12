<?php

namespace AnthonyBrindley\DesignPatternImplementor\Tests\Unit\Behavioural;

test('our standard observer pattern happy path works', function(){

    // Folder Names
    $context = 'WeatherStation';
    $contracts = 'Contracts';
    $observers = 'Observers';

    // File Names
    $observer1 = 'MobileObserver';
    $observer2 = 'WebObserver';
    $observable = 'WeatherStationObservable';
    
    // can these questions be refactored to be updatable on the command class and pull through into here?
    $this->artisan('implement-pattern:observer')
        ->expectsQuestion(__('design-pattern-implementor::common.prompt.add-to-existing-domain'), false)
        ->expectsQuestion(__('design-pattern-implementor::common.prompt.new-domain'), 'App\Domains')
        ->expectsQuestion(__('design-pattern-implementor::common.prompt.context'), $context)
        ->expectsQuestion(__('design-pattern-implementor::common.prompt.interface-folder'), $contracts)
        ->expectsQuestion(__('design-pattern-implementor::observer.prompt.observer-class'), $observer1)
        ->expectsQuestion(__('design-pattern-implementor::observer.prompt.observer-class'), $observer2)
        ->expectsQuestion(__('design-pattern-implementor::observer.prompt.observer-class'), "")
        ->expectsQuestion(__('design-pattern-implementor::observer.prompt.observable-class'), $observable)
        ->assertExitCode(0);

    $observableNamespace = getNamespace($context);
    $contractsNamespace = getNamespace($context.'\\'.$contracts);
    $observersNamespace = getNamespace($context.'\\'.$observers);

    expectFileToExist($observableNamespace, $observable.'.php');
    expectFileToExist($contractsNamespace, 'IsObserver.php');
    expectFileToExist($contractsNamespace, 'IsObservable.php');
    expectFileToExist($observersNamespace, $observer1.'.php');
    expectFileToExist($observersNamespace, $observer2.'.php');
});