<?php

namespace Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Tests\Concerns\CreatesPassportClientsForTests;

abstract class TestCase extends BaseTestCase
{
    use RefreshDatabase;
    use CreatesPassportClientsForTests;

    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpPassportClients();
    }
}
