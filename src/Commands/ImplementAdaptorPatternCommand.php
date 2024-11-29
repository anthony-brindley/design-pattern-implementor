<?php

namespace AnthonyBrindley\DesignPatternImplementor\Commands;

use AnthonyBrindley\DesignPatternImplementor\Commands\BaseImplementorCommand;
use Illuminate\Support\Str;
use InvalidArgumentException;

use function Laravel\Prompts\{text, info, spin};

class ImplementAdaptorPatternCommand extends BaseImplementorCommand
{

    protected $signature = 'implement-pattern:adaptor';
    protected $description = 'Generate a adaptor pattern implementation';

    public function handle(): void
    {
        $adaptorFolderName = config('design-pattern-implementor.adaptors_folder_name', 'Adaptors');

        $this->setSetting('adaptorFolderName', $adaptorFolderName);

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
                label: "Place new adaptor inside a subdirectory in this folder?",
                default: false,
                hint: "If you select 'yes', you'll be prompted to name the folder", 
            ))
            {
                $folderName = $this->promptAdaptorFolderName();
                $domain = $domain.'\\'.$folderName;
                $domainPath = $this->getDirectoryPath($domain);
                
                $this->ensureDirectoryExists($domainPath);
            }

            $this->setSetting('targetFolderPath', $domainPath);
            $this->setSetting('targetFolderNamespace', $domain);
        }

        $this->handleTargetInterfaceDetails();

        $this->handleIncompatibleClassDetails();

        $this->generateAdaptor();
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
        $this->updateSetting('baseNamespace', $data['domain']);
        $this->setSetting('baseDirectory', $data['domainPath']);
        $this->handleTargetFolderDetails();
    }

    

    protected function confirmAddToExistingDomain()
    {
        return $this->confirmWithUser(
            label: "Add new adaptor to an existing domain?",
            default: false,
            hint: "If yes, you'll be asked to provide the domain location"
        );
    }

    protected function promptExistingDomain()
    {
        $adaptorFolderName = $this->getSetting('adaptorFolderName');

        return $this->promptFor(
            label: "Please provide the namespace to the target domain (adaptors will be created inside a $adaptorFolderName folder)",
            placeholder: "App\\Domains\\SomeContext",
            required: true,
        );   
    }

    protected function promptNewDomain(): string
    {
        $adaptorFolderName = $this->getSetting('adaptorFolderName');
        $baseNamespace = $this->getSetting('baseNamespace');
        $default = $baseNamespace.'\\'.$adaptorFolderName;

        return $this->promptFor(
            label: "Please provide the namespace to the new domain  - leave blank to use default",
            placeholder: "App\\Domains\\SomeDomain",
            required: false,
            default: $default
        );  
    }

    protected function handleTargetFolderDetails()
    {
        $confirmedName = $this->promptAdaptorFolderName();
        $targetFolderPath = $this->getSetting('baseDirectory').'/'.$confirmedName;
        $targetFolderNamespace = $this->getSetting('baseNamespace').'\\'.$confirmedName;
        $this->ensureDirectoryExists($targetFolderPath);

        $this->setSetting('targetFolderPath', $targetFolderPath);
        $this->setSetting('targetFolderNamespace', $targetFolderNamespace);
    }

    protected function promptAdaptorFolderName(): string
    {
        $adaptorFolderName = $this->getSetting('adaptorFolderName');

        return $this->promptFor(
            label: "What folder should the new class be created in?",
            default: $adaptorFolderName,
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
                $this->processValidDomain($data);

                // Example interaction with the valid input
                info("Using verified interface: {$data['interface']}");
            }
        );
        // This value must have a namespace with single backslashes in it
        

        
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
            'interface' => $targetInterface,
            'interfacePath' => $this->getDirectoryPath($targetInterface)
        ];
    }

    protected function promptTargetInterface()
    {
        return $this->promptFor(
            label: "Please provide the namespace to (and the name of) the interface class that you are adapting to",
            placeholder: "App\Domains\SomeContext\Contracts\SomeInterface",
            required: true,
        );
    }

    protected function qualifyTarget(string $route): bool
    {
        return class_exists($route) || interface_exists($route);
    }

    protected function handleIncompatibleClassDetails()
    {
        $c = 3;

        $attemptsMessage = 'attempts';
        do {
            $class = $this->promptIncompatibleClass();

            if(!class_exists($class))
            {
                $c--;
                if($c === 0)
                {
                    break;     
                } else {
                    if($c === 1) $attemptsMessage = Str::singular($attemptsMessage);
                }
                
                info("That class doesn't exist. Please review and try again. ($c $attemptsMessage remaining)");                
            } else {
                info("Class verified. Moving on...");
                break;
            }
            
        } while($c > 0);
        
        if($c === 0)
        {
            throw new InvalidArgumentException("Unable to validate the class references provided. Exiting.");
        } else {
            $name = class_basename($class);
            $this->addReplacement('IncompatibleClass', $name);
            $this->addImport('incompatible', $class);
        }
    }

    protected function promptIncompatibleClass(): string
    {
        return $this->promptFor(
            label: "Please provide the namespace to (and the name of) the class that you are adapting",
            placeholder: "App\\Domains\\SomeContext\\SomeClass",
            required: true,
        );
    }

    protected function generateAdaptor()
    {
        info('Generating the adaptor class...');

        $incompatibleClassName = $this->replacements['IncompatibleClass'];
        $targetInterfaceName = $this->replacements['TargetInterface'];

        $suggestedName = $incompatibleClassName.$targetInterfaceName.'Adaptor';
        $confirmedName = $this->promptForAdaptorClassName($suggestedName);

        $this->fileForGeneration = 'adaptor/adaptor';

        // Add replacements for the manager class
        $this->addReplacement('DummyNamespace', $this->getSetting('targetFolderNamespace'));
        $this->addReplacement('DummyClass', "{$confirmedName}");

        $this->createClassFile(
            $confirmedName,
            $this->getSetting('targetFolderNamespace'),
            $this->getSetting('targetFolderPath'),
            $this->getStub()
        );

    }

    protected function promptForAdaptorClassName(string $suggestedName)
    {
        return $this->promptForClassName(
            label: "What should your adaptor class be called?",
            default: $suggestedName,
        );
    }
}
