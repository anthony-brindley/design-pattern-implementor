<?php

namespace AnthonyBrindley\DesignPatternImplementor\Commands;

use AnthonyBrindley\DesignPatternImplementor\Traits\FileGenerator;
use AnthonyBrindley\DesignPatternImplementor\Traits\GeneratesMethods;
use Illuminate\Console\GeneratorCommand;
use Illuminate\Filesystem\Filesystem;

abstract class BaseImplementorCommand extends GeneratorCommand
{
    use FileGenerator;
    use GeneratesMethods;

    protected array $settings = [];

    protected string $fileForGeneration = '';

    public function __construct(Filesystem $files)
    {
        parent::__construct($files);
        $this->setSetting('baseNamespace', config('design-pattern-implementor.default_namespace'));
        $this->setSetting('interfacesFolderName', config('design-pattern-implementor.interface_folder_name', 'Contracts'));
    }

    protected function getSetting(string $key)
    {
        if(!isset($this->settings[$key])) return null;

        return $this->settings[$key];
    }

    protected function updateSetting(string $key, mixed $value): bool
    {
        if(!isset($this->settings[$key])) return false;

        $this->settings[$key] = $value;

        return true;
    }

    protected function setSetting(string $key, mixed $value): void
    {
        $this->settings[$key] = $value;
    }

    protected function removeSetting(string $key)
    {
        if(isset($this->settings[$key]))
        {
            unset($this->settings[$key]);
        }
    }

}
