<?php

namespace AnthonyBrindley\DesignPatternImplementor\Commands;

use AnthonyBrindley\DesignPatternImplementor\Commands\BaseImplementor;

class ImplementProxyPatternCommand extends BaseImplementor
{
    protected $signature = 'implement-pattern:proxy';
    protected static string $patternName = 'proxy';

    public function handle(): void
    {
        $this->handleDomainSpecifics();

        $proxyFolderName = config('design-pattern-implementor.proxys_folder_name', 'Proxies');
        $this->handleContextSpecifics($proxyFolderName);
        
        $this->handleTargetInterfaceDetails();

        $this->handleSubjectClassDetails();

        $this->generateProxy();
    }  

    protected function handleSubjectClassDetails()
    {
        $this->attempt(
            action: fn() => $this->validateSubjectClassAttempt(),
            maxAttempts: 3,
            failureMessage: $this->trans('error.subject-class-missing'),
            onSuccess: function ($subjectClass) {      
                
                $this->setSetting('subjectClassNamespace', $subjectClass);
                info($this->trans('success.subject-class-verified', ['className' => class_basename($subjectClass)]));
            }
        );
    }

    protected function validateSubjectClassAttempt(): string|false
    {
        $subjectClass = $this->promptSubjectClass();

        if(!class_exists($subjectClass)) return false;

        return $subjectClass;
    }

    

    protected function promptSubjectClass(): string
    {
        return $this->promptFor(
            label: $this->trans('prompt.subject-class'),
            placeholder: "App\Domains\SomeContext\SomeClass",
            required: true,
        );
    }

    protected function generateProxy()
    {
        info('Generating the proxy class...');

        $targetInterfaceNamespace = $this->getSetting('targetInterfaceNamespace');

        $targetInterfaceName = class_basename($targetInterfaceNamespace);

        

        $suggestedName = $targetInterfaceName.'Proxy';
        $confirmedName = $this->promptForProxyClassName($suggestedName);

        $this->loadInterface($targetInterfaceNamespace);
        $methods = $this->generateMethods();

        $this->addScopedReplacement($confirmedName, 'methods', $methods);

        $this->fileForGeneration = 'proxy/proxy';

        $this->createClassFile(
            $confirmedName,
            $this->getSetting('baseNamespace'),
            $this->getStub()
        );

        info($this->trans('success'));
    }

    protected function promptForProxyClassName(string $suggestedName)
    {
        return $this->promptForClassName(
            label: $this->trans('prompt.proxy-class'),
            default: $suggestedName,
        );
    }
}


