<?php

namespace RH\AdminUtils\Tests\Pest;

use RH\AdminUtils\AttachmentsHelper;

class AttachmentsHelperTest extends IntegrationTestCase
{
    public function test_acf_fields_hide_trashed_and_deleted_attachments(): void
    {
        remove_all_filters('acf/load_value/type=image');
        remove_all_filters('acf/load_value/type=file');
        remove_all_filters('acf/load_value/type=gallery');
        AttachmentsHelper::init();

        [$available, $trashed, $deleted] = $this->factory()->attachment->create_many(3);
        wp_trash_post($trashed);
        wp_delete_attachment($deleted, true);

        $load_value = fn (string $type, mixed $value) => apply_filters("acf/load_value/type=$type", $value, 0, []);

        foreach (['image', 'file'] as $type) {
            $this->assertSame((string) $available, $load_value($type, (string) $available));
            $this->assertNull($load_value($type, (string) $trashed));
            $this->assertNull($load_value($type, $deleted));
            $this->assertSame('', $load_value($type, ''));
        }

        $this->assertSame(
            [(string) $available],
            $load_value('gallery', [(string) $trashed, (string) $available, (string) $deleted])
        );
    }
}
