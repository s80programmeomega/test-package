<?php
namespace Jonas\TestPackage;

class TestPackage
{
    public function greet(string $name = 'World'): string
    {
        return "Hello, {$name}!";
    }
}
