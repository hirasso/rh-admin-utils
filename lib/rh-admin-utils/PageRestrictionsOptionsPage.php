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

        add_action('admin_menu', array(__CLASS__, 'add_custom_menu_page'));
        add_action('admin_init', array(__CLASS__, 'register_options_page_settings'));

        add_action('admin_enqueue_scripts', [__CLASS__, 'enqueue_custom_scripts']);
    }

    public static function enqueue_custom_scripts()
    {
        wp_enqueue_script('rhau-select-2', '//unpkg.com/select2@4.1.0-rc.0/dist/js/select2.js');
        wp_enqueue_style('rhau-select-2', '//unpkg.com/select2@4.1.0-rc.0/dist/css/select2.min.css');
    }

    public static function add_custom_menu_page()
    {
        add_submenu_page(
            parent_slug: "edit.php?post_type=page",
            page_title: self::$page_title,
            menu_title: self::$menu_title,
            capability: 'manage_options',
            menu_slug: PageRestrictions::get_options_slug(),
            callback: [__CLASS__, 'render_custom_menu_page'],
            position: 99
        );
    }

    public static function render_custom_menu_page()
    {
?>
        <div class="wrap">
            <h2><?= self::$page_title ?></h2>
            <form method="post">
                <?php settings_fields('rhau_restrictions_options'); ?>
                <?php do_settings_sections('rhau-permissions-section'); ?>
                <?php submit_button(__('Save Settings')); ?>
            </form>
        </div>
    <?php
    }

    public static function register_options_page_settings()
    {
        register_setting(
            'rhau_restrictions_options',
            'rhau_restricted_templates',
            [__CLASS__, 'sanitize_restricted_templates']
        );

        add_settings_section(
            id: 'rhau_restrictions_section',
            title: '',
            callback: function() {},
            page: 'rhau-permissions-section',
            args: [],
        );

        add_settings_field(
            id: 'restricted_templates_field',
            title: 'Restricted Templates',
            callback: array(__CLASS__, 'restricted_templates_field_callback'),
            page: 'rhau-permissions-section',
            section: 'rhau_restrictions_section',
            args: [
                'label_for' => 'rhau-restricted-templates',
            ]
        );
    }



    public static function restricted_templates_field_callback()
    {
        $restricted = (array) get_option('rhau_restricted_templates', []);
        $page_templates = get_page_templates();
    ?>
        <select multiple="multiple" name="rhau_restricted_templates[]" class="rhau-multiselect" id="rhau-restricted-templates">
            <?php foreach ($page_templates as $file => $name) : ?>
                <option value="<?php echo esc_attr($file); ?>" <?php selected(in_array($file, $restricted)); ?>>
                    <?php echo esc_html($name); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <script>
            jQuery(document).ready(function() {
                console.log(jQuery('.rhau-multiselect'));
                jQuery('.rhau-multiselect').select2({

                });
            });
        </script>
        <style>
            .wp-core-ui select.rhau-multiselect {
                width: 100%;
                max-width: 50rem;
            }
        </style>
<?php
    }

    public static function sanitize_restricted_templates($input)
    {
        dump($input, true);
        return sanitize_text_field($input);
    }
}
