<?php

/*
 * Copyright (c) Rasso Hilber
 * https://rassohilber.com
 *
 */

namespace RH\AdminUtils;

class PageRestrictionsOptionsPage
{
    private static string $page_title;
    private static string $menu_title;

    public static function init()
    {
        self::$page_title = __('Global Page Restrictions', 'rhau');
        self::$menu_title = __('Restrictions', 'rhau');

        add_action('admin_menu', array(__CLASS__, 'add_options_page'));
        add_action('admin_init', array(__CLASS__, 'register_options_page_settings'));

        // add_action('admin_enqueue_scripts', [__CLASS__, 'enqueue_custom_scripts']);
    }

    /**
     * Enqueue custom scripts for the UI
     */
    public static function enqueue_custom_scripts()
    {
        wp_enqueue_script('rhau-select-2', '//unpkg.com/select2@4.1.0-rc.0/dist/js/select2.js');
        wp_enqueue_style('rhau-select-2', '//unpkg.com/select2@4.1.0-rc.0/dist/css/select2.min.css');
    }

    /**
     * Add the custom options page
     */
    public static function add_options_page()
    {
        add_submenu_page(
            parent_slug: "edit.php?post_type=page",
            page_title: self::$page_title,
            menu_title: self::$menu_title,
            capability: 'manage_options',
            menu_slug: PageRestrictions::get_options_slug(),
            callback: [__CLASS__, 'render_options_page'],
            position: 99
        );
    }

    /**
     * Render the custom options page
     */
    public static function render_options_page()
    {
        ?>
        <div class="wrap">
            <h2><?= self::$page_title ?></h2>
            <form method="post" action="options.php">
                <?php settings_fields('rhau_restrictions_options'); ?>
                <?php do_settings_sections('rhau-permissions-section'); ?>
                <?php submit_button(__('Save Settings')); ?>
            </form>
        </div>
        <?php
    }

    /**
     * Register fields for the options page
     */
    public static function register_options_page_settings()
    {
        register_setting(
            option_group: 'rhau_restrictions_options',
            option_name: 'rhau_protected_templates',
            args: [
                'type' => 'array',
                'description' => 'A list of all protected templates',
                'sanitize_callback' => [__CLASS__, 'sanitize_setting_protected_templates'],
                'default' => []
            ]
        );

        add_settings_section(
            id: 'rhau_restrictions_section',
            title: '',
            callback: function () {
            },
            page: 'rhau-permissions-section',
            args: [],
        );

        add_settings_field(
            id: 'protected_templates_field',
            title: 'Protected Page Templates',
            callback: array(__CLASS__, 'protected_templates_field_callback'),
            page: 'rhau-permissions-section',
            section: 'rhau_restrictions_section',
            args: []
        );
    }

    /**
     * Render the field for protected page templates
     */
    public static function protected_templates_field_callback()
    {
        $protected_templates = PageRestrictions::get_protected_page_templates();
        $page_templates = PageRestrictions::get_unfiltered_page_templates();
        ?>
        <fieldset>
            <?php foreach ($page_templates as $file => $name) : ?>
                <?php
                $value = esc_attr($file);
                $id = "protected-template--$value";
                $checked = checked(array_key_exists($file, $protected_templates), true, false);
                ?>
                <label for="<?= $id ?>">
                    <input id="<?= $id ?>" type="checkbox" name="rhau_protected_templates[]" value="<?= $file ?>" <?= $checked ?>></input>
                    <?php echo esc_html($name); ?>
                </label><br>
            <?php endforeach; ?>
        </fieldset>
        <script>
            jQuery(function() {
                jQuery('.rhau-multiselect').select2();
            });
        </script>
        <?php
    }

    /**
     * Sanitize the protected templates
     */
    public static function sanitize_setting_protected_templates(?array $protected_templates): array
    {
        if (empty($protected_templates)) return [];

        $all_templates = PageRestrictions::get_unfiltered_page_templates();

        $value = [];
        foreach ($protected_templates as $key) {
            $value[$key] = $all_templates[$key];
        }

        return $value;
    }
}
