<?php

/*
 * Copyright (c) Rasso Hilber
 * https://rassohilber.com
 *
 * Adds support for custom restrictions on a per-page level:
 *
 *  - Lock the slug of a page
 *  - Prevent the deletion of a page
 *  - Disallow children for a page
 *  - Protect selected page templates so that they can only be selected and changed by administrators
 *
 */

namespace RH\AdminUtils;

class PageRestrictions
{
    private static string $prefix = 'rhau_page_restrictions';

    public static function init()
    {
        PageRestrictionsOptionsPage::init();
        add_action('acf/init', [__CLASS__, 'add_page_field_group']);
        add_action('add_meta_boxes', [__CLASS__, 'adjust_meta_boxes']);
        add_filter('get_sample_permalink_html', [__CLASS__, 'get_sample_permalink_html'], 10, 5);
        add_action('admin_head', [__CLASS__, 'inject_styles']);
        add_filter('map_meta_cap', [__CLASS__, 'disallow_deletion'], 10, 4);
        add_filter('page_attributes_dropdown_pages_args', [__CLASS__, 'page_dropdown_args_lock_post_parent'], 20, 2);
        add_filter('page_attributes_dropdown_pages_args', [__CLASS__, 'page_dropdown_args_no_children_allowed']);
        add_filter('quick_edit_dropdown_pages_args', [__CLASS__, 'quick_edit_dropdown_pages_args']);
        add_action('page_attributes_misc_attributes', [__CLASS__, 'render_protected_page_template']);
        add_action('current_screen', [__CLASS__, 'restrict_page_templates_for_screen']);
        add_action('page_attributes_meta_box_template', [__CLASS__, 'render_protected_template_hint'], 10, 2);

        add_filter('manage_pages_columns', [__CLASS__, 'pages_list_col']);
        add_action('manage_pages_custom_column', [__CLASS__, 'pages_list_col_value'], 10, 2);

        add_action('save_post', [__CLASS__, 'on_post_state_change']);
        add_action('deleted_post', [__CLASS__, 'on_post_state_change']);
        add_action('trashed_post', [__CLASS__, 'on_post_state_change']);
        add_action('untrash_post', [__CLASS__, 'on_post_state_change']);
    }

    /**
     * Render the field group for the page restrictions
     */
    public static function add_page_field_group(): void
    {
        $group_key = "group_" . self::$prefix;

        acf_add_local_field_group(array(
            'key' => $group_key,
            'title' => 'Restrictions',
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
            'message' => 'Lock slug',
            'name' => '_rhau_lock_slug',
            'translations' => 'sync',
            'type' => 'true_false',
            'default_value' => 0
        ]);

        acf_add_local_field([
            'parent' => $group_key,
            'key' => "field_rhau_lock_post_parent",
            'label' => 'Children',
            'message' => 'Lock parent',
            'name' => '_rhau_lock_post_parent',
            'translations' => 'sync',
            'type' => 'true_false',
            'default_value' => 0
        ]);

        acf_add_local_field([
            'parent' => $group_key,
            'key' => "field_rhau_lock_post_status",
            'label' => 'Post Status',
            'message' => 'Lock post status',
            'name' => '_rhau_lock_post_status',
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
     * Get all theme templates
     */
    public static function get_unfiltered_page_templates(): array
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
     * Renders a hint for administrators that a template is protected
     */
    public static function render_protected_template_hint(string $template, \WP_Post $post): void
    {
        if (!self::is_template_protected($post)) return;
        if (!self::apply_restrictions()) {
            echo self::get_locked_icon('Only editable for administrators');
        }
    }

    /**
     * Should the restrictions be applied?
     */
    private static function apply_restrictions(): bool
    {
        return !current_user_can('administrator');
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
        if (!self::apply_restrictions()) return $templates;

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
     * Get the slug for the restrictions options page
     */
    public static function get_options_slug(): string
    {
        return 'rhau-page-restrictions';
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
     * Inject custom styles for the restrictions field group
     */
    public static function inject_styles(): void
    {
        ob_start() ?>
        <style>
            #acf-group_rhau_page_restrictions .acf-field-true-false .acf-label,
            #acf-group_rhau_page_restrictions_options .acf-field-true-false .acf-label {
                display: none !important;
            }

            <?php if (self::is_post_status_restricted()) :
                ?>.edit-post-status {
                display: none !important;
            }

            <?php endif; ?>
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
     * Hide the parent page dropdown if a page's post parent is locked
     */
    public static function page_dropdown_args_lock_post_parent(array $args, \WP_Post $post): array
    {
        if (!get_field('_rhau_lock_post_parent', $post)) return $args;

        /** no post is a child of -1 */
        $args['child_of'] = -1;

        $parent_id = $post->post_parent;
        $parent_title = __('Main Page (no parent)');

        if ($parent_id !== 0) {
            $parent_title = get_the_title($parent_id);
        }

        self::render_locked_page_attribute(
            __('Parent'),
            $parent_title,
            'parent_id',
            $parent_id
        );

        return $args;
    }

    /**
     * Exclude pages disallowed as parent pages
     */
    public static function page_dropdown_args_no_children_allowed(array $args): array
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
     * Completely hide the parent page dropdown in the bulk UI as it doesn't
     * play nicely with locked post parents
     */
    public static function quick_edit_dropdown_pages_args(array $args): array
    {
        $args['child_of'] = -1;
        $locked_icon = self::get_locked_icon();
        echo "$locked_icon";
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
        if (!self::is_template_protected($post)) return;

        if (!self::apply_restrictions()) return;

        $all_templates = self::get_unfiltered_page_templates();

        $template =  self::get_page_template($post);
        $template_name = $all_templates[$template] ?? $template;

        self::render_locked_page_attribute(
            __('Template'),
            $template_name,
            'page_template',
            $template
        );
    }

    /**
     * Render a locked page attribute
     * Also renders a hidden field to make sure the value is being preserved
     * (even though this might not be necessary)
     */
    private static function render_locked_page_attribute(
        string $label_prefix,
        string $label_title,
        string $hidden_field_name,
        string $hidden_field_value
    ): void {
        $locked_icon = self::get_locked_icon();
        $out = "<p class=\"post-attributes-label-wrapper\"><strong>$label_prefix</strong>: $label_title $locked_icon</p>";
        $out .= "<input type='hidden' name='$hidden_field_name' value='$hidden_field_value'></input>";
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
     */
    public static function get_protected_page_templates(): ?array
    {
        return (array) get_option('rhau_protected_templates', []);
    }

    /**
     * Render a column with a lock for locked posts
     */
    public static function pages_list_col($cols, $post_type = 'page'): array
    {
        if ($post_type !== 'page') return $cols;

        $cols["rhau_is_locked"] = __('Locked');

        return $cols;
    }

    /**
     * Render a lock for locked pages
     */
    public static function pages_list_col_value(string $column_name, int $post_id): void
    {
        if ($column_name !== "rhau_is_locked") return;

        $locks = [
            [
                'label' => 'Parent',
                'active' => get_field('_rhau_lock_post_parent', $post_id),
            ],
            [
                'label' => 'Slug',
                'active' => get_field('_rhau_lock_slug', $post_id),
            ],
            [
                'label' => 'Deletion',
                'active' => get_field('_rhau_prevent_deletion', $post_id),
            ],
            [
                'label' => 'Status',
                'active' => get_field('_rhau_lock_post_status', $post_id),
            ],
            [
                'label' => 'Children',
                'active' => get_field('_rhau_disallow_children', $post_id),
            ]
        ];
        $active_locks = array_filter($locks, fn ($lock) => (bool) $lock['active']);

        if (empty($active_locks)) return;

        $active_locks_string = implode(', ', array_column($active_locks, 'label'));

        echo self::get_locked_icon() . "$active_locks_string";
    }

    /**
     * Is the provided post or the global post restricted?
     */
    private static function is_post_status_restricted(?int $post_id = null): bool
    {
        if ($post_id) return (bool) get_field('_rhau_lock_post_status', $post_id);

        $screen = get_current_screen();

        if (!$screen || $screen->id !== 'page') return false;

        return (bool) get_field('_rhau_lock_post_status', get_post());
    }
}
