<?php
/**
 * Description: A compagnion must-use plugin rh-admin-utils
 * Author: Rasso Hilber
 * Author URI: https://rassohilber.com
 */

namespace RH\AdminUtils\HelperPlugin;

/**
 * Prevent notice for `_load_textdomain_just_in_time` in WP 6.7.1
 * @see https://github.com/10up/debug-bar-elasticpress/issues/102#issuecomment-2504554953
 * @see https://gist.github.com/kowsar89/ed30f2b7abc5d4784ba4b05503c70fe0
 */
add_filter('doing_it_wrong_trigger_error', function ($status, $function_name) {
    if ('_load_textdomain_just_in_time' === $function_name) {
        return false;
    }
    return $status;
}, 10, 2);
