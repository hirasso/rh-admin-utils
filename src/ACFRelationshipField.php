<?php

namespace RH\AdminUtils;

use WP_Post;

/**
 * Enhancements for the ACF relationship and post object fields
 */
class ACFRelationshipField
{
    public static function init()
    {
        add_filter("acf/fields/relationship/result", self::relationship_result(...), 10, 3);
        add_filter('acf/prepare_field', self::prepare_field(...), 20);
        add_filter('acf/fields/relationship/query', self::query_with_relevanssi(...));
        add_filter('acf/fields/post_object/query', self::query_with_relevanssi(...));
    }

    /**
     * Search with Relevanssi if it indexes all queried post types
     */
    private static function query_with_relevanssi(array $args): array
    {
        if (empty($args['s'])) {
            return $args;
        }

        $indexed = (array) get_option('relevanssi_index_post_types', []);
        $post_types = array_filter((array) ($args['post_type'] ?? []));

        if (!$post_types || array_diff($post_types, $indexed)) {
            return $args;
        }

        return [...$args, 'relevanssi' => true];
    }

    /**
     * Handle ACF code fields
     */
    private static function prepare_field(mixed $field): mixed
    {
        $type = $field['type'] ?? null;

        if ($type !== 'relationship') {
            return $field;
        }

        $field['wrapper']['rhau-x-data'] = 'ACFRelationshipField';

        return $field;
    }

    /**
     * Render an edit link in relationship results, and the post type if the field allows several
     */
    private static function relationship_result(string $text, WP_Post $post, array $field): string
    {
        $post_types = array_filter((array) ($field['post_type'] ?? []));

        if (count($post_types) !== 1) {
            $text .= sprintf(' (%s)', esc_html(get_post_type($post)));
        }

        return sprintf(
            "{$text} %s",
            sprintf(
                <<<HTML
                <a
                    href="%s"
                    class="acf-icon -pencil small dark"
                    data-name="edit"
                    title="%s"
                    aria-label="%s"></a>
                HTML,
                esc_attr(get_edit_post_link($post)),
                __('Edit post'),
                __('Edit post')
            )
        );
    }
}
