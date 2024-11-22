<?php

namespace AnthonyBrindley\DesignPatternImplementor\Traits;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

trait FileGenerator
{

    protected array $replacements = [];
    protected array $imports = [];

    public function getSupportedFileExtensions(): array
    {
        return ['php', 'jpeg', 'jpg', 'png', 'txt', 'json'];
    }

    public function populateStub(string $stubPath, array $replacements): string
    {
        $stub = File::get($stubPath);

        foreach ($replacements as $search => $replace) {
            $stub = str_replace('{{ '.$search.' }}', $replace, $stub);
        }

        return $stub;
    }

    public function ensureDirectoryExists(string $path): bool
    {
        $directory = $this->getDirectoryPath($path);

        if (!$this->checkDirectoryExists($directory)) {
            return $this->createDirectory($directory);
        }

        return true;
    }

    public function getDirectoryPath(string $path): string
    {
        if (Str::startsWith($path, '\\')) {
            $path = Str::replaceFirst('\\', '', $path);
        }

        if (Str::contains($path, '\\')) {
            $path = Str::replace('\\', '/', $path);
        }

        if (Str::startsWith($path, 'App/')) {
            $path = Str::replaceFirst('App/', '', $path);
        } elseif (Str::startsWith($path, 'app/')) {
            $path = Str::replaceFirst('app/', '', $path);
        }

        if (Str::endsWith($path, $this->getSupportedFileExtensions())) {
            $path = dirname($path);
        }

        return app_path($path);
    }

    public function checkFileExists(string $path): bool
    {
        return File::exists($path);
    }

    public function checkDirectoryExists(string $directoryPath): bool
    {
        return File::isDirectory($directoryPath);
    }

    public function createDirectory(string $directoryPath)
    {
        return File::makeDirectory($directoryPath, 0755, true);
    }

    public function writeToFile(string $path, string $content): bool|int
    {
        return File::put($path, $content);
    }

    public function getStubsFolderPath(?string $subfolder = null)
    {
        $basepath = dirname(__DIR__, 2).'/stubs';

        return ($subfolder) ? $basepath.'/'.$subfolder : $basepath;
    }

    public function addReplacement(string $key, mixed $value)
    {
        $this->replacements[$key] = $value;
    }

    public function addImport(string $key, string $namespace)
    {
        if(!in_array($key, array_keys($this->imports)))
        {
            $this->imports[$key] = $namespace;
        }
    }

    public function createClassFile(string $name, string $namespace, string $directory, string $stubPath): string
    {
        $className = $this->formatClassName($name);
        $path      = $directory."/{$className}.php";

        $this->checkFileExists($path);

        $replacements = array_merge([
            'DummyNamespace' => $namespace,
            'DummyClass'     => $className,
        ], $this->replacements);

        $stub = $this->populateStub($stubPath, $replacements);

        return $this->writeToFile($path, $stub);
    }

    public function returnWriteToFileErrorResponse(?string $customMessage = null, ?int $customCode = null): mixed
    {
        $message = $customMessage ?? 'Failed to write to file';
        $code    = $customCode    ?? 500;

        return (app()->runningInConsole())
            ? $message
            : throw new \Exception(message: $message, code: $code);
    }

    public function formatClassName(string $rawName): string
    {
        return Str::studly($rawName);
    }

    public function getDestinationPath(string $name): string
    {
        return $this->getPath($name);
    }

    public function getQualifiedName(string $name): string
    {
        return $this->qualifyClass($name);
    }

    public function getNewName(): string
    {
        return $this->getNameInput();
    }

    public function getProcessedPath(?string $name = null): string
    {
        return $this->getDestinationPath($this->getQualifiedName($name ?? $this->getNewName()));
    }

    protected function replaceContent($contents, $replacements)
    {
        $revisedContent = $contents; // loop through replacements
        foreach ($replacements as $new => $placeholders) {
            $placeholders = Arr::wrap($placeholders);
            foreach ($placeholders as $placeholder) {
                if (Str::startsWith($new, 'use__')) {
                    $new = Str::replaceFirst('__', ' ', $new);
                    $new = $new.';';
                }

                $revisedContent = str_replace($placeholder, $new, $revisedContent);
            }
        }

        return $revisedContent;
    }

    protected function persistFileChanges($filePath, $revisedContent): void
    {
        file_put_contents($filePath, $revisedContent);
    }

    protected function populateImportsToStub(string $stubPath)
    {
        $stub = File::get($stubPath);

        $importsString = '';

        foreach($this->imports as $import)
        {
            $importsString = $importsString.'\r\n';
            $importsString = $importsString.'use '.$import.';';
        }

        $stub = str_replace('{{ imports }}', $importsString, $stub);

        return $stub;
    }
}
