<?php

namespace AnthonyBrindley\DesignPatternImplementor\Traits;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

trait HandlesFileReplacements
{
    public array $imports = [];
    public array $replacements = [];
    public array $scopedImports = [];
    public array $scopedReplacements = [];
    private array $disallowedDirectReplacementKeys = ["imports", "methods"];
    private array $requiredPlaceholders = ["DummyClass", "DummyNamespace"];

    private function keyIsAllowed(string $key): bool
    {
      return !in_array($key, $this->disallowedDirectReplacementKeys);
    }

    private function keyCheck(string $key)
    {
        if(!$this->keyIsAllowed($key))
        {
            throw new \Exception("The key '$key' cannot be directly manipulated");
        }
    }

  
    public function addDependency(string $namespace, string $key)
    {
      $this->addImport($namespace);
      $this->addReplacement($key, $this->getClassName($namespace));
    }
  
    public function addScopedDependency(
      string $className,
      string $namespace,
      string $key
    ) {
      $this->keyCheck($key);
    
      $this->scopedImports[$className][] = $namespace;
      $this->scopedReplacements[$className][$key] = $this->getClassName($namespace);
    }
  
    public function addImport(string $path)
    {
      if (!in_array($path, $this->imports)) {
        $this->imports[] = $path;
      }
    }
  
    public function addScopedImport(string $className, string $path)
    {
      if (!in_array($path, $this->scopedImports[$className])) {
        $this->scopedImports[$className][] = $path;
      }
    }
  
    public function addReplacement(string $key, string $replacement)
    {
      $this->keyCheck($key);
  
      if (!array_key_exists($key, $this->replacements)) {
        $this->replacements[$key] = $replacement;
      }
    }
  
    public function addScopedReplacement(
      string $className,
      string $key,
      string $replacement
    ) {
      $className = $this->formatClassName($className);
      $this->scopedReplacements[$className][$key] ??= $replacement;
     
    }
}
