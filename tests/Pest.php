<?php

use AnthonyBrindley\DesignPatternImplementor\Tests\TestCase;
use AnthonyBrindley\DesignPatternImplementor\Traits\HandlesFileCreation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Pest\Expectation;
use Pest\Mixins\Expectation as MixinsExpectation;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "pest()" function to bind a different classes or traits.
|
*/
uses(RefreshDatabase::class);


pest()->extend(TestCase::class)->in('Feature');
pest()->extend(TestCase::class)->in('Unit');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
|
| When you're writing tests, you often need to check that values meet certain conditions. The
| "expect()" function gives you access to a set of "expectations" methods that you can use
| to assert different things. Of course, you may extend the Expectation API at any time.
|
*/

expect()->extend('toBeOne', function () {
    return $this->toBe(1);
});

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| While Pest is very powerful out-of-the-box, you may have some testing code specific to your
| project that you don't want to repeat in every file. Here you can also expose helpers as
| global functions to help you to reduce the number of lines of code in your test files.
|
*/


function useTraitMethod(string $methodName, $params = null)
{
    $class = (new class(){
        use HandlesFileCreation;


    });

    if(method_exists($class, $methodName))
    {
        return $class->$methodName($params);
    }
}

function getNamespace(?string $appendMe = null)
{
    $base = config('design-pattern-implementor.default_namespace');

    if(!is_null($appendMe))
    {
        $base = $base.'\\'.$appendMe;
    }

    return $base;
}

function getDirectoryPath(string $namespace)
{
    return useTraitMethod('getDirectoryPath', $namespace);
}

function checkFileExists(string $path): bool
{
    return useTraitMethod('checkFileExists', $path);
}

function expectFileToExist(string $namespace, string $filename): Expectation|MixinsExpectation
{
    return expect(checkFileExists(getDirectoryPath($namespace.'\\'.$filename)))->toBeTrue();
}