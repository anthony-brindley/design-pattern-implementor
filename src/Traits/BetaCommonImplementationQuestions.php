<?php

namespace AnthonyBrindley\DesignPatternImplementor\Traits;

use AnthonyBrindley\DesignPatternImplementor\Traits\PromptsUsers;

trait BetaCommonImplementationQuestions
{
    use PromptsUsers;
    
    /**
     * DOMAIN SPECIFICS
     */

    public function handleDomainSpecifics()
    {
        $action = $this->validateNewDomainAttempt();
        $failureMessage = "That domain already exists. Please review and try again.";
        
        if($addToExisting = $this->confirmAddToExistingDomain())
        {
            $action = $this->validateExistingDomainAttempt();
            $failureMessage = "That domain doesn't exist. Please review and try again.";
        }

        $this->setSetting('addToExisting', $addToExisting);

        $this->attempt(
            action: function() use ($action)
                {
                    return $action();
                },
            failureMessage: $failureMessage,
            onSuccess: function($data){
                $this->processValidDomain($data);
            }
        );
    }

    protected function confirmAddToExistingDomain()
    {
        return $this->confirmWithUser(
            label: "Add new functionality to an existing domain?",
            default: false,
            hint: "If yes, you'll be asked to provide the domain location"
        );
    }

    protected function promptExistingDomain()
    {
        return $this->promptFor(
            label: "Please provide the namespace to the target domain",
            placeholder: "App\Domains",
            required: true,
        );   
    }

    protected function promptNewDomain(): string
    {
        return $this->promptFor(
            label: "Please provide the namespace to the new domain",
            placeholder: "App\Domains\SomeDomain",
            required: false,
        );  
    }

    private function validateExistingDomainAttempt(): array|false
    {
        $domain = $this->promptExistingDomain();
        $domainPath = $this->getDirectoryPath($domain);

        return $this->checkDirectoryExists($domainPath) ? compact('domain', 'domainPath') : false;
    }

    private function validateNewDomainAttempt(): array|false
    {
        $domain = $this->promptNewDomain();
        $domainPath = $this->getDirectoryPath($domain);

        return $this->folderDoesntAlreadyExist($domainPath) ? compact('domain', 'domainPath') : false; 
    }

    private function processValidDomain(array $data): void
    {
        info("Verified. Moving on...");

        $domain = str_ends_with($data['domain'], '\\') ? rtrim($data['domain'], '\\') : $data['domain'];
        $this->setSetting('baseNamespace', $domain);
        $this->setSetting('basePath', $data['domainPath']);
    }

    /**
     * FOLDER DETAILS
     */
    protected function handleFolderNames()
    {
        // not sure how to handle this one on a per-pattern basis

        // context - Requires context setting in the command?
        /**
         * Adaptor pattern
         * - can we check for the existence of an Adaptors folder and ask that question?
         * - adaptors can either be added to a folder within an existing domain OR added to a new folder. Easiest way to determine this is to 
         * ask with suggestion based on previous answers......
         * Decorator Pattern
         * FacadePattern
         * ProxyPattern
         * 
         * if we are NOT adding to an existing domain...
         * - ask for the new target domain
         * - check if it's last directory matches the pattern name (/Adaptors for the adaptor pattern for instance)
         * - if so, skip asking for a context ELSE ask whether they want to create a folder to contain Adaptors inside
         * - if not
         * 
         * Most other patterns
         * - will want a wrapping context folder to keep things together
         */
        $this->handleContextDetails();

        // requires interfaces setting?
        // interfaces
        // if command doesnt require interface generation, we can skip these questions

        // other
        // some other patterns may require other folders e.g a States folder for the state pattern
    }

    // Handle folder creation. 

    protected function createPatternDirectories(): void
    {
        info('Creating directories for the pattern...');

        $this->ensureDirectoryExists($this->getSetting('basePath'));
        $this->generateInterfacesDirectory();
        //$this->ensureDirectoryExists($statesDirectory);
        $this->generateOtherDirectory('states');
    }


    protected function generateInterfacesDirectory()
    {
        $this->ensureDirectoryExists($this->captureFolderDetails('interfaces'));
    }

    protected function generateOtherDirectory(string $key)
    {
        $this->ensureDirectoryExists($this->captureFolderDetails($key));
    }

    protected function captureFolderDetails(string $key)
    {
        $base = $this->getSetting('baseNamespace');
        [$namespace, $directoryPath] = $this->getAppendedNamespacePathArray($base, $key);

        $this->setSetting($key.'Namespace', $namespace);
        $this->setSetting($key.'DirectoryPath', $directoryPath);

        return $directoryPath;
    }

    protected function handleInterfaceSpecifics()
    {

    }

    protected function createInterface()
    {
        // confirm name (get suggestion)

        // 
    }    

}
