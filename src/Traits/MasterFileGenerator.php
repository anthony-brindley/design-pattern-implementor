<?php

namespace AnthonyBrindley\DesignPatternImplementor\Traits;

use AnthonyBrindley\DesignPatternImplementor\Traits\HandlesFileCreation;
use AnthonyBrindley\DesignPatternImplementor\Traits\HandlesStubPopulation;

trait MasterFileGenerator
{
    use HandlesStubPopulation, HandlesFileCreation, HandlesDomainSpecifics;

    public function handleContextSpecifics(?string $proposedContextName = null)
    {
        $adding = $this->getSetting('addToExisting');
        $basePath = $this->getSetting('basePath');

        $suggestedFolder = static::$defaultContext;

        if($adding)
        {
            // if we dont require a context
            if(!static::$requiresContext)
            {
                $proposedDirectoryPath = $basePath.'/'.$suggestedFolder;
                if($this->checkDirectoryExists($proposedDirectoryPath))
                {
                    $confirm = $this->confirmWithUser(
                        label: "Add files to existing $suggestedFolder folder?",
                        required: false
                    );

                    dd('hh', $confirm, $proposedDirectoryPath);
                }
            }
            // check to see if a 
        }


        if(!$adding)
        {

            // get basePath
            
            $last = collect(explode('/', $basePath))->last();

            // explode on /
            // get Last and compare
            if($last === $suggestedFolder)
            {
                // update namespace
dd('here1', $last, $suggestedFolder);
            } else {
                $namespace = $this->getSetting('baseNamespace').'\\'.$suggestedFolder;
                $folderName = $this->promptForClassName(
                    label: "Create $suggestedFolder Folder here?",
                    default: $namespace,
                    required: true
                );
                dd('here2', $last, $suggestedFolder, $folderName);
            }
            
            
        }

        if(!static::$requiresContext)
        {
            
            echo "doesnt require context. $adding";
        } else {
            echo "requires context. $adding";
        }
        //dd('here', $this);
    }
}
