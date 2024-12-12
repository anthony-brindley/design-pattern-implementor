<?php

namespace AnthonyBrindley\DesignPatternImplementor\Commands;

use AnthonyBrindley\DesignPatternImplementor\Commands\BaseImplementor;

class ImplementDecoratorPatternCommand extends BaseImplementor
{
    protected $signature = 'implement-pattern:decorator';
    protected static string $patternName = 'decorator';

    public function handle(): void
    {
        $this->handleDomainSpecifics();
        
        $decoratorFolderName = config('design-pattern-implementor.decorators_folder_name', 'Adaptors');
        $this->handleContextSpecifics($decoratorFolderName); 
        
        $this->handleTargetInterfaceDetails();

        $this->handleIncompatibleClassDetails();

        $this->generateDecorator();
    }

    protected function generateDecorator()
    {
        info('Generating the decorator class...');

        $incompatibleClassNamespace = $this->getSetting('incompatibleClassNamespace');
        $targetInterfaceNamespace = $this->getSetting('targetInterfaceNamespace');

        $suggestedName = class_basename($targetInterfaceNamespace).'Decorator';
        $confirmedName = $this->promptForDecoratorClassName($suggestedName);

        $this->fileForGeneration = 'decorator/decorator';

        $this->addScopedDependency($confirmedName, $incompatibleClassNamespace, 'IncompatibleClass');
        $this->addScopedDependency($confirmedName, $targetInterfaceNamespace, 'TargetInterface');

        $this->createClassFile(
            $confirmedName,
            $this->getSetting('baseNamespace'),
            $this->getStub()
        );

        info($this->trans('success'));
    }

    protected function promptForDecoratorClassName(string $suggestedName)
    {
        return $this->promptForClassName(
            label: $this->trans("prompt.decorator-class"),
            default: $suggestedName,
        );
    }
}


