<?php

namespace AnthonyBrindley\DesignPatternImplementor\Traits;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
trait HandlesFileCreation
{
    protected string $fileForGeneration = '';

    public function writeToFile(string $path, string $content): void
    {
        File::put($path, $content);
    }

    public function getDirectoryPath(string $path): string
    {
        // Normalize the path to handle Laravel conventions
        $normalizedPath = Str::of($path)
            ->ltrim('\\')
            ->replace('\\', '/') // Replace backslashes with forward slashes
            ->replaceFirst('App/', '') // Remove 'App/' prefix
            ->replaceFirst('app/', ''); // Remove 'app/' prefix

        // Check if the path ends with a supported file extension
        if (Str::endsWith($path, $this->getSupportedFileExtensions())) {
            $normalizedPath = dirname($normalizedPath);
        }

        return app_path($normalizedPath);
    }

    public function ensureDirectoryExists(string $path): void
    {
        if (!$this->checkDirectoryExists($path)) {
            File::makeDirectory($path, 0755, true);
        }
    }

    public function checkDirectoryExists(string $path)
    {
        return File::isDirectory($path);
    }

    protected function folderDoesntAlreadyExist(string $path): bool
    {
        return !$this->checkDirectoryExists($path);
    }

    public function checkFileExists(string $path): bool
    {
        return File::exists($path);
    }

    public function checkClassExists(string $classNamespace): bool
    {
        return class_exists($classNamespace);
    }

    public function checkInterfaceExists(string $interfaceNamespace): bool
    {
        return interface_exists($interfaceNamespace);
    }

    public function formatClassName(string $rawName): string
    {
        return Str::studly($rawName); // Converts strings like "my_class" or "my-class" to "MyClass"
    }

    protected function getSupportedFileExtensions(): array
    {
        return ['php', 'json'];
    }

    public function createClassFile(
        string $className,
        string $targetNamespace,
        string $stubPath
      ) {
        $className = $this->formatClassName($className);
  
        $directory = $this->getDirectoryPath($targetNamespace);
        $this->addScopedReplacement($className, "DummyNamespace", $targetNamespace);
        $this->addScopedReplacement($className, "DummyClass", $className);
    
        $this->ensureDirectoryExists($directory);
    
        $filePath = "$directory/$className.php";
        if (File::exists($filePath)) {
          throw new \Exception("File already exists at: $filePath");
        }
    
        if (!File::exists($stubPath)) {
          throw new \Exception("No stub file found at: $stubPath");
        }
  
        $replacementsArray = $this->generateReplacements($className);
    
        $stub = $this->populateStub($stubPath, $replacementsArray);
    
        $this->writeToFile($filePath, $stub);
      }
}
