<?php
/**
 * Plugin Name: Insicore Chat Bubble
 * Plugin URI:  https://github.com/hadenlin/insicore-chat-bubble
 * Description: A customizable floating contact button for WhatsApp, LINE, Messenger, Telegram and more. Includes display rules, greeting message, badge, animations, themes, analytics and built-in contact form.
 * Version:     1.0.0
 * Author:      Pang Guo Design Limited
 * Author URI:  https://github.com/hadenlin
 * Text Domain: insicore-chat-bubble
 * Domain Path: /languages
 * License:     GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Requires at least: 5.9
 * Requires PHP: 7.4
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'PCB_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'PCB_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

// Load database helpers first (pcb_get_settings / pcb_save_settings / etc.)
require_once PCB_PLUGIN_DIR . 'includes/database.php';
require_once PCB_PLUGIN_DIR . 'includes/settings.php';
require_once PCB_PLUGIN_DIR . 'includes/frontend.php';
require_once PCB_PLUGIN_DIR . 'includes/privacy.php';

// ── Activation: create custom table ───────────────────────
register_activation_hook( __FILE__, 'pcb_activate' );
function pcb_activate() {
    pcb_create_table();
}

// ── Deactivation: intentionally left empty (data preserved) ──
register_deactivation_hook( __FILE__, '__return_null' );

// ── Uninstall: drop table + all plugin data ───────────────
register_uninstall_hook( __FILE__, 'pcb_uninstall' );
function pcb_uninstall() {
    pcb_drop_table();
}

// ── Translations ───────────────────────────────────────────
function pcb_load_textdomain() {
    load_plugin_textdomain( 'insicore-chat-bubble', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
}
add_action( 'plugins_loaded', 'pcb_load_textdomain' );

// Admin asset enqueuing lives in includes/admin/assets.php (loaded via settings.php)
