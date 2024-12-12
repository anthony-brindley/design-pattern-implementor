<?php

namespace AnthonyBrindley\DesignPatternImplementor\Services\MethodGenerator;

use AnthonyBrindley\DesignPatternImplementor\Services\MethodGenerator\DTO\MethodDTO;

class InterfaceGeneratorService
{
    public function generateInterface(string $interfaceName, array $methods): string
    {
        $methodStrings = array_map(fn(MethodDTO $method) => '    ' . $method->toInterfaceString(), $methods);
        $methodBlock = implode(PHP_EOL, $methodStrings);

        return <<<EOT
        <?php

        interface {$interfaceName}
        {
        {$methodBlock}
        }
        EOT;
    }
}
