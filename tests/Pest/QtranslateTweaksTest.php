<?php

namespace RH\AdminUtils\Tests\Pest;

use RH\AdminUtils\Misc;

class QtranslateTweaksTest extends IntegrationTestCase
{
    public function setUp(): void
    {
        parent::setUp();
        $GLOBALS['q_config'] = ['lsb_style' => 'Simple_Buttons.css', 'highlight_mode' => 1];
    }

    public function tearDown(): void
    {
        unset($GLOBALS['q_config']);
        parent::tearDown();
    }

    public function test_overrides_qtranslate_defaults(): void
    {
        Misc::getInstance()->overwrite_qtranslate_defaults();

        $this->assertSame('custom', $GLOBALS['q_config']['lsb_style']);
        $this->assertStringContainsString('rhau-qtranslate-tweaks', Misc::getInstance()->admin_body_class(''));
    }

    public function test_filter_keeps_qtranslate_defaults(): void
    {
        add_filter('rhau/qtranslate_tweaks', '__return_false');

        Misc::getInstance()->overwrite_qtranslate_defaults();

        $this->assertSame('Simple_Buttons.css', $GLOBALS['q_config']['lsb_style']);
        $this->assertSame(1, $GLOBALS['q_config']['highlight_mode']);
        $this->assertStringNotContainsString('rhau-qtranslate-tweaks', Misc::getInstance()->admin_body_class(''));
    }
}
