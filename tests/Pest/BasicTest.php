<?php

namespace RH\AdminUtils\Tests\Pest;

use RH\AdminUtils\AdminUtils;
use Yoast\WPTestUtils\BrainMonkey\TestCase;

class BasicTest extends TestCase
{
    private AdminUtils $instance;

    public function setUp(): void
    {
        parent::setUp();
        $this->instance = AdminUtils::getInstance();
    }

    public function test_has_required_plugins(): void
    {
        $this->assertTrue(function_exists('acf_get_field'));
    }
}
