<?php
/**
 * Frontend asset enqueuing + inline CSS variables.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

add_action( 'wp_enqueue_scripts', 'pcb_enqueue_assets' );
add_action( 'wp_footer',          'pcb_preview_listener', 10 );

// Hide admin bar inside the preview iframe.
add_action( 'init', function () {
    if ( isset( $_GET['pcb_preview'] ) ) {
        add_filter( 'show_admin_bar', '__return_false' );
    }
} );

function pcb_enqueue_assets() {
    wp_enqueue_style(  'pcb-style',  PCB_PLUGIN_URL . 'assets/css/style.css',           [], '3.0.0' );
    wp_enqueue_script( 'pcb-bubble', PCB_PLUGIN_URL . 'assets/js/frontend-bubble.js',   [], '3.0.0', true );

    $settings = pcb_get_settings();
    $inline   = pcb_build_inline_css( $settings );

    // Theme preset CSS.
    if ( ! empty( $settings['theme_preset'] ) ) {
        if ( ! function_exists( 'pcb_get_theme_presets' ) ) {
            require_once PCB_PLUGIN_DIR . 'includes/admin/themes.php';
        }
        $themes = pcb_get_theme_presets();
        if ( isset( $themes[ $settings['theme_preset'] ]['css'] ) ) {
            $inline .= "\n" . $themes[ $settings['theme_preset'] ]['css'];
        }
    }

    // Custom CSS (sanitized to strip </style> type bytes but preserve selectors).
    if ( ! empty( $settings['custom_css'] ) ) {
        $inline .= "\n" . wp_strip_all_tags( $settings['custom_css'] );
    }

    wp_add_inline_style( 'pcb-style', $inline );
}

function pcb_preview_listener() {
    if ( ! isset( $_GET['pcb_preview'] ) ) return;
    wp_enqueue_script( 'pcb-live-preview', PCB_PLUGIN_URL . 'assets/js/live-preview.js', [], '3.0.0', true );
}

/**
 * Resolve a CSS pixel value from a setting, falling back to a numeric default.
 * Returns an integer (or empty string if the raw value is empty AND $default is '').
 */
function pcb_px( $raw, $default ) {
    return ( isset( $raw ) && $raw !== '' ) ? (int) $raw : $default;
}

/**
 * Build the CSS custom-property block that drives the bubble's look & feel.
 */
function pcb_build_inline_css( array $o ) {
    /* Desktop ---------------------------------------------------- */
    $primary  = $o['bubble_color']       ?: '#1a202c';
    $hover    = $o['bubble_hover_color'] ?: $primary;
    $corner   = $o['corner']             ?: 'right';

    $btn_size = pcb_px( $o['button_size'],   60 );
    $off_bot  = pcb_px( $o['offset_bottom'], 30 );
    $off_side = pcb_px( $o['offset_side'],   30 );

    $radius_raw = pcb_px( $o['border_radius'], '' );
    $radius     = $radius_raw !== '' ? "{$radius_raw}px" : '50%';

    $tip_bg   = $o['tooltip_bg_color'] ?: '#ffffff';
    $tip_col  = $o['tooltip_color']    ?: '#1a202c';
    $tip_size = pcb_px( $o['tooltip_font_size'],     13 );
    $tip_pad  = pcb_px( $o['tooltip_padding'],        8 );
    $tip_rad  = pcb_px( $o['tooltip_border_radius'], 20 );

    /* Mobile (blank = inherit desktop) --------------------------- */
    $corner_m   = $o['corner_mobile'] ?: $corner;
    $btn_size_m = pcb_px( $o['button_size_mobile'],   $btn_size );
    $off_bot_m  = pcb_px( $o['offset_bottom_mobile'], $off_bot );
    $off_side_m = pcb_px( $o['offset_side_mobile'],   $off_side );

    $radius_m_raw = pcb_px( $o['border_radius_mobile'], '' );
    $radius_m     = $radius_m_raw !== '' ? "{$radius_m_raw}px" : $radius;

    $tip_bg_m   = $o['tooltip_bg_color_mobile'] ?: $tip_bg;
    $tip_col_m  = $o['tooltip_color_mobile']    ?: $tip_col;
    $tip_size_m = pcb_px( $o['tooltip_font_size_mobile'],     $tip_size );
    $tip_pad_m  = pcb_px( $o['tooltip_padding_mobile'],       $tip_pad );
    $tip_rad_m  = pcb_px( $o['tooltip_border_radius_mobile'], $tip_rad );

    $mobile_pos = $corner_m === 'left'
        ? 'left: var(--pcb-side) !important; right: auto !important;'
        : 'right: var(--pcb-side) !important; left: auto !important;';

    return "
        :root {
            --pcb-primary-color: {$primary};
            --pcb-hover-color:   {$hover};
            --pcb-tip-bg:     {$tip_bg};
            --pcb-tip-color:  {$tip_col};
            --pcb-tip-size:   {$tip_size}px;
            --pcb-tip-pad:    {$tip_pad}px;
            --pcb-tip-radius: {$tip_rad}px;
            --pcb-bottom: {$off_bot}px;
            --pcb-side:   {$off_side}px;
            --pcb-radius: {$radius};
            --pcb-size:   {$btn_size}px;
        }
        @media (max-width: 768px) {
            #pcb-container {
                --pcb-bottom:     {$off_bot_m}px;
                --pcb-side:       {$off_side_m}px;
                --pcb-radius:     {$radius_m};
                --pcb-tip-bg:     {$tip_bg_m};
                --pcb-tip-color:  {$tip_col_m};
                --pcb-tip-size:   {$tip_size_m}px;
                --pcb-tip-pad:    {$tip_pad_m}px;
                --pcb-tip-radius: {$tip_rad_m}px;
                --pcb-size:       {$btn_size_m}px;
                {$mobile_pos}
            }
        }
    ";
}
