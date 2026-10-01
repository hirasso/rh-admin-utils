<?php

namespace RH\AdminUtils\Tests\Pest;

class HardeningTest extends IntegrationTestCase
{
    /** Stand in for DISALLOW_FILE_MODS=true, which applies before any filter */
    private function disallow_file_mods(): void
    {
        add_filter('file_mod_allowed', '__return_false', 5);
    }

    private function should_auto_update(?bool $update): ?bool
    {
        return apply_filters('auto_update_plugin', $update, (object) ['plugin' => 'foo/foo.php']);
    }

    public function test_file_mods_allowed_leaves_plugin_auto_updates_untouched(): void
    {
        $this->assertNull($this->should_auto_update(null));
        $this->assertTrue($this->should_auto_update(true));
    }

    public function test_disallowed_file_mods_disable_plugin_auto_updates(): void
    {
        $this->disallow_file_mods();

        $this->assertFalse($this->should_auto_update(null));
        $this->assertFalse($this->should_auto_update(true));
    }

    public function test_disallowed_file_mods_still_allow_the_core_updater(): void
    {
        $this->disallow_file_mods();

        $this->assertTrue(wp_is_file_mod_allowed('automatic_updater'));
    }
}
