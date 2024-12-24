<?php

namespace AnthonyBrindley\DesignPatternImplementor\Commands;

use Illuminate\Support\Str;

use function Laravel\Prompts\{select, info};










class ImplementTemplatePatternCommand extends BaseImplementor
{
    protected $signature = 'implement-pattern:template';

    protected static string $patternName = 'template';

    public function handle(): void
    {
        // allows us to define an abstract clas to act as the 'structure'
        // this should define the steps of our process and the necessary method names within it
        // this will define a templateMethod method which cannot be extended or overwritten to act as the 'list of steps'
        // the other methods can be declared abstract or have default implementations

        $this->handleDomainSpecifics();

        // define abstract class specifics
        $this->handleAbstractClassSpecifics();

        // capture any concretions
        $this->captureConcretions();

        // process
        $this->process();
          
    }

    protected function handleAbstractClassSpecifics()
    {
        // specify abstract class name
        $this->promptForAbstractClassName();

        // specify templateMethod method name
        $this->promptForTemplateMethodName();
        
        // make templateMethod final?
        $this->confirmMakeTemplateMethodFinal();

        // define steps
        $this->captureSteps();
    }

    protected function promptForAbstractClassName()
    {
        $this->setSetting('abstractClassName', $this->promptForClassName(
            label: "What should we call your base template class",
            required: true,
            hint: "This is the class that all relevant concretions will extend"
        ));
    }

    protected function promptForTemplateMethodName()
    {
        $this->setSetting('templateMethodName', $this->promptForCamel(
            label: "What should we call the main template method?",
            default: "templateMethod",
            required: true
        ));
    }

    protected function confirmMakeTemplateMethodFinal()
    {
        $templateMethodName = $this->getSetting('templateMethodName');
        
        $this->setSetting('isTemplateMethodFinal', $this->confirmWithUser(
            label: "Should we make the $templateMethodName a non-editable method?",
            default: true,
        ));
    }

    protected function captureSteps()
    {
        $steps = [];

        while($step = $this->promptForStepName())
        {
            if(array_key_exists($step, $steps)){
                info('Already a submission with this value, please try again...');
            } else {
                $stepType = $this->confirmMethodType($step);

                if('base-implementation' === $stepType)
                {
                    $body = $this->captureMethodBody();
                }

                $steps[$step] = [
                    'type' => $stepType,
                    'body' => $body ?? null
                ];
            }

            
        }


        // foreach step:
       ////    $this->promptForStepName();
            // ensure unique
            // should this method be abstract, base or a hook?
        ////    $this->confirmMethodType();
            // if base, we should capture a body
       ///     $this->captureMethodBody();

            // if a hook, we should add to hooks list and provide a null implementation in the abstract

    }

    protected function promptForStepName()
    {
        return $this->promptFor(
            label: "Please provide a method for the next step of your process",
            required: false
        );
    }

    protected function confirmMethodType(string $methodName)
    {
        return select(
            label: "What type of method should the $methodName method be?",
            options: ['abstract', 'hook', 'base-implementation'],
            default: 'abstract',
            required: true
        );
    }

    protected function captureMethodBody()
    {
        return $this->promptFor(
            label: "Please provide the method body as a string",
            required: true,
            default: "// populate this"
        );
    }

    protected function captureConcretions()
    {
        $this->attempt(
            action: fn() => {},
            onSuccess: function(){

            }
        );
        $this->promptForConcretion();
    }

    protected function promptForConcretion()
    {

    }
    
    protected function process()
    {
        // create directories 

        // create classes
    }

    protected function createPatternDirectories()
    {

    }

    protected function createAbstractClass()
    {

    }

    protected function createConcretions()
    {

    }
}
