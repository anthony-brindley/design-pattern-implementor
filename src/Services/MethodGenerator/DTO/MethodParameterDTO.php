<?php

namespace AnthonyBrindley\DesignPatternImplementor\Services\MethodGenerator\DTO;

use InvalidArgumentException;

class MethodParameterDTO
{
    public string $name;
    public ?string $type = null;
    public mixed $default = null;
    public bool $defaultSet = false;
    public array $attributes = []; // Store PHP attributes for the parameter

    public array $dependencies = [];
    private function __construct(string $name)
    {
        $this->name = $name;
    }

    public static function make(string $name): self
    {
        return new self($name);
    }

    public function type(string $type): self
    {
        if($this->isBasicType($type))
        {
            $this->type = $type;
            return $this;
        }

        if(
            $this->isInterfaceType($type) ||
            $this->isClassType($type)
        )
        {
            $this->dependencies[] = $type;
            $typeName = class_basename($type);
            $this->type = $typeName;

            return $this;
        }

        throw new InvalidArgumentException('Unable to resolve property type. Please review');
    }

    private function isBasicType(string $type): bool
    {
        $basicTypes = ['int', 'string', 'float', 'bool', 'array', 'callable', 'iterable', 'object', 'mixed'];
        return in_array(strtolower($type), $basicTypes, true);
    }

    private function isClassType(string $fullyQualifiedName): bool
    {
        return class_exists($fullyQualifiedName);
    }

    private function isInterfaceType(string $fullyQualifiedName): bool
    {
        return interface_exists($fullyQualifiedName);
    }

    public function setDefault(mixed $default): self
    {
        // validation needed here
        $this->default = $default;
        $this->defaultSet = true;
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

        $type = $this->type ? "{$this->type} " : '';

        $defaultVal = match($this->default){
            'null' || (null && $this->defaultSet) => "null",
            true || "true" => "true",
            false || "false" => "false",
            default => $this->default
        };

        if($defaultVal === 'null')
        {
            $type = '?'.$type;
        }

        //$default = $this->defaultSet ? " = " . var_export($this->default, true) : '';
        

        


        return "{$attributes}{$type}\${$this->name}{$defaultVal}";
    }

    private function formatAttributes(): string
    {
        return implode(PHP_EOL, array_map(fn($attr) => "#[{$attr}]", $this->attributes)) . PHP_EOL;
    }

    public function getDependencies(): array
    {
        return $this->dependencies;
    }
}
