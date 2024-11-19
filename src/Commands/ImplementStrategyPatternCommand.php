<?php

namespace Anthonybrindley\DesignPatternImplementor\Commands;

use Anthonybrindley\DesignPatternImplementor\Traits\FileGenerator;
use Illuminate\Console\Command;
use Illuminate\Console\GeneratorCommand;

use function Laravel\Prompts\text;

class ImplementStrategyPatternCommand extends GeneratorCommand
{
    use FileGenerator;

    protected string $fileForGeneration = '';

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'pattern:implement-strategy';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate a strategy pattern implementation';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $context = text(
            label: 'What is the functionality context for this implementation?',
            placeholder: 'e.g PaymentHandling',
            required: true,
            hint: 'This will be used as a containing folder name'
        );

        dd('here', $context);
    }

    /**
     * @inheritDoc
     */
    protected function getStub(): string
    {
        $fileName = $this->fileForGeneration;
        return __DIR__.'/../../stubs/'.$fileName.'.php.stub';
    }
}
