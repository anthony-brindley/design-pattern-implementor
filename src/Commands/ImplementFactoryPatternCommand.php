<?php

namespace AnthonyBrindley\DesignPatternImplementor\Commands;

use AnthonyBrindley\DesignPatternImplementor\Traits\FileGenerator;
use Illuminate\Console\GeneratorCommand;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;

use function Laravel\Prompts\{text, info, spin};

class ImplementFactoryPatternCommand extends GeneratorCommand
{
    use FileGenerator;

    protected $signature = 'implement-pattern:factory';
    protected $description = 'Generate a factory pattern implementation';

    protected string $fileForGeneration = '';

    protected array $settings = [];

    public function __construct(Filesystem $files)
    {
        parent::__construct($files);
        $this->setSetting('baseNamespace', config('design-pattern-implementor.default_namespace'));
        $this->setSetting('interfacesFolderName', config('design-pattern-implementor.interface_folder_name', 'Contracts'));
    }

    protected function getSetting(string $key)
    {
        if(!isset($this->settings[$key])) return null;

        return $this->settings[$key];
    }

    protected function updateSetting(string $key, mixed $value): bool
    {
        if(!isset($this->settings[$key])) return false;

        $this->settings[$key] = $value;

        return true;
    }

    protected function setSetting(string $key, mixed $value): void
    {
        $this->settings[$key] = $value;
    }

    public function handle(): void
    {
        // questions
        /**
         * 1. Context
         * 2. What folder should the ProductFactories live in? (Helps us understand what the classes we are creating are)
         * 3. Objects returned by the factory should implement what interface?
         * 4. What method should this interface enforce?
         * 5. What should the Creator class be called?
         * 6. What should the creator class's main factory method be called?
         * 7. What are the possible returned objects from the creator class? (Factory classes)
         */

        // 1 
        $context = $this->promptContext();
        $this->setSetting('context', $context);
        $this->updateSetting('baseNamespace', $this->getSetting('baseNamespace').'\\' . $context);

        $baseDirectory = $this->getDirectoryPath($this->getSetting('baseNamespace'));
        $this->setSetting('baseDirectory', $baseDirectory);

        $interfacesFolderName = $this->getSetting('interfacesFolderName');
        $this->setSetting('interfacesDirectory', "$baseDirectory/$interfacesFolderName");

        $baseNamespace = $this->getSetting('baseNamespace');
        $this->setSetting('interfacesNamespace', "$baseNamespace\\$interfacesFolderName");
        $interfacesNamespace = "$baseNamespace\\$interfacesFolderName";

        // 2
        $productFolderName = $this->promptProductFolderName();
        $singularProductName = Str::singular($productFolderName);

        $this->setSetting('productFolderName', $productFolderName);
        $this->setSetting('productSingular', $singularProductName);

        // 3
        $productInterface = $this->promptProductInterface($context, $singularProductName);
        $this->setSetting('productInterfaceName', $productInterface);

        // 4
        $productInterfaceActionMethod = $this->promptActionMethod();
        $this->setSetting('productInterfaceActionMethod', $productInterfaceActionMethod);

        // 5
        $creatorClass = $this->promptCreatorClass($singularProductName);
        $this->setSetting('creatorClass', $creatorClass);

/**
 * Conventions:
 * 
 * - ask === collate multiple
 * - prompt === get 1
 */


        // 6
        $creatorClassFactoryMethod = $this->promptFactoryMethod();
        $this->setSetting('creatorClassFactoryMethod', $creatorClassFactoryMethod);

        // 7
        $productClasses = $this->askProducts($singularProductName);
        $this->setSetting('productClasses', $productClasses);


//         // CREATE DIRECTORIES
        $this->createPatternDirectories($baseDirectory, $this->getSetting('interfacesDirectory'), $productFolderName);

//         // CREATE INTERFACES
//         // ProductInterface

        $methodString = $this->generateMethodString($productInterfaceActionMethod);
        $this->addScopedReplacement($creatorClass, 'product_action', $productInterfaceActionMethod);
        $this->addScopedReplacement($creatorClass, 'factory_method', $creatorClassFactoryMethod);
        $this->setSetting('productActionMethod', $methodString);

        $interfaceMethodString = $this->generateMethodString(methodName: $productInterfaceActionMethod, forInterface: true);
        $this->setSetting('productInterfaceActionMethod', $interfaceMethodString);


        $this->generateInterface($productInterface, $interfacesNamespace, $this->getSetting('interfacesDirectory'), $interfaceMethodString);
        
        
        // create creator class
        $creatorFactoryMethodString = $this->generateMethodString($creatorClassFactoryMethod);
        $creatorFactoryMethodString = $creatorFactoryMethodString."\n\n".$methodString;
        $this->generateCreator($creatorClass, $baseNamespace, $baseDirectory, $creatorFactoryMethodString);

        // create products
        $this->fileForGeneration = 'factory/product';
        $productNamespace = $baseNamespace.'\\'.$this->getSetting('productFolderName');

        $this->setSetting('productNamespace', $productNamespace);

        $productDirectory = $this->getDirectoryPath($productNamespace);
        $this->setSetting('productDirectory', $productDirectory);


        $this->addReplacement('DummyNamespace', $productNamespace);
        

        foreach($productClasses as $product)
        {
            $this->addReplacement('DummyClass', "{$product}");
            //$methodString = $this->generateMethodString($productInterfaceActionMethod);
            $this->addScopedReplacement($product, 'IsProductInterface', $productInterface);
            $this->addScopedImport($product, 'interface', $interfacesNamespace.'\\'.$productInterface);
            $this->addScopedReplacement($product, 'product_action', $productInterfaceActionMethod);

            //dd('hererer', $this);
            $this->createClassFile(
                "{$product}",
                $productNamespace,
                $productDirectory,
                $this->getStub()
            );
        }


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
            $this->promptFor(
                label: 'What is the functionality context for this implementation?',
                placeholder: 'e.g PaymentHandling',
                required: true,
                hint: 'This will be used as a containing folder name'
            )
        );
    }

    protected function promptProductFolderName(): string
    {
        return $this->formatClassName(
            $this->promptFor(
            label: "What folder should the 'products' live in?",
            placeholder: "e.g Products",
            default: "Products",
            required: false
        
        ));
    }

    protected function promptProductInterface(string $context, string $productName): string
    {
        $defaultInterfaceClassName = "Is{$context}{$productName}";

        return $this->promptForClassName(
            label: "What interface should the products of the $context factory return?",
            placeholder: "e.g $defaultInterfaceClassName",
            default:  $defaultInterfaceClassName,
            required: true
        );
    }

    protected function promptActionMethod()
    {
        $context = $this->getSetting('context');
        $label = "What action method should the products of the $context factory have?";
        $default = "handle";

        return $this->promptForCamel(
            label: $label,
            placeholder: "e.g $default",
            default:  $default,
            required: true
        );
    }

    protected function promptFactoryMethod()
    {
        $context = $this->getSetting('context');
        $label = "What factory method should the factories of the $context have?";
        $default = "get";

        return $this->promptForCamel(
            label: $label,
            placeholder: "e.g $default",
            default:  $default,
            required: true
        );
    }

    protected function askMethods(string $label, string $example = 'handle', bool $required = false, ?int $limit = null): array
    {
        $methods = $this->captureAnswers(
            label: $label,
            placeholder: $example,
            required: $required,
            limit: $limit,
        );

        return $methods;
    }

    protected function promptCreatorClass(string $productName): string
    {
        $default = "{$productName}Creator";

        return $this->promptForClassName(
            label: "What should the main 'Creator' class be called?",
            placeholder: "e.g $default",
            default: $default,
            required: false
        );
    }

    protected function askProducts(string $singularProductName): array
    {
        $products = $this->captureAnswers(
            label: "Create a new {$singularProductName} class called: (leave blank to skip)",
            placeholder: "e.g Base{$singularProductName}",
            required: false,
            limit: null,
        );

        return $products;
    }

    protected function createPatternDirectories(string $baseDirectory, string $interfacesDirectory, string $productFolderName): void
    {
        info('Creating directories for the factory pattern...');

        $this->ensureDirectoryExists($baseDirectory);
        $this->ensureDirectoryExists($interfacesDirectory);
        $this->ensureDirectoryExists("$baseDirectory/$productFolderName");
    }

    protected function generateInterface(string $name, string $namespace, string $directory, string $methodString = '', string $stubName = 'general/interface'): void
    {
        info('Generating interface file: '.$name);

        $this->fileForGeneration = $stubName;

        $this->addScopedReplacement($name, 'methods', $methodString);

        $this->createClassFile(
            $name,
            $namespace,
            $directory,
            $this->getStub()
        );  
    }
    protected function generateCreator(string $name, string $namespace, string $baseDirectory, string $methodString): void
    {
        info('Generating the creator class...');

        $this->fileForGeneration = 'factory/creator';

        // Add replacements for the manager class
        $this->addReplacement('DummyNamespace', $namespace);
        $this->addReplacement('DummyClass', "{$name}");

        $productInterfaceName = $this->getSetting('productInterfaceName');
        $productInterfaceNamespace = $this->getSetting('interfacesNamespace').'\\'.$productInterfaceName;

        $this->addScopedReplacement($name, 'IsProductInterface', $productInterfaceName);
        $this->addScopedImport($name, 'interfaces', $productInterfaceNamespace);


        // Add the manager interface import

        $this->createClassFile(
            "{$name}",
            $namespace,
            $baseDirectory,
            $this->getStub()
        );
    }
}
