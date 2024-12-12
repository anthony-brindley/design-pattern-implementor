<?php

namespace AnthonyBrindley\DesignPatternImplementor\Services\MethodGenerator\DTO;

use Illuminate\Support\Str;
use InvalidArgumentException;

class MethodDTO
{
    public array $parameters = [];
    public ?string $returnType = null;
    public string $body = '';
    public bool $isStatic = false;
    public array $attributes = []; // Store PHP attributes for the method
    public bool $generatingInterface = false;

    public array $dependencies = [];

    public bool $final = false;

    private function __construct(
        public string $name, 
        public string $visibility
    )
    {
        if (!in_array($visibility, ['public', 'protected', 'private'], true)) {
            throw new InvalidArgumentException('Invalid visibility: ' . $visibility);
        }

        $this->name = $this->formatName($name);
        $this->visibility = $visibility;
    }

    public static function public(string $name): self
    {
        return new self($name, 'public');
    }

    public static function protected(string $name): self
    {
        return new self($name, 'protected');
    }

    public static function private(string $name): self
    {
        return new self($name, 'private');
    }

    public function final(): self
    {
        $this->final = true;

        return $this;
    }

    public function parameter(MethodParameterDTO $parameter): self
    {
        $this->parameters[] = $parameter;
        
        // Merge dependencies from the parameter
        $this->dependencies = array_merge($this->dependencies, $parameter->getDependencies());

        return $this;
    }

    public function returnType(?string $returnType): self
    {
        $this->returnType = $returnType;

        // Extract dependencies from return type
        if ($returnType) {
            foreach (explode('|', $returnType) as $type) {
                $type = trim($type);
                if ($this->isClassType($type)) {
                    $this->dependencies[] = $type;
                }
            }
        }

        return $this;
    }

    public function body(string $body): self
    {
        if ($this->generatingInterface) {
            throw new InvalidArgumentException('Static methods cannot have a body when generating an interface.');
        }
        $this->body = $body;
        return $this;
    }

    public function static(bool $isStatic = true): self
    {
        $this->isStatic = $isStatic;
        return $this;
    }

    public function addAttribute(string $attribute): self
    {
        $this->attributes[] = $attribute;
        return $this;
    }

    public function toString(): string
    {
        $attributes = $this->formatAttributes();
        $staticKeyword = $this->isStatic ? 'static ' : '';
        $parameterList = implode(', ', array_map(fn($param) => $param->toString(), $this->parameters));
        $returnTypeName = class_basename($this->returnType);
        $returnType = $this->returnType ? ": {$returnTypeName}" : '';
        $final = (false === $this->final) ? '' : ' final';

        return <<<EOT
             {$attributes}{$final} {$this->visibility} {$staticKeyword}function {$this->name}({$parameterList}){$returnType}
             {
                 {$this->body}
             }
        EOT;
    }

    public function toInterfaceString(): string
    {
        $this->generatingInterface = true;

        if($this->visibility !== 'public') return '';

        $attributes = $this->formatAttributes();
        $staticKeyword = $this->isStatic ? 'static ' : '';
        $parameterList = implode(', ', array_map(fn($param) => $param->toString(), $this->parameters));
        $returnType = $this->returnType ? ": {$this->returnType}" : '';

        return "\t{$attributes}{$this->visibility} {$staticKeyword}function {$this->name}({$parameterList}){$returnType};";
    }

    private function formatAttributes(): string
    {
        return implode(PHP_EOL, array_map(fn($attr) => "#[{$attr}]", $this->attributes)) . PHP_EOL;
    }

    private function formatName(string $name)
    {
        return Str::camel($name);
    }

    private function isClassType(string $type): bool
    {
        $basicTypes = ['int', 'string', 'float', 'bool', 'array', 'callable', 'iterable', 'object', 'mixed', 'null', 'void'];
        return !in_array(strtolower($type), $basicTypes, true);
    }

    public function getDependencies(): array
    {
        return array_unique($this->dependencies);
    }
}
