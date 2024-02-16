<?php
/**
 * Plugin Name: RH Admin Utilities
 * Version: 2.0.4
 * Requires PHP: 8.0
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
ForceLowercaseURLs::init();
AttachmentsHelper::init();
RemoveAds::getInstance();
PageRestrictions::init();
ACFSyncPostDate::init();
QueryOptimizer::init();
AdminDashboard::init();
WpCliCommands::init();
ACFCodeField::init();
ACFTextField::init();
Misc::getInstance();
