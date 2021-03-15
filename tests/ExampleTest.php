<?php

namespace Moonshiner\SafeQueuing\Tests;

use Orchestra\Testbench\TestCase;
use Moonshiner\SafeQueuing\SafeQueuingServiceProvider;

class ExampleTest extends TestCase
{

    protected function getPackageProviders($app)
    {
        return [SafeQueuingServiceProvider::class];
    }
    
    /** @test */
    public function true_is_true()
    {
        $this->assertTrue(true);
    }
}
