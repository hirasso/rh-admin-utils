<?php
/*
 * Copyright (c) Rasso Hilber
 * https://rassohilber.com
 *
 * Adds support for custom permissions on a per-page level:
 *
 *  - Lock the slug of a page
 *  - Prevent the deletion of a page
 *  - Disallow children for a page
 *  - Protect selected page templates so that they can only be selected and changed by administrators
 *
 */

namespace RH\AdminUtils;

class PagePermissions
{
    private static string $prefix = 'rhau_page_permissions';

    public static function init()
    {
        add_action('acf/init', [__CLASS__, 'add_options_page']);
        add_action('acf/init', [__CLASS__, 'add_page_field_group']);
        add_action('add_meta_boxes', [__CLASS__, 'adjust_meta_boxes']);
        add_filter('get_sample_permalink_html', [__CLASS__, 'get_sample_permalink_html'], 10, 5);
        add_action('admin_head', [__CLASS__, 'inject_styles']);
        add_filter('map_meta_cap', [__CLASS__, 'disallow_deletion'], 10, 4);
        add_filter('page_attributes_dropdown_pages_args', [__CLASS__, 'post_parent_dropdown_args']);
        add_filter('quick_edit_dropdown_pages_args', [__CLASS__, 'post_parent_dropdown_args']);
        add_action('page_attributes_misc_attributes', [__CLASS__, 'render_protected_page_template']);
        add_action('current_screen', [__CLASS__, 'restrict_page_templates_for_screen']);

        add_action('save_post', [__CLASS__, 'on_post_state_change']);
        add_action('deleted_post', [__CLASS__, 'on_post_state_change']);
        add_action('trashed_post', [__CLASS__, 'on_post_state_change']);
        add_action('untrash_post', [__CLASS__, 'on_post_state_change']);
    }

    /**
     * Render the field group for the page permissions
     */
    public static function add_page_field_group(): void
    {
        $group_key = "group_" . self::$prefix;

        acf_add_local_field_group(array(
            'key' => $group_key,
            'title' => 'Permissions',
            'location' => array(
                array(
                    array(
                        'param' => 'post_type',
                        'operator' => '==',
                        'value' => 'page',
                    ),
                    array(
                        'param' => 'current_user_role',
                        'operator' => '==',
                        'value' => 'administrator',
                    ),
                ),
            ),
            'position' => 'side',
        ));

        acf_add_local_field([
            'parent' => $group_key,
            'key' => "field_rhau_prevent_deletion",
            'label' => 'Deletion',
            'message' => 'Prevent deletion',
            'name' => '_rhau_prevent_deletion',
            'translations' => 'sync',
            'type' => 'true_false',
            'default_value' => 0
        ]);

        acf_add_local_field([
            'parent' => $group_key,
            'key' => "field_rhau_lock_slug",
            'label' => 'Slug',
            'message' => 'Lock the slug',
            'name' => '_rhau_lock_slug',
            'translations' => 'sync',
            'type' => 'true_false',
            'default_value' => 0
        ]);

        acf_add_local_field([
            'parent' => $group_key,
            'key' => "field_rhau_disallow_children",
            'label' => 'Children',
            'message' => 'Disallow children',
            'name' => '_rhau_disallow_children',
            'translations' => 'sync',
            'type' => 'true_false',
            'default_value' => 0
        ]);
    }

    /**
     * Add global options
     */
    public static function add_options_page()
    {

        acf_add_options_page([
            'page_title' => "Global Page Permissions",
            'menu_title' => "Permissions",
            'menu_slug' =>  self::get_options_slug(),
            'post_id' =>  self::get_options_slug(),
            'parent_slug' => "edit.php?post_type=page",
        ]);

        $group_key = "group_" . self::$prefix . "_options";

        acf_add_local_field_group(array(
            'key' => $group_key,
            'title' => 'Global Page Permissions',
            'location' => array(
                array(
                    array(
                        "param" => "options_page",
                        "operator" => "==",
                        "value" =>  self::get_options_slug()
                    ),
                ),
            ),
        ));

        $templates =  self::get_unfiltered_page_templates();

        acf_add_local_field([
            'parent' => $group_key,
            'key' => "field_rhau_protected_page_templates",
            'label' => 'Protected Page Templates',
            'instructions' => 'Protected templates can\'t be selected or changed by users with a role lower than <code>administrator</code>',
            'name' => '_protected_page_templates',
            'return_format' => 'array',
            'translations' => 'sync',
            'type' => 'select',
            'multiple' => true,
            'ui' => true,
            'choices' => $templates
        ]);
    }

    /**
     * Get all theme templates
     */
    private static function get_unfiltered_page_templates(): array
    {
        if (!function_exists('\wp_get_theme')) {
            require_once ABSPATH . WPINC . '/class-wp-theme.php';
        }

        $post_templates = wp_get_theme()->get_post_templates();
        $page_templates = $post_templates['page'] ?? [];

        return $page_templates;
    }

    /**
     * Restrict page templates for specific admin screens
     */
    public static function restrict_page_templates_for_screen(): void
    {
        $screen = get_current_screen();

        /**
         * Completely hide the page templates dropdown in the bulk edit UI on post list screens
         */
        if ($screen->id === 'edit-page') {
            add_filter('theme_page_templates', '__return_empty_array');
            return;
        }

        /**
         * Filter the allowed page templates on post edit screens
         */
        if ($screen->id === 'page') {
            add_filter('theme_page_templates', [__CLASS__, 'filter_page_templates'], 10, 4);
            return;
        }
    }

    /**
     * Restrict page templates
     */
    public static function filter_page_templates(array $templates, \WP_Theme $theme, ?\WP_Post $post, string $post_type): array
    {
        /**
         * Make sure this never runs during a post save of the like
         */
        if (!empty($_POST)) return $templates;

        /**
         * Administrators can select and change all templates
         */
        if (current_user_can('administrator')) return $templates;

        /**
         * Completely hide the templates dropdown if the current page is protected
         */
        if (self::is_template_protected($post)) return [];

        /**
         * Return non-protected templates only
         */
        return array_diff(
            self::get_unfiltered_page_templates(),
            self::get_protected_page_templates()
        );
    }

    /**
     * Get the slug for the permissions options page
     */
    private static function get_options_slug(): string
    {
        return 'rhau-page-permissions';
    }

    /**
     * Don't show the slug div
     */
    public static function adjust_meta_boxes(): void
    {
        global $pagenow, $post_id;

        if ($pagenow !== 'post.php') return;

        if (get_field('_rhau_lock_slug', $post_id)) {
            remove_meta_box('slugdiv', 'page', 'normal');
        }
    }

    /**
     * Render a non-editable sample permalink if the post's slug is locked
     */
    public static function get_sample_permalink_html(string $html, int $post_id, ?string $new_title, ?string $new_slug, ?\WP_Post $post): string
    {
        if (!get_field('_rhau_lock_slug', $post_id)) return $html;

        $title = __('Permalink:');
        $permalink = get_permalink($post_id);
        $display_permalink = preg_replace('/\/([^\/]*)\/$/', "/<strong>$1</strong>/", $permalink);
        $locked_icon = self::get_locked_icon();

        $html = "<strong>$title</strong>\n";
        $html .= "<a href=\"$permalink\" target=\"_blank\">$display_permalink</a> $locked_icon\n";
        return $html;
    }

    /**
     * Get a dashicon for locked things
     */
    private static function get_locked_icon(string $title = 'Locked'): string
    {
        return "<span
            class=\"dashicons dashicons-lock acf-js-tooltip rhau-lock\"
            title=\"$title\"
            aria-label=\"locked\"
            style=\"
                display: inline-block;
                font-size: 1.3em;
                vertical-align: middle;
                height: 1.1em;
                line-height: 1;
                color: rgb(0 0 0 / 0.4);\"></span>";
    }

    /**
     * Get all pages that aren't allowed to have children
     */
    public static function get_pages_with_no_children_allowed(bool $use_cache = true): array
    {
        $transient = get_transient('pages_with_no_children_allowed');

        if ($use_cache && is_array($transient)) return $transient;

        $result = self::query_pages_by_meta_key('_rhau_disallow_children', '1');

        set_transient('pages_with_no_children_allowed', $result, WEEK_IN_SECONDS);

        return $result;
    }

    /**
     * Recreate caches when saving or deleting posts
     */
    public static function on_post_state_change(int $post_id): void
    {
        if (get_post_type($post_id) !== 'page') return;
        if (get_post_status($post_id) === 'auto-draft') return;

        self::get_pages_with_no_children_allowed(use_cache: false);
    }

    /**
     * Inject custom styles for the permissions field group
     */
    public static function inject_styles(): void
    {
        ob_start() ?>
        <style>
            #acf-group_rhau_page_permissions .acf-field-true-false .acf-label,
            #acf-group_rhau_page_permissions_options .acf-field-true-false .acf-label {
                display: none !important;
            }
        </style>
<?php echo ob_get_clean();
    }

    /**
     * Restrict deletion
     */
    public static function disallow_deletion(array $caps, string $cap, int $user_id, mixed $args): array
    {
        if ($cap !== 'delete_post') return $caps;

        $post_id = $args[0] ?? null;

        if (!$post_id) return $caps;

        if (get_field('_rhau_prevent_deletion', $post_id)) $caps[] = 'do_not_allow';

        return $caps;
    }

    /**
     * Exclude pages disallowed as parent pages
     */
    public static function post_parent_dropdown_args(array $args): array
    {
        $exclude_tree = $args['exclude_tree'] ?? [];
        if (is_int($exclude_tree)) $exclude_tree = [$exclude_tree];

        $args['exclude_tree'] = array_merge(
            $exclude_tree,
            self::get_pages_with_no_children_allowed()
        );

        return $args;
    }

    /**
     * Get all pages with children disallowed
     */
    private static function query_pages_by_meta_key(string $key, mixed $value): array
    {
        $query = new \WP_Query([
            'post_type' => 'page',
            'meta_key' => $key,
            'meta_value' => $value,
            'posts_per_page' => -1,
            'fields' => 'ids',
            'suppress_filters' => true
        ]);
        return $query->posts;
    }

    /**
     * Render the template if it's protected for the current user
     */
    public static function render_protected_page_template(\WP_Post $post): void
    {
        if (current_user_can('administrator')) return;

        if (!self::is_template_protected($post)) return;

        $all_templates = self::get_unfiltered_page_templates();

        $template =  self::get_page_template($post);
        $template_name = $all_templates[$template] ?? $template;
        $title = __('Template');
        $locked_icon = self::get_locked_icon();

        $out = "<p class=\"post-attributes-label-wrapper\"><strong>$title</strong>: $template_name $locked_icon</p>";
        // Just to make sure WordPress doesn't delete the value, add a hidden input with the current page template
        $out .= "<input type='hidden' name='page_template' value='$template'></input>";
        echo $out;
    }

    /**
     * Get the page template with a fallback of "default"
     */
    private static function get_page_template(?\WP_Post $post): string
    {
        return $post->page_template ?? '' ?: 'default';
    }

    /**
     * Determine if a page's template is protected
     */
    private static function is_template_protected(?\WP_Post $post): bool
    {
        $current_template =  self::get_page_template($post);

        if ($current_template === 'default') return false;

        $protected_templates =  self::get_protected_page_templates();

        return array_key_exists($current_template, $protected_templates);
    }

    /**
     * Return an associative array of protected page templates
     *
     * Converts the ACF value from this:
     * [
     *  [
     *   'value' => 'my-template.php',
     *   'label' => 'My Template'
     *  ]
     * ]
     *
     * ...to this:
     * [
     *  'my-template.php' => 'My Template'
     * ]
     */
    private static function get_protected_page_templates(): ?array
    {
        $field_value = get_field('_protected_page_templates',  self::get_options_slug()) ?: [];

        if (empty($field_value)) return [];

        $result = [];
        foreach ($field_value as $value_and_label) {
            ['value' => $key, 'label' => $label] = $value_and_label;
            $result[$key] = $label;
        }

        return $result;
    }
}
