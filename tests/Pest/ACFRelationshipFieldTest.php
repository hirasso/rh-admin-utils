<?php

namespace RH\AdminUtils\Tests\Pest;

use RH\AdminUtils\ACFRelationshipField;

class ACFRelationshipFieldTest extends IntegrationTestCase
{
    public function test_result_shows_post_type_only_for_several_post_types(): void
    {
        remove_all_filters('acf/fields/relationship/result');
        ACFRelationshipField::init();

        $post = $this->factory()->post->create_and_get(['post_title' => 'Hello']);
        $get_result = fn (array $field) => apply_filters('acf/fields/relationship/result', 'Hello', $post, $field, 0);

        $this->assertStringNotContainsString('(post)', $get_result(['post_type' => ['post']]));
        $this->assertStringContainsString('(post)', $get_result(['post_type' => ['post', 'page']]));
        $this->assertStringContainsString('(post)', $get_result(['post_type' => []]));
    }

    public function test_search_uses_relevanssi_only_for_indexed_post_types(): void
    {
        remove_all_filters('acf/fields/relationship/query');
        ACFRelationshipField::init();
        update_option('relevanssi_index_post_types', ['post', 'page']);

        $get_args = fn (array $args) => apply_filters('acf/fields/relationship/query', $args, [], 0);

        $this->assertTrue($get_args(['s' => 'foo', 'post_type' => ['post', 'page']])['relevanssi'] ?? false);
        $this->assertArrayNotHasKey('relevanssi', $get_args(['s' => 'foo', 'post_type' => ['post', 'attachment']]));
        $this->assertArrayNotHasKey('relevanssi', $get_args(['s' => '', 'post_type' => ['post']]));

        add_filter('relevanssi_search_ok', 'relevanssi_acf_relationship_fields');
        $get_args(['s' => 'foo', 'post_type' => ['post', 'attachment']]);
        $this->assertNotFalse(has_filter('relevanssi_search_ok', 'relevanssi_acf_relationship_fields'));
        $get_args(['s' => 'foo', 'post_type' => ['post']]);
        $this->assertFalse(has_filter('relevanssi_search_ok', 'relevanssi_acf_relationship_fields'));
    }
}
