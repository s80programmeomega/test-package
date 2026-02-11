<?php
namespace Jonas\TestPackage\Commands;

use Illuminate\Console\Command;

class TestCommand extends Command
{
    protected $signature = 'testpackage:greet {name?}';
    protected $description = 'Greet someone';

    public function handle()
    {
        $name = $this->argument('name') ?? 'World';
        $this->info("Hello, {$name}!");
    }
}