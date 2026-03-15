<?php

namespace RH\AdminUtils\Tests\Pest;

use RH\AdminUtils\ACFSyncPostDate;
use Yoast\WPTestUtils\BrainMonkey\TestCase;

class ACFSyncPostDateTest extends TestCase
{
    public function test_correctly_parses_post_date(): void
    {
        $this->assertSame(ACFSyncPostDate::parse_post_date('20260315'), '2026-03-15 23:59:59');
        $this->assertSame(ACFSyncPostDate::parse_post_date('2026-03-15'), '2026-03-15 23:59:59');
        $this->assertSame(ACFSyncPostDate::parse_post_date('2026-03-15 15:00:00'), '2026-03-15 15:00:00');

        $this->assertSame(ACFSyncPostDate::parse_post_date('2026-03'), null);
        $this->assertSame(ACFSyncPostDate::parse_post_date('malformed'), null);
        $this->assertSame(ACFSyncPostDate::parse_post_date(3), null);
    }
}
