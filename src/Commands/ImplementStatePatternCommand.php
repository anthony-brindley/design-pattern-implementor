<?php

namespace AnthonyBrindley\DesignPatternImplementor\Commands;

use AnthonyBrindley\DesignPatternImplementor\Commands\BaseImplementor;
use Illuminate\Support\Str;


class ImplementStatePatternCommand extends BaseImplementor
{
    protected $signature = 'implement-pattern:state';

    protected static string $patternName = 'state';

    public function handle(): void
    {
        $this->handleDomainSpecifics();

        $this->handleFolderNames();    
        
        $this->handleInterfaceSpecifics();

        $this->handleStateSpecifics();

        $this->handleContextSpecifics();
    }

    protected function handleFolderNames()
    {

        $this->handleContextSpecifics();
        
        $this->captureStatesFolderName();
        $this->captureContractsFolderName();

        // assuming all folders are valid, lets create them
        $this->createPatternDirectories();
    }

    

    protected function folderDoesntAlreadyExist(string $path): bool
    {
        return !$this->checkDirectoryExists($path);
    }


    protected function captureStatesFolderName()
    {
        $statesFolderName = $this->promptStateFolderName();
        $this->setSetting('statesFolderName', $statesFolderName);
        $this->setSetting('stateSingluar', $this->interpretSingular($statesFolderName));
    }

    protected function promptStateFolderName(): string
    {
        return $this->promptFor(
            label: $this->trans('prompt.state-folder'),
            default: config('design-pattern-implementor.state_folder_name', 'States'),
            required: true,
            hint: $this->trans('hint.state-folder')
        );
    }

    protected function createPatternDirectories(): void
    {
        info('Creating directories for the state pattern...');

        $base = $this->getSetting('baseNamespace');

        [$interfacesNamespace, $interfacesDirectory] = $this->getAppendedNamespacePathArray($base, $this->getSetting('contractsFolderName'));
        $this->setSetting('interfacesNamespace', $interfacesNamespace);
        $this->setSetting('interfacesDirectoryPath', $interfacesDirectory);

        [$statesNamespace, $statesDirectory] = $this->getAppendedNamespacePathArray($base, $this->getSetting('statesFolderName'));

        $this->setSetting('statesNamespace', $statesNamespace);
        $this->setSetting('statesDirectoryPath', $statesDirectory);

        $this->ensureDirectoryExists($this->getSetting('basePath'));
        $this->ensureDirectoryExists($interfacesDirectory);
        $this->ensureDirectoryExists($statesDirectory);
    }

    protected function handleInterfaceSpecifics()
    {
        $stateInterfaceName = $this->promptStateInterfaceName();

        [$stateInterfaceNamespace, $stateInterfaceDirectory] = $this->getAppendedNamespacePathArray($this->getSetting('interfacesNamespace'), $stateInterfaceName);

        $this->setSetting('stateInterfaceName', $stateInterfaceName);
        $this->setSetting('stateInterfaceNamespace', $stateInterfaceNamespace);
        $this->setSetting('stateInterfaceDirectory', $stateInterfaceDirectory);

        $contextInterfaceName = $this->promptHasStatesInterfaceName();

        [$contextInterfaceNamespace, $contextInterfaceDirectory] = $this->getAppendedNamespacePathArray($this->getSetting('interfacesNamespace'), $contextInterfaceName);

        $this->setSetting('contextInterfaceName', $contextInterfaceName);
        $this->setSetting('contextInterfaceNamespace', $contextInterfaceNamespace);
        $this->setSetting('contextInterfaceDirectory', $contextInterfaceDirectory);

        $this->askStateInterfaceMethods();

        $this->createInterfaces();
    }

    protected function interpretSingular(string $possiblePlural): string
    {
        return Str::singular($possiblePlural);
    }

    protected function getAppendedNamespacePathArray(string $baseNamespace, string $appendment): array
    {
        $proposedNamespace = $baseNamespace.'\\'.$appendment;
        $proposedDirectory = $this->getDirectoryPath($proposedNamespace);

        return [
            $proposedNamespace,
            $proposedDirectory
        ];
    }

    protected function promptStateInterfaceName()
    {
        $stateClassSingular = $this->getSetting('stateSingluar');
        $contextSinglular = $this->getSetting('contextSingular');

        $suggestedName = "Is{$contextSinglular}{$stateClassSingular}";

        return $this->promptForClassName(
            label: "What interface should all $stateClassSingular classes implement?",
            default: $suggestedName,
            required: true
        );
    }

    protected function promptHasStatesInterfaceName()
    {
        $contextSinglular = $this->getSetting('contextSingular');

        $statesFolderName = $this->getSetting('statesFolderName');

        $suggestedName = "Has{$contextSinglular}{$statesFolderName}";

        return $this->promptForClassName(
            label: $this->trans('prompt.has-state-interface-class', ['contextSinglular' => $contextSinglular]),
            default: $suggestedName,
            required: true
        );
    }

    protected function askStateInterfaceMethods()
    {
        $stateClassSingular = $this->getSetting('stateSingluar');

        $methods = $this->captureAnswers(
            label: $this->trans('prompt.state-interface-methods', ['stateClassSingular' => $stateClassSingular]),
            required: false,
            hint: "Leave blank to confirm youre finished"
        );

        $this->interfaceMethods = $methods;

        $methodString = $this->generateInterfaceMethods();

        $this->setSetting('stateInterfaceMethodsArray', $methods);
        $this->setSetting('stateInterfaceMethodString', $methodString);
    }

    protected function createInterfaces()
    {
        // State interface
        $this->createIsStateInterface();
        
        // HasState interface
        $this->createHasStatesInterface();
    }

    private function createIsStateInterface()
    {
        $className = $this->getSetting('stateInterfaceName');

        $this->addScopedReplacement($className, 'methods', $this->getSetting('stateInterfaceMethodString'));

        $this->fileForGeneration = 'state/state_interface';

        $this->createClassFile(
            className: $className,
            targetNamespace: $this->getSetting('interfacesNamespace'),
            stubPath: $this->getStub()
        );
    }

    private function createHasStatesInterface()
    {
        $className = $this->getSetting('contextInterfaceName');

        $this->addScopedDependency($className, $this->getSetting('stateInterfaceNamespace'), 'StateInterfaceName');
        $this->addScopedReplacement($className, 'StateClassSingular', Str::camel($this->getSetting('stateSingluar')));

        $this->fileForGeneration = 'state/context_interface';

        $this->createClassFile(
            className: $className,
            targetNamespace: $this->getSetting('interfacesNamespace'),
            stubPath: $this->getStub()
        );
    }
    
    protected function handleStateSpecifics()
    {
        $stateClassSingular = $this->getSetting('stateSingluar');

        $states = $this->captureAnswers(
            label: $this->trans('prompt.state-classes', ['stateClassSingular' => $stateClassSingular]),
            required: false,
            hint: "Leave blank to confirm you're finished"
        );

        // should we provide an option to create an abstract base state?

        $this->fileForGeneration = 'state/state';

        $stateMethods = $this->getSetting('stateInterfaceMethodsArray') ?? [];
        $methodString = '';
        foreach($stateMethods as $method)
        {
            $methodString .= "public function {$method}()\n\t{\n\t\t//Fill this in\n\t}\n\n";
        }

        foreach($states as $state)
        {
            $className = $this->formatClassName($state);

            $this->addScopedDependency($className, $this->getSetting('stateInterfaceNamespace'), 'StateInterfaceName');

            $this->addScopedReplacement($className, 'methods', $methodString);
            
            $this->createClassFile(
                className: $className,
                targetNamespace: $this->getSetting('statesNamespace'),
                stubPath: $this->getStub()
            );
        }
    }

    public function handleContextSpecifics(?string $proposedContextName = null)
    {
        $className = $this->promptContextClassName();

        $this->fileForGeneration = 'state/context';

        $this->loadInterface($this->getSetting('contextInterfaceNamespace'));
        $methods = $this->generateMethods(); 

        $this->addScopedReplacement($className, 'methods', $methods);
        $this->addScopedDependency($className, $this->getSetting('contextInterfaceNamespace'), 'HasStatesInterfaceName');
        $this->addScopedDependency($className, $this->getSetting('stateInterfaceNamespace'), 'StateInterfaceName');
        
        $this->createClassFile(
            className: $className,
            targetNamespace: $this->getSetting('baseNamespace'),
            stubPath: $this->getStub()
        );

    }

    protected function promptContextClassName()
    {
        $suggestedClassName = $this->getSetting('contextSingular');    
        $statesFolderName = $this->getSetting('statesFolderName');

        return $this->promptForClassName(
            label: $this->trans('prompt.context-class'),
            default: $suggestedClassName,
            hint: $this->trans('hint.context-class', ['statesFolderName' => $statesFolderName]),
            required: true
        );
    }
}


