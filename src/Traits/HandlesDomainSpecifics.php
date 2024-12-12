<?php

namespace AnthonyBrindley\DesignPatternImplementor\Traits;

use AnthonyBrindley\DesignPatternImplementor\Traits\PromptsUsers;
use Illuminate\Validation\Rule;

trait HandlesDomainSpecifics
{
    use PromptsUsers;

    /**
     * DOMAIN SPECIFICS
     */

     public function handleDomainSpecifics()
     {
        $failureMessage = ($addToExisting = $this->confirmAddToExistingDomain()) 
                                        ? __('design-pattern-implementor::common.error.domain-doesnt-exist') 
                                        : __('design-pattern-implementor::common.error.domain-already-exists');
        
        $this->setSetting('addToExisting', $addToExisting);

        $this->attempt(
             action: function()
                 {
                    return ($this->getSetting('addToExisting')) 
                                        ? $this->validateExistingDomainAttempt() 
                                        : $this->validateNewDomainAttempt();
                 },
             failureMessage: $failureMessage,
             onSuccess: function($data){
                 $this->processValidDomain($data);
             }
         );
     }
 
     protected function confirmAddToExistingDomain()
     {
         return $this->confirmWithUser(
             label: __("design-pattern-implementor::common.prompt.add-to-existing-domain"),
             default: false,
             hint: __("design-pattern-implementor::common.hint.add-to-existing-domain")
         );
     }
 
     protected function promptExistingDomain()
     {
         return $this->promptFor(
             label: __('design-pattern-implementor::common.prompt.target-domain'),
             placeholder: "App\Domains",
             required: true,
         );   
     }
 
     protected function promptNewDomain(): string
     {
         return $this->promptFor(
             label: __('design-pattern-implementor::common.prompt.new-domain'),
             placeholder: "App\Domains",
             required: true,
         );  
     }
 
     private function validateExistingDomainAttempt(): array|false
     {
         $domain = $this->promptExistingDomain();
         $domainPath = $this->getDirectoryPath($domain);
 
         return $this->checkDirectoryExists($domainPath) ? compact('domain', 'domainPath') : false;
     }
 
     private function validateNewDomainAttempt(): array|false
     {
         $domain = $this->promptNewDomain();
         $domainPath = $this->getDirectoryPath($domain);
 
         return $this->folderDoesntAlreadyExist($domainPath) ? compact('domain', 'domainPath') : false; 
     }
 
     public function processValidDomain(array $data): void
     {
         info("Verified. Moving on...");
 
         $domain = str_ends_with($data['domain'], '\\') ? rtrim($data['domain'], '\\') : $data['domain'];
         $this->setSetting('baseNamespace', $domain);
         $this->setSetting('basePath', $data['domainPath']);
     }

    // CONTEXT
    public function handleContextSpecifics(?string $proposedContextName = null)
    {
        if($proposedContextName)
        {
            $baseNamespace = $this->getSetting('baseNamespace');
            $namespaceLastSegment = $this->getNamespaceLastSegment($baseNamespace);

            $proposedFolderNamespace = ($namespaceLastSegment === $proposedContextName) ? $baseNamespace : $baseNamespace.'\\'.$proposedContextName;
            $proposedFolderPath = $this->getDirectoryPath($proposedFolderNamespace);

            if($this->checkWhetherContextFolderAlreadyExists($proposedFolderPath))
            {
                if($this->confirmWithUser(
                    label: __('design-pattern-implementor::common.prompt.confirm-use-existing-folder', ['folder' => $proposedContextName]),
                    default: false,
                ))
                {
                    $this->updateSetting('baseNamespace', $proposedFolderNamespace);
                    $this->updateSetting('basePath', $proposedFolderPath);

                    return;
                }
            }
        }

        $this->attempt(
            action: function()
                {
                    $proposedContextName = $this->promptContextFolderName();
                    $proposedFolderNamespace = $this->getSetting('baseNamespace').'\\'.$proposedContextName;

                    $proposedFolderPath = $this->getDirectoryPath($proposedFolderNamespace);

                    if($this->checkWhetherContextFolderAlreadyExists($proposedFolderPath))
                    {
                        if(!$this->confirmWithUser(
                            label: __('design-pattern-implementor::common.prompt.confirm-use-existing-folder', ['folder' => $proposedContextName]),
                            default: false,
                        ))
                        {
                            return false;
                        }
                    }

                    return $proposedFolderNamespace;
                },
            failureMessage: __('design-pattern-implementor::common.error.domain-missing'),
            onSuccess: function($proposedFolderNamespace){
                    $this->updateSetting('baseNamespace', $proposedFolderNamespace);
                    $proposedFolderPath = $this->getDirectoryPath($proposedFolderNamespace);
                    $this->updateSetting('basePath', $proposedFolderPath);
            }
        );
    }

    protected function getNamespaceLastSegment(string $namespace)
    {
        return collect(explode($namespace, '\\'))->last();
    }

    protected function checkWhetherContextFolderAlreadyExists(string $proposedFolderPath): bool
    {
        return $this->checkDirectoryExists($proposedFolderPath);
    }

    protected function promptContextFolderName()
    {
        $lastSegment = $this->getNamespaceLastSegment($this->getSetting('baseNamespace'));

        return $this->promptFor(
            label: __('design-pattern-implementor::common.prompt.context'),
            placeholder: "App\Domains",
            required: false,
            hint: __('design-pattern-implementor::commont.hint.context'),
            validate: ['context' => Rule::notIn([$lastSegment])]
        );  
    }

    // CONTRACTS FOLDER
     public function handleContractsFolderDetails()
     {
        $confirmedFolderName = $this->promptContractsFolderName();

        $proposedFolderNamespace = $this->getSetting('baseNamespace').'\\'.$confirmedFolderName;
        $proposedFolderPath = $this->getDirectoryPath($proposedFolderNamespace);

        $this->setSetting('interfacesFolderNamespace', $proposedFolderNamespace);
        $this->setSetting('interfacesFolderPath', $proposedFolderPath);
     }

     protected function promptContractsFolderName(): string
     {
        return $this->promptForClassName(
            label: __('design-pattern-implementor::common.prompt.interface-folder'),
            default: $this->getSetting('interfacesFolderName') ?? 'Contracts',
            required: true
        );
     }

    // TARGET INTERFACE
    protected function handleTargetInterfaceDetails()
    {
        $this->attempt(
            action: fn() => $this->validateTargetInterfaceAttempt(),
            maxAttempts: 3,
            failureMessage: $this->trans('error.target-interface-missing'),
            onSuccess: function ($targetInterfaceNamespace) {
                $this->setSetting('targetInterfaceNamespace', $targetInterfaceNamespace);

                info($this->trans('success.target-interface-verified', ['targetInterfaceNamespace' => $targetInterfaceNamespace]));
            }
        );
    }

    private function validateTargetInterfaceAttempt(): string|false
    {
        $targetInterface = $this->promptTargetInterface();

        if(!interface_exists($targetInterface)) return false;

        return $targetInterface;
    }

    protected function promptTargetInterface()
    {
        return $this->promptFor(
            label: $this->trans('prompt.target-interface'),
            placeholder: "App\Domains\SomeContext\Contracts\SomeInterface",
            required: true,
        );
    } 

    // INCOMPATIBLE CLASS
    protected function handleIncompatibleClassDetails()
    {
        $this->attempt(
            action: fn() => $this->validateIncompatibleClassAttempt(),
            failureMessage: $this->trans('error.incompatible-class-missing'),
            onSuccess: function($incompatibleClass) {
                $this->setSetting('incompatibleClassNamespace', $incompatibleClass);

                info($this->trans('success.incompatible-class-verified', ['incompatibleClass' => $incompatibleClass]));
            }
        );
    }

    protected function validateIncompatibleClassAttempt()
    {
        $incompatibleClass = $this->promptIncompatibleClass();

        if(!class_exists($incompatibleClass)) return false;

        return $incompatibleClass;
    }

    protected function promptIncompatibleClass(): string
    {
        return $this->promptFor(
            label: $this->trans('prompt.incompatible-class'),
            placeholder: "App\Domains\SomeContext\SomeClass",
            required: true,
        );
    }
}
