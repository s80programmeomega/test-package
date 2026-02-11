<?php

namespace Jonas\TestPackage\Facades;

use Illuminate\Support\Facades\Facade;

class TestPackage extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor(): string
    {
        return 'test-package';
    }
}
