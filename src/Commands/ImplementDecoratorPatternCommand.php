<?php

namespace AnthonyBrindley\DesignPatternImplementor\Commands;

use AnthonyBrindley\DesignPatternImplementor\Commands\BaseImplementorCommand;
use Illuminate\Support\Str;
use InvalidArgumentException;

class ImplementDecoratorPatternCommand extends BaseImplementorCommand
{
    protected $signature = 'implement-pattern:decorator';
    protected $description = 'Generate a decorator pattern implementation';

    public function handle(): void
    {
        // add to existing domain?
        $decoratorFolderName = config('design-pattern-implementor.decorators_folder_name', 'Decorators');
        $this->setSetting('decoratorFolderName', $decoratorFolderName);

        if($this->confirmAddToExistingDomain())
        {

            $this->attempt(
                action: fn() => $this->validateDomainAttempt(),
                maxAttempts: 3,
                failureMessage: "That domain doesn't exist. Please review and try again.",
                onSuccess: function ($data) {
                    $this->processValidDomain($data);
    
                    // Example interaction with the valid input
                    info("Using verified domain: {$data['domain']}");
                }
            );

            $decoratorFolderName = $this->getSetting('decoratorFolderName');
            $targetNamespace = $this->getSetting('targetFolderNamespace').'\\'.$decoratorFolderName;
            $targetDirectory = $this->getDirectoryPath($targetNamespace);

            $this->ensureDirectoryExists($targetDirectory);

            $this->setSetting('targetFolderPath', $targetDirectory);
            $this->setSetting('targetFolderNamespace', $targetNamespace);
            
        } else {
            // get new domain details
            $domain = $this->promptNewDomain();
            $domainPath = $this->getDirectoryPath($domain);

            if($this->checkDirectoryExists($domainPath))
            {
                dd('stop - directory exists', $domainPath);
            }

            //inside a new folder of the domain?
            if($this->confirmWithUser(
                label: "Place new decorator inside a subdirectory in this folder?",
                default: false,
                hint: "If you select 'yes', you'll be prompted to name the folder", 
            ))
            {
                $this->handleTargetFolderDetails();
            } else {
                $this->setSetting('targetFolderPath', $domainPath);
                $this->setSetting('targetFolderNamespace', $domain);
            }

            
        }

        $this->handleTargetInterfaceDetails();

        $this->handleIncompatibleClassDetails();

        $this->generateDecorator();
    }

    protected function confirmAddToExistingDomain()
    {
        return $this->confirmWithUser(
            label: "Add new decorator to an existing domain?",
            default: false,
            hint: "If yes, you'll be asked to provide the domain location"
        );
    }

    private function validateDomainAttempt(): array|false
    {
        $domain = $this->promptExistingDomain();
        $domainPath = $this->getDirectoryPath($domain);

        return $this->checkDirectoryExists($domainPath) ? compact('domain', 'domainPath') : false;
    }

    private function processValidDomain(array $data): void
    {
        info("Domain verified. Moving on...");

        $domain = str_ends_with($data['domain'], '\\') ? rtrim($data['domain'], '\\') : $data['domain'];
        $this->setSetting('targetFolderNamespace', $domain);
        $this->setSetting('targetFolderPath', $data['domainPath']);
    }


    protected function promptExistingDomain()
    {
        $decoratorFolderName = $this->getSetting('decoratorFolderName');

        return $this->promptFor(
            label: "Please provide the namespace to the target domain (decorators will be created inside a $decoratorFolderName folder)",
            placeholder: "App\Domains\SomeContext",
            required: true,
        );   
    }

    protected function promptNewDomain(): string
    {
        $decoratorFolderName = $this->getSetting('decoratorFolderName');
        $baseNamespace = $this->getSetting('baseNamespace');
        $default = $baseNamespace.'\\'.$decoratorFolderName;

        return $this->promptFor(
            label: "Please provide the namespace to the new domain  - leave blank to use default",
            placeholder: "App\Domains\SomeDomain",
            required: false,
            default: $default
        );  
    }

    protected function promptDecoratorFolderName(): string
    {
        $decoratorFolderName = $this->getSetting('decoratorFolderName');

        return $this->promptFor(
            label: "What folder should the new class be created in?",
            default: $decoratorFolderName,
            required: true,
            hint: "If folder doesn't exist it will be created. If it exists already, the new class will be created inside it."
        );
    }

    protected function handleTargetInterfaceDetails()
    {
        $this->attempt(
            action: fn() => $this->validateTargetInterfaceAttempt(),
            maxAttempts: 3,
            failureMessage: "That interface doesn't exist. Please review and try again.",
            onSuccess: function ($data) {
                
                info("Using verified interface: {$data['domain']}");
            }
        );
    }

    private function validateTargetInterfaceAttempt(): array|false
    {
        $targetInterface = $this->promptTargetInterface();

        if(!interface_exists($targetInterface)) return false;

        // get methods
        $this->loadInterface($targetInterface);
        $methods = $this->generateMethods();

        $this->addReplacement('methods', $methods);
        $this->addImport('t', $targetInterface);
        $this->addReplacement('TargetInterface', class_basename($targetInterface));

        return [
            'domain' => $targetInterface,
            'domainPath' => $this->getDirectoryPath($targetInterface)
        ];
    }

    protected function promptTargetInterface()
    {
        return $this->promptFor(
            label: "Please provide the namespace to (and the name of) the interface class that you are decorating to",
            placeholder: "App\Domains\SomeContext\Contracts\SomeInterface",
            required: true,
        );
    }

    protected function validateIncompatibleClassAttempt(): array|false
    {
        $incompatibleClass = $this->promptIncompatibleClass();

        if(!class_exists($incompatibleClass)) return false;

        $name = class_basename($incompatibleClass);
        $this->addReplacement('IncompatibleClass', $name);
        $this->addImport('incompatible', $incompatibleClass);

        return [
            'domain' => $incompatibleClass,
            'domainPath' => $this->getDirectoryPath($incompatibleClass)
        ];
    }

    protected function handleTargetFolderDetails()
    {
        $confirmedName = $this->promptDecoratorFolderName();
        $targetFolderPath = $this->getSetting('baseDirectory').'/'.$confirmedName;
        $targetFolderNamespace = $this->getSetting('baseNamespace').'\\'.$confirmedName;
        $this->ensureDirectoryExists($targetFolderPath);

        $this->setSetting('targetFolderPath', $targetFolderPath);
        $this->setSetting('targetFolderNamespace', $targetFolderNamespace);
    }

    protected function handleIncompatibleClassDetails()
    {
        $this->attempt(
            action: fn() => $this->validateIncompatibleClassAttempt(),
            maxAttempts: 3,
            failureMessage: "That class doesn't exist. Please review and try again.",
            onSuccess: function ($data) {                
                info("Class verified: {$data['domain']}. Moving on...");
            }
        );
    }

    protected function promptIncompatibleClass(): string
    {
        return $this->promptFor(
            label: "Please provide the namespace to (and the name of) the class that you are decorating",
            placeholder: "App\Domains\SomeContext\SomeClass",
            required: true,
        );
    }

    protected function generateDecorator()
    {
        info('Generating the decorator class...');

        $targetInterfaceName = $this->replacements['TargetInterface'];

        $suggestedName = $targetInterfaceName.'Decorator';
        $confirmedName = $this->promptForDecoratorClassName($suggestedName);

        $this->fileForGeneration = 'decorator/decorator';

        $targetNamespace = $this->getSetting('targetFolderNamespace');
        $targetDirectory = $this->getSetting('targetFolderPath');


        // Add replacements for the manager class
        $this->addReplacement('DummyNamespace', $this->getSetting('targetFolderNamespace'));
        $this->addReplacement('DummyClass', "{$confirmedName}");

        $this->createClassFile(
            $confirmedName,
            $targetNamespace,
            $targetDirectory,
            $this->getStub()
        );

    }

    protected function promptForDecoratorClassName(string $suggestedName)
    {
        return $this->promptForClassName(
            label: "What should your decorator class be called?",
            default: $suggestedName,
        );
    }
}


