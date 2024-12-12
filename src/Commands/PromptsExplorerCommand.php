<?php

namespace AnthonyBrindley\DesignPatternImplementor\Commands;

use App\Models\User;
use Illuminate\Console\Command;

use function Laravel\Prompts\{text, textarea, password, confirm, select, multiselect, suggest, search, multisearch, pause, spin, form, info, table, progress};

class PromptsExplorerCommand extends Command
{
    protected $signature = 'prompts-test {prompt}';
    protected $description = 'Generate a state pattern implementation';

    public function handle()
    {
        $prompt = $this->argument('prompt');

        match($prompt)
        {
            'text' => $this->testText(),
            'textarea' => $this->testTextarea(),
            'password' => $this->testPassword(),
            'confirm'  => $this->testConfirm(),
            'select' => $this->testSelect(),
            'multiselect' => $this->testMultiselect(),
            'suggest' => $this->testSuggest(),
            // search
            // multisearch
            'pause' => $this->testPause(),
            'table' => $this->testTable()
        };
    }

    protected function testText()
    {
        text(
            "This is a text example"
        );
    }

    protected function testTextarea()
    {
        textarea('Tell me a story.');
    }

    protected function testPassword()
    {
        password('What is your password?');
    }

    protected function testConfirm()
    {
        confirm('Do you accept the terms?');
    }

    protected function testSelect()
    {
        select(
            label: 'What role should the user have?',
            options: ['Member', 'Contributor', 'Owner']
        );
    }

    protected function testMultiselect()
    {
        multiselect(
            label: 'What permissions should be assigned?',
            options: ['Read', 'Create', 'Update', 'Delete']
        );
    }

    protected function testSuggest()
    {
        suggest('What is your name?', ['Taylor', 'Dayle']);
    }

    protected function testSearch()
    {
        search('What is your name?', ['Taylor', 'Dayle', 'Fred', 'Pete', 'Janice']);
    }

    protected function testPause()
    {
        pause('Press ENTER to continue');
    }

    protected function testTable()
    {
        table(
            headers: ['Name', 'Email'],
            rows: User::all(['name', 'email'])->toArray()
        );
    }
}
