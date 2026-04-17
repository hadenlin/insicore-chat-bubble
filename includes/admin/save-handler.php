<?php
/**
 * Save handler for the Premium Contact Bubble admin form.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

add_action( 'admin_post_pcb_save_settings', 'pcb_handle_save_settings' );

/**
 * Return an absint when the input has a value, otherwise an empty string.
 * Used for "inherit-when-blank" fields.
 */
function pcb_opt_absint( $value ) {
    return ( isset( $value ) && $value !== '' ) ? absint( $value ) : '';
}

function pcb_handle_save_settings() {
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_die( __( 'Unauthorized', 'insicore-chat-bubble' ), 403 );
    }
    check_admin_referer( 'pcb_save_settings', 'pcb_nonce' );

    $input         = isset( $_POST['pcb_settings'] ) ? (array) $_POST['pcb_settings'] : [];
    $allowed_anims = [ 'none', 'pulse', 'bounce', 'float', 'shake', 'zoom', 'tada', 'rotate', 'glow' ];
    $allowed_corn  = [ 'left', 'right' ];
    $allowed_themes = array_keys( pcb_get_theme_presets() );

    $clean = [
        // Global
        'bubble_color'       => sanitize_hex_color( $input['bubble_color']       ?? '' ) ?: '#1a202c',
        'bubble_hover_color' => sanitize_hex_color( $input['bubble_hover_color'] ?? '' ) ?: '',
        'animation_type'     => in_array( $input['animation_type'] ?? '', $allowed_anims, true ) ? $input['animation_type'] : 'none',
        'show_tooltip'       => ! empty( $input['show_tooltip'] ) ? 1 : 0,
        'tooltip_text'       => sanitize_text_field( $input['tooltip_text'] ?? '' ),
        'show_labels'        => ! empty( $input['show_labels'] ) ? 1 : 0,
        'custom_svg'         => esc_url_raw( $input['custom_svg'] ?? '' ),

        // Tooltip style (desktop + mobile)
        'tooltip_bg_color'             => sanitize_hex_color( $input['tooltip_bg_color']        ?? '' ) ?: '',
        'tooltip_color'                => sanitize_hex_color( $input['tooltip_color']           ?? '' ) ?: '',
        'tooltip_font_size'            => pcb_opt_absint( $input['tooltip_font_size']           ?? '' ),
        'tooltip_padding'              => pcb_opt_absint( $input['tooltip_padding']             ?? '' ),
        'tooltip_border_radius'        => pcb_opt_absint( $input['tooltip_border_radius']       ?? '' ),
        'tooltip_bg_color_mobile'      => sanitize_hex_color( $input['tooltip_bg_color_mobile'] ?? '' ) ?: '',
        'tooltip_color_mobile'         => sanitize_hex_color( $input['tooltip_color_mobile']    ?? '' ) ?: '',
        'tooltip_font_size_mobile'     => pcb_opt_absint( $input['tooltip_font_size_mobile']    ?? '' ),
        'tooltip_padding_mobile'       => pcb_opt_absint( $input['tooltip_padding_mobile']      ?? '' ),
        'tooltip_border_radius_mobile' => pcb_opt_absint( $input['tooltip_border_radius_mobile']?? '' ),

        // Desktop layout
        'corner'         => in_array( $input['corner']        ?? '', $allowed_corn, true ) ? $input['corner']        : 'right',
        'button_size'    => absint( $input['button_size']    ?? 60 ) ?: 60,
        'border_radius'  => pcb_opt_absint( $input['border_radius'] ?? '' ),
        'offset_bottom'  => absint( $input['offset_bottom']  ?? 30 ),
        'offset_side'    => absint( $input['offset_side']    ?? 30 ),

        // Mobile layout (blank = inherit desktop)
        'corner_mobile'        => in_array( $input['corner_mobile'] ?? '', $allowed_corn, true ) ? $input['corner_mobile'] : '',
        'button_size_mobile'   => pcb_opt_absint( $input['button_size_mobile']   ?? '' ),
        'border_radius_mobile' => pcb_opt_absint( $input['border_radius_mobile'] ?? '' ),
        'offset_bottom_mobile' => pcb_opt_absint( $input['offset_bottom_mobile'] ?? '' ),
        'offset_side_mobile'   => pcb_opt_absint( $input['offset_side_mobile']   ?? '' ),

        // Badge
        'badge_enabled'     => ! empty( $input['badge_enabled'] ) ? 1 : 0,
        'badge_text'        => substr( sanitize_text_field( $input['badge_text'] ?? '1' ), 0, 10 ),
        'badge_bg_color'    => sanitize_hex_color( $input['badge_bg_color']    ?? '' ) ?: '#ef4444',
        'badge_text_color'  => sanitize_hex_color( $input['badge_text_color']  ?? '' ) ?: '#ffffff',

        // Message Bubble
        'msgbubble_enabled'    => ! empty( $input['msgbubble_enabled'] ) ? 1 : 0,
        'msgbubble_text'       => sanitize_text_field( $input['msgbubble_text'] ?? '' ),
        'msgbubble_delay'      => max( 0, min( 120, absint( $input['msgbubble_delay']    ?? 3 ) ) ),
        'msgbubble_autohide'   => max( 0, min( 600, absint( $input['msgbubble_autohide'] ?? 0 ) ) ),
        'msgbubble_bg_color'   => sanitize_hex_color( $input['msgbubble_bg_color']   ?? '' ) ?: '#ffffff',
        'msgbubble_text_color' => sanitize_hex_color( $input['msgbubble_text_color'] ?? '' ) ?: '#1a202c',

        // Theme + custom CSS
        'theme_preset' => in_array( $input['theme_preset'] ?? '', $allowed_themes, true ) ? $input['theme_preset'] : '',
        'custom_css'   => wp_strip_all_tags( wp_unslash( $input['custom_css'] ?? '' ) ),

        // Triggers
        'trigger_scroll_enabled'      => ! empty( $input['trigger_scroll_enabled'] ) ? 1 : 0,
        'trigger_scroll_percent'      => max( 5, min( 100, absint( $input['trigger_scroll_percent'] ?? 30 ) ) ),
        'trigger_time_enabled'        => ! empty( $input['trigger_time_enabled'] ) ? 1 : 0,
        'trigger_time_seconds'        => max( 1, min( 600, absint( $input['trigger_time_seconds'] ?? 10 ) ) ),
        'trigger_exit_intent_enabled' => ! empty( $input['trigger_exit_intent_enabled'] ) ? 1 : 0,

        // Contact form
        'form_notify_email'    => sanitize_email(      $input['form_notify_email']    ?? '' ),
        'form_success_message' => sanitize_text_field( $input['form_success_message'] ?? '' ),

        // JSON payloads (channels + visibility)
        'channels_data'    => pcb_sanitize_channels_json( $input['channels_data']    ?? '[]' ),
        'visibility_rules' => pcb_sanitize_rules_json(    $input['visibility_rules'] ?? '[]' ),
    ];

    pcb_save_settings( $clean );

    wp_safe_redirect( admin_url( 'admin.php?page=pcb-builder&settings-updated=true' ) );
    exit;
}

/**
 * Sanitize the channels_data JSON blob: unslash, decode, sanitize hex colors,
 * preserve preset_message / agents config, re-encode.
 */
function pcb_sanitize_channels_json( $raw ) {
    $decoded = json_decode( wp_unslash( $raw ), true );
    if ( ! is_array( $decoded ) ) return '[]';

    $clean = [];
    foreach ( $decoded as $ch ) {
        if ( ! is_array( $ch ) ) continue;

        $row = [
            'type'    => sanitize_key( $ch['type'] ?? '' ),
            'label'   => sanitize_text_field( $ch['label'] ?? '' ),
            'url'     => isset( $ch['url'] ) ? sanitize_text_field( (string) $ch['url'] ) : '',
            'enabled' => isset( $ch['enabled'] ) ? (bool) $ch['enabled'] : true,
            'color'   => sanitize_hex_color( $ch['color'] ?? '' ) ?: '#4a5568',
            'hover'   => sanitize_hex_color( $ch['hover'] ?? '' ) ?: '#2d3748',
            'icon'    => isset( $ch['icon'] ) ? wp_kses( (string) $ch['icon'], pcb_allowed_icon_tags() ) : '',
        ];
        if ( isset( $ch['icon_value'] ) ) {
            $row['icon_value'] = sanitize_text_field( (string) $ch['icon_value'] );
        }
        if ( isset( $ch['preset_message'] ) ) {
            $row['preset_message'] = sanitize_text_field( (string) $ch['preset_message'] );
        }
        if ( ! empty( $ch['agents_enabled'] ) ) {
            $row['agents_enabled'] = 1;
        }
        if ( ! empty( $ch['agents'] ) && is_array( $ch['agents'] ) ) {
            $agents = [];
            foreach ( $ch['agents'] as $a ) {
                if ( ! is_array( $a ) ) continue;
                $name  = sanitize_text_field( $a['name']  ?? '' );
                $value = sanitize_text_field( $a['value'] ?? '' );
                if ( $value === '' ) continue;
                $agents[] = [ 'name' => $name, 'value' => $value ];
            }
            if ( $agents ) $row['agents'] = $agents;
        }
        if ( ! empty( $ch['form_config'] ) && is_array( $ch['form_config'] ) ) {
            $fc = $ch['form_config'];
            $row['form_config'] = [
                'title'        => sanitize_text_field( $fc['title']        ?? '' ),
                'submit_label' => sanitize_text_field( $fc['submit_label'] ?? '' ),
                'show_phone'   => ! empty( $fc['show_phone'] ),
                'show_message' => ! empty( $fc['show_message'] ) || ! isset( $fc['show_message'] ),
            ];
        }
        $clean[] = $row;
    }

    return wp_json_encode( $clean );
}

/**
 * Sanitize the visibility_rules JSON blob: unslash, decode, sanitize each rule, re-encode.
 */
function pcb_sanitize_rules_json( $raw ) {
    $decoded = json_decode( wp_unslash( $raw ), true );
    if ( ! is_array( $decoded ) ) return '[]';

    $allowed_types = [ 'pages', 'posts', 'categories', 'tags', 'taxonomies', 'archives', 'others' ];
    $allowed_other = [ 'home', '404', 'search', 'all' ];

    $clean = [];
    foreach ( $decoded as $rule ) {
        if ( ! is_array( $rule ) ) continue;

        $type = sanitize_key( $rule['type'] ?? 'pages' );
        if ( ! in_array( $type, $allowed_types, true ) ) continue;

        $targets = [];
        if ( ! empty( $rule['targets'] ) && is_array( $rule['targets'] ) ) {
            foreach ( $rule['targets'] as $t ) {
                if ( ! is_array( $t ) ) continue;
                $id    = isset( $t['id'] ) ? ( is_numeric( $t['id'] ) ? absint( $t['id'] ) : sanitize_key( (string) $t['id'] ) ) : '';
                $title = sanitize_text_field( $t['title'] ?? '' );
                if ( $id === '' && $title === '' ) continue;
                $targets[] = [ 'id' => $id, 'title' => $title ];
            }
        }

        $clean[] = [
            'enabled' => ! empty( $rule['enabled'] ),
            'type'    => $type,
            'targets' => $targets,
        ];
    }

    return wp_json_encode( $clean );
}

