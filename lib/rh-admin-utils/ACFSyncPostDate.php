<?php

namespace RH\AdminUtils;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class ACFSyncPostDate
{

    /**
     * Init
     */
    public static function init()
    {
        add_action('acf/render_field_settings/type=date_picker', [__CLASS__, 'render_field_settings']);
        add_action('acf/render_field_settings/type=date_time_picker', [__CLASS__, 'render_field_settings']);

        add_filter('acf/load_value/type=date_picker', [__CLASS__, 'load_value'], 10, 3);
        add_filter('acf/update_value/type=date_picker', [__CLASS__, 'update_value'], 10, 4);
        add_filter('acf/render_field/type=date_picker', [__CLASS__, 'render_field'], 10);

        add_filter('acf/load_value/type=date_time_picker', [__CLASS__, 'load_value'], 10, 3);
        add_filter('acf/update_value/type=date_time_picker', [__CLASS__, 'update_value'], 10, 4);
        add_filter('acf/render_field/type=date_time_picker', [__CLASS__, 'render_field'], 10);
    }

    /**
     * Render the field setting
     */
    public static function render_field_settings(array $field): void
    {
        acf_render_field_setting($field, array(
            'label'  => __('Sync the post date with this field'),
            'instructions'  => '',
            'name' => 'rhau_sync_post_date',
            'type' => 'true_false',
            'ui' => 1,
        ));
    }

    /**
     * Check if a field should be synced to a post
     */
    private static function should_sync(array $field, string|int $post_id): bool
    {
        if (empty($field['rhau_sync_post_date'])) return false;

        return is_int($post_id) && !!get_post($post_id);
    }

    /**
     * Load the post date for existing posts
     */
    public static function load_value(?string $value, string|int $post_id, array $field): ?string
    {
        if (!self::should_sync($field, $post_id)) return $value;

        if (get_post_status($post_id) === 'auto-draft') return $value;

        return match ($field['type']) {
            'date_picker' => get_post_datetime($post_id)->format("Ymd"),
            'date_time_picker' => get_post_datetime($post_id)->format("Y-m-d H:i:s"),
            default => $value
        };
    }

    /**
     * Save the post date based on synced fields
     */
    public static function update_value(?string $value, string|int $post_id, array $field, ?string $original): ?string
    {
        if (empty($value)) return $value;

        if (!self::should_sync($field, $post_id)) return $value;

        /** Ensure the post_date has a format of 'Y-m-d H:i:s' */
        $post_date = match ($field['type']) {
            'date_picker' => date_create_immutable_from_format('Ymd', $value, wp_timezone())->format('Y-m-d H:i:s'),
            'date_time_picker' => $value,
        };

        wp_update_post([
            'ID'                => $post_id,
            'post_date'         => $post_date,
            'post_date_gmt'     => get_gmt_from_date($post_date)
        ]);

        return $value;
    }

    /**
     * Hide the built-in post date UI for synced posts
     */
    public static function render_field(array $field): void
    {
        if (empty($field['rhau_sync_post_date'])) return;
        ob_start() ?>
        <style>
            .misc-pub-curtime {
                display: none !important;
            }
        </style>
<?php echo ob_get_clean();
    }
}
