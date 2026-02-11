<?php

namespace Jonas\TestPackage\Tests;

use Jonas\TestPackage\Facades\TestPackage;
use Jonas\TestPackage\TestPackageServiceProvider;
use Orchestra\Testbench\TestCase;

class TestPackageTest extends TestCase
{
    protected function getPackageProviders($app)
    {
        return [TestPackageServiceProvider::class];
    }

    protected function getPackageAliases($app)
    {
        return [
            'TestPackage' => TestPackage::class,
        ];
    }

    public function test_service_provider_loads()
    {
        $this->assertTrue(true);
    }
}