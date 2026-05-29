<?php

namespace RH\AdminUtils\Tests\Pest;

class BasicTest extends IntegrationTestCase
{
    public function setUp(): void
    {
        parent::setUp();
    }

    public function test_has_required_plugins(): void
    {
        $this->assertTrue(function_exists('acf_get_field'));
    }
}
