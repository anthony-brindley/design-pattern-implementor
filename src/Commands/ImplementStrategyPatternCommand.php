<?php

namespace AnthonyBrindley\DesignPatternImplementor\Commands;

use AnthonyBrindley\DesignPatternImplementor\Traits\FileGenerator;
use Illuminate\Console\GeneratorCommand;
use Illuminate\Support\Str;

use function Laravel\Prompts\{text, info, spin};

class ImplementStrategyPatternCommand extends GeneratorCommand
{
    use FileGenerator;

    protected $signature = 'implement-pattern:strategy';
    protected $description = 'Generate a strategy pattern implementation';

    protected string $fileForGeneration = '';

    public function handle(): void
    {
        $context = $this->promptContext();
        $baseNamespace = config('design-pattern-implementor.default_namespace') . '\\' . $context;
        $baseDirectory = $this->getDirectoryPath($baseNamespace);

        $interfacesFolderName = config('design-pattern-implementor.interface_folder_name', 'Contracts');
        $interfacesDirectory = "$baseDirectory/$interfacesFolderName";
        $interfacesNamespace = "$baseNamespace\\$interfacesFolderName";

        $strategyInterface = "Is{$context}Strategy";
        $managerInterface = "Is{$context}Manager";

        $this->createPatternDirectories($baseDirectory, $interfacesDirectory);
        $this->generateInterfaces($context, $interfacesNamespace, $interfacesDirectory);
        
        $strategyReplacements = [
            'StrategyInterface' => $strategyInterface
        ];
        $strategyImports = [
            'StrategyInterface' => $interfacesNamespace."\\{$strategyInterface}"
        ];
        
        $this->generateStrategies($context, $baseNamespace,  $strategyReplacements, $strategyImports);
        
        $managerReplacements = [
            'StrategyInterface' => $strategyInterface,
            'ManagerInterface' => $managerInterface
        ];
        $managerImports = [
            'StrategyInterface' => $interfacesNamespace."\\{$strategyInterface}",
            'ManagerInterface' => $interfacesNamespace."\\{$managerInterface}"
        ];

        $this->generateManager($context, $baseNamespace, $baseDirectory, $managerReplacements, $managerImports);
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
                placeholder: 'e.g PaymentHandling',
                required: true,
                hint: 'This will be used as a containing folder name'
            )
        );
    }
    protected function createPatternDirectories(string $baseDirectory, string $interfacesDirectory): void
    {
        info('Creating directories for the strategy pattern...');

        $this->ensureDirectoryExists($baseDirectory);
        $this->ensureDirectoryExists($interfacesDirectory);
        $this->ensureDirectoryExists("$baseDirectory/Strategies");
    }

    protected function generateInterfaces(string $context, string $namespace, string $directory): void
    {
        info('Generating interface files...');

        $interfaces = [
            "Is{$context}Manager" => 'is_strategy_manager',
            "Is{$context}Strategy" => 'is_strategy',
        ];

        foreach ($interfaces as $className => $stub) {
            $this->fileForGeneration = "strategy/$stub";
            $this->createClassFile(
                $className,
                $namespace,
                $directory,
                $this->getStub()
            );
            
        }
    }

    protected function generateStrategies(string $context, string $namespace, array $replacements = [], array $imports = []): void
    {
        info('Generating strategy classes...');
        $strategies = $this->askStrategies();

        $strategiesDirectory = $this->getDirectoryPath("$namespace/Strategies"); // Normalize path for directory creation

        foreach ($strategies as $strategy) {
            $this->fileForGeneration = 'strategy/strategy';

            // Normalize namespace for imports
           // $formattedInterfaceNamespace = str_replace('/', '\\', $interfacesNamespace);

            // Add replacements for the strategy class
            $this->addReplacement('DummyNamespace', str_replace('/', '\\', "$namespace/Strategies"));
            $this->addReplacement('DummyClass', $this->formatClassName($strategy));

            foreach($replacements as $k => $v)
            {
                $this->addReplacement($k, $v);
            }

            foreach($imports as $k => $v)
            {
                $this->addImport($k, $v);    
            }

            $this->createClassFile(
                $this->formatClassName($strategy),
                str_replace('/', '\\', "$namespace\\Strategies"), // Normalize namespace for file generation
                $strategiesDirectory,
                $this->getStub()
            );
        }
    }

    protected function askStrategies(): array
    {
        $strategies = [];
        
        while($strategy = $this->formatClassName(text(
            label: 'Enter a strategy name (or leave blank to stop):',
            placeholder: 'e.g CreditCardPayment',
            required: false
        )))
        {
            if(!in_array($strategy, $strategies)) $strategies[] = $strategy;
            else {
                info('Already a strategy with this name due for creation, please try again...');
            }
        };

        return $strategies;
    }

    protected function generateManager(string $context, string $namespace, string $baseDirectory, array $replacements = [], array $imports = []): void
    {
        info('Generating the strategy manager class...');

        $this->fileForGeneration = 'strategy/strategy_manager';

        // Normalize namespace for imports
       // $formattedInterfaceNamespace = str_replace('/', '\\', $interfacesNamespace);

        // Add replacements for the manager class
        $this->addReplacement('DummyNamespace', $namespace);
        $this->addReplacement('DummyClass', "{$context}Manager");
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
            "{$context}Manager",
            $namespace,
            $baseDirectory,
            $this->getStub()
        );
    }
}
