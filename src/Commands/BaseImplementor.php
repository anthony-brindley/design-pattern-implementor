<?php

namespace AnthonyBrindley\DesignPatternImplementor\Commands;

use AnthonyBrindley\DesignPatternImplementor\Traits\HandlesDomainSpecifics;
use AnthonyBrindley\DesignPatternImplementor\Traits\HandlesFileCreation;
use AnthonyBrindley\DesignPatternImplementor\Traits\HandlesFileReplacements;
use AnthonyBrindley\DesignPatternImplementor\Traits\HandlesMethodGeneration;
use AnthonyBrindley\DesignPatternImplementor\Traits\HandlesSettingManagement;
use AnthonyBrindley\DesignPatternImplementor\Traits\HandlesStubPopulation;
use Illuminate\Console\GeneratorCommand;
use Illuminate\Filesystem\Filesystem;

class BaseImplementor extends GeneratorCommand
{
    use HandlesSettingManagement;
    use HandlesFileReplacements;
    use HandlesStubPopulation;
    use HandlesFileCreation;
    use HandlesDomainSpecifics;
    use HandlesMethodGeneration;

    protected static string $patternName = '';


    public function __construct(Filesystem $files)
    {
        parent::__construct($files);
        $this->setSetting('baseNamespace', config('design-pattern-implementor.default_namespace'));
        $this->setSetting('interfacesFolderName', config('design-pattern-implementor.interface_folder_name', 'Contracts'));
    }

    public static function getDefaultDescription(): ?string
    {
        $patternName = static::$patternName ?? '';
        return __("design-pattern-implementor::{$patternName}.description");
    }

    protected function trans(string $index, array $variables = [])
    {
        $patternName = static::$patternName;
        return __("design-pattern-implementor::{$patternName}.{$index}", $variables);
    }
}
