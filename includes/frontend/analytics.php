<?php
/**
 * Click / event analytics tracking.
 *
 * AJAX endpoint: `pcb_track_event` (no-login + login). Inserts one row per
 * click into the pcb_events table. Throttling is handled client-side to avoid
 * flooding the DB.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

add_action( 'wp_ajax_pcb_track_event',        'pcb_handle_track_event' );
add_action( 'wp_ajax_nopriv_pcb_track_event', 'pcb_handle_track_event' );

function pcb_handle_track_event() {
    // Lightweight nonce check (track endpoint is low-risk; we still reject
    // any request missing the nonce to block casual abuse).
    if ( ! check_ajax_referer( 'pcb_track', 'nonce', false ) ) {
        wp_send_json_error( [], 400 );
    }

    $allowed_events = [ 'click', 'impression', 'form_submit', 'open' ];
    $event_type     = in_array( $_POST['event_type'] ?? '', $allowed_events, true )
        ? $_POST['event_type']
        : 'click';

    // Rate limit: same IP + same event type ??max 1 per 5 seconds.
    $rl_key = 'pcb_rl_track_' . md5( pcb_client_ip() . $event_type );
    if ( get_transient( $rl_key ) ) {
        wp_send_json_success(); // silently drop, don't reveal throttling
        return;
    }
    set_transient( $rl_key, 1, 5 );

    global $wpdb;
    $wpdb->insert(
        pcb_get_events_table(),
        [
            'event_at'      => current_time( 'mysql' ),
            'event_type'    => $event_type,
            'channel_type'  => sanitize_key(         wp_unslash( $_POST['channel_type']  ?? '' ) ),
            'channel_label' => sanitize_text_field(  wp_unslash( $_POST['channel_label'] ?? '' ) ),
            'page_url'      => esc_url_raw(          wp_unslash( $_POST['page_url']      ?? '' ) ),
            'device'        => sanitize_key(         wp_unslash( $_POST['device']        ?? '' ) ),
        ]
    );

    wp_send_json_success();
}

/**
 * Aggregated stats helpers used by the admin dashboard.
 */
function pcb_analytics_totals( $days = 30 ) {
    global $wpdb;
    $t = pcb_get_events_table();
    $since = gmdate( 'Y-m-d H:i:s', strtotime( "-{$days} days" ) );

    return [
        'total_clicks' => (int) $wpdb->get_var( $wpdb->prepare(
            "SELECT COUNT(*) FROM {$t} WHERE event_type = 'click' AND event_at >= %s", $since
        ) ),
        'total_opens'  => (int) $wpdb->get_var( $wpdb->prepare(
            "SELECT COUNT(*) FROM {$t} WHERE event_type = 'open'  AND event_at >= %s", $since
        ) ),
        'total_forms'  => (int) $wpdb->get_var( $wpdb->prepare(
            "SELECT COUNT(*) FROM {$t} WHERE event_type = 'form_submit' AND event_at >= %s", $since
        ) ),
    ];
}

function pcb_analytics_by_channel( $days = 30 ) {
    global $wpdb;
    $t     = pcb_get_events_table();
    $since = gmdate( 'Y-m-d H:i:s', strtotime( "-{$days} days" ) );

    return (array) $wpdb->get_results( $wpdb->prepare(
        "SELECT channel_type, channel_label, COUNT(*) AS clicks
         FROM {$t}
         WHERE event_type = 'click' AND event_at >= %s AND channel_type <> ''
         GROUP BY channel_type, channel_label
         ORDER BY clicks DESC
         LIMIT 20",
        $since
    ), ARRAY_A );
}

function pcb_analytics_daily_series( $days = 30 ) {
    global $wpdb;
    $t     = pcb_get_events_table();
    $since = gmdate( 'Y-m-d', strtotime( "-{$days} days" ) );

    $rows = (array) $wpdb->get_results( $wpdb->prepare(
        "SELECT DATE(event_at) AS d, COUNT(*) AS c
         FROM {$t} WHERE event_type='click' AND event_at >= %s
         GROUP BY DATE(event_at) ORDER BY d ASC",
        $since
    ), ARRAY_A );

    // Fill zero-days so the chart is smooth.
    $map = [];
    foreach ( $rows as $r ) $map[ $r['d'] ] = (int) $r['c'];

    $series = [];
    for ( $i = $days - 1; $i >= 0; $i-- ) {
        $d        = gmdate( 'Y-m-d', strtotime( "-{$i} days" ) );
        $series[] = [ 'date' => $d, 'clicks' => $map[ $d ] ?? 0 ];
    }
    return $series;
}

function pcb_analytics_by_device( $days = 30 ) {
    global $wpdb;
    $t     = pcb_get_events_table();
    $since = gmdate( 'Y-m-d H:i:s', strtotime( "-{$days} days" ) );

    return (array) $wpdb->get_results( $wpdb->prepare(
        "SELECT device, COUNT(*) AS clicks
         FROM {$t} WHERE event_type='click' AND event_at >= %s AND device <> ''
         GROUP BY device ORDER BY clicks DESC",
        $since
    ), ARRAY_A );
}

function pcb_analytics_top_pages( $days = 30, $limit = 10 ) {
    global $wpdb;
    $t     = pcb_get_events_table();
    $since = gmdate( 'Y-m-d H:i:s', strtotime( "-{$days} days" ) );

    return (array) $wpdb->get_results( $wpdb->prepare(
        "SELECT page_url, COUNT(*) AS clicks
         FROM {$t} WHERE event_type='click' AND event_at >= %s AND page_url <> ''
         GROUP BY page_url ORDER BY clicks DESC LIMIT %d",
        $since, $limit
    ), ARRAY_A );
}
