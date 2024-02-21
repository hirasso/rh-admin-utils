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

class PageRestrictionsMetaBox
{
    private static string $meta_box_id = 'rhau-page-restrictions-metabox';
    private static string $nonce_action = 'rhau_page_restrictions_box';
    private static string $nonce_name = '_rhau_page_restrictions_nonce';

    // Static function to initialize the meta box
    public static function init()
    {
        add_action('add_meta_boxes_page', array(__CLASS__, 'add_meta_box'));
        add_action('save_post', array(__CLASS__, 'save_custom_meta_box'));
    }

    /** Add the meta box */
    public static function add_meta_box(\WP_Post $post): void
    {
        if (!current_user_can('manage_options')) return;
        \add_meta_box(
            id: self::$meta_box_id,
            title: __('Restrictions', RHAU_TEXT_DOMAIN),
            callback: [__CLASS__, 'render_custom_meta_box'],
            screen: 'page',
            context: 'side',
            priority: 'high',
        );
    }

    /* Render the meta box */
    public static function render_custom_meta_box(\WP_Post $post): void
    {

        ?>
        <style type="text/css">
            #rhau-page-restrictions-metabox .inside {
                padding: 0;
                margin: 0;
            }
            #rhau-page-restrictions-metabox .inside label {
                display:block;
            }
            #rhau-page-restrictions-metabox .inside p {
                padding: 8px 12px;
                margin: 0;
            }
            #rhau-page-restrictions-metabox .inside p+p {
                border-top: 1px solid rgb(0 0 0 / 0.1);
            }
            #rhau-page-restrictions-metabox .inside p:last-of-type {
                margin-bottom: 0;
            }
        </style>

        <p>
            <label for="rhau_locked_field"
                class="acf-js-tooltip"
                title="<?= esc_attr__('Lock the slug, parent, post status, visiblity and post template for this page', RHAU_TEXT_DOMAIN) ?>">
                <input
                    type="checkbox"
                    id="rhau_locked_field"
                    name="<?= PageRestrictions::get_locked_meta_key() ?>"
                    <?php checked(PageRestrictions::is_locked($post)); ?>
                    value="1"></input>
                <?php esc_html_e('Lock this Page', RHAU_TEXT_DOMAIN); ?>
            </label>
        </p>
        <p>
            <label for="rhau_disallow_children_field">
                <input
                    type="checkbox"
                    id="rhau_disallow_children_field"
                    name="<?= PageRestrictions::get_disallow_children_meta_key() ?>"
                    <?php checked(PageRestrictions::is_children_disallowed($post)); ?>
                    value="1"></input>
                <?php esc_html_e('Disallow Children', RHAU_TEXT_DOMAIN); ?>
            </label>
        </p>
        <?php
        wp_nonce_field(self::$nonce_action, self::$nonce_name);
    }

    /** Save the meta box data */
    public static function save_custom_meta_box(int $post_id)
    {
        $nonce = $_POST[self::$nonce_name] ?? null;

        // Verify that the nonce
        if (empty($nonce) || !wp_verify_nonce($nonce, self::$nonce_action)) {
            return;
        }

        /** Verify the post type */
        if (get_post_type($post_id) !== 'page') return;

        /** Bail early if this is an autosave */
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;

        /** Check user caps */
        if (!current_user_can('mange_options', $post_id)) return;

        /** Update the locked status of the post */
        $locked = $_POST[PageRestrictions::get_locked_meta_key()] ?? null;
        if (!is_null($locked)) {
            update_post_meta(
                $post_id,
                PageRestrictions::get_locked_meta_key(),
                (int) $locked
            );
        }

        /** Update the disallow children setting for the post */
        $disallow_children = $_POST[PageRestrictions::get_disallow_children_meta_key()] ?? null;
        if (!is_null($disallow_children)) {
            update_post_meta(
                $post_id,
                PageRestrictions::get_disallow_children_meta_key(),
                (int) $disallow_children
            );
        }
    }
}
