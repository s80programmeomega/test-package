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

    /**
     * @method static void log(string $action, ?int $userId = null, array $data = [])
     * @method static array getLogs()
     * @method static \Illuminate\Database\Eloquent\Collection getFromDatabase(?int $userId = null, int $limit = 100)
     * @method static void clear()
     * @method static void clearDatabase()
     */
    protected static function getFacadeAccessor(): string
    {
        return 'test-package';
    }
}
