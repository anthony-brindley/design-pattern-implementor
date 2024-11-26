<?php

namespace AnthonyBrindley\DesignPatternImplementor\Commands;

use AnthonyBrindley\DesignPatternImplementor\Traits\FileGenerator;
use Illuminate\Console\GeneratorCommand;
use Illuminate\Support\Str;

use function Laravel\Prompts\{text, info, spin};

class ImplementObserverPatternCommand extends GeneratorCommand
{
    use FileGenerator;

    protected $signature = 'implement-pattern:observer';
    protected $description = 'Generate a observer pattern implementation';

    protected string $fileForGeneration = '';

    public function handle(): void
    {
        $context = $this->promptContext();
        $baseNamespace = config('design-pattern-implementor.default_namespace') . '\\' . $context;
        $baseDirectory = $this->getDirectoryPath($baseNamespace);

        $interfacesFolderName = config('design-pattern-implementor.interface_folder_name', 'Contracts');
        $interfacesDirectory = "$baseDirectory/$interfacesFolderName";
        $interfacesNamespace = "$baseNamespace\\$interfacesFolderName";

        $observableInterface = "Is{$context}Observable";
        $observerInterface = "Is{$context}Observer";

        $this->createPatternDirectories($baseDirectory, $interfacesDirectory);

        $replacements = [
            'ObserverInterface' => $observerInterface,
            'ObservableInterface' => $observableInterface
        ];
        $imports = [
            'ObserverInterface' => $interfacesNamespace."\\{$observerInterface}",
            'ObservableInterface' => $interfacesNamespace."\\{$observableInterface}"
        ];

        $this->generateInterfaces($context, $interfacesNamespace, $interfacesDirectory, $replacements, $imports);
        
        $this->generateObservers($context, $baseNamespace,  $replacements, $imports);

        $this->generateObservable($context, $baseNamespace, $baseDirectory, $replacements, $imports);
    }

    protected function getStub(): string
    {
        if (!empty($this->fileForGeneration)) {
            return $this->getStubsFolderPath() . '/' . $this->fileForGeneration . ".php.stub";
        }

        throw new \RuntimeException('No file specified for generation. Set $fileForGeneration in the command.');
    }

    protected function promptContext(): string
    {
        return $this->formatClassName(
            text(
                label: 'What is the functionality context for this implementation?',
                placeholder: 'e.g WeatherStation',
                required: true,
                hint: 'This will be used as a containing folder name'
            )
        );
    }
    protected function createPatternDirectories(string $baseDirectory, string $interfacesDirectory): void
    {
        info('Creating directories for the observer pattern...');

        $this->ensureDirectoryExists($baseDirectory);
        $this->ensureDirectoryExists($interfacesDirectory);
        $this->ensureDirectoryExists("$baseDirectory/Observers");
    }

    protected function generateInterfaces(string $context, string $namespace, string $directory, array $replacements = [], array $imports = []): void
    {
        info('Generating interface files...');

        $interfaces = [
            "Is{$context}Observable" => 'is_observable',
            "Is{$context}Observer" => 'is_observer',
        ];

        foreach ($interfaces as $className => $stub) {
            
            

            $this->fileForGeneration = "observer/$stub";

            foreach($replacements as $k => $v)
            {
                if($k !== $className)
                {
                    $this->addReplacement($k, $v);
                }
            }

            foreach($imports as $k => $v)
            {
                if($k !== $className)
                {
                    $this->addImport($k, $v);    
                }
            }

            $this->createClassFile(
                $className,
                $namespace,
                $directory,
                $this->getStub()
            );
            
        }
    }

    protected function generateObservers(string $context, string $namespace, array $replacements = [], array $imports = []): void
    {
        info('Generating observer classes...');
        $observers = $this->askObservers();

        $observersDirectory = $this->getDirectoryPath("$namespace/Observers"); // Normalize path for directory creation

        foreach ($observers as $observer) {
            $this->fileForGeneration = 'observer/observer';

            // Normalize namespace for imports
           // $formattedInterfaceNamespace = str_replace('/', '\\', $interfacesNamespace);

            // Add replacements for the strategy class
            $this->addReplacement('DummyNamespace', str_replace('/', '\\', "$namespace/Observers"));
            $this->addReplacement('DummyClass', $this->formatClassName($observer));

            foreach($replacements as $k => $v)
            {
                $this->addReplacement($k, $v);
            }

            foreach($imports as $k => $v)
            {
                $this->addImport($k, $v);    
            }

            $this->createClassFile(
                $this->formatClassName($observer),
                str_replace('/', '\\', "$namespace\\Observers"), // Normalize namespace for file generation
                $observersDirectory,
                $this->getStub()
            );
        }
    }

    protected function askObservers(): array
    {
        $observers = [];
        
        while($observer = $this->formatClassName(text(
            label: 'Enter an observer name (or leave blank to stop):',
            placeholder: 'e.g MobileFeed',
            required: false
        )))
        {
            if(!in_array($observer, $observers)) $observers[] = $observer;
            else {
                info('Already a observer with this name due for creation, please try again...');
            }
        };

        return $observers;
    }

    protected function generateObservable(string $context, string $namespace, string $baseDirectory, array $replacements = [], array $imports = []): void
    {
        info('Generating the observable class...');

        $this->fileForGeneration = 'observer/observable';

        // Normalize namespace for imports
       // $formattedInterfaceNamespace = str_replace('/', '\\', $interfacesNamespace);

        // Add replacements for the manager class
        $this->addReplacement('DummyNamespace', $namespace);
        $this->addReplacement('DummyClass', "{$context}Observable");
        //$this->addReplacement('DummyInterface', $managerInterface);

        foreach($replacements as $k => $v)
        {
            $this->addReplacement($k, $v);
        }

        foreach($imports as $k => $v)
        {
            $this->addImport($k, $v);    
        }

        // Add the manager interface import

        $this->createClassFile(
            "{$context}Observable",
            $namespace,
            $baseDirectory,
            $this->getStub()
        );
    }
}
