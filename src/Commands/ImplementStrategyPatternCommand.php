<?php

namespace AnthonyBrindley\DesignPatternImplementor\Commands;

use AnthonyBrindley\DesignPatternImplementor\Commands\BaseImplementor;

use function Laravel\Prompts\{text, info, spin};

class ImplementStrategyPatternCommand extends BaseImplementor
{
    protected $signature = 'implement-pattern:strategy';

    protected static string $patternName = 'strategy';

    public function handle(): void
    {
        
        $this->handleDomainSpecifics();
        $this->handleContextSpecifics();

        $this->handleFolderNames();

        $this->createPatternDirectories();

        $this->handleInterfaceSpecifics();

        $this->generateStrategies();
        
        $this->generateManager();
    }

    

    protected function handleFolderNames()
    {
        $this->handleContractsFolderDetails();
        $this->handleStrategyFolderDetails();

    }

    protected function handleStrategyFolderDetails()
    {
        $confirmedFolderName = $this->promptForClassName(
            label: $this->trans('prompt.strategy-folder'),
            default: "Strategies",
            required: true
        );

        $this->setSetting('strategyFolderName', $confirmedFolderName);

        $proposedFolderNamespace = $this->getSetting('baseNamespace').'\\'.$confirmedFolderName;
        $proposedFolderPath = $this->getDirectoryPath($proposedFolderNamespace);

        $this->setSetting('strategyFolderNamespace', $proposedFolderNamespace);
        $this->setSetting('strategyFolderPath', $proposedFolderPath);
    }

    protected function handleInterfaceSpecifics()
    {

        info('Generating interface files...');

        $this->generateIsStrategyInterface();
        $this->generateStrategyManagerInterface();
    }

    protected function generateIsStrategyInterface()
    {
        $context = $this->getSetting('context');
        $className = "Is{$context}Strategy";

        $this->fileForGeneration = 'strategy/is_strategy';

        $this->createClassFile(
            $className,
            $this->getSetting('interfacesFolderNamespace'),
            $this->getStub()
        );

        $this->setSetting('isStrategyInterfaceNamespace', $this->getSetting('interfacesFolderNamespace').'\\'.$className);
    }

    protected function generateStrategyManagerInterface()
    {
        $context = $this->getSetting('context');
        $className = "Is{$context}Manager";
        $this->fileForGeneration = 'strategy/is_strategy_manager';

        $this->createClassFile(
            $className,
            $this->getSetting('interfacesFolderNamespace'),
            $this->getStub()
        );

        $this->setSetting('strategyManagerInterfaceNamespace', $this->getSetting('interfacesFolderNamespace').'\\'.$className);
    }

    protected function createPatternDirectories(): void
    {
        info('Creating directories for the strategy pattern...');

        $this->ensureDirectoryExists($this->getDirectoryPath($this->getSetting('baseNamespace')));
        $this->ensureDirectoryExists($this->getSetting('interfacesFolderPath'));
        $this->ensureDirectoryExists($this->getSetting('strategyFolderPath'));
    }

    

    protected function generateStrategies(): void
    {
        $strategies = $this->askStrategies();

        if(empty($strategies)) return;

        info('Generating strategy classes...');
        
        $this->fileForGeneration = 'strategy/strategy';


        foreach ($strategies as $strategy) {
            $className = $this->formatClassName($strategy);

            $this->addScopedDependency($className, $this->getSetting('isStrategyInterfaceNamespace'), 'StrategyInterface');

            $this->createClassFile(
                $className,
                $this->getSetting('strategyFolderNamespace'),
                $this->getStub()
            );
        }
    }

    protected function askStrategies(): array
    {
        return $this->captureAnswers(
            label: 'Enter a strategy name (or leave blank to stop):',
            placeholder: 'e.g CreditCardPayment',
            required: false
        );
    }

    protected function generateManager(): void
    {
        info('Generating the strategy manager class...');

        $this->fileForGeneration = 'strategy/strategy_manager';

        $context = $this->getSetting('context');

        $className = "{$context}Manager";

        $this->addScopedDependency($className, $this->getSetting('strategyManagerInterfaceNamespace'), 'ManagerInterface');

        $this->addScopedDependency($className, $this->getSetting('isStrategyInterfaceNamespace'), 'StrategyInterface');

        $this->createClassFile(
            className: $className,
            namespace: $this->getSetting('baseNamespace'),
            stubPath: $this->getStub()
        );
    }
}
