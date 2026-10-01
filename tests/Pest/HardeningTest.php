<?php

namespace RH\AdminUtils\Tests\Pest;

class HardeningTest extends IntegrationTestCase
{
    /** @return array<string, array{string}> */
    public static function auto_update_types(): array
    {
        return [
            'plugin' => ['plugin'],
            'theme' => ['theme'],
        ];
    }

    /** Stand in for DISALLOW_FILE_MODS=true, which applies before any filter */
    private function disallow_file_mods(): void
    {
        add_filter('file_mod_allowed', '__return_false', 5);
    }

    private function should_auto_update(string $type, ?bool $update): ?bool
    {
        return apply_filters("auto_update_$type", $update, (object) [$type => "foo/foo.php"]);
    }

    /** @dataProvider auto_update_types */
    public function test_file_mods_allowed_leave_auto_updates_untouched(string $type): void
    {
        $this->assertNull($this->should_auto_update($type, null));
        $this->assertTrue($this->should_auto_update($type, true));
    }

    /** @dataProvider auto_update_types */
    public function test_disallowed_file_mods_disable_auto_updates(string $type): void
    {
        $this->disallow_file_mods();

        $this->assertFalse($this->should_auto_update($type, null));
        $this->assertFalse($this->should_auto_update($type, true));
    }

    public function test_disallowed_file_mods_still_allow_the_core_updater(): void
    {
        $this->disallow_file_mods();

        $this->assertTrue(wp_is_file_mod_allowed('automatic_updater'));
    }
}
