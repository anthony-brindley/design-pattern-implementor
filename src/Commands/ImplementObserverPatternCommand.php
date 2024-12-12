<?php

namespace AnthonyBrindley\DesignPatternImplementor\Commands;

use AnthonyBrindley\DesignPatternImplementor\Commands\BaseImplementor;

use function Laravel\Prompts\{info};

class ImplementObserverPatternCommand extends BaseImplementor
{
    protected $signature = 'implement-pattern:observer';
    protected static string $patternName = 'observer';


    public function handle(): void
    {
        $this->handleDomainSpecifics();

        $this->handleContextSpecifics();
        
        $this->handleInterfaceSpecifics();

        $this->generateObservers();

        $this->generateObservable();
    }

    protected function handleInterfaceSpecifics()
    {
        info('Processing interface files...');

        $this->handleContractsFolderDetails();

        $this->createPatternDirectories();
        
        // We need to prep this before generating the classes due to the inter-dependencies of the interfaces
        // i.e both need to be aware of each other so we need class references etc before file generation

        $context = $this->getSetting('context');
        $isObservableInterfaceClassName = "Is{$context}Observable";
        $this->setSetting('observableInterfaceName', $isObservableInterfaceClassName);
        $this->setSetting('observableInterfaceNamespace', $this->getSetting('interfacesFolderNamespace').'\\'.$isObservableInterfaceClassName);

        $isObserverInterfaceClassName = "Is{$context}Observer";
        $this->setSetting('observerInterfaceName', $isObserverInterfaceClassName);
        $this->setSetting('observerInterfaceNamespace', $this->getSetting('interfacesFolderNamespace').'\\'.$isObserverInterfaceClassName);

        $this->generateIsObservableInterface();
        $this->generateIsObserverInterface();
    }

    protected function generateIsObservableInterface()
    {
        $className = $this->getSetting('observableInterfaceName');
        
        $this->fileForGeneration = 'observer/is_observable';

        $this->addScopedDependency($className, $this->getSetting('observerInterfaceNamespace'), 'ObserverInterface');

        $this->createClassFile(
            className: $className,
            targetNamespace: $this->getSetting('interfacesFolderNamespace'),
            stubPath: $this->getStub()
        );
    }

    protected function generateIsObserverInterface()
    {
        $className = $this->getSetting('observerInterfaceName');
        
        $this->fileForGeneration = 'observer/is_observer';

        $this->addScopedDependency($className, $this->getSetting('observableInterfaceNamespace'), 'ObservableInterface');

        $this->createClassFile(
            className: $className,
            targetNamespace: $this->getSetting('interfacesFolderNamespace'),
            stubPath: $this->getStub()
        );
    }

    protected function createPatternDirectories(): void
    {
        info('Creating directories for the observer pattern...');

        $this->ensureDirectoryExists($this->getSetting('basePath'));
        $this->ensureDirectoryExists($this->getSetting('interfacesFolderPath'));
        $this->ensureDirectoryExists($this->getSetting('basePath')."/Observers");
    }

    protected function generateObservers(): void
    {
        info('Generating observer classes...');

        $observers = $this->askObservers();

        $observersNamespace = $this->getSetting('baseNamespace').'\\Observers';

        $this->fileForGeneration = 'observer/observer';

        foreach ($observers as $observer) {
            
            $className = $this->formatClassName($observer);

            $this->addScopedDependency($className, $this->getSetting('observerInterfaceNamespace'), 'ObserverInterface');
            $this->addScopedDependency($className, $this->getSetting('observableInterfaceNamespace'), 'ObservableInterface');

            $this->createClassFile(
                $className,
                $observersNamespace,
                $this->getStub()
            );
        }
    }

    protected function askObservers(): array
    {
        return $this->captureAnswers(
            label: $this->trans('prompt.observer-class'),
            placeholder: 'e.g MobileFeed',
            required: false
        );
    }

    protected function generateObservable(): void
    {
        info('Generating the observable class...');

        $this->fileForGeneration = 'observer/observable';

        $className = $this->promptForClassName(
            label: $this->trans('prompt.observable-class'),
            default: $this->getSetting('context').'Observable',
            required: true
        );

        $this->addScopedDependency($className, $this->getSetting('observerInterfaceNamespace'), 'ObserverInterface');
        $this->addScopedDependency($className, $this->getSetting('observableInterfaceNamespace'), 'ObservableInterface');

        $this->createClassFile(
            $className,
            $this->getSetting('baseNamespace'),
            $this->getStub()
        );

        info($this->trans('success'));
    }
}
