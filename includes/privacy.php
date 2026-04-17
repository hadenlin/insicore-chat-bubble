<?php
/**
 * GDPR / Privacy support for OmniChat Bubble.
 *
 * Registers personal-data exporters and erasers so site owners can
 * fulfil Data Subject Access Requests (DSAR) through the built-in
 * WordPress Privacy ??Erase Personal Data / Export Personal Data tools.
 *
 * Data covered: contact form submissions stored in {prefix}pcb_submissions
 * (name, email, phone, message, page URL, IP address).
 *
 * Click-analytics events in {prefix}pcb_events do NOT contain directly
 * identifying information (no name / email); IP addresses used for
 * rate-limiting are stored only in transients and expire within 60 s.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/* ?€?€ Exporter ?€?€?€?€?€?€?€?€?€?€?€?€?€?€?€?€?€?€?€?€?€?€?€?€?€?€?€?€?€?€?€?€?€?€?€?€?€?€?€?€?€?€?€?€?€?€ */

add_filter( 'wp_privacy_personal_data_exporters', 'pcb_register_privacy_exporters' );

function pcb_register_privacy_exporters( $exporters ) {
    $exporters['insicore-chat-bubble'] = [
        'exporter_friendly_name' => __( 'OmniChat Bubble ??Contact Form Submissions', 'insicore-chat-bubble' ),
        'callback'               => 'pcb_privacy_exporter',
    ];
    return $exporters;
}

function pcb_privacy_exporter( $email_address, $page = 1 ) {
    global $wpdb;
    $table = pcb_get_submissions_table();
    $email = sanitize_email( $email_address );

    $rows = $wpdb->get_results(
        $wpdb->prepare(
            "SELECT * FROM {$table} WHERE email = %s ORDER BY submitted_at DESC",
            $email
        ),
        ARRAY_A
    );

    $export_items = [];
    foreach ( $rows as $r ) {
        $export_items[] = [
            'group_id'    => 'omnichat-bubble-submissions',
            'group_label' => __( 'OmniChat Bubble ??Contact Submissions', 'insicore-chat-bubble' ),
            'item_id'     => 'pcb-submission-' . (int) $r['id'],
            'data'        => [
                [ 'name' => __( 'Date Submitted', 'insicore-chat-bubble' ), 'value' => $r['submitted_at'] ],
                [ 'name' => __( 'Name',           'insicore-chat-bubble' ), 'value' => $r['name'] ],
                [ 'name' => __( 'Email',          'insicore-chat-bubble' ), 'value' => $r['email'] ],
                [ 'name' => __( 'Phone',          'insicore-chat-bubble' ), 'value' => $r['phone'] ],
                [ 'name' => __( 'Message',        'insicore-chat-bubble' ), 'value' => $r['message'] ],
                [ 'name' => __( 'Page URL',       'insicore-chat-bubble' ), 'value' => $r['page_url'] ],
                [ 'name' => __( 'IP Address',     'insicore-chat-bubble' ), 'value' => $r['ip_address'] ],
            ],
        ];
    }

    return [
        'data' => $export_items,
        'done' => true,
    ];
}

/* ?€?€ Eraser ?€?€?€?€?€?€?€?€?€?€?€?€?€?€?€?€?€?€?€?€?€?€?€?€?€?€?€?€?€?€?€?€?€?€?€?€?€?€?€?€?€?€?€?€?€?€?€?€ */

add_filter( 'wp_privacy_personal_data_erasers', 'pcb_register_privacy_erasers' );

function pcb_register_privacy_erasers( $erasers ) {
    $erasers['insicore-chat-bubble'] = [
        'eraser_friendly_name' => __( 'OmniChat Bubble ??Contact Form Submissions', 'insicore-chat-bubble' ),
        'callback'             => 'pcb_privacy_eraser',
    ];
    return $erasers;
}

function pcb_privacy_eraser( $email_address, $page = 1 ) {
    global $wpdb;
    $table = pcb_get_submissions_table();
    $email = sanitize_email( $email_address );

    $count = (int) $wpdb->get_var(
        $wpdb->prepare( "SELECT COUNT(*) FROM {$table} WHERE email = %s", $email )
    );

    if ( $count > 0 ) {
        $wpdb->delete( $table, [ 'email' => $email ], [ '%s' ] );
    }

    return [
        'items_removed'  => $count > 0,
        'items_retained' => false,
        'messages'       => [],
        'done'           => true,
    ];
}
