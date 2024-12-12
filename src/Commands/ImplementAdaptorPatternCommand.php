<?php

namespace AnthonyBrindley\DesignPatternImplementor\Commands;

use AnthonyBrindley\DesignPatternImplementor\Commands\BaseImplementor;

use function Laravel\Prompts\{info};

class ImplementAdaptorPatternCommand extends BaseImplementor
{
    protected $signature = 'implement-pattern:adaptor';

    protected static string $patternName = 'adaptor';

    public function handle(): void
    {
        $this->handleDomainSpecifics();

        $adaptorFolderName = config('design-pattern-implementor.adaptors_folder_name', 'Adaptors');
        $this->handleContextSpecifics($adaptorFolderName);     

        $this->handleTargetInterfaceDetails();

        $this->handleIncompatibleClassDetails();

        $this->generateAdaptor();
    }

    protected function generateAdaptor()
    {
        info('Generating the adaptor class...');

        $incompatibleClassNamespace = $this->getSetting('incompatibleClassNamespace');
        $targetInterfaceNamespace = $this->getSetting('targetInterfaceNamespace');

        $suggestedName = class_basename($incompatibleClassNamespace).class_basename($targetInterfaceNamespace).'Adaptor';
        $confirmedName = $this->promptForAdaptorClassName($suggestedName);

        $this->fileForGeneration = 'adaptor/adaptor';

        $this->addScopedDependency($confirmedName, $incompatibleClassNamespace, 'IncompatibleClass');
        $this->addScopedDependency($confirmedName, $targetInterfaceNamespace, 'TargetInterface');

        $this->addScopedReplacement($confirmedName, 'methods', $this->constructAdaptorClassMethods());

        $this->createClassFile(
            $confirmedName,
            $this->getSetting('baseNamespace'),
            $this->getStub()
        );

        info($this->trans('success'));
    }

    protected function promptForAdaptorClassName(string $suggestedName)
    {
        return $this->promptForClassName(
            label: $this->trans("prompt.adaptor-class"),
            default: $suggestedName,
        );
    }

    protected function constructAdaptorClassMethods()
    {
        $interface = $this->getSetting('targetInterfaceNamespace');

        $this->loadInterface($interface);
        return $this->generateMethods();
    }
}
