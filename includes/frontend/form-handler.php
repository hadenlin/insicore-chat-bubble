<?php
/**
 * Built-in contact form submission handler.
 *
 * Exposes a single AJAX endpoint (no login required): `pcb_submit_form`.
 * Handles validation, optional email notification, and row insert.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

add_action( 'wp_ajax_pcb_submit_form',        'pcb_handle_form_submission' );
add_action( 'wp_ajax_nopriv_pcb_submit_form', 'pcb_handle_form_submission' );

function pcb_handle_form_submission() {
    check_ajax_referer( 'pcb_form', 'nonce' );

    // Rate limit: 1 submission per IP per 60 seconds.
    $rl_key = 'pcb_rl_form_' . md5( pcb_client_ip() );
    if ( get_transient( $rl_key ) ) {
        wp_send_json_error( [ 'message' => __( 'Please wait a moment before submitting again.', 'insicore-chat-bubble' ) ], 429 );
    }
    set_transient( $rl_key, 1, 60 );

    $settings = pcb_get_settings();

    // Honeypot (bots fill every field).
    if ( ! empty( $_POST['pcb_hp'] ) ) {
        wp_send_json_error( [ 'message' => 'invalid' ], 400 );
    }

    $name    = sanitize_text_field( wp_unslash( $_POST['name']    ?? '' ) );
    $email   = sanitize_email(      wp_unslash( $_POST['email']   ?? '' ) );
    $phone   = sanitize_text_field( wp_unslash( $_POST['phone']   ?? '' ) );
    $message = sanitize_textarea_field( wp_unslash( $_POST['message'] ?? '' ) );

    // Message is only required when the form config enables it (default: required).
    $message_required = true;
    $channels = json_decode( pcb_get_settings()['channels_data'] ?? '[]', true );
    if ( is_array( $channels ) ) {
        foreach ( $channels as $ch ) {
            if ( ( $ch['type'] ?? '' ) === 'form' && isset( $ch['form_config']['show_message'] ) ) {
                $message_required = ! empty( $ch['form_config']['show_message'] );
                break;
            }
        }
    }

    if ( $name === '' || $email === '' || ( $message_required && $message === '' ) ) {
        wp_send_json_error( [ 'message' => __( 'Please fill in all required fields.', 'insicore-chat-bubble' ) ], 422 );
    }
    if ( ! is_email( $email ) ) {
        wp_send_json_error( [ 'message' => __( 'Please enter a valid email address.', 'insicore-chat-bubble' ) ], 422 );
    }

    global $wpdb;
    $inserted = $wpdb->insert(
        pcb_get_submissions_table(),
        [
            'submitted_at' => current_time( 'mysql' ),
            'name'         => $name,
            'email'        => $email,
            'phone'        => $phone,
            'message'      => $message,
            'page_url'     => esc_url_raw( wp_unslash( $_POST['page_url'] ?? '' ) ),
            'ip_address'   => pcb_client_ip(),
            'is_read'      => 0,
        ]
    );

    if ( $inserted === false ) {
        wp_send_json_error( [ 'message' => __( 'Sorry, something went wrong saving your message.', 'insicore-chat-bubble' ) ], 500 );
    }

    // Optional admin email notification.
    $notify_to = trim( (string) ( $settings['form_notify_email'] ?? '' ) );
    if ( $notify_to === '' ) $notify_to = get_option( 'admin_email' );
    if ( $notify_to && is_email( $notify_to ) ) {
        $subject = sprintf(
            /* translators: %s = site name */
            __( '[%s] New message via Insicore Chat Bubble', 'insicore-chat-bubble' ),
            wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES )
        );
        $body  = __( 'You received a new message via Insicore Chat Bubble:', 'insicore-chat-bubble' ) . "\n\n";
        $body .= __( 'Name: ',    'insicore-chat-bubble' ) . $name    . "\n";
        $body .= __( 'Email: ',   'insicore-chat-bubble' ) . $email   . "\n";
        $body .= __( 'Phone: ',   'insicore-chat-bubble' ) . $phone   . "\n";
        $body .= __( 'Page: ',    'insicore-chat-bubble' ) . esc_url_raw( wp_unslash( $_POST['page_url'] ?? '' ) ) . "\n\n";
        $body .= __( 'Message:',  'insicore-chat-bubble' ) . "\n" . $message . "\n";

        // Sanitize name for header to prevent header injection (strip newlines).
        $safe_name = str_replace( [ "\r", "\n" ], '', $name );
        wp_mail( $notify_to, $subject, $body, [ 'Reply-To: ' . $safe_name . ' <' . $email . '>' ] );
    }

    $success = $settings['form_success_message'] !== ''
        ? $settings['form_success_message']
        : __( 'Thanks! We will get back to you soon.', 'insicore-chat-bubble' );

    wp_send_json_success( [ 'message' => $success ] );
}

function pcb_client_ip() {
    $keys = [ 'HTTP_CF_CONNECTING_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR' ];
    foreach ( $keys as $k ) {
        if ( ! empty( $_SERVER[ $k ] ) ) {
            $ip = explode( ',', (string) $_SERVER[ $k ] )[0];
            $ip = filter_var( trim( $ip ), FILTER_VALIDATE_IP );
            if ( $ip ) return $ip;
        }
    }
    return '';
}
