<?php

namespace RH\AdminUtils;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

/**
 * Adds custom plugins to TinyMCE editors
 */
class TinyMcePlugins
{

    public static function init()
    {
        add_filter('mce_buttons', [self::class, 'mce_buttons'], PHP_INT_MAX - 100);
        add_filter('mce_external_plugins', [self::class, 'mce_external_plugins']);
    }

    public static function mce_buttons($buttons)
    {
        $customButtons = ['rhau_link_to_file'];
        $customButtons = apply_filters('rhau/tinymce/buttons', $customButtons) ?: [];

        foreach ($customButtons as $btn) {
            $buttons[] = $btn;
        }

        return $buttons;
    }

    public static function mce_external_plugins($plugins)
    {
        $plugins['rhauTinyMcePlugins'] = rhau()->asset_uri('assets/rhau-tinymce-plugins.js');
        return $plugins;
    }
}
