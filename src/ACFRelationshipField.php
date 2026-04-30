<?php

namespace RH\AdminUtils;

use WP_Post;

/**
 * Enhancements for the ACF relationship field
 */
class ACFRelationshipField
{
    public static function init()
    {
        add_filter("acf/fields/relationship/result", self::relationship_result(...), 10, 2);
        add_filter('acf/prepare_field/type=relationship', self::prepare_field(...));
    }

    /**
     * Handle ACF code fields
     */
    private static function prepare_field(array $field): array
    {
        $field['wrapper']['rhau-x-data'] = 'ACFRelationshipField';
        return $field;
    }

    /**
     * Render the post type and an edit link in relationship results
     */
    private static function relationship_result(string $text, WP_Post $post): string
    {
        return sprintf(
            "{$text} (%s) %s",
            esc_html(get_post_type($post)),
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
