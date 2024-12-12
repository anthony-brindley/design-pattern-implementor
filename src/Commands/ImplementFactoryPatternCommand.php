<?php

namespace AnthonyBrindley\DesignPatternImplementor\Commands;

use AnthonyBrindley\DesignPatternImplementor\Commands\BaseImplementor;
use Illuminate\Support\Str;

use function Laravel\Prompts\{infon};

class ImplementFactoryPatternCommand extends BaseImplementor
{
    protected $signature = 'implement-pattern:factory';

    protected static string $patternName = 'factory';

    public function handle(): void
    {
        $this->handleDomainSpecifics();
        $this->handleContextSpecifics();
        $this->handleContractsFolderDetails();

        $this->handleProductFolderDetails();
        $this->handleInterfaceSpecifics();        

        $this->handleCreatorClassSpecifics();
        
        // CREATE DIRECTORIES
        $this->createPatternDirectories();

        $this->generateInterfaces();

        $this->generateProductClasses();

        $this->generateCreatorClass();
    }

    protected function handleProductFolderDetails()
    {
        $productFolderName = $this->promptProductFolderName();
        $singularProductName = Str::singular($productFolderName);

        $this->setSetting('productFolderName', $productFolderName);
        $this->setSetting('productSingular', $singularProductName);
    }

    protected function promptProductFolderName(): string
    {
        return $this->formatClassName(
            $this->promptFor(
            label: $this->trans('prompt.products-folder'),
            placeholder: "e.g Products",
            default: "Products",
            required: false
        
        ));
    }

    protected function handleInterfaceSpecifics()
    {
        $this->handleProductInterface();
        
    }

    protected function handleProductInterface()
    {
        $productInterface = $this->promptProductInterface();
        $this->setSetting('productInterfaceName', $productInterface);
        $this->handleProductInterfaceActionMethod();
    }

    protected function promptProductInterface(): string
    {
        $context = $this->getSetting('context');
        $productName = $this->getSetting('productSingular');
        $defaultInterfaceClassName = "Is{$context}{$productName}";

        return $this->promptForClassName(
            label: $this->trans('prompt.product-interface', ['context' => $context]),
            placeholder: "e.g $defaultInterfaceClassName",
            default:  $defaultInterfaceClassName,
            required: true
        );
    }

    protected function handleProductInterfaceActionMethod()
    {
        $productInterfaceActionMethod = $this->promptActionMethod();
        $this->setSetting('productInterfaceActionMethod', $productInterfaceActionMethod);

        $this->interfaceMethods = [];
        $this->interfaceMethods[] = $productInterfaceActionMethod;

        $interfaceMethodString = $this->generateInterfaceMethods();
        $this->setSetting('productActionInterfaceMethodString', $interfaceMethodString);

        $methodString = $this->generateMethodString($productInterfaceActionMethod);
        $this->setSetting('productActionMethodString', $methodString);
    }

    protected function promptActionMethod()
    {
        $context = $this->getSetting('context');
        $label = $this->trans('prompt.action-method', ['context' => $context]);
        $default = "handle";

        return $this->promptForCamel(
            label: $label,
            placeholder: "e.g $default",
            default:  $default,
            required: true
        );
    }

    protected function handleCreatorClassSpecifics()
    {
        $singularProductName = $this->getSetting('productSingular');
        $creatorClass = $this->promptCreatorClass($singularProductName);
        $this->setSetting('creatorClass', $creatorClass);

        $this->handleCreatorFactoryMethod();

        $this->addScopedReplacement($creatorClass, 'product_action', $this->getSetting('productActionMethodString'));
        $this->addScopedReplacement($creatorClass, 'factory_method', $this->getSetting('creatorClassFactoryMethodString'));
    }

    protected function handleCreatorFactoryMethod()
    {
        $creatorClassFactoryMethod = $this->promptFactoryMethod();
        $this->setSetting('creatorClassFactoryMethod', $creatorClassFactoryMethod);

        $this->interfaceMethods = [];
        $this->interfaceMethods[] = $creatorClassFactoryMethod;

        $interfaceMethodString = $this->generateInterfaceMethods();
        $this->setSetting('creatorClassFactoryInterfaceMethodString', $interfaceMethodString);

        $methodString = $this->generateMethodString($creatorClassFactoryMethod);
        $this->setSetting('creatorClassFactoryMethodString', $methodString);
    }

    protected function promptFactoryMethod()
    {
        $context = $this->getSetting('context');
        $label = $this->trans('prompt.factory-method', ['context' => $context]);
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
            label: $this->trans('prompt.creator-class'),
            placeholder: "e.g $default",
            default: $default,
            required: false
        );
    }

    protected function askProducts(string $singularProductName): array
    {
        $products = $this->captureAnswers(
            label: $this->trans('prompt.product-class', ['singularProductName' => $singularProductName]),
            placeholder: "e.g Base{$singularProductName}",
            required: false,
            limit: null,
        );

        return $products;
    }

    protected function createPatternDirectories(): void
    {
        info('Creating directories for the factory pattern...');

        $baseNamespace = $this->getSetting('baseNamespace');
        $basePath = $this->getDirectoryPath($baseNamespace); 

        $this->ensureDirectoryExists($basePath);
        $this->ensureDirectoryExists($this->getSetting('interfacesFolderPath'));

        $productNamespace = $basePath.'/'.$this->getSetting('productFolderName');
        $this->setSetting('productFolderNamespace', $productNamespace);
        $this->ensureDirectoryExists($this->getDirectoryPath($productNamespace));
    }

    protected function generateInterfaces()
    {
        $this->generateProductInterface();
    }

    protected function generateProductInterface(): void
    {
        $interfaceName = $this->getSetting('productInterfaceName');
        $namespace = $this->getSetting('interfacesFolderNamespace');

        $interfaceNamespace = $namespace.'\\'.$interfaceName;
        $this->setSetting('productInterfaceNamespace', $interfaceNamespace);

        $this->fileForGeneration('general/interface');

        info('Generating interface file: '.$interfaceName);

        $this->addScopedReplacement($interfaceName, 'methods', $this->getSetting('productActionInterfaceMethodString'));

        $this->createClassFile(
            $interfaceName,
            $namespace,
            $this->getStub()
        );  

        
    }

    protected function generateProductClasses()
    {
        $this->fileForGeneration = 'factory/product';
  
        $productClasses = $this->askProducts($this->getSetting('productSingular'));
        
        foreach($productClasses as $product)
        {
            $className = $this->formatClassName($product);

            info('Generating the '.$className.' class...');

            $this->addScopedDependency($className, $this->getSetting('productInterfaceNamespace'), 'IsProductInterface');

            $this->addScopedReplacement($className, 'product_action', $this->getSetting('productActionMethodString'));

            $this->createClassFile(
                $className,
                $this->getSetting('productFolderNamespace'),
                $this->getStub()
            );
        }
    }

    protected function generateCreator(): void
    {
        $className = $this->getSetting('creatorClass');
        info('Generating the creator class...');

        $this->fileForGeneration = 'factory/creator';

        $this->addScopedDependency($className, $this->getSetting('productInterfaceNamespace'), 'IsProductInterface');

        $this->createClassFile(
            $className,
            $this->getSetting('baseNamespace'),
            $this->getStub()
        );

        info($this->trans('success'));
    }
}
