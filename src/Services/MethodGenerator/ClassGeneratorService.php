<?php

namespace AnthonyBrindley\DesignPatternImplementor\Services\MethodGenerator;

use AnthonyBrindley\DesignPatternImplementor\Services\MethodGenerator\DTO\MethodDTO;
use AnthonyBrindley\DesignPatternImplementor\Services\MethodGenerator\DTO\PropertyDTO;
use AnthonyBrindley\DesignPatternImplementor\Traits\HandlesFileCreation;
use AnthonyBrindley\DesignPatternImplementor\Traits\HandlesStubPopulation;
use Illuminate\Support\Facades\File;
use InvalidArgumentException;

class ClassGeneratorService
{

    use HandlesFileCreation;
    use HandlesStubPopulation;
    
    public string $extends = '';
    public array $imports = [];

    public array $implements = [];

    public array $methods = [];

    public array $properties = [];

    public bool $final = false;

    private function __construct(
        protected string $name = '', 
        protected bool $abstract = false
    )
    {}

    public static function abstract(string $name): ClassGeneratorService
    {
        return new self(name: $name, abstract: true);
    }

    public static function new(string $name)
    {
        return new self(name: $name, abstract: false);
    }

    public function extends(string $class)
    {
        if(!empty($this->extends))
        {
            throw new InvalidArgumentException('Unable to extend more than one parent class.');
        }

        if(!class_exists($class))
        {
            throw new InvalidArgumentException('Parent class doesnt exist. Please review.');
        }

        $extendClassName = class_basename($class);

        $this->extends = $extendClassName;
        $this->imports[$extendClassName] = $class; 

        return $this;
    }

    public function implements(string $interface)
    {
        if(!interface_exists($interface))
        {
            throw new InvalidArgumentException('Interface specified doesnt exist.');
        }

        $interfaceName = class_basename($interface);

        $this->implements[$interfaceName] = $interfaceName;
        $this->imports[$interfaceName] = $interface; 

        return $this;
    }

    public function addMethods(array $methods)
    {
        foreach($methods as $method)
        {
            if($method instanceof MethodDTO)
            {
                $this->addMethod($method);
            }
        }
        
        return $this;
    }

    public function addMethod(MethodDTO $method)
    {    
        if(!array_key_exists($method->name, $this->methods))
        {
            $this->methods[$method->name] = $method;
        }

        return $this;
    }

    public function addProperty(PropertyDTO $property)
    {
        if(!array_key_exists($property->name, $this->properties))
        {
            $this->properties[$property->name] = $property;
        }

        return $this;
    }

    public function final()
    {
        $this->final = true;

        return $this;
    }

    public function generateClass(string $namespace)
    {
        $proposedNamespace = $namespace.'\\'.$this->name;
        if(class_exists($proposedNamespace))
        {
            throw new InvalidArgumentException('Class already exists in proposed location');
        }

        // we should call the methodgeneratorservice first. 
        // this should help us extract dependencies for import and from this
        // list we can create the import statements

        $methodBlock = $this->generateMethodBlock();
        //$namespace = $proposedNamespace;

        $className = $this->formatClassName($this->name);
        $abstract = ($this->abstract) ? 'abstract ' : '';

        $extends = $this->extends;
        $extendsBlock = (!empty($extends)) ? " extends {$extends}" : '';

        $properties = $this->properties;
        $propertyBlock = (!empty($properties)) ? $this->generatePropertyBlock() : '';
        
        $importBlock = $this->generateImportBlock();
        $implements = (!empty($this->implements)) ? ' implements '.implode(', ', array_values($this->implements)) : '';
        
        $final = (false === $this->final) ? '' : 'final ';

        $content = <<<EOT
        <?php

        namespace {$namespace};

        {$importBlock}

        {$final}{$abstract}class {$className}{$extendsBlock}{$implements}
        {
            {$propertyBlock}
            {$methodBlock}
        }
        EOT;

        $this->ensureDirectoryExists($this->getDirectoryPath($namespace));

        $path = $this->getDirectoryPath($proposedNamespace).".php";
        
        //dd('g', $path);
        File::put($path, $content);

        

        // $this->fileForGeneration = 'empty';

        // $this->createClassFile(
        //     className: $className,
        //     targetNamespace: $namespace,
        //     stubPath: $this->getStub()
        // );
    }

    protected function generateMethodBlock(): string
    {
        $methodGeneratorService = app(MethodGeneratorService::class);
        $methods = $this->methods;

        $methodBlock = "\t";

        foreach($methods as $method)
        {
            foreach($method->getDependencies() as $d)
            {
                $name = class_basename($d);
                $this->imports[$name] = $d;
            }
            $methodBlock .= "     ".$method->toString()."\n\n";
        }

        return $methodBlock;
    }

    protected function generatePropertyBlock(): string
    {
        $propertyBlock = '';

        foreach($this->properties as $property)
        {
            $propertyBlock .= $property->toString();
        }

        return $propertyBlock;
    }

    protected function generateImportBlock(): string
    {
        $importBlock = '';
        $importedKeys = [];
        foreach($this->imports as $k => $v)
        {
            if(!in_array($k, $importedKeys))
            {
                $importBlock .= "use ".$v.";\n";
            }
        }

        return $importBlock;
    }
}
