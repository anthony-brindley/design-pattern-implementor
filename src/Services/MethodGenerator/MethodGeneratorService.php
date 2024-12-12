<?php

namespace AnthonyBrindley\DesignPatternImplementor\Services\MethodGenerator;

use AnthonyBrindley\DesignPatternImplementor\Services\MethodGenerator\DTO\MethodDTO;
use InvalidArgumentException;

class MethodGeneratorService
{
    protected array $methodData = [];

    public function processMethod(MethodDTO $methodDTO)
    {
        $this->methodData[$methodDTO->name] = [
            'dependencies' => $methodDTO->getDependencies() ?? [],
            'class_string' => $methodDTO->toString() ?? '',
            'interface_string' => $methodDTO->toInterfaceString() ?? '',
        ];
        
        return $this;
    }

    public function getDependencies(string $methodName): array
    {
        $this->checkMethodProcessed($methodName);
        
        return $this->methodData[$methodName]['dependencies'];
    }

    // should help us handle imports etc
    public function getMethodString(string $methodName): string
    {
        $this->checkMethodProcessed($methodName);

        return $this->methodData[$methodName]['class_string'];
    }

    public function getInterfaceMethodString(string $methodName): string
    {
        $this->checkMethodProcessed($methodName);

        return $this->methodData[$methodName]['interface_string'];
    }
    
    protected function checkMethodProcessed(string $methodName)
    {
        if(!isset($this->methodData[$methodName]))
        {
            throw new InvalidArgumentException('No method with that name processed');
        }
    }

    public function refresh()
    {
        $this->methodData = [];
        return $this;
    }
}
