<?php

namespace AnthonyBrindley\DesignPatternImplementor\Traits;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

trait HandlesStubPopulation
{
    public function getStubsFolderPath(?string $subfolder = null): string
    {
        $basepath = dirname(__DIR__, 2) . '/stubs';
        return $subfolder ? "$basepath/$subfolder" : $basepath;
    }

    private function getClassName(string $pathOrNamespace): string
    {
      return class_basename($pathOrNamespace);
    }

    public function generateReplacements(string $className)
    {
      $className = $this->formatClassName($className);

      $importString = $this->generateImports($className);
  
      $replacements = $this->generateReplacementsArray($className);
      $replacements["imports"] = $importString;
  
      // handle methods
      if (!isset($replacements["methods"]) || empty($replacements["methods"])) {
        $replacements["methods"] = "";
      }
  
      return $replacements;
    }
  
    protected function generateImports(string $className)
    {
      $generalImports = $this->imports;
      $extraImports = $this->scopedImports[$className] ?? [];
  
      $imports = array_merge($generalImports, $extraImports);
  
      $importString = "";
      foreach ($imports as $import) {
        $importString .= "use " . $import . ";\n";
      }
  
      return $importString;
    }
  
    protected function generateReplacementsArray(string $className): array
    {
      $replacements = $this->replacements;
  
      if (!isset($this->scopedReplacements[$className])) {
        return $replacements;
      }
  
      $scopedReplacements = $this->scopedReplacements[$className];
  
      foreach ($scopedReplacements as $k => $v) {
        if (array_key_exists($k, $replacements)) {
          continue;
        } else {
          $replacements[$k] = $v;
        }
      }
  
      return $replacements;
    }

    protected function getStub(): string
    {
        if (!empty($this->fileForGeneration)) {
            return $this->getStubsFolderPath() . '/' . $this->fileForGeneration . ".php.stub";
        }

        throw new \RuntimeException('No file specified for generation. Set $fileForGeneration in the command.');
    }

    protected function populateStub(string $stubPath, array $replacements): string
    {
      $stub = File::get($stubPath);
  
      foreach ($replacements as $k => $v) {
        $stub = str_replace("{{ $k }}", $v, $stub);
      }
  
      return $stub;
    }
}
