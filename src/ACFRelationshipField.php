<?php

namespace RH\AdminUtils;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class ACFRelationshipField
{
    /**
     * Init
     */
    public static function init()
    {
        add_filter('acf/prepare_field/type=relationship', [__CLASS__, 'prepare_field']);
    }

    /**
     * Handle ACF code fields
     *
     * @param array $field
     * @return array
     */
    public static function prepare_field(array $field): array
    {
        $field['wrapper']['rhau-x-data'] = 'ACFRelationshipField';
        return $field;
    }
}
