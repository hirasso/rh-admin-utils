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
        PageRestrictionsMetaBox::init();
        PageRestrictionsOptionsPage::init();
        add_action('add_meta_boxes', [__CLASS__, 'adjust_meta_boxes']);
        add_filter('get_sample_permalink_html', [__CLASS__, 'get_sample_permalink_html'], 10, 5);
        add_filter('map_meta_cap', [__CLASS__, 'disallow_deletion'], 10, 4);
        add_filter('page_attributes_dropdown_pages_args', [__CLASS__, 'page_dropdown_args_lock_post_parent'], 20, 2);
        add_filter('page_attributes_dropdown_pages_args', [__CLASS__, 'page_dropdown_args_no_children_allowed']);

        add_action('page_attributes_misc_attributes', [__CLASS__, 'render_protected_page_template']);
        add_action('current_screen', [__CLASS__, 'restrict_page_templates_for_screen']);
        // add_action('page_attributes_meta_box_template', [__CLASS__, 'render_protected_template_hint'], 10, 2);

        add_filter('manage_pages_columns', [__CLASS__, 'pages_list_col']);
        add_action('manage_pages_custom_column', [__CLASS__, 'pages_list_col_value'], 10, 2);

        add_filter('bulk_actions-edit-page', [__CLASS__, 'remove_page_bulk_action_edit']);

        add_filter('admin_body_class', [__CLASS__, 'admin_body_class']);
        add_action('admin_head', [__CLASS__, 'inject_styles']);
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

        if (self::is_locked($post_id)) {
            remove_meta_box('slugdiv', 'page', 'normal');
        }
    }

    /**
     * Render a non-editable sample permalink if the post's slug is locked
     */
    public static function get_sample_permalink_html(string $html, int $post_id, ?string $new_title, ?string $new_slug, ?\WP_Post $post): string
    {
        if (!self::is_locked($post_id)) return $html;

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
        $title = esc_attr__($title, RHAU_TEXT_DOMAIN);
        return "<span
            class=\"dashicons dashicons-lock rhau-lock\"
            title=\"$title\"
            aria-label=\"locked\"
            style=\"
                display: inline-block;
                font-size: 1em;
                vertical-align: middle;
                position: relative;
                top: -0.05em;
                height: 1.1em;
                line-height: 1;
                margin-left: -0.3em;
                color: rgb(0 0 0 / 0.4);\"></span>";
    }

    /**
     * Get all pages that aren't allowed to have children
     */
    public static function get_pages_with_no_children_allowed(): array
    {
        return self::query_pages_by_meta_key('_rhau_disallow_children', '1');
    }

    /**
     * Restrict deletion
     */
    public static function disallow_deletion(array $caps, string $cap, int $user_id, mixed $args): array
    {
        if ($cap !== 'delete_post') return $caps;

        $post_id = $args[0] ?? null;

        if (!$post_id) return $caps;

        if (self::is_locked($post_id)) $caps[] = 'do_not_allow';

        return $caps;
    }

    /**
     * Hide the parent page dropdown if a page's post parent is locked
     */
    public static function page_dropdown_args_lock_post_parent(array $args, \WP_Post $post): array
    {
        if (!self::is_locked($post)) return $args;

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
     * Get all pages with children disallowed
     */
    private static function query_pages_by_meta_key(string $key, mixed $value): array
    {
        $args = [
            'post_type' => 'page',
            'meta_key' => $key,
            'meta_value' => $value,
            'posts_per_page' => -1,
            'fields' => 'ids',
            'suppress_filters' => true,
        ];
        $args = QueryOptimizer::optimize_query_args($args);
        $query = new \WP_Query($args);
        return $query->posts;
    }

    /**
     * Render the template if it's protected for the current user
     */
    public static function render_protected_page_template(\WP_Post $post): void
    {
        if (!self::is_template_protected($post)) return;

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
        $out = "<p class=\"post-attributes-label-wrapper\"><strong>$label_prefix</strong>:<br>$label_title $locked_icon</p>";
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
        if (!self::apply_restrictions()) return false;

        $current_template = self::get_page_template($post);

        if ($current_template === 'default') return false;

        $protected_templates =  self::get_protected_page_templates();

        if ($post && self::is_locked($post)) return true;

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

        if (self::is_locked($post_id)) {
            echo self::get_locked_icon();
        }
    }

    /**
     * Is the provided post or the global post restricted?
     */
    private static function is_post_status_restricted(?int $post_id = null): bool
    {
        if ($post_id) return self::is_locked($post_id);

        $screen = get_current_screen();

        if (!$screen || $screen->id !== 'page') return false;

        return self::is_locked(get_post());
    }

    /**
     * Return the meta key for locked posts, publicly
     */
    public static function get_locked_meta_key(): string
    {
        return '_rhau_locked';
    }

    /**
     * Return the meta key for locked posts, publicly
     */
    public static function get_disallow_children_meta_key(): string
    {
        return '_rhau_disallow_children';
    }

    /**
     * Check if a post is locked
     */
    public static function is_locked(int|\WP_Post $post): bool
    {
        $post_id = $post->ID ?? $post;

        return (bool) get_post_meta($post_id, self::get_locked_meta_key(), true);
    }

    /**
     * Check if a post is allowed to have children
     */
    public static function is_children_disallowed(int|\WP_Post $post): bool
    {
        $post_id = $post->ID ?? $post;

        return (bool) get_post_meta($post_id, self::get_disallow_children_meta_key(), true);
    }

    /**
     * Remove the bulk "Edit" action for the post type "page"
     */
    public static function remove_page_bulk_action_edit(?array $actions): ?array
    {
        if (empty($actions)) return $actions;
        unset($actions['edit']);
        return $actions;
    }

    /**
     * Check if currently editing a locked post
     */
    private static function is_editing_locked_post(): bool
    {
        global $post;
        if (get_current_screen()->id !== 'page') return false;
        if (get_post_status($post) === 'auto-draft') return false;

        return self::is_locked($post);
    }

    /**
     * Adds an admin body class for locked posts
     */
    public static function admin_body_class(string $class): string
    {
        if (!self::is_editing_locked_post()) return $class;

        $class .= ' rhau-locked-post';

        return $class;
    }

    public static function inject_styles(): void
    {
        if (!self::is_editing_locked_post()) return;
        ?>
        <style>
            .misc-pub-visibility .edit-visibility,
            .misc-pub-post-status .edit-post-status,
            .misc-pub-curtime .edit-timestamp,
            .post-type-switcher {
                display: none !important;
            }
            #post-status-display:after,
            #post-visibility-display:after {
                content: "\f160";
                font-family: "dashicons";
                color: rgb(0 0 0 / 0.4);
                font-size: 1em;
                display: inline-block;
                position: relative;
                top: 0.15em;
            }
        </style>
        <?php
    }
}
