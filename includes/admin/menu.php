<?php
/**
 * Admin menu registration + main builder shell.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

add_action( 'admin_menu', 'pcb_add_admin_menu' );

function pcb_add_admin_menu() {
    // Top-level ??clicking "Contact Bubble" lands on the Analytics page.
    add_menu_page(
        __( 'Contact Bubble', 'insicore-chat-bubble' ),
        __( 'Contact Bubble', 'insicore-chat-bubble' ),
        'manage_options',
        'insicore-chat-bubble',
        'pcb_render_analytics_page',
        'dashicons-format-chat',
        58
    );

    // First submenu overrides the auto-generated entry ??shows as "Analytics".
    add_submenu_page(
        'insicore-chat-bubble',
        __( 'Bubble Analytics', 'insicore-chat-bubble' ),
        __( 'Analytics',        'insicore-chat-bubble' ),
        'manage_options',
        'insicore-chat-bubble',
        'pcb_render_analytics_page'
    );

    // Builder as a dedicated submenu.
    add_submenu_page(
        'insicore-chat-bubble',
        __( 'Bubble Builder', 'insicore-chat-bubble' ),
        __( 'Builder',        'insicore-chat-bubble' ),
        'manage_options',
        'pcb-builder',
        'pcb_settings_page'
    );

    // Form Submissions.
    add_submenu_page(
        'insicore-chat-bubble',
        __( 'Bubble Submissions', 'insicore-chat-bubble' ),
        __( 'Submissions',        'insicore-chat-bubble' ),
        'manage_options',
        'pcb-submissions',
        'pcb_render_submissions_page'
    );
}

/**
 * Shortcut used by the tab view files to read a setting with a fallback.
 */
function pcb_opt( array $options, $key, $default = '' ) {
    return $options[ $key ] ?? $default;
}

/**
 * Render the whole admin page (sidebar form + canvas preview iframe).
 */
function pcb_settings_page() {
    $options       = pcb_get_settings();
    $presets       = pcb_get_preset_channels();
    $channels_json = $options['channels_data'] ?? '[]';
    $back_url      = admin_url( 'index.php' );
    ?>
    <div class="pcb-builder-shell">

        <aside class="pcb-builder-sidebar">
            <div class="pcb-sidebar-nav">
                <a href="<?php echo esc_url( $back_url ); ?>" class="pcb-back-btn" title="<?php esc_attr_e( 'Back to Dashboard', 'insicore-chat-bubble' ); ?>">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"/></svg>
                </a>
                <h2><?php esc_html_e( 'Bubble Builder', 'insicore-chat-bubble' ); ?></h2>
            </div>

            <div class="pcb-sidebar-scroller">
                <form action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" method="post" id="pcb-settings-main-form">
                    <input type="hidden" name="action" value="pcb_save_settings">
                    <?php wp_nonce_field( 'pcb_save_settings', 'pcb_nonce' ); ?>
                    <?php pcb_render_builder_body( $options, $presets, $channels_json ); ?>
                </form>
            </div>

            <div class="pcb-sidebar-save">
                <button type="button" id="pcb-save-trigger" class="pcb-save-btn">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                    <?php esc_html_e( 'Save & Publish', 'insicore-chat-bubble' ); ?>
                </button>
            </div>
        </aside>

        <main class="pcb-builder-canvas">
            <div class="pcb-canvas-toolbar">
                <div class="pcb-device-bar">
                    <button type="button" class="pcb-device-btn active" data-device="desktop">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="3" width="20" height="14" rx="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/></svg>
                        <?php esc_html_e( 'Desktop', 'insicore-chat-bubble' ); ?>
                    </button>
                    <button type="button" class="pcb-device-btn" data-device="mobile">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="5" y="2" width="14" height="20" rx="2"/><circle cx="12" cy="18" r="1" fill="currentColor"/></svg>
                        <?php esc_html_e( 'Mobile', 'insicore-chat-bubble' ); ?>
                    </button>
                </div>
            </div>
            <iframe src="<?php echo esc_url( home_url( '/?pcb_preview=1' ) ); ?>" class="pcb-preview-frame" id="pcb-preview-frame"></iframe>
        </main>
    </div>

    <div class="pcb-toast" id="pcb-toast"></div>
    <?php
}

/**
 * Render the tab nav + four tab panels + preview lock.
 */
function pcb_render_builder_body( array $options, array $presets, $channels_json ) {
    ?>
    <!-- Tab Navigation -->
    <div class="pcb-tab-nav">
        <button type="button" class="pcb-tab-btn active" data-tab="channels">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 12a19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 3.6 1.18h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L7.91 8.78a16 16 0 0 0 6.29 6.29l.97-.97a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
            <?php esc_html_e( 'Channels', 'insicore-chat-bubble' ); ?>
        </button>
        <button type="button" class="pcb-tab-btn" data-tab="design">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="13.5" cy="6.5" r=".5"/><circle cx="17.5" cy="10.5" r=".5"/><circle cx="8.5" cy="7.5" r=".5"/><circle cx="6.5" cy="12.5" r=".5"/><path d="M12 2C6.5 2 2 6.5 2 12s4.5 10 10 10c.926 0 1.648-.746 1.648-1.688 0-.437-.18-.835-.437-1.125-.29-.289-.438-.652-.438-1.125a1.64 1.64 0 0 1 1.668-1.668h1.996c3.051 0 5.555-2.503 5.555-5.554C21.965 6.012 17.461 2 12 2z"/></svg>
            <?php esc_html_e( 'Design', 'insicore-chat-bubble' ); ?>
        </button>
        <button type="button" class="pcb-tab-btn" data-tab="visibility">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
            <?php esc_html_e( 'Visibility', 'insicore-chat-bubble' ); ?>
        </button>
        <button type="button" class="pcb-tab-btn" data-tab="settings">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09a1.65 1.65 0 0 0-1-1.51 1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09a1.65 1.65 0 0 0 1.51-1 1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>
            <?php esc_html_e( 'Settings', 'insicore-chat-bubble' ); ?>
        </button>
    </div>

    <div class="pcb-builder-sidebar-sections">
        <?php
        require PCB_PLUGIN_DIR . 'includes/admin/views/tab-channels.php';
        require PCB_PLUGIN_DIR . 'includes/admin/views/tab-design.php';
        require PCB_PLUGIN_DIR . 'includes/admin/views/tab-visibility.php';
        require PCB_PLUGIN_DIR . 'includes/admin/views/tab-settings.php';
        ?>
    </div><!-- /.pcb-builder-sidebar-sections -->

    <!-- Preview Lock -->
    <div class="pcb-preview-ops">
        <label class="pcb-switch-mini">
            <input type="checkbox" id="pcb-force-expand">
            <span class="pcb-slider-mini"></span>
        </label>
        <span><?php esc_html_e( 'Lock Preview Open', 'insicore-chat-bubble' ); ?></span>
    </div>
    <?php
}
