<?php

namespace AnthonyBrindley\DesignPatternImplementor\Commands;

class UnfinishedImplementMediatorPatternCommand extends BaseImplementor
{
    protected $signature = 'implement-pattern:mediator';

    protected static string $patternName = 'mediator';

    public function handle(): void
    {
        $this->handleDomainSpecifics();

        $this->handleContextSpecifics();

        $this->handleInterfaceSpecifics();

        $this->confirmBaseComponent();

        $this->confirmMediatedComponents();
        
        $this->confirmMediatorClass();

        $this->process();

        // define a mediator interface with a single main 'notify' method that accepts a 'sender' type object

        // define any classes that should act as 'components' to be managed by the mediator

        // each of these classes should accept a mediator object - possibly via a base component class

        // define a concrete mediator that keeps track of all components it manages and has the main 'notify' method which
        // contains the business logic that determines which component(S) should react to the notification. WE can pass all the 
        // concrete components in it's constructor. 

        // the notify method should accept the sender and possibly an event identifier so we can logically determine who to mediate to
    }

    protected function handleInterfaceSpecifics()
    {
        $this->promptMediatorInterfaceName();
        $this->promptMediatorInterfaceMethod();

        $this->promptMediatedInterfaceName();
        
    }

    protected function promptMediatorInterfaceName()
    {
        $interfaceName = $this->promptForClassName(
            label: "What should the interface that your Mediator class implements be called?",
            default: "IsMediator",
            required: true
        );

        $this->setSetting('isMediatorInterfaceName', $interfaceName);
    }

    protected function promptMediatorInterfaceMethod()
    {
        $methodName = $this->promptForCamel(
            label: "What should the main mediate action method be called?",
            default: "notify",
            required: true
        );

        $this->setSetting('isMediatorInterfaceMethod', $methodName);
    }

    protected function promptMediatedInterfaceName()
    {
        $interfaceName = $this->promptForClassName(
            label: "What should the interface that your Mediated classes implement be called?",
            default: "IsMediated",
            required: true
        );

        $this->setSetting('isMediatedInterfaceName', $interfaceName);
    }

    protected function confirmBaseComponent()
    {
        $baseComponentClassName = $this->promptForClassName(
            label: "What should the base component class be called?",
            default: "BaseMediatedComponent",
            required: true
        );

        $this->setSetting('baseMediatedComponentName', $baseComponentClassName);
    }

    protected function confirmMediatedComponents()
    {
        $components = $this->captureAnswers(
            label: "What component classes should we create/reference to be mediated by the concrete mediator?",
            required: false,
        );

        // check they dont already exist

        $this->setSetting('mediatedComponentClasses', $components);
    }

    protected function confirmMediatorClass()
    {
        $className = $this->promptForClassName(
            label: "What should your concrete Mediator class be called?",
            default: "ConcreteMediator",
            required: true
        );

        // check it doesnt already exist

        $this->setSetting('concreteMediatorClassName', $className);
    }

    protected function process()
    {
        // create folders
        $this->createPatternDirectories();

        // create interfaces
        $this->createInterfaces();

        // create components
        $this->createBaseComponent();
        $this->createComponents();

        // create mediator
        $this->createConcreteMediator();

    }

    protected function createPatternDirectories()
    {
        info('Creating directories for the mediator pattern...');

        $basePath = $this->getSetting('basePath');
        $this->ensureDirectoryExists($basePath);

        $contractsFolder = $this->getSetting('interfacesFolderName');
        $contractsFolderNamespace = $this->getSetting('baseNamespace').'\\'.$contractsFolder;
        $contractsFolderPath = $this->getDirectoryPath($contractsFolderNamespace);

        $this->ensureDirectoryExists($contractsFolderPath);

        $this->setSetting('interfacesFolderNamespace', $contractsFolderNamespace);


    }

    protected function createInterfaces()
    {
        $interfacesNamespace = $this->getSetting('interfacesFolderNamespace');

        $isMediatorInterfaceName = $this->getSetting('isMediatorInterfaceName');
        $isMediatorInterfaceNamespace = $interfacesNamespace.'\\'.$isMediatorInterfaceName;

        $isMediatedInterfaceName = $this->getSetting('isMediatedInterfaceName');
        $mediatedInterfaceNamespace = $interfacesNamespace.'\\'.$isMediatedInterfaceName;

        $this->addScopedDependency(className: $isMediatorInterfaceName, namespace: $mediatedInterfaceNamespace, key: 'isMediatedInterfaceName');
        $this->addScopedReplacement($isMediatorInterfaceName, 'mediate_action', $this->getSetting('isMediatorInterfaceMethod'));
      
        $this->addScopedDependency(className: $isMediatedInterfaceName, namespace: $isMediatorInterfaceNamespace, key: 'isMediatorInterfaceName');


        $this->fileForGeneration = 'mediator/is_mediator';

        info('Generating interface file: '.$isMediatorInterfaceName);

        $this->createClassFile(
            $isMediatorInterfaceName,
            $interfacesNamespace,
            $this->getStub()
        ); 


        // isMediatedInterface
        $this->fileForGeneration = 'mediator/is_mediated';

        info('Generating interface file: '.$isMediatedInterfaceName);

        $this->createClassFile(
            $isMediatedInterfaceName,
            $interfacesNamespace,
            $this->getStub()
        ); 
    }

    protected function createBaseComponent()
    {
        $componentName = $this->getSetting('baseMediatedComponentName');

        $this->fileForGeneration = 'mediator/component_base';

        info('Generating base component file: '.$componentName);

        $mediatedInterfaceNamespace = $this->getSetting('interfacesFolderNamespace').'\\'.$this->getSetting('isMediatedInterfaceName');
        $this->addScopedDependency(className: $componentName, namespace: $mediatedInterfaceNamespace, key: 'isMediatedInterfaceName');

        $mediatorInterfaceNamespace = $this->getSetting('interfacesFolderNamespace').'\\'.$this->getSetting('isMediatorInterfaceName');
        $this->addScopedDependency(className: $componentName, namespace: $mediatorInterfaceNamespace, key: 'isMediatorInterfaceName');

        $this->createClassFile(
            className: $componentName,
            targetNamespace: $this->getSetting('baseNamespace'),
            stubPath: $this->getStub()
        ); 
    }

    protected function createComponents()
    {
        $this->fileForGeneration = 'mediator/component';

        foreach($this->getSetting('mediatedComponentClasses') as $component)
        {
            $baseNamespace = $this->getSetting('baseNamespace');
            $baseComponentNamespace = $baseNamespace.'\\'.$this->getSetting('baseMediatedComponentName');
            $this->addScopedDependency($component, $baseComponentNamespace, 'BaseComponentClassName');

            $this->createClassFile(
                className: $component,
                targetNamespace: $baseNamespace,
                stubPath: $this->getStub()
            );
        }
    }

    protected function createConcreteMediator()
    {
        $mediatorClassName = $this->getSetting('concreteMediatorClassName');

        info('Generating concrete mediator class file: '.$mediatorClassName);

        $this->fileForGeneration = 'mediator/concrete_mediator';

        $mediatorInterfaceNamespace = $this->getSetting('interfacesFolderNamespace').'\\'.$this->getSetting('isMediatorInterfaceName');
        $this->addScopedDependency(className: $mediatorClassName, namespace: $mediatorInterfaceNamespace, key: 'isMediatorInterfaceName');

        $mediatedInterfaceNamespace = $this->getSetting('interfacesFolderNamespace').'\\'.$this->getSetting('isMediatedInterfaceName');
        $this->addScopedDependency(className: $mediatorClassName, namespace: $mediatedInterfaceNamespace, key: 'isMediatedInterfaceName');

        $this->addScopedReplacement($mediatorClassName, 'mediate_action', $this->getSetting('isMediatorInterfaceMethod'));

        $this->createClassFile(
            $mediatorClassName,
            $this->getSetting('baseNamespace'),
            $this->getStub()
        ); 
    }
}
