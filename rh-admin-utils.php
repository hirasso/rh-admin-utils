<?php

/**
 * Plugin Name: RH Admin Utilities
 * Version: 1.7.3
 * Author: Rasso Hilber
 * Description: Admin Utilities for WordPress. Removes plugin ads, adds custom buttons to the admin bar (publish, clear cache), allows editors to add users (except administrators), disables comments. Provides filters to adjust functionality.
 * Author URI: https://rassohilber.com
 **/

namespace RH\AdminUtils;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

/**
 * Require composer autoloader
 */
require_once(__DIR__ . '/lib/vendor/autoload.php');


define('RHAU_PLUGIN_DIR', plugin_dir_path(__FILE__));

/**
 * Initialize main class
 */
AdminUtils::getInstance();

/**
 * Make AdminUtils instance available API calls
 *
 * @return AdminUtils
 */
function rhau()
{
    return AdminUtils::getInstance();
}

/**
 * Initialize util classes
 */
EditorsAddUsers::getInstance();
WpscClearCache::getInstance();
RemoveAds::getInstance();
AdminBarPublishButton::getInstance();
Misc::getInstance();
Environments::getInstance();
EditorInChief::getInstance();
DisableComments::getInstance();
PendingReviews::getInstance();
ACFPasswordUtilities::init();
AdminDashboard::init();
WpscHtaccessHelper::init();
ACFSyncFieldGroups::init();
WpCliCommands::init();
