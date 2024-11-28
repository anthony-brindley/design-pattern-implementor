<?php

namespace AnthonyBrindley\DesignPatternImplementor\Traits;

use Closure;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use InvalidArgumentException;

use function Laravel\Prompts\{confirm, text, info, spin};


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

    protected function confirmWithUser(string $label, bool $default = false, string $hint = '', string $yes = 'Yes', string $no = 'No', bool $required = false)
    {
        return confirm(
            label: $label,
            default: $default,
            yes: $yes,
            no: $no,
            required: $required,
            hint: $hint
        );
    }

    /**
     * Reusable attempt handler.
     *
     * @param Closure $action A closure that performs the main action. Must return truthy on success or falsy on failure.
     * @param int $maxAttempts The maximum number of allowed attempts.
     * @param string $failureMessage A message displayed on each failure.
     * @param Closure|null $onSuccess Optional closure executed on success, receiving the successful result.
     * @throws InvalidArgumentException If all attempts fail.
     */
    public function attempt(
        Closure $action,
        int $maxAttempts = 3,
        string $failureMessage = 'Invalid input. Please try again.',
        ?Closure $onSuccess = null
    ): void {
        $remainingAttempts = $maxAttempts;

        while ($remainingAttempts > 0) {
            $result = $action();

            if ($result) {
                if ($onSuccess) {
                    $onSuccess($result); // Pass valid input to success handler
                }
                return; // Exit early on success
            }

            $remainingAttempts--;
            $attemptsLabel = Str::plural('attempt', $remainingAttempts);
            info("{$failureMessage} ({$remainingAttempts} {$attemptsLabel} remaining)");
        }

        throw new InvalidArgumentException('Maximum attempts reached. Unable to complete the action.');
    }
}
