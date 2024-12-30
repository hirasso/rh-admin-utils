<?php

/*
* Copyright (c) 2022 Rasso Hilber
* https://rassohilber.com
*/

namespace RH\AdminUtils;

/**
 * A class to use one base template to wrap all other wordpress templates
 */
class ACFPasswordUtilities
{
    /**
     * Init
     */
    public static function init()
    {
        add_action('acf/input/admin_footer', [__CLASS__, 'acf_admin_footer']);
        add_action('acf/render_field/type=password', [__CLASS__, 'after_render_field_password']);
    }

    /**
     * Adds buttons to password field
     *
     * @param array $field
     * @return void
     */
    public static function after_render_field_password(array $field): void
    {
        ob_start() ?>
        <div rhau-x-data="ACFPasswordUtilities" class="acf-input-append acf-password-utilities row-actions">
            <span><a href="##" rhau-x-ref="toggle" rhau-x-text="toggleText"></a></span>
            <span><a href="##" rhau-x-ref="generator">Generate</a></span>
            <template rhau-x-if="!!value && copySupported">
                <span><a href="##" rhau-x-on:click.prevent="onCopyClick" rhau-x-ref="copy" rhau-x-text="copyText"></a></span>
            </template>
        </div>
        <?php echo ob_get_clean();
    }

    public static function acf_admin_footer()
    {
        ob_start() ?>
        <style>
            .acf-password-utilities,
            .acf-password-utilities * {
                box-sizing: border-box;
            }

            .acf-password-utilities {
                padding: 0 1em;
                height: 100%;
                display: flex;
                left: auto;
                align-items: center;
                top: 0;
                right: 0;
                position: absolute;
                border-left: 1px solid #8c8f94;
            }

            .acf-password-utilities a {
                text-decoration: none;
            }

            .acf-password-utilities>*+*:before {
                content: "|";
                display: inline-block;
                margin: 0 0.5em;
            }
        </style>
        <?php echo ob_get_clean();
    }
}
