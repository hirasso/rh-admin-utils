<?php

namespace RH\AdminUtils\Tests\Pest;

use RH\AdminUtils\Hardening\HardenHtaccess;

class HardenHtaccessTest extends IntegrationTestCase
{
    private const CRON_HOOK = 'rhau/harden-htaccess';
    private const FAILURE_OPTION = 'rhau-htaccess-hardening-failure';

    private string $file;
    private ?string $backup;

    public function set_up(): void
    {
        parent::set_up();
        require_once ABSPATH . 'wp-admin/includes/file.php';
        require_once ABSPATH . 'wp-admin/includes/misc.php';

        $this->file = get_home_path() . '.htaccess';
        $this->backup = file_exists($this->file) ? file_get_contents($this->file) : null;
        file_put_contents($this->file, "# original\n");

        $GLOBALS['is_apache'] = true;
        wp_clear_scheduled_hook(self::CRON_HOOK);
    }

    public function tear_down(): void
    {
        $this->backup === null ? unlink($this->file) : file_put_contents($this->file, $this->backup);
        $GLOBALS['is_apache'] = false;
        wp_clear_scheduled_hook(self::CRON_HOOK);
        parent::tear_down();
    }

    private function call(string $method): mixed
    {
        return (new \ReflectionMethod(HardenHtaccess::class, $method))->invoke(null);
    }

    /** Fake the loopback requests, one status code per request (null = WP_Error) */
    private function fake_loopback(?int ...$codes): void
    {
        add_filter('pre_http_request', function () use (&$codes) {
            $code = array_shift($codes);
            return $code === null
                ? new \WP_Error('http_request_failed', 'blocked')
                : ['response' => ['code' => $code], 'headers' => [], 'body' => ''];
        });
    }

    public function test_schedules_hardening_on_outdated_apache_site(): void
    {
        $this->call('maybeScheduleHtaccessHardening');
        $this->assertNotFalse(wp_next_scheduled(self::CRON_HOOK));
    }

    public function test_does_not_schedule_without_apache(): void
    {
        $GLOBALS['is_apache'] = false;
        $this->call('maybeScheduleHtaccessHardening');
        $this->assertFalse(wp_next_scheduled(self::CRON_HOOK));
    }

    public function test_does_not_retry_after_failure_with_same_directives(): void
    {
        $this->fake_loopback(null);
        $this->call('autoApplyHtaccessHardening');
        $this->call('maybeScheduleHtaccessHardening');

        $this->assertFalse(wp_next_scheduled(self::CRON_HOOK));
    }

    public function test_retries_after_directives_changed(): void
    {
        update_site_option(self::FAILURE_OPTION, ['hash' => 'outdated', 'reason' => 'foo']);
        $this->call('maybeScheduleHtaccessHardening');

        $this->assertNotFalse(wp_next_scheduled(self::CRON_HOOK));
    }

    public function test_writes_directives_when_site_responds(): void
    {
        $this->fake_loopback(200, 200);
        $this->call('autoApplyHtaccessHardening');

        $this->assertStringContainsString('Options -Indexes', file_get_contents($this->file));
        $this->assertFalse($this->call('needsHtaccessHardening'));
        $this->assertFalse(get_site_option(self::FAILURE_OPTION));

        $this->call('maybeScheduleHtaccessHardening');
        $this->assertFalse(wp_next_scheduled(self::CRON_HOOK));
    }

    public function test_skips_write_without_loopback(): void
    {
        $this->fake_loopback(null);
        $this->call('autoApplyHtaccessHardening');

        $this->assertSame("# original\n", file_get_contents($this->file));
        $this->assertIsString($this->call('getFailureReason'));
    }

    public function test_rolls_back_when_directives_break_the_site(): void
    {
        $this->fake_loopback(200, 500);
        $this->call('autoApplyHtaccessHardening');

        $this->assertSame("# original\n", file_get_contents($this->file));
        $this->assertStringContainsString('AllowOverride', $this->call('getFailureReason'));
    }

    public function test_detects_modified_block(): void
    {
        $this->fake_loopback(200, 200);
        $this->call('autoApplyHtaccessHardening');

        insert_with_markers($this->file, 'AdminUtils\Hardening', ['Options -Indexes']);

        $this->assertTrue($this->call('needsHtaccessHardening'));
    }
}
