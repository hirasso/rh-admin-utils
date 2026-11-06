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
}
