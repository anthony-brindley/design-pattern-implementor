<?php

namespace AnthonyBrindley\DesignPatternImplementor\Commands;

class ImplementCommandPatternCommand extends BaseImplementor
{
    protected $signature = 'implement-pattern:command';

    protected static string $patternName = 'command';

    public function handle(): void
    {
        //Invoker (invoker) needs to reference a command object - LARAVEL QUEUE - in refactoring.guru example, a queue is used to invoke commands after
        // storing them initially. 
        // -setCommand(command)
        // -executeCommand()

        // command interface or abstract/base class
        // -execute()
        // consider that anything that changes 'state' should be undo-able - possibly via a commandHistory class which stores the order of received commands

        // concrete commands - implement the command interface

        // receiver may contain business logic. receives command and handles it

        // client = creates command objects and passes them into the Invoker inc any params


        ////////// V2 - Better Understanding

        // An Invoker (such as a remote) can contain references to 1 or more Command objects but is ONLY coupled to the interface, not specific concretions
        // Each command object implements the IsCommand interface meaning that, any command, can be 'triggered' using a common method - e.g. execute
        // The invoker doesn't know what the command does or what 'receiver' handles the process, its decoupled
        // The receiver is defined on the command object and again, the invoker doesn't know about it
        // The command should provide an 'execute' and an equivalent 'un-execute' function so it can undo it's effects
        // Command concretions are passed any necessary parameters needed to perform the 'execution' during instantiation meaning that `->execute()`
        // doesn't require any extra specific parameters to be passed which could cause unnecessary coupling. Typically params are passed via constructor
        // to command objects. One of these params should most likely be the receiver object
        // 
        // Invoker objects may necessarily require different amounts of Command objects to assign to different 'indexes' or triggers - let's consider a
        // remote control with 4 buttons vs a remote control with 7 buttons. Each button needs to do something (even if that 'something' is actually nothing)
        // and necessarily the relevant command objects should be passed to the object via the constructor and then be 'indexed' or 'referenced' in such a way
        // that we understand that pressing button 3 would trigger the 'execute' method on the command in position 3

        // So an invoker should receive, as it's constructor params, any commands it needs to be able to action and each of those commands should in turn
        // reference a receiver that it's action is received by as well as any additional information required for the action to be performed.






        $this->handleDomainSpecifics();
        $this->handleContextSpecifics();

        $this->handleCommandInterfaceSpecifics();
        $this->gatherCommandClasses();

        $this->handleInvokerSpecifics();

        $this->handleReceiverSpecifics();

        $this->process();
    }

    protected function handleCommandInterfaceSpecifics()
    {
        $this->confirmCommandInterfaceName();
        $this->confirmCommandInterfaceExecuteMethod();
    }

    protected function confirmCommandInterfaceName()
    {
        $interfaceName = $this->promptForClassName(
            label: "What should we call your Command Interface class?",
            default: "IsCommand",
            required: true
        );

        $this->setSetting('commandInterfaceName', $interfaceName);
    }

    protected function confirmCommandInterfaceExecuteMethod()
    {
        $interfaceName = $this->getSetting('commandInterfaceName');

        $method = $this->promptFor(
            label: "What method should the $interfaceName enforce?",
            default: "execute",
            required: true
        );

        $this->setSetting('commandMethod', $method);
    }

    protected function gatherCommandClasses()
    {
        $this->confirmCommandClassesWrappingDirectory();

        $this->confirmBaseCommandClass();

        $method = $this->getSetting('commandMethod');

        $commandClasses = $this->captureAnswers(
            label: "Please provide a Command class name",
            required: false,
            placeholder: "GenerateOutcomeCommand",
            hint: "These will be the classes that actually perform actions via the $method()"
        );

        $this->setSetting('commandClasses', $commandClasses);
    }

    protected function confirmCommandClassesWrappingDirectory()
    {
        $this->setSetting('commandNamespace', $this->getSetting('baseNamespace'));

        $interfaceName = $this->getSetting('commandInterfaceName');

        if($this->confirmWithUser(
            label: "Should we create $interfaceName classes in a wrapping directory?",
            default: false,
        ))
        {
            $wrappingDirectoryName = $this->promptFor(
                label: "What should the wrapping directory be called?",
                default: "Commands",
                required: true
            );

            // check if pre-existing
            $commandNamespace = $this->getSetting('commandNamespace').'\\'.$wrappingDirectoryName;
            $this->updateSetting('commandNamespace', $commandNamespace);
        }
    }

    protected function confirmBaseCommandClass()
    {
        $baseCommandClassName = null;

        if($this->confirmWithUser(
            label: "Should we create a base command class?",
            default: false,
        ))
        {
            $baseCommandClassName = $this->promptFor(
                label: "What should the base Command class  be called?",
                default: "BaseCommand",
                required: true
            );

            // check if pre-existing
        }

        $this->setSetting('baseCommandName', $baseCommandClassName);

    }

    protected function handleInvokerSpecifics()
    {
        // Invoker can be the queue
        $this->confirmInvokerClassesWrappingDirectory();
        $this->confirmInvokerInterfaceName();
        $this->gatherInvokerClasses();
    }

    protected function confirmInvokerClassesWrappingDirectory()
    {
        $InvokerDirectoryNamespace = $this->getSetting('baseNamespace');

        if($this->confirmWithUser(
            label: "Should we wrap your Invoker classes in a wrapping directory?",
            default: false
        ))
        {
            $InvokerWrappingDirectoryName = $this->promptFor(
                label: "What should we call the wrapping directory?",
                default: "Invokers",
                required: true
            );

            // check if pre-existing

            $InvokerDirectoryNamespace = $InvokerDirectoryNamespace.'\\'.$InvokerWrappingDirectoryName;
        }

        $this->setSetting('InvokerDirectoryNamespace', $InvokerDirectoryNamespace);
    }

    protected function confirmInvokerInterfaceName()
    {
        $InvokerInterfaceName = $this->promptForClassName(
            label: "What should the interface that Invoker-type classes implement be called?",
            default: "IsInvoker",
            required: true
        );

        $this->setSetting('InvokerInterfaceName', $InvokerInterfaceName);
    }

    protected function gatherInvokerClasses()
    {
        $InvokerClasses = $this->captureAnswers(
            label: "Please provide a Invoker class name",
            required: false,
            hint: "These will be the classes that actually dispatch the commands"
        );

        $this->setSetting('InvokerClasses', $InvokerClasses);
    }

    protected function handleReceiverSpecifics()
    {
        $this->confirmReceiverClassesWrappingDirectory();
        $this->confirmReceiverInterfaceName();
        $this->gatherReceiverClasses();
    }

    protected function confirmReceiverClassesWrappingDirectory()
    {
        $receiverDirectoryNamespace = $this->getSetting('baseNamespace');

        if($this->confirmWithUser(
            label: "Should we wrap your receiver classes in a wrapping directory?",
            default: false
        ))
        {
            $receiverWrappingDirectoryName = $this->promptFor(
                label: "What should we call the wrapping directory?",
                default: "Receivers",
                required: true
            );

            // check if pre-existing

            $receiverDirectoryNamespace = $receiverDirectoryNamespace.'\\'.$receiverWrappingDirectoryName;

        }

        $this->setSetting('receiverDirectoryNamespace', $receiverDirectoryNamespace);
    }

    protected function confirmReceiverInterfaceName()
    {
        $receiverInterfaceName = $this->promptForClassName(
            label: "What should the interface that Receiver-type classes implement be called?",
            default: "IsReceiver",
            required: true
        );

        $this->setSetting('receiverInterfaceName', $receiverInterfaceName);
    }


    protected function gatherReceiverClasses()
    {
        $receiverClasses = $this->captureAnswers(
            label: "Please provide a Receiver class name",
            required: false,
            hint: "These will be the classes that handle the commands"
        );

        $this->setSetting('receiverClasses', $receiverClasses);
    }

    protected function process()
    {
        $this->createPatternDirectories();
        $this->createInterfaces();
        $this->createCommandClasses();
        $this->createInvokerClasses();
        $this->createReceiverClasses();
    }

    protected function createPatternDirectories()
    {
        info('Creating directories for the command pattern...');

        $basePath = $this->getSetting('basePath');
        $this->ensureDirectoryExists($basePath);

        $contractsFolder = $this->getSetting('interfacesFolderName');
        $contractsFolderNamespace = $this->getSetting('baseNamespace').'\\'.$contractsFolder;
        $contractsFolderPath = $this->getDirectoryPath($contractsFolderNamespace);

        $this->ensureDirectoryExists($contractsFolderPath);

        $this->setSetting('interfacesFolderNamespace', $contractsFolderNamespace);

        // Invokers
        $this->ensureDirectoryExists($this->getDirectoryPath($this->getSetting('InvokerDirectoryNamespace')));

        // receivers
        $this->ensureDirectoryExists($this->getDirectoryPath($this->getSetting('receiverDirectoryNamespace')));

        // commands
        $this->ensureDirectoryExists($this->getDirectoryPath($this->getSetting('commandNamespace')));

    }

    protected function createInterfaces()
    {
        $interfacesNamespace = $this->getSetting('interfacesFolderNamespace');
        
        // is_command
        $this->fileForGeneration = 'command/is_command';

        $className = $this->getSetting('commandInterfaceName');
        $isCommandMethod = $this->getSetting('commandMethod');

        $this->addScopedReplacement($className, 'commandMethod', $isCommandMethod);

        $this->createClassFile(
            className: $className,
            targetNamespace: $interfacesNamespace,
            stubPath: $this->getStub()
        );

        // is_receiver
        $this->fileForGeneration = 'command/is_receiver';

        $receiverClassName = $this->getSetting('receiverInterfaceName');

        $this->createClassFile(
            className: $receiverClassName,
            targetNamespace: $interfacesNamespace,
            stubPath: $this->getStub()
        );

        // is_Invoker
        $this->fileForGeneration = 'command/is_invoker';

        $InvokerClassName = $this->getSetting('InvokerInterfaceName');

        $this->addScopedReplacement($InvokerClassName, 'commandMethod', $isCommandMethod);

        $this->createClassFile(
            className: $InvokerClassName,
            targetNamespace: $interfacesNamespace,
            stubPath: $this->getStub()
        );
    }

    protected function createCommandClasses()
    {
        // set initial file for generation stub
        $this->fileForGeneration = 'command/parent_command';

        // set initial null base class namespace - overwritten if needed
        $baseClassNamespace = null;

        $interfacesNamespace = $this->getSetting('interfacesFolderNamespace');

        $commandNamespace = $this->getSetting('commandNamespace');
        $commandInterfaceNamespace = $interfacesNamespace.'\\'.$this->getSetting('commandInterfaceName');

        $receiverInterface = $this->getSetting('receiverInterfaceName');
        $receiverInterfaceNamespace = $interfacesNamespace.'\\'.$receiverInterface;

        // consider base command flag
        $createBaseCommand = (!is_null($this->getSetting('baseCommandName')));

        if(false !== $createBaseCommand)
        {
            // create base command
            $this->fileForGeneration = 'command/base_command';

            $className = $this->getSetting('baseCommandName');

            $this->addScopedDependency($className, $commandInterfaceNamespace, 'isCommandInterfaceName');
            $this->addScopedDependency($className, $receiverInterfaceNamespace, 'isReceiverInterfaceName');

            $this->createClassFile(
                className: $className,
                targetNamespace: $commandNamespace,
                stubPath: $this->getStub()
            );

            // set the file for generation for other command classes
            $this->fileForGeneration = 'command/child_command';

            $baseClassNamespace = $commandNamespace.'\\'.$className;
        }

        $this->loadInterface($commandInterfaceNamespace);
        $methodString = $this->generateMethods();

        foreach($this->getSetting('commandClasses') as $commandName)
        {
            if(!is_null($baseClassNamespace))
            {
                $this->addScopedDependency($commandName, $baseClassNamespace, 'baseCommandClassName');
            }

            $this->addScopedReplacement($commandName, 'methods', $methodString);
            $this->addScopedDependency($commandName, $commandInterfaceNamespace, 'isCommandInterfaceName');
            $this->addScopedDependency($className, $receiverInterfaceNamespace, 'isReceiverInterfaceName');

            $this->createClassFile(
                className: $commandName,
                targetNamespace: $commandNamespace,
                stubPath: $this->getStub()
            );
        }
    }

    protected function createInvokerClasses()
    {
        // InvokerDirectoryNamespace
        $InvokerNamespace = $this->getSetting('InvokerDirectoryNamespace');

        $interfacesNamespace = $this->getSetting('interfacesFolderNamespace');

        // isInvokerInterface
        $InvokerInterface = $this->getSetting('InvokerInterfaceName');
        $InvokerInterfaceNamespace = $interfacesNamespace.'\\'.$InvokerInterface;

        // Command namespace
        $commandInterfaceNamespace = $interfacesNamespace.'\\'.$this->getSetting('commandInterfaceName');

        $this->fileForGeneration = 'command/invoker';

        foreach($this->getSetting('InvokerClasses') as $InvokerClass)
        {
            $this->addScopedDependency($InvokerClass, $commandInterfaceNamespace, $commandInterfaceNamespace);
            $this->addScopedDependency($InvokerClass, $InvokerInterfaceNamespace, 'isInvokerInterfaceName');

            $this->createClassFile(
                className: $InvokerClass,
                targetNamespace: $InvokerNamespace,
                stubPath: $this->getStub()
            );

        }
    }

    protected function createReceiverClasses()
    {
        $receiverNamespace = $this->getSetting('receiverDirectoryNamespace');

        $interfacesNamespace = $this->getSetting('interfacesFolderNamespace');

        // isReceiverInterface
        $receiverInterface = $this->getSetting('receiverInterfaceName');
        $receiverInterfaceNamespace = $interfacesNamespace.'\\'.$receiverInterface;

         // Command namespace
         $commandInterfaceNamespace = $interfacesNamespace.'\\'.$this->getSetting('commandInterfaceName');


        $this->fileForGeneration = 'command/receiver';

        foreach($this->getSetting('receiverClasses') as $receiverClass)
        {
            $this->addScopedDependency($receiverClass, $commandInterfaceNamespace, $commandInterfaceNamespace);
            $this->addScopedDependency($receiverClass, $receiverInterfaceNamespace, 'isReceiverInterfaceName');

            $this->createClassFile(
                className: $receiverClass,
                targetNamespace: $receiverNamespace,
                stubPath: $this->getStub()
            );
        }
    }
}
