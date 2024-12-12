<?php

namespace AnthonyBrindley\DesignPatternImplementor\Traits;

trait HandlesSettingManagement
{
    protected array $settings = [];

    protected function getSetting(string $key)
    {
        if(!isset($this->settings[$key])) return null;

        return $this->settings[$key];
    }

    protected function updateSetting(string $key, mixed $value): bool
    {
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

    protected function isSet(string $key): bool
    {
        return $this->getSetting($key) !== null;
    }
}
