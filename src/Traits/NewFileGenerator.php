<?php

namespace AnthonyBrindley\DesignPatternImplementor\Traits;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

trait NewFileGenerator
{
    protected array $replacements = [];
    protected array $imports = [];

    public function populateStub(string $stubPath, array $replacements): string
    {
        $stub = File::get($stubPath);

        // Add imports to replacements
        $imports = $this->generateImports();
        $replacements['imports'] = $imports;

        foreach ($replacements as $search => $replace) {
            $stub = str_replace("{{ $search }}", $replace, $stub);
        }

        return $stub;
    }

    public function generateImports(): string
    {
        if (empty($this->imports)) {
            return ''; // No imports to add
        }

        return collect($this->imports)
            ->map(fn($namespace) => "use $namespace;")
            ->implode("\n");
    }

    public function addImport(string $key, string $namespace): void
    {
        if (!array_key_exists($key, $this->imports)) {
            $this->imports[$key] = $namespace;
        }
    }

    public function createClassFile(string $className, string $namespace, string $directory, string $stubPath): void
    {
        $this->ensureDirectoryExists($directory);

        $filePath = "$directory/$className.php";
        if (File::exists($filePath)) {
            throw new \Exception("File already exists at: $filePath");
        }

        $stub = $this->populateStub($stubPath, array_merge([
            'DummyNamespace' => $namespace,
            'DummyClass'     => $className,
        ], $this->replacements));

        $this->writeToFile($filePath, $stub);
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

    protected function getSupportedFileExtensions(): array
    {
        return ['php', 'json'];
    }

    public function getStubsFolderPath(?string $subfolder = null): string
    {
        $basepath = dirname(__DIR__, 2) . '/stubs';
        return $subfolder ? "$basepath/$subfolder" : $basepath;
    }

    public function ensureDirectoryExists(string $path): void
    {
        if (!File::isDirectory($path)) {
            File::makeDirectory($path, 0755, true);
        }
    }

    public function writeToFile(string $path, string $content): void
    {
        File::put($path, $content);
    }

    public function formatClassName(string $rawName): string
    {
        return Str::studly($rawName); // Converts strings like "my_class" or "my-class" to "MyClass"
    }

    public function addReplacement(string $key, mixed $value): void
    {
        $this->replacements[$key] = $value;
    }

    public function getReplacements(): array
    {
        return $this->replacements;
    }
}
