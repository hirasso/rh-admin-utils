<?php

/**
 * Plugin Name: RH Admin Utilities
 * Version: 3.0.3
 * Requires PHP: 8.2
 * Author: Rasso Hilber
 * Description: Admin Utilities for WordPress. Removes plugin ads, adds custom buttons to the admin bar (publish, clear cache), allows editors to add users (except administrators), disables comments. Provides filters to adjust functionality.
 * Author URI: https://rassohilber.com
 * License: GPL-2.0-or-later
 **/

declare(strict_types=1);

namespace RH\AdminUtils;

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}


/**
 * Require the composer autoloader
 */
if (is_readable(__DIR__ . '/vendor/autoload.php')) {
    require_once __DIR__ . '/vendor/autoload.php';
}

define('RHAU_PLUGIN_URI', untrailingslashit(plugin_dir_url(__FILE__)));
define('RHAU_PLUGIN_DIR', untrailingslashit(__DIR__));

define('RHAU_TEXT_DOMAIN', 'rh-admin-utils');

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
 * Initialize the modules
 */
AdminBarPublishButton::getInstance();
EditorsAddUsers::getInstance();
DisableComments::getInstance();
ACFRestrictFieldAccess::init();
WpscClearCache::getInstance();
PendingReviews::getInstance();
ACFPasswordUtilities::init();
ACFRelationshipField::init();
EditorInChief::getInstance();
Environments::getInstance();
ACFSyncFieldGroups::init();
ACFOembedWhitelist::init();
ForceLowercaseURLs::init();
AttachmentsHelper::init();
RemoveAds::getInstance();
PageRestrictions::init();
ACFSyncPostDate::init();
ACFOembedCache::init();
QueryOptimizer::init();
AdminDashboard::init();
TinyMcePlugins::init();
WpCliCommands::init();
ACFCodeField::init();
ACFTextField::init();
Misc::getInstance();


UpdateChecker::init(entryPoint: __FILE__);
