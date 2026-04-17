<?php
/**
 * Admin: Click Analytics page.
 * Registered as the top-level Contact Bubble page (via menu.php).
 * Uses standard WordPress admin UI ??no custom CSS needed.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

add_action( 'admin_post_pcb_analytics_reset', 'pcb_handle_analytics_reset' );
function pcb_handle_analytics_reset() {
    if ( ! current_user_can( 'manage_options' ) ) wp_die( __( 'Unauthorized', 'insicore-chat-bubble' ), 403 );
    check_admin_referer( 'pcb_analytics_reset' );

    global $wpdb;
    $wpdb->query( 'TRUNCATE TABLE ' . pcb_get_events_table() );
    wp_safe_redirect( admin_url( 'admin.php?page=omnichat-bubble&reset=1' ) );
    exit;
}

function pcb_render_analytics_page() {
    $days       = max( 7, min( 365, absint( $_GET['days'] ?? 30 ) ) );
    $totals     = pcb_analytics_totals( $days );
    $by_channel = pcb_analytics_by_channel( $days );
    $daily      = pcb_analytics_daily_series( $days );
    $by_device  = pcb_analytics_by_device( $days );
    $top_pages  = pcb_analytics_top_pages( $days, 10 );

    $reset_url = esc_url( admin_url( 'admin-post.php' ) );
    $base_url  = admin_url( 'admin.php?page=omnichat-bubble' );
    ?>
    <div class="wrap">
        <h1><?php esc_html_e( 'Contact Bubble Analytics', 'insicore-chat-bubble' ); ?></h1>

        <?php if ( ! empty( $_GET['reset'] ) ) : ?>
            <div class="notice notice-success is-dismissible"><p><?php esc_html_e( 'Analytics data has been reset.', 'insicore-chat-bubble' ); ?></p></div>
        <?php endif; ?>

        <!-- Date range -->
        <p>
            <?php foreach ( [ 7, 30, 90, 365 ] as $r ) :
                $url = add_query_arg( 'days', $r, $base_url );
                $cls = $r === $days ? 'button button-primary' : 'button';
            ?>
                <a class="<?php echo esc_attr( $cls ); ?>" href="<?php echo esc_url( $url ); ?>" style="margin-right:4px;">
                    <?php echo esc_html( sprintf( _n( 'Last %d day', 'Last %d days', $r, 'insicore-chat-bubble' ), $r ) ); ?>
                </a>
            <?php endforeach; ?>
        </p>

        <!-- Summary -->
        <h2><?php esc_html_e( 'Summary', 'insicore-chat-bubble' ); ?></h2>
        <table class="widefat" style="max-width:420px;">
            <tbody>
                <tr>
                    <th scope="row"><?php esc_html_e( 'Total Clicks', 'insicore-chat-bubble' ); ?></th>
                    <td><strong><?php echo number_format( (int) $totals['total_clicks'] ); ?></strong></td>
                </tr>
                <tr>
                    <th scope="row"><?php esc_html_e( 'Bubble Opens', 'insicore-chat-bubble' ); ?></th>
                    <td><strong><?php echo number_format( (int) $totals['total_opens'] ); ?></strong></td>
                </tr>
                <tr>
                    <th scope="row"><?php esc_html_e( 'Form Submissions', 'insicore-chat-bubble' ); ?></th>
                    <td><strong><?php echo number_format( (int) $totals['total_forms'] ); ?></strong></td>
                </tr>
            </tbody>
        </table>

        <!-- Clicks by Channel -->
        <h2 style="margin-top:24px;"><?php esc_html_e( 'Clicks by Channel', 'insicore-chat-bubble' ); ?></h2>
        <?php if ( empty( $by_channel ) ) : ?>
            <p class="description"><?php esc_html_e( 'No data yet.', 'insicore-chat-bubble' ); ?></p>
        <?php else : ?>
            <table class="widefat striped" style="max-width:520px;">
                <thead>
                    <tr>
                        <th><?php esc_html_e( 'Channel', 'insicore-chat-bubble' ); ?></th>
                        <th><?php esc_html_e( 'Type', 'insicore-chat-bubble' ); ?></th>
                        <th><?php esc_html_e( 'Clicks', 'insicore-chat-bubble' ); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ( $by_channel as $c ) : ?>
                        <tr>
                            <td><?php echo esc_html( $c['channel_label'] ?: '?? ); ?></td>
                            <td><?php echo esc_html( $c['channel_type'] ); ?></td>
                            <td><?php echo number_format( (int) $c['clicks'] ); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>

        <!-- Clicks by Device -->
        <h2 style="margin-top:24px;"><?php esc_html_e( 'Clicks by Device', 'insicore-chat-bubble' ); ?></h2>
        <?php if ( empty( $by_device ) ) : ?>
            <p class="description"><?php esc_html_e( 'No data yet.', 'insicore-chat-bubble' ); ?></p>
        <?php else :
            $dev_total = max( 1, array_sum( array_column( $by_device, 'clicks' ) ) );
        ?>
            <table class="widefat striped" style="max-width:380px;">
                <thead>
                    <tr>
                        <th><?php esc_html_e( 'Device', 'insicore-chat-bubble' ); ?></th>
                        <th><?php esc_html_e( 'Clicks', 'insicore-chat-bubble' ); ?></th>
                        <th><?php esc_html_e( '%', 'insicore-chat-bubble' ); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ( $by_device as $d ) : ?>
                        <tr>
                            <td><?php echo esc_html( ucfirst( $d['device'] ) ); ?></td>
                            <td><?php echo number_format( (int) $d['clicks'] ); ?></td>
                            <td><?php echo round( (int) $d['clicks'] / $dev_total * 100, 1 ); ?>%</td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>

        <!-- Top Pages -->
        <h2 style="margin-top:24px;"><?php esc_html_e( 'Top Pages by Clicks', 'insicore-chat-bubble' ); ?></h2>
        <?php if ( empty( $top_pages ) ) : ?>
            <p class="description"><?php esc_html_e( 'No page data yet.', 'insicore-chat-bubble' ); ?></p>
        <?php else : ?>
            <table class="widefat striped" style="max-width:700px;">
                <thead>
                    <tr>
                        <th><?php esc_html_e( 'Page URL', 'insicore-chat-bubble' ); ?></th>
                        <th style="width:100px;"><?php esc_html_e( 'Clicks', 'insicore-chat-bubble' ); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ( $top_pages as $p ) : ?>
                        <tr>
                            <td><a href="<?php echo esc_url( $p['page_url'] ); ?>" target="_blank"><?php echo esc_html( $p['page_url'] ); ?></a></td>
                            <td><?php echo number_format( (int) $p['clicks'] ); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>

        <!-- Daily Clicks -->
        <h2 style="margin-top:24px;"><?php esc_html_e( 'Daily Clicks', 'insicore-chat-bubble' ); ?></h2>
        <table class="widefat striped" style="max-width:300px;">
            <thead>
                <tr>
                    <th><?php esc_html_e( 'Date', 'insicore-chat-bubble' ); ?></th>
                    <th><?php esc_html_e( 'Clicks', 'insicore-chat-bubble' ); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ( array_reverse( $daily ) as $d ) : ?>
                    <tr>
                        <td><?php echo esc_html( $d['date'] ); ?></td>
                        <td><?php echo (int) $d['clicks']; ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <!-- Reset -->
        <p style="margin-top:30px;">
            <form method="post" action="<?php echo esc_url( $reset_url ); ?>" style="display:inline;" onsubmit="return confirm('<?php echo esc_js( __( 'Reset ALL analytics data? This cannot be undone.', 'insicore-chat-bubble' ) ); ?>');">
                <input type="hidden" name="action" value="pcb_analytics_reset">
                <?php wp_nonce_field( 'pcb_analytics_reset' ); ?>
                <button class="button button-link-delete"><?php esc_html_e( 'Reset analytics data', 'insicore-chat-bubble' ); ?></button>
            </form>
        </p>
    </div>
    <?php
}
