<?php

namespace Tests\Unit;

use Tests\TestCase;

class ImportProductTest extends TestCase
{
    /**
     * A basic unit test example.
     */
    public function test_command_productImport_limitedRaws(): void
    {
        $this->artisan('product:import --count=5')->assertSuccessful();
    }
}
