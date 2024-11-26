<?php

namespace AnthonyBrindley\DesignPatternImplementor\Traits;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;

use function Laravel\Prompts\{text, info, spin};


trait PromptsUsers
{
    protected function promptFor(string $label, string $placeholder = '', string $default = '', string $hint = '', bool $required = false): string
    {
        return text(
                label: $label,
                placeholder: $placeholder,
                required: $required,
                default: $default,
                hint: $hint
        );
    }

    protected function promptForClassName(string $label, string $placeholder = '', string $default = '', string $hint = '', bool $required = false)
    {
        return $this->formatClassName($this->promptFor(label: $label, placeholder: $placeholder, default: $default, hint: $hint, required: $required));
    }

    protected function promptForCamel(string $label, string $placeholder = '', string $default = '', string $hint = '', bool $required = false)
    {
        return Str::camel($this->promptFor(label: $label, placeholder: $placeholder, default: $default, hint: $hint, required: $required));
    }

    public function captureAnswers(string $label, string $placeholder = '', string $default = '', string $hint = '', bool $required = false, ?int $limit = null): array
    {
        if($limit === 1)
        {
            return Arr::wrap($this->promptFor(
                label: $label,
                placeholder: $placeholder,
                default: $default,
                hint: $hint,
                required: $required
            ));
        }

        $answers = [];

        $placeholder = (!empty($placeholder)) ? "e.g. {$placeholder}" : null;

        while($answer = $this->promptFor(
            label: $label,
            placeholder: $placeholder,
            default: $default,
            hint: $hint,
            required: $required
        ))
        {
            if(!in_array($answer, $answers)) $answers[] = $answer;
            else {
                info('Already a submission with this value, please try again...');
            }

            if($limit)
            {
                $count = count($answers);
                if($count === $limit)
                {
                    info('Limit reached for allowed submissions. Moving on......');
                    break;
                } 
            }
        }

        return $answers;
    }
}
