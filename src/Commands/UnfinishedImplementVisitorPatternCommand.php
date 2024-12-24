<?php

namespace AnthonyBrindley\DesignPatternImplementor\Commands;

use Illuminate\Support\Str;

class UnfinishedImplementVisitorPatternCommand extends BaseImplementor
{
    protected $signature = 'implement-pattern:visitor';

    protected static string $patternName = 'visitor';

    public function handle(): void
    {
        $this->handleDomainSpecifics();

        $this->handleContextSpecifics();

        $this->handleContractsFolderDetails();

        // Essentially, this pattern encourages us to add a common method to each class that could be affected by some
        // new functionality and this new method should accept a 'visitor' class. This method essentially acts as a 'hook'
        // or 'port' so that we can add new functionality to an existing class without having to alter the classes we're adding
        // functionality to.

        // These 'Visitor' classes may need to interact with more than one other class in order to implement it's functionality
        // and each class that it needs to interact with should have it's own 'referenced' method on the Visitor class. 

        // EXAMPLE: Imagine that we have an interface that allows us to ensure a class within our application can accept a 'visitor'
        // We'll call it 'IsVisitable' and it enforces inclusion of an 'accept' method that takes a 'Visitor' class as it's param. 
        // In this example we'll call the visitable class 'Department' and this class enforeces an 'Entity' interface. From this setup
        // we could understandably assume that there may be other Entity type classes that also should be visitable - how about 'Employee'
        // for instance.

        // We've effectively opened the 'Entity' enforcing classes up to allow our Visitor class to work with them. So, imagine we have a class that's
        // responsible for helping generate data for reporting. This 'GenerateReport' class may need data from various sources and so would 
        // need to be able to interact with those classes in a clear and obvious way. 

        // We would create the class and ensure it enforced a 'IsVisitor' interface. This interface would in turn enforce methods that 
        // clearly communicate the classes that it 'visits':

        //  visitDepartment(IsVisitable $department)
        //  visitEmployee(IsVisitable $employee)

        // Given that these entities are different in terms of how you would imagine data is collated, granulated etc, each step of the reporting
        // process may need some individual handling of that data in order to make it useful for it's purpose. For example, a 'Department' would suggest
        // that it works with multiple employees and therefore any data provided by it may need granulating down to a comparible 'single employee'
        // level that would work well with the 'Employee' class's data. 

        // This pattern allows us to provide a common interface into classes that could be potentially un-related, but separates any interactions 
        // with them away from being part of those classes, and over into the class that's actually interacting with them meaning that you provide
        // essentially unrestricted flexibility with those interactions. 

        // - You could have multiple report generating classes that interact with totally different, partially similar or the the same datasets but handles
        // the data received as part as that dataset completely independantly

        // - You could provide decorator/facade/proxy type functionality to any number of Visitable classes and easy manage any specific details within the 
        // Visitor class and away from the extended classes meaning that you completely encapsulate the new functionality


        ///////

        // IN ORDER TO MAKE THIS WORK

        // 1. We need to establish either an existing Interface or a new one with a corresponding method that acts as the port
            // Q: Is there an existing interface that allows your target classes to be 'Visitable'?
                // Y: Please provide a reference to that interface - this should be validated and allow the user 3 attempts to set it correctly
                    // Upon validation we should assess the methods within the interface and list the methods within it to allow the user to select 
                    // which one should be the method that allows access (via select prompt). From this, we should be able to infer an interface for the 'IsVisitor'
                    // class. If the user doesnt want to select an existing method, they should be able to provide a new method along with a 'IsVisitor' interface name. 
                        // Existing Method Selection: 
                        // New Method Selection: We should see if we can add the new method, along with interface dependency, to the interface class. This will then prompt
                        // all classes currently implementing that interface to add the new method. (I wonder if we can somehow get a list?)
                // N: Provide a name for the new 'IsVisitable' interface as well as a method name to act as the port. Validate that this is a unique interface name
                    // Provide a name for the 'IsVisitor' interface and again, check it's unique

            // Q: Provide the namespaces of any classes that you want to be visitable. If class doesnt exist, queue it for creation, if it does exist, check whether it 
            // implements the required interface and add relevant method if necessary. 

            // Q: Ask for the class name for the new IsVisitor class. At this point, we should be able to create the necessary methods in the class based on the classes
            // that should be visitable. ($reportGenerator->visitDepartment($this) or $reportGenerator->visitEmployee($this));

            // QUERY - should we offer a follow-up command that allows users to generate IsVisitor classes on their own based on an existing setup? i.e a different report generating class?

            // MVP version
            // Declare the 'IsVisitor' interface & initial Visitor class name
            $this->handleIsVisitorInterfaceDetails();
            // Capture the 'IsVisitable' classes so we can generate the concrete methods in the IsVisitor interface.
            $this->handleVisitableClasses();
            // Declare or reference the 'IsVisitable' interface and it's acceptance method.
            $this->handleIsVisitableInterfaceDetails();
            // Ask if there is a base class where we can quickly add necessary method to all related classes quickly. If yes, see if we can do it





        $this->handleVisitorSpecifics();

        // $this->generateVisitorClass();
    }

    protected function handleIsVisitorInterfaceDetails()
    {
        
        $this->handleIsVisitorInterfaceName();
        $this->handleVisitorClassName();

        
    }

    protected function handleIsVisitorInterfaceName()
    {
        $this->attempt(
            action: function(){
                $name = $this->promptIsVisitorInterface();

                $directory = $this->getSetting('interfacesFolderNamespace');
                $namespace = $directory.'\\'.$name;

                if($this->checkInterfaceExists($namespace)) return false;

                return $name;
            },
            onSuccess: function($name){
                $this->setSetting('isVisitorInterfaceName', $name);
            }
        );
    }

    protected function promptIsVisitorInterface(): string
    {
        $context = $this->getSetting('context');
        $suggestedName = "Is{$context}Visitor";

        return $this->promptForClassName(
            label: "What should the interface that denotes a class that 'can visit' be called?",
            default: $suggestedName,
            required: true
        );
    }

    protected function handleVisitorClassName()
    {
        $this->attempt(
            action: function(){
                $name = $this->promptVisitorClassName();

                $directory = $this->getSetting('baseNamespace');
                $path = $directory.'\\'.$name;

                if($this->checkClassExists($path)) return false;

                return $name;
            },
            onSuccess: function($name)
            {
                $this->setSetting('visitorClassName', $name);
            }
        );
    }

    protected function promptVisitorClassName(): string
    {
        $interfaceName = $this->getSetting('isVisitorInterfaceName');

        return $this->promptForClassName(
            label: "What should the initial class that implements your $interfaceName interface be called?",
            placeholder: "This is the class that will encapsulate all the processing logic.",
            required: true
        );
    }

    protected function handleVisitableClasses()
    {
        $this->captureVisitableClasses();
    }

    protected function captureVisitableClasses()
    {
        $classes = [];

        while($answer = $this->promptVisitableClass())
        {
            
            
            if(!$this->checkClassExists($answer))
            {
                if($confirm = $this->confirmWithUser(
                    label: "The class $answer doesn't exist. Create it?",
                    required: false,
                    default: true,
                ))
                {
                    $classes['create'][] = $answer;
                }
            } else {
                $classes['reference'][] = $answer;
            }
        }

        $this->setSetting('visitableClasses', $classes);
    }

    protected function promptVisitableClass()
    {
        return $this->promptForClassName(
            label: "Please reference a 'visitable' class that your functionality should interact with...",
            placeholder: "",
            required: false
        );
    }

    protected function handleIsVisitableInterfaceDetails()
    {
        $this->handleIsVisitableInterfaceName();

        $this->handleVisitableMethodSpecifics();
    }

    protected function handleIsVisitableInterfaceName()
    {
        $this->attempt(
            action: function(){
                $name = $this->promptIsVisitableInterface();

                $directory = $this->getSetting('interfacesFolderNamespace');
                $namespace = $directory.'\\'.$name;

                if($this->checkInterfaceExists($namespace)) return false;

                return $name;
            },
            onSuccess: function($name){
                $this->setSetting('isVisitableInterfaceName', $name);
            }
        );

        dd('here2', $this->settings);
    }

    protected function promptIsVisitableInterface()
    {
        $context = $this->getSetting('context');
        $suggestedName = "Is{$context}Visitable";

        return $this->promptForClassName(
            label: "What should the interface that denotes a class that 'can be visited' be called?",
            default: $suggestedName,
            required: true
        );
    }

    protected function handleVisitableMethodSpecifics(): void
    {
        $method = $this->promptFor(
            label: "What method should visitable classes implement?",
            default: 'accept',
            required: true
        );

        $this->setSetting('isVisitableMethod', $method);
    }

    protected function handleIsVisitedMethodSpecifics()
    {
        // responsible for generating the methods to feature on the Visitor class
        $visitableClasses = $this->getSetting('visitableClasses');
        // flatten these
        
        // Get classnames
        $classNames = [];

        foreach($visitableClasses as $c)
        {
            $classNames[] = class_basename($c);
        }

        // generate methodNames
        $methodNames = [];

        foreach($classNames as $name)
        {
            $methodNames[$name] = Str::camel("visit{$name}");
        }

        // generate interface method string
        $string = '';

        $isVisitorInterfaceName = $this->getSetting('isVisitorInterfaceName');
        foreach($methodNames as $m)
        {
            $string .= "\t public function {$m}({$isVisitorInterfaceName} $visitor)"
        }
    }

    


    protected function handleVisitorSpecifics()
    {
        if($this->confirmWithUser(label: $this->trans('confirm.target-interface')))
        {
            $this->handleTargetInterfaceDetails();
        }

        $this->validateProposedVisitorClass();
    }
    
    protected function validateProposedVisitorClass()
    {
        $proposedVisitorClass = $this->promptVisitorClass();

        
        $proposedVisitorClassPath = $this->getSetting('basePath').'/'.$proposedVisitorClass.'.php';


        if($this->checkFileExists($proposedVisitorClassPath))
        {
            throw new \Exception(__('design-pattern-implementor::common.error.file-exists', ['fileName' => $proposedVisitorClass.'.php']));
        }

        $this->setSetting('visitorClassName', $proposedVisitorClass);
        $this->setSetting('visitorClassNamespace', $this->getSetting('basePath'));
    }

    protected function promptVisitorClass()
    {
        $default = '';

        if($this->isSet('targetInterfaceNamespace'))
        {
            $interfaceName = class_basename($this->getSetting('targetInterfaceNamespace'));
            $default = $interfaceName.'Visitor';
        }

        return $this->promptForClassName(
            label: $this->trans('prompt.visitor-class'),
            required: true,
            default: $default
        );
    }

    protected function generateVisitorClass()
    {
        $this->fileForGeneration = 'visitor/visitor';
        $className = $this->getSetting('visitorClassName');

        if($this->isSet('targetInterfaceNamespace'))
        {
            $interfaceNamespace = $this->getSetting('targetInterfaceNamespace');
            $this->addScopedDependency($className, $interfaceNamespace, 'TargetInterface');
            $this->addScopedReplacement($className, 'implements', 'implements');

            $this->loadInterface($interfaceNamespace);
            $methods = $this->generateMethods();

            $this->addScopedReplacement($className, 'methods', $methods);
        } else {
            $this->addScopedReplacement($className, 'TargetInterface', '');
            $this->addScopedReplacement($className, 'implements', '');
        }

        $this->createClassFile(
            $className,
            $this->getSetting('baseNamespace'),
            $this->getStub()
        );

        info($this->trans('success'));
    }

    
}
