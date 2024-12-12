<?php

namespace AnthonyBrindley\DesignPatternImplementor\Services\MethodGenerator\DTO;

use InvalidArgumentException;

class PropertyDTO
{
    public string $name;
    public bool $isStatic = false;
    public string $visibility;
    public mixed $default = null;
    public bool $defaultSet = false;
    public mixed $type = null;

    public array $dependencies = [];

    private function __construct(string $name, string $visibility, mixed $default = null)
    {
        if (!in_array($visibility, ['public', 'protected', 'private'], true)) {
            throw new InvalidArgumentException('Invalid visibility: ' . $visibility);
        }

        $this->name = $name;
        $this->visibility = $visibility;
        $this->default = $default;
    }

    public static function public(string $name, mixed $default = null): self
    {
        return new self($name, 'public', default: $default);
    }

    public static function protected(string $name,  mixed $default = null): self
    {
        return new self($name, 'protected', default: $default);
    }

    public static function private(string $name, mixed $default = null): self
    {
        return new self($name, 'private', default: $default);
    }

    public function static()
    {
        $this->isStatic = true;
        return $this;
    }

    public function setDefault(mixed $default = null)
    {
        $this->default = $default ?? 'null';
        $this->defaultSet = true;

        return $this;
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

    public function toString()
    {
        $visibility = $this->visibility;
        $static = ($this->isStatic) ? ' static' : '';
        $type = (!empty($this->type)) ? $this->type : '';
        $name = $this->name;

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

        
        $default = ($this->defaultSet) ? " = {$defaultVal}" : ''; // should allow null

        return <<<EOT
        {$visibility}{$static} {$type} \${$name}{$default};\n
        EOT;
    }

    public function getDependencies(): array
    {
        return array_unique($this->dependencies);
    }

}
