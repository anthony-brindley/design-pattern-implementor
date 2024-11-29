<?php

namespace AnthonyBrindley\DesignPatternImplementor\Commands;

use AnthonyBrindley\DesignPatternImplementor\Commands\BaseImplementorCommand;
use Illuminate\Support\Str;
use InvalidArgumentException;

class ImplementProxyPatternCommand extends BaseImplementorCommand
{
    protected $signature = 'implement-pattern:proxy';
    protected $description = 'Generate a proxy pattern implementation';

    public function handle(): void
    {
        // add to existing domain?
        $proxyFolderName = config('design-pattern-implementor.proxys_folder_name', 'Proxies');
        $this->setSetting('proxyFolderName', $proxyFolderName);

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

            $proxyFolderName = $this->getSetting('proxyFolderName');
            $targetNamespace = $this->getSetting('targetFolderNamespace').'\\'.$proxyFolderName;
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
                label: "Place new proxy inside a subdirectory in this folder?",
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

        $this->handleSubjectClassDetails();

        $this->generateProxy();
    }

    protected function confirmAddToExistingDomain()
    {
        return $this->confirmWithUser(
            label: "Add new proxy to an existing domain?",
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
        $proxyFolderName = $this->getSetting('proxyFolderName');

        return $this->promptFor(
            label: "Please provide the namespace to the target domain (proxys will be created inside a $proxyFolderName folder)",
            placeholder: "App\Domains\SomeContext",
            required: true,
        );   
    }

    protected function promptNewDomain(): string
    {
        $proxyFolderName = $this->getSetting('proxyFolderName');
        $baseNamespace = $this->getSetting('baseNamespace');
        $default = $baseNamespace.'\\'.$proxyFolderName;

        return $this->promptFor(
            label: "Please provide the namespace to the new domain  - leave blank to use default",
            placeholder: "App\Domains\SomeDomain",
            required: false,
            default: $default
        );  
    }

    protected function promptProxyFolderName(): string
    {
        $proxyFolderName = $this->getSetting('proxyFolderName');

        return $this->promptFor(
            label: "What folder should the new class be created in?",
            default: $proxyFolderName,
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
        $this->addReplacement('SubjectInterface', class_basename($targetInterface));

        return [
            'domain' => $targetInterface,
            'domainPath' => $this->getDirectoryPath($targetInterface)
        ];
    }

    protected function promptTargetInterface()
    {
        return $this->promptFor(
            label: "Please provide the namespace to (and the name of) the interface class that your new proxy should adhere to",
            placeholder: "App\Domains\SomeContext\Contracts\SomeInterface",
            required: true,
        );
    }

    protected function validateSubjectClassAttempt(): array|false
    {
        $subjectClass = $this->promptSubjectClass();

        if(!class_exists($subjectClass)) return false;

        $name = class_basename($subjectClass);
        $this->addReplacement('SubjectClass', $name);
        $this->addImport('subject', $subjectClass);

        return [
            'domain' => $subjectClass,
            'domainPath' => $this->getDirectoryPath($subjectClass)
        ];
    }

    protected function handleTargetFolderDetails()
    {
        $confirmedName = $this->promptProxyFolderName();
        $targetFolderPath = $this->getSetting('baseDirectory').'/'.$confirmedName;
        $targetFolderNamespace = $this->getSetting('baseNamespace').'\\'.$confirmedName;
        $this->ensureDirectoryExists($targetFolderPath);

        $this->setSetting('targetFolderPath', $targetFolderPath);
        $this->setSetting('targetFolderNamespace', $targetFolderNamespace);
    }

    protected function handleSubjectClassDetails()
    {
        $this->attempt(
            action: fn() => $this->validateSubjectClassAttempt(),
            maxAttempts: 3,
            failureMessage: "That class doesn't exist. Please review and try again.",
            onSuccess: function ($data) {                
                info("Class verified: {$data['domain']}. Moving on...");
            }
        );
    }

    protected function promptSubjectClass(): string
    {
        return $this->promptFor(
            label: "Please provide the namespace to (and the name of) the subject class",
            placeholder: "App\Domains\SomeContext\SomeClass",
            required: true,
        );
    }

    protected function generateProxy()
    {
        info('Generating the proxy class...');

        $targetInterfaceName = $this->replacements['SubjectInterface'];

        $suggestedName = $targetInterfaceName.'Proxy';
        $confirmedName = $this->promptForProxyClassName($suggestedName);

        $this->fileForGeneration = 'proxy/proxy';

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

    protected function promptForProxyClassName(string $suggestedName)
    {
        return $this->promptForClassName(
            label: "What should your proxy class be called?",
            default: $suggestedName,
        );
    }
}


