<?php

namespace AnthonyBrindley\DesignPatternImplementor\Traits;

use InvalidArgumentException;
use ReflectionClass;

trait GeneratesMethods
{
        protected array $methods = [];

        public function loadInterface(string $interfaceName): void
        {
            if (!interface_exists($interfaceName)) {
                throw new InvalidArgumentException("Interface $interfaceName does not exist.");
            }

            $reflection = new ReflectionClass($interfaceName);

            foreach ($reflection->getMethods() as $method) {
                $this->methods[] = [
                    'name'      => $method->getName(),
                    'parameters' => array_map(
                        fn($param) => [
                            'name' => $param->getName(),
                            'type' => $param->getType() ? $param->getType()->getName() : null,
                        ],
                        $method->getParameters()
                    ),
                    'returnType' => $method->getReturnType() ? $method->getReturnType()->getName() : null,
                ];
            }
        }

        public function generateMethods(): string
        {
            $code = "";

            foreach ($this->methods as $method) {
                $parameters = array_map(
                    fn($param) => ($param['type'] ? $param['type'] . ' ' : '') . '$' . $param['name'],
                    $method['parameters']
                );

                $returnType = $method['returnType'] ? ': ' . $method['returnType'] : '';
                $methodCode = "    public function {$method['name']}(" . implode(', ', $parameters) . ")$returnType \n\t{";
                $methodCode .= "        // TODO: Implement {$method['name']} logic.\n";
                $methodCode .= "    }\n\n";
                $code .= $methodCode;
            }

            return $code;
        }
    }
