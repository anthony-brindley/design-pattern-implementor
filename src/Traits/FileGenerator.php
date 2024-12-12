<?php

namespace AnthonyBrindley\DesignPatternImplementor\Traits;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

trait FileGenerator
{
    use PromptsUsers;

    protected array $replacements = [];
    protected array $imports = [];

    protected array $scopedReplacements = [];
    protected array $scopedImports = [];

    protected array $mergedScopedImports = [];
    protected array $mergedScopedReplacements = [];

    public function addImport(string $key, string $namespace): void
    {
        if (!array_key_exists($key, $this->imports)) {
            $this->imports[$key] = $namespace;
        }
    }

    public function addScopedImport(string $scope, string $key, string $namespace): void
    {
        if (!array_key_exists($key, $this->imports)) {
            $this->scopedImports[$scope][$key][] = $namespace;
        }
    }

    public function addReplacement(string $key, mixed $value): void
    {
        $this->replacements[$key] = $value;
    }

    public function addScopedReplacement(string $scope, string $key, mixed $value): void
    {
        $this->scopedReplacements[$scope][$key] = $value;
    }

    public function getReplacements(): array
    {
        return $this->replacements;
    }

    public function getScopedReplacements(?string $scope = null, ?string $key = null): array
    {
        if(is_null($scope)) return $this->scopedReplacements;

        if(!isset($this->scopedReplacements[$scope]))  return [];

        if(is_null($key)) return $this->scopedReplacements[$scope];

        if(!isset($this->scopedReplacements[$scope][$key])) return [];

        return Arr::wrap($this->scopedReplacements[$scope][$key]);
    }

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

    // public function generateMethodString(string $methodName, ?string $returnType = null, bool $forInterface = false): string
    // {
    //     $returnType = (empty($returnType)) ? '' : ': '.$returnType;
    //     $methodName = Str::camel($methodName);

    //     $base = "\tpublic function $methodName(){$returnType}";

    //     if(true === $forInterface)
    //     {
    //         $string = $base.";";
    //         return $string;
    //     } 

    //     return $base."\n\t{\n\t\t//populate this\n\t}\n\n";        
    // }

    public function createClassFile(string $className, string $namespace, string $directory, string $stubPath): void
    {
        $this->ensureDirectoryExists($directory);

        $filePath = "$directory/$className.php";
        if (File::exists($filePath)) {
            throw new \Exception("File already exists at: $filePath");
        }

        $this->mergeImportArrays($className);

        $replacementsArray = $this->mergeReplacements($className, $namespace);

        $stub = $this->populateStub($stubPath, $replacementsArray);

        $this->writeToFile($filePath, $stub);

        $this->removeMergedScopedImports($className);
    }

    protected function mergeImportArrays(string $className)
    {
        if(isset($this->scopedImports[$className]))
        {
            $imports = [];
            $keys = array_keys($this->scopedImports[$className]);

            foreach($keys as $key)
            {
                foreach($this->scopedImports[$className][$key] as $value)
                {
                    if(!in_array($value, $imports))
                    {
                        $imports[] = $value;
                    }
                }
            }

            foreach($imports as $import)
            {
                if(!in_array($import, $this->imports))
                {
                    $this->imports[] = $value;
                    $this->mergedScopedImports[$className][] = $value;
                }
            }
        }
    }

    protected function mergeReplacements(string $className, string $namespace): array
    {
        $baseArray = [
            'DummyNamespace' => $namespace,
            'DummyClass'     => $className,
        ];

        $replacements = $this->replacements ?? [];

        $mergedReplacements = array_merge($baseArray, $replacements);

        $scopedReplacements = [];
        
        if(isset($this->scopedReplacements[$className]))
        {
            foreach($this->scopedReplacements[$className] as $k => $r)
            {
                if(!in_array($r, $scopedReplacements))
                {
                    $scopedReplacements[$k] = $r;
                }
            }

            foreach($scopedReplacements as $k => $r)
            {
                if(!in_array($k, array_keys($mergedReplacements)))
                {
                    $mergedReplacements[$k] = $r;
                    $this->mergedScopedReplacements[$className][$k] = $r; 
                }
            }
        }

        if(!isset($mergedReplacements['methods']) || empty($mergedReplacements['methods']))
        {
            $mergedReplacements['methods'] = '';
        }

        return $mergedReplacements;
    }

    protected function removeMergedScopedImports(string $className)
    {
        if(!isset($this->mergedScopedImports[$className])) return;

        $mergedScopedImports = $this->mergedScopedImports[$className];

        foreach($this->imports as $k => $v)
        {
            if(in_array($v, $mergedScopedImports))
            {
                unset($this->imports[$k]);
            }
        }

        $this->mergedScopedImports = [];
    }

    // protected function removeMergedScopedReplacements()
    // {

    // }

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
        if (!$this->checkDirectoryExists($path)) {
            File::makeDirectory($path, 0755, true);
        }
    }

    public function checkDirectoryExists(string $path)
    {
        return File::isDirectory($path);
    }

    public function writeToFile(string $path, string $content): void
    {
        File::put($path, $content);
    }

    public function formatClassName(string $rawName): string
    {
        return Str::studly($rawName); // Converts strings like "my_class" or "my-class" to "MyClass"
    }

    public function formatMethodName(string $rawName): string
    {
        return Str::camel($rawName);
    }
}
