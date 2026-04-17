<?php
/**
 * Loader for the admin-side pieces of Premium Contact Bubble.
 * The heavy lifting has been split into `includes/admin/*.php`.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

require_once PCB_PLUGIN_DIR . 'includes/admin/themes.php';
require_once PCB_PLUGIN_DIR . 'includes/admin/presets.php';
require_once PCB_PLUGIN_DIR . 'includes/admin/menu.php';
require_once PCB_PLUGIN_DIR . 'includes/admin/save-handler.php';
require_once PCB_PLUGIN_DIR . 'includes/admin/assets.php';
require_once PCB_PLUGIN_DIR . 'includes/admin/submissions.php';
require_once PCB_PLUGIN_DIR . 'includes/admin/analytics-page.php';
