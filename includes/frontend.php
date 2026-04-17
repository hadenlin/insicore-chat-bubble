<?php
/**
 * Loader for the public-side pieces of Premium Contact Bubble.
 * The heavy lifting has been split into `includes/frontend/*.php`.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

require_once PCB_PLUGIN_DIR . 'includes/frontend/channel-url.php';
require_once PCB_PLUGIN_DIR . 'includes/frontend/visibility.php';
require_once PCB_PLUGIN_DIR . 'includes/frontend/assets.php';
require_once PCB_PLUGIN_DIR . 'includes/frontend/render.php';
require_once PCB_PLUGIN_DIR . 'includes/frontend/form-handler.php';
require_once PCB_PLUGIN_DIR . 'includes/frontend/analytics.php';
