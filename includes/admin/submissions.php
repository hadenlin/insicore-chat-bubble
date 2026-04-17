<?php
/**
 * Admin: Contact Form Submissions page + row actions (mark-read / delete).
 */

if ( ! defined( 'ABSPATH' ) ) exit;

add_action( 'admin_post_pcb_submission_delete', 'pcb_handle_submission_delete' );
function pcb_handle_submission_delete() {
    if ( ! current_user_can( 'manage_options' ) ) wp_die( __( 'Unauthorized', 'insicore-chat-bubble' ), 403 );
    check_admin_referer( 'pcb_submission_delete' );

    global $wpdb;
    $id = isset( $_POST['id'] ) ? (int) $_POST['id'] : 0;
    if ( $id > 0 ) {
        $wpdb->delete( pcb_get_submissions_table(), [ 'id' => $id ], [ '%d' ] );
    }
    wp_safe_redirect( admin_url( 'admin.php?page=pcb-submissions' ) );
    exit;
}

add_action( 'admin_post_pcb_submission_toggle_read', 'pcb_handle_submission_toggle_read' );
function pcb_handle_submission_toggle_read() {
    if ( ! current_user_can( 'manage_options' ) ) wp_die( __( 'Unauthorized', 'insicore-chat-bubble' ), 403 );
    check_admin_referer( 'pcb_submission_toggle' );

    global $wpdb;
    $id = isset( $_POST['id'] ) ? (int) $_POST['id'] : 0;
    if ( $id > 0 ) {
        $row = $wpdb->get_row( $wpdb->prepare( 'SELECT is_read FROM ' . pcb_get_submissions_table() . ' WHERE id=%d', $id ) );
        if ( $row ) {
            $wpdb->update( pcb_get_submissions_table(), [ 'is_read' => $row->is_read ? 0 : 1 ], [ 'id' => $id ], [ '%d' ], [ '%d' ] );
        }
    }
    wp_safe_redirect( admin_url( 'admin.php?page=pcb-submissions' ) );
    exit;
}

function pcb_render_submissions_page() {
    global $wpdb;
    $table = pcb_get_submissions_table();
    $per   = 25;
    $pg    = max( 1, (int) ( $_GET['paged'] ?? 1 ) );
    $off   = ( $pg - 1 ) * $per;

    $rows  = $wpdb->get_results(
        $wpdb->prepare( "SELECT * FROM {$table} ORDER BY submitted_at DESC LIMIT %d OFFSET %d", $per, $off ),
        ARRAY_A
    );
    $total = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$table}" );
    $pages = max( 1, (int) ceil( $total / $per ) );

    $action_url = esc_url( admin_url( 'admin-post.php' ) );
    ?>
    <div class="wrap">
        <h1><?php esc_html_e( 'Contact Bubble ??Submissions', 'insicore-chat-bubble' ); ?></h1>
        <p class="description"><?php printf( esc_html__( '%d total submissions.', 'insicore-chat-bubble' ), (int) $total ); ?></p>

        <table class="widefat striped pcb-submissions-table">
            <thead>
                <tr>
                    <th style="width:140px"><?php esc_html_e( 'Received', 'insicore-chat-bubble' ); ?></th>
                    <th><?php esc_html_e( 'Name', 'insicore-chat-bubble' ); ?></th>
                    <th><?php esc_html_e( 'Email', 'insicore-chat-bubble' ); ?></th>
                    <th><?php esc_html_e( 'Phone', 'insicore-chat-bubble' ); ?></th>
                    <th><?php esc_html_e( 'Message', 'insicore-chat-bubble' ); ?></th>
                    <th style="width:140px"><?php esc_html_e( 'Actions', 'insicore-chat-bubble' ); ?></th>
                </tr>
            </thead>
            <tbody>
            <?php if ( ! $rows ) : ?>
                <tr><td colspan="6" style="text-align:center;padding:24px;color:#64748b;"><?php esc_html_e( 'No submissions yet.', 'insicore-chat-bubble' ); ?></td></tr>
            <?php else : foreach ( $rows as $r ) : ?>
                <tr<?php echo empty( $r['is_read'] ) ? ' style="font-weight:600"' : ''; ?>>
                    <td><?php echo esc_html( mysql2date( 'Y-m-d H:i', $r['submitted_at'] ) ); ?></td>
                    <td><?php echo esc_html( $r['name'] ); ?></td>
                    <td><a href="mailto:<?php echo esc_attr( $r['email'] ); ?>"><?php echo esc_html( $r['email'] ); ?></a></td>
                    <td><?php echo esc_html( $r['phone'] ); ?></td>
                    <td><?php echo nl2br( esc_html( mb_strimwidth( $r['message'], 0, 200, '?? ) ) ); ?>
                        <?php if ( ! empty( $r['page_url'] ) ) : ?>
                            <div style="color:#94a3b8;font-size:11px;margin-top:4px;">
                                <?php esc_html_e( 'From:', 'insicore-chat-bubble' ); ?>
                                <a href="<?php echo esc_url( $r['page_url'] ); ?>" target="_blank"><?php echo esc_html( $r['page_url'] ); ?></a>
                            </div>
                        <?php endif; ?>
                    </td>
                    <td>
                        <form method="post" action="<?php echo esc_url( $action_url ); ?>" style="display:inline">
                            <input type="hidden" name="action" value="pcb_submission_toggle_read">
                            <input type="hidden" name="id" value="<?php echo (int) $r['id']; ?>">
                            <?php wp_nonce_field( 'pcb_submission_toggle' ); ?>
                            <button class="button button-small" type="submit"><?php echo empty( $r['is_read'] ) ? esc_html__( 'Mark read', 'insicore-chat-bubble' ) : esc_html__( 'Unread', 'insicore-chat-bubble' ); ?></button>
                        </form>
                        <form method="post" action="<?php echo esc_url( $action_url ); ?>" style="display:inline" onsubmit="return confirm('<?php echo esc_js( __( 'Delete this submission?', 'insicore-chat-bubble' ) ); ?>');">
                            <input type="hidden" name="action" value="pcb_submission_delete">
                            <input type="hidden" name="id" value="<?php echo (int) $r['id']; ?>">
                            <?php wp_nonce_field( 'pcb_submission_delete' ); ?>
                            <button class="button button-small button-link-delete" type="submit"><?php esc_html_e( 'Delete', 'insicore-chat-bubble' ); ?></button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; endif; ?>
            </tbody>
        </table>

        <?php if ( $pages > 1 ) : ?>
        <div class="tablenav"><div class="tablenav-pages">
            <?php for ( $i = 1; $i <= $pages; $i++ ) :
                $active = ( $i === $pg );
                $url = add_query_arg( 'paged', $i, admin_url( 'admin.php?page=pcb-submissions' ) );
            ?>
            <a class="button <?php echo $active ? 'button-primary' : ''; ?>" href="<?php echo esc_url( $url ); ?>"><?php echo $i; ?></a>
            <?php endfor; ?>
        </div></div>
        <?php endif; ?>
    </div>
    <?php
}
