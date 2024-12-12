<?php

namespace AnthonyBrindley\DesignPatternImplementor\Traits;

use Illuminate\Support\Str;
use InvalidArgumentException;
use ReflectionClass;

trait HandlesMethodGeneration
{
        protected array $methods = [];
        protected array $interfaceMethods = [];

        protected array $possibleImports = [];

        public function loadInterface(string $interfaceName): void
        {
            if (!interface_exists($interfaceName)) {
                throw new InvalidArgumentException("Interface $interfaceName does not exist.");
            }

            $reflection = new ReflectionClass($interfaceName);

            foreach ($reflection->getMethods() as $method) {
                $this->methods[] = [
                    'static'    => $method->isStatic(),
                    'name'      => $method->getName(),
                    'parameters' => array_map(
                        fn($param) => [
                            'name' => $param->getName(),
                            'type' => $param->getType() ? $this->getTypeDetails($param->getType()->getName()) : null,
                        ],
                        $method->getParameters()
                    ),
                    'returnType' => $method->getReturnType() ? $this->getTypeDetails($method->getReturnType()->getName()) : null,
                ];
            }
        }

        /**
         * If Reflection has returned a full namespaced string to a class, this will extract the classname and add the namespace to the
         * possibleImports array (keyed by the classname)
         * 
         * @param string $fullTypeReference
         * @return string
         */
        protected function getTypeDetails(?string $fullTypeReference = null)
        {
            if(is_null($fullTypeReference)) return null;
            if(!str_contains($fullTypeReference, '\\')) return $fullTypeReference;

            $type = $fullTypeReference;
            $pieces = collect(explode('\\', $type));
            $paramType = $pieces->last();

            $this->possibleImports[$paramType] = $fullTypeReference;

            return $paramType;
        }

        public function generateMethods(): string
        {
            $code = "";

            foreach ($this->methods as $method) {
                $parameters = array_map(
                    fn($param) => ($param['type'] ? $param['type'] . ' ' : '') . '$' . $param['name'],
                    $method['parameters']
                );

                $static = ($method['static']) ? 'static ' : '';

                $returnType = $method['returnType'] ? ': ' . $method['returnType'] : '';
                $methodCode = "    public {$static}function {$method['name']}(" . implode(', ', $parameters) . ")$returnType \n\t{\n\t";
                $methodCode .= "        // TODO: Implement {$method['name']} logic.\n";
                $methodCode .= "    }\n\n";
                $code .= $methodCode;
            }

            return $code;
        }

        public function generateInterfaceMethods(): string
        {
            $code = "";

            foreach($this->interfaceMethods as $method)
            {
                $code .= "\tpublic function {$method}();\n";
            }

            return $code;
        }

        public function generateMethodString(string $methodName, ?string $returnType = null)
        {
            $returnType = (empty($returnType)) ? '' : ': '.$returnType;
            $methodName = Str::camel($methodName);
    
            return "\tpublic function $methodName(){$returnType}\n\t{\n\t\t//populate this\n\t}\n\n";
        }
    }
