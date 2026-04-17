<?php
/**
 * Custom database tables for Insicore Chat Bubble.
 * Tables:
 *   {prefix}pcb_settings      — single-row settings store
 *   {prefix}pcb_submissions   — built-in contact form submissions
 *   {prefix}pcb_events        — click analytics events
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/* ── Table name helpers ──────────────────────────────────── */
function pcb_get_table() {
    global $wpdb;
    return $wpdb->prefix . 'pcb_settings';
}

function pcb_get_submissions_table() {
    global $wpdb;
    return $wpdb->prefix . 'pcb_submissions';
}

function pcb_get_events_table() {
    global $wpdb;
    return $wpdb->prefix . 'pcb_events';
}

/* ── Factory defaults (single source of truth) ───────────── */
function pcb_default_settings() {
    static $defaults = null;
    if ( $defaults === null ) {
        $defaults = [
            // Global
            'bubble_color'                 => '#1a202c',
            'bubble_hover_color'           => '',
            'animation_type'               => 'none',
            'show_tooltip'                 => 0,
            'tooltip_text'                 => '',
            'show_labels'                  => 0,
            'custom_svg'                   => '',
            // Desktop layout
            'corner'                       => 'right',
            'button_size'                  => 60,
            'border_radius'                => '',
            'offset_bottom'                => 30,
            'offset_side'                  => 30,
            // Mobile layout (blank = inherit desktop)
            'corner_mobile'                => '',
            'button_size_mobile'           => '',
            'border_radius_mobile'         => '',
            'offset_bottom_mobile'         => '',
            'offset_side_mobile'           => '',
            // Tooltip style
            'tooltip_bg_color'             => '',
            'tooltip_color'                => '',
            'tooltip_font_size'            => '',
            'tooltip_padding'              => '',
            'tooltip_border_radius'        => '',
            'tooltip_bg_color_mobile'      => '',
            'tooltip_color_mobile'         => '',
            'tooltip_font_size_mobile'     => '',
            'tooltip_padding_mobile'       => '',
            'tooltip_border_radius_mobile' => '',
            // Notification badge
            'badge_enabled'                => 0,
            'badge_text'                   => '1',
            'badge_bg_color'               => '#ef4444',
            'badge_text_color'             => '#ffffff',
            // Message bubble popup
            'msgbubble_enabled'            => 0,
            'msgbubble_text'                => '',
            'msgbubble_delay'               => 3,
            'msgbubble_autohide'            => 0,
            'msgbubble_bg_color'            => '#ffffff',
            'msgbubble_text_color'          => '#1a202c',
            // Theme preset + Custom CSS
            'theme_preset'                 => '',
            'custom_css'                   => '',
            // Triggers
            'trigger_scroll_enabled'       => 0,
            'trigger_scroll_percent'       => 30,
            'trigger_time_enabled'         => 0,
            'trigger_time_seconds'         => 10,
            'trigger_exit_intent_enabled'  => 0,
            // Contact form
            'form_notify_email'            => '',
            'form_success_message'         => '',
            // JSON payloads
            'channels_data'                => '[]',
            'visibility_rules'             => '[]',
        ];
    }
    return $defaults;
}

/* ── Current DB schema version ──────────────────────────── */
const PCB_DB_VERSION = '3.3.0';

/**
 * Create or migrate the settings table.
 */
function pcb_create_table() {
    global $wpdb;
    $t       = pcb_get_table();
    $subs_t  = pcb_get_submissions_table();
    $events_t = pcb_get_events_table();
    $cc      = $wpdb->get_charset_collate();

    $sql_settings = "CREATE TABLE {$t} (
        id tinyint unsigned NOT NULL DEFAULT 1,
        bubble_color varchar(20) NOT NULL DEFAULT '#1a202c',
        bubble_hover_color varchar(20) NOT NULL DEFAULT '',
        animation_type varchar(20) NOT NULL DEFAULT 'none',
        show_tooltip tinyint(1) NOT NULL DEFAULT 0,
        tooltip_text varchar(500) NOT NULL DEFAULT '',
        tooltip_bg_color varchar(20) NOT NULL DEFAULT '',
        tooltip_color varchar(20) NOT NULL DEFAULT '',
        tooltip_font_size varchar(10) NOT NULL DEFAULT '',
        tooltip_padding varchar(10) NOT NULL DEFAULT '',
        tooltip_border_radius varchar(10) NOT NULL DEFAULT '',
        tooltip_bg_color_mobile varchar(20) NOT NULL DEFAULT '',
        tooltip_color_mobile varchar(20) NOT NULL DEFAULT '',
        tooltip_font_size_mobile varchar(10) NOT NULL DEFAULT '',
        tooltip_padding_mobile varchar(10) NOT NULL DEFAULT '',
        tooltip_border_radius_mobile varchar(10) NOT NULL DEFAULT '',
        show_labels tinyint(1) NOT NULL DEFAULT 0,
        custom_svg varchar(2048) NOT NULL DEFAULT '',
        corner varchar(10) NOT NULL DEFAULT 'right',
        button_size smallint unsigned NOT NULL DEFAULT 60,
        border_radius varchar(10) NOT NULL DEFAULT '',
        offset_bottom smallint unsigned NOT NULL DEFAULT 30,
        offset_side smallint unsigned NOT NULL DEFAULT 30,
        corner_mobile varchar(10) NOT NULL DEFAULT '',
        button_size_mobile varchar(10) NOT NULL DEFAULT '',
        border_radius_mobile varchar(10) NOT NULL DEFAULT '',
        offset_bottom_mobile varchar(10) NOT NULL DEFAULT '',
        offset_side_mobile varchar(10) NOT NULL DEFAULT '',
        badge_enabled tinyint(1) NOT NULL DEFAULT 0,
        badge_text varchar(10) NOT NULL DEFAULT '1',
        badge_bg_color varchar(20) NOT NULL DEFAULT '#ef4444',
        badge_text_color varchar(20) NOT NULL DEFAULT '#ffffff',
        msgbubble_enabled tinyint(1) NOT NULL DEFAULT 0,
        msgbubble_text varchar(500) NOT NULL DEFAULT '',
        msgbubble_delay smallint unsigned NOT NULL DEFAULT 3,
        msgbubble_autohide smallint unsigned NOT NULL DEFAULT 0,
        msgbubble_bg_color varchar(20) NOT NULL DEFAULT '#ffffff',
        msgbubble_text_color varchar(20) NOT NULL DEFAULT '#1a202c',
        theme_preset varchar(30) NOT NULL DEFAULT '',
        custom_css longtext,
        trigger_scroll_enabled tinyint(1) NOT NULL DEFAULT 0,
        trigger_scroll_percent smallint unsigned NOT NULL DEFAULT 30,
        trigger_time_enabled tinyint(1) NOT NULL DEFAULT 0,
        trigger_time_seconds smallint unsigned NOT NULL DEFAULT 10,
        trigger_exit_intent_enabled tinyint(1) NOT NULL DEFAULT 0,
        form_notify_email varchar(200) NOT NULL DEFAULT '',
        form_success_message varchar(500) NOT NULL DEFAULT '',
        channels_data longtext,
        visibility_rules longtext,
        PRIMARY KEY  (id)
    ) {$cc};";

    $sql_subs = "CREATE TABLE {$subs_t} (
        id bigint unsigned NOT NULL AUTO_INCREMENT,
        submitted_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
        name varchar(200) NOT NULL DEFAULT '',
        email varchar(200) NOT NULL DEFAULT '',
        phone varchar(60) NOT NULL DEFAULT '',
        message text NOT NULL,
        page_url varchar(500) NOT NULL DEFAULT '',
        ip_address varchar(45) NOT NULL DEFAULT '',
        is_read tinyint(1) NOT NULL DEFAULT 0,
        PRIMARY KEY  (id),
        KEY submitted_at (submitted_at),
        KEY is_read (is_read)
    ) {$cc};";

    $sql_events = "CREATE TABLE {$events_t} (
        id bigint unsigned NOT NULL AUTO_INCREMENT,
        event_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
        event_type varchar(30) NOT NULL DEFAULT 'click',
        channel_type varchar(30) NOT NULL DEFAULT '',
        channel_label varchar(100) NOT NULL DEFAULT '',
        page_url varchar(500) NOT NULL DEFAULT '',
        device varchar(20) NOT NULL DEFAULT '',
        campaign varchar(50) NOT NULL DEFAULT '',
        PRIMARY KEY  (id),
        KEY event_at (event_at),
        KEY channel_type (channel_type),
        KEY event_type (event_type)
    ) {$cc};";

    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    dbDelta( $sql_settings );
    dbDelta( $sql_subs );
    dbDelta( $sql_events );

    // Seed the single settings row if it doesn't exist yet.
    $wpdb->query( "INSERT IGNORE INTO `{$t}` (`id`) VALUES (1)" );

    update_option( 'pcb_db_version', PCB_DB_VERSION );
}

/**
 * Auto-migrate on admin requests when the stored DB version is out of date.
 */
add_action( 'admin_init', 'pcb_maybe_upgrade_schema' );
function pcb_maybe_upgrade_schema() {
    if ( get_option( 'pcb_db_version' ) !== PCB_DB_VERSION ) {
        pcb_create_table();
    }
}

/* ── Drop tables (called on uninstall) ───────────────────── */
function pcb_drop_table() {
    global $wpdb;
    $wpdb->query( 'DROP TABLE IF EXISTS `' . pcb_get_table() . '`' );
    $wpdb->query( 'DROP TABLE IF EXISTS `' . pcb_get_submissions_table() . '`' );
    $wpdb->query( 'DROP TABLE IF EXISTS `' . pcb_get_events_table() . '`' );
    delete_option( 'pcb_db_version' );
}

/* ── Read settings (with static cache) ───────────────────── */
function pcb_get_settings( $clear_cache = false ) {
    static $cached = null;
    if ( $clear_cache ) { $cached = null; return null; }
    if ( $cached !== null ) return $cached;

    global $wpdb;
    $row = $wpdb->get_row(
        $wpdb->prepare( 'SELECT * FROM `' . pcb_get_table() . '` WHERE id = %d', 1 ),
        ARRAY_A
    );

    if ( ! is_array( $row ) ) {
        $cached = pcb_default_settings();
        return $cached;
    }

    // Merge with defaults so newly added columns always resolve.
    $row = array_merge( pcb_default_settings(), $row );

    // Normalise LONGTEXT JSON columns to sane defaults.
    $row['channels_data']    = $row['channels_data']    ?: '[]';
    $row['visibility_rules'] = $row['visibility_rules'] ?: '[]';
    $row['custom_css']       = $row['custom_css']       ?: '';

    $cached = $row;
    return $cached;
}

/* ── Write settings (uses UPDATE instead of REPLACE) ─────── */
function pcb_save_settings( array $clean ) {
    global $wpdb;

    // Keep only keys we know about, filled in with defaults where missing.
    $data = array_intersect_key(
        array_merge( pcb_default_settings(), $clean ),
        pcb_default_settings()
    );

    // Invalidate the static cache so subsequent reads reflect the new values.
    pcb_get_settings( true );

    return $wpdb->update( pcb_get_table(), $data, [ 'id' => 1 ] ) !== false;
}
