<?php

namespace AnthonyBrindley\DesignPatternImplementor\Commands;


class ImplementFacadePatternCommand extends BaseImplementor
{
    protected $signature = 'implement-pattern:facade';

    protected static string $patternName = 'facade';

    public function handle(): void
    {
        $this->handleDomainSpecifics();

        $this->handleContextSpecifics();

        $this->handleFacadeSpecifics();

        $this->generateFacadeClass();
    }

    protected function handleFacadeSpecifics()
    {
        if($this->confirmWithUser(label: $this->trans('confirm.target-interface')))
        {
            $this->handleTargetInterfaceDetails();
        }

        $this->validateProposedFacadeClass();
    }
    
    protected function validateProposedFacadeClass()
    {
        $proposedFacadeClass = $this->promptFacadeClass();

        
        $proposedFacadeClassPath = $this->getSetting('basePath').'/'.$proposedFacadeClass.'.php';


        if($this->checkFileExists($proposedFacadeClassPath))
        {
            throw new \Exception(__('design-pattern-implementor::common.error.file-exists', ['fileName' => $proposedFacadeClass.'.php']));
        }

        $this->setSetting('facadeClassName', $proposedFacadeClass);
        $this->setSetting('facadeClassNamespace', $this->getSetting('basePath'));
    }

    protected function promptFacadeClass()
    {
        $default = '';

        if($this->isSet('targetInterfaceNamespace'))
        {
            $interfaceName = class_basename($this->getSetting('targetInterfaceNamespace'));
            $default = $interfaceName.'Facade';
        }

        return $this->promptForClassName(
            label: $this->trans('prompt.facade-class'),
            required: true,
            default: $default
        );
    }

    protected function generateFacadeClass()
    {
        $this->fileForGeneration = 'facade/facade';
        $className = $this->getSetting('facadeClassName');

        if($this->isSet('targetInterfaceNamespace'))
        {
            $interfaceNamespace = $this->getSetting('targetInterfaceNamespace');
            $this->addScopedDependency($className, $interfaceNamespace, 'TargetInterface');
            $this->addScopedReplacement($className, 'implements', 'implements');

            $this->loadInterface($interfaceNamespace);
            $methods = $this->generateMethods();

            $this->addScopedReplacement($className, 'methods', $methods);
        } else {
            $this->addScopedReplacement($className, 'TargetInterface', '');
            $this->addScopedReplacement($className, 'implements', '');
        }

        $this->createClassFile(
            $className,
            $this->getSetting('baseNamespace'),
            $this->getStub()
        );

        info($this->trans('success'));
    }

    
}
