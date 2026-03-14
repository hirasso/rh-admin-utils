<?php

namespace RH\AdminUtils\Tests\Pest;

use RH\AdminUtils\AdminUtils;
use WP_UnitTestCase;

class BasicTest extends WP_UnitTestCase
{
    private AdminUtils $instance;

    public function setUp(): void
    {
        parent::setUp();
        $this->instance = AdminUtils::getInstance();
    }

    public function test_has_required_plugins(): void
    {
        $this->assertTrue($this->instance->is_plugin_active('advanced-custom-fields-pro'));
    }
}
