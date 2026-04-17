<?php
/**
 * Render the floating contact bubble markup in the site footer.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

add_action( 'wp_footer', 'pcb_render_bubble', 5 );

function pcb_render_bubble() {
    $options    = pcb_get_settings();
    $is_preview = isset( $_GET['pcb_preview'] ) && current_user_can( 'manage_options' );

    // Visibility gate ??previews always render so the admin can edit.
    if ( ! $is_preview && ! pcb_should_show_bubble( $options ) ) {
        return;
    }

    $channels = json_decode( $options['channels_data'] ?? '[]', true );
    if ( ! is_array( $channels ) ) $channels = [];

    $corner      = $options['corner']         ?? 'right';
    $custom_svg  = $options['custom_svg']     ?? '';
    $show_labels = ! empty( $options['show_labels'] );
    $anim_type   = $options['animation_type'] ?? 'none';
    $anim_class  = $anim_type !== 'none' ? ' pcb-anim-' . $anim_type : '';

    // Trigger mode: when any trigger is active, start hidden and let JS reveal.
    $has_trigger = ! empty( $options['trigger_scroll_enabled'] )
                || ! empty( $options['trigger_time_enabled'] )
                || ! empty( $options['trigger_exit_intent_enabled'] );
    $hidden_init = ( $has_trigger && ! $is_preview ) ? ' pcb-hidden-init' : '';

    // Collect form-type channels so we can render their popups.
    $forms = [];
    foreach ( $channels as $idx => $ch ) {
        if ( ( $ch['type'] ?? '' ) === 'form' && ( ! empty( $ch['enabled'] ) || $is_preview ) ) {
            $forms[ $idx ] = $ch;
        }
    }

    // Inject per-request data for the frontend JS (triggers + analytics).
    $config = [
        'triggers' => [
            'scroll'   => [
                'enabled' => ! empty( $options['trigger_scroll_enabled'] ),
                'percent' => (int) ( $options['trigger_scroll_percent'] ?: 30 ),
            ],
            'time'     => [
                'enabled' => ! empty( $options['trigger_time_enabled'] ),
                'seconds' => (int) ( $options['trigger_time_seconds'] ?: 10 ),
            ],
            'exit'     => [
                'enabled' => ! empty( $options['trigger_exit_intent_enabled'] ),
            ],
        ],
        'msgBubble' => [
            'enabled'  => ! empty( $options['msgbubble_enabled'] ),
            'text'     => (string) ( $options['msgbubble_text'] ?? '' ),
            'delay'    => (int) ( $options['msgbubble_delay'] ?: 3 ),
            'autohide' => (int) ( $options['msgbubble_autohide'] ?: 0 ),
        ],
        'analytics' => [
            'enabled' => ! $is_preview,
            'nonce'   => wp_create_nonce( 'pcb_track' ),
        ],
        'form' => [
            'nonce' => wp_create_nonce( 'pcb_form' ),
        ],
        'ajaxUrl' => admin_url( 'admin-ajax.php' ),
        'i18n' => [
            'thanks'       => __( 'Thanks!',                          'insicore-chat-bubble' ),
            'error'        => __( 'Something went wrong.',            'insicore-chat-bubble' ),
            'networkError' => __( 'Network error. Please try again.', 'insicore-chat-bubble' ),
        ],
    ];
    ?>
    <div id="pcb-container" class="pcb-container pcb-pos-<?php echo esc_attr( $corner ); ?> pcb-no-tip<?php echo esc_attr( $hidden_init ); ?>">

        <div class="pcb-menu" id="pcb-menu">
            <?php foreach ( $channels as $index => $ch ) : ?>
                <?php pcb_render_channel_item( $ch, $index, $show_labels, $is_preview ); ?>
            <?php endforeach; ?>
        </div>

        <div class="pcb-main-wrap<?php echo esc_attr( $anim_class ); ?>" id="pcb-main-wrap">
            <button class="pcb-main-bubble" id="pcb-main-bubble"
                    aria-label="<?php esc_attr_e( 'Contact us', 'insicore-chat-bubble' ); ?>">
                <?php if ( ! empty( $custom_svg ) ) : ?>
                    <img src="<?php echo esc_url( $custom_svg ); ?>" class="pcb-chat-icon" alt="">
                <?php else : ?>
                    <svg class="pcb-chat-icon" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M21 15C21 15.5304 20.7893 16.0391 20.4142 16.4142C20.0391 16.7893 19.5304 17 19 17H7L3 21V5C3 4.46957 3.21071 3.96086 3.58579 3.58579C3.96086 3.21071 4.46957 3 5 3H19C19.5304 3 20.0391 3.21071 20.4142 3.58579C20.7893 3.96086 21 4.46957 21 5V15Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                <?php endif; ?>
                <?php if ( ! empty( $options['badge_enabled'] ) ) : ?>
                    <span class="pcb-badge" style="background:<?php echo esc_attr( $options['badge_bg_color'] ?: '#ef4444' ); ?>;color:<?php echo esc_attr( $options['badge_text_color'] ?: '#ffffff' ); ?>">
                        <?php echo esc_html( $options['badge_text'] ?: '1' ); ?>
                    </span>
                <?php endif; ?>
            </button>
        </div>

        <?php if ( ! empty( $options['msgbubble_enabled'] ) && ! empty( $options['msgbubble_text'] ) ) : ?>
        <div class="pcb-msgbubble" id="pcb-msgbubble"
             style="--pcb-mb-bg:<?php echo esc_attr( $options['msgbubble_bg_color'] ?: '#ffffff' ); ?>;--pcb-mb-color:<?php echo esc_attr( $options['msgbubble_text_color'] ?: '#1a202c' ); ?>">
            <button class="pcb-msgbubble-close" type="button" aria-label="<?php esc_attr_e( 'Close', 'insicore-chat-bubble' ); ?>">&times;</button>
            <div class="pcb-msgbubble-text"><?php echo esc_html( $options['msgbubble_text'] ); ?></div>
        </div>
        <?php endif; ?>
    </div>

    <?php foreach ( $forms as $idx => $form ) :
        $fc = $form['form_config'] ?? [];
        $show_phone   = ! empty( $fc['show_phone'] );
        $show_message = isset( $fc['show_message'] ) ? $fc['show_message'] : true;
        $title        = $fc['title']        ?? __( 'Send us a message', 'insicore-chat-bubble' );
        $submit_label = $fc['submit_label'] ?? __( 'Send Message', 'insicore-chat-bubble' );
    ?>
    <div class="pcb-form-modal" id="pcb-form-modal-<?php echo (int) $idx; ?>" data-idx="<?php echo (int) $idx; ?>" aria-hidden="true">
        <div class="pcb-form-overlay"></div>
        <div class="pcb-form-dialog" role="dialog" aria-modal="true">
            <button type="button" class="pcb-form-close" aria-label="<?php esc_attr_e( 'Close', 'insicore-chat-bubble' ); ?>">&times;</button>
            <h3 class="pcb-form-title"><?php echo esc_html( $title ); ?></h3>
            <form class="pcb-contact-form" novalidate>
                <input type="hidden" name="action" value="pcb_submit_form">
                <input type="hidden" name="nonce" value="<?php echo esc_attr( wp_create_nonce( 'pcb_form' ) ); ?>">
                <input type="hidden" name="page_url" value="<?php echo esc_attr( esc_url_raw( home_url( wp_unslash( $_SERVER['REQUEST_URI'] ?? '/' ) ) ) ); ?>">
                <!-- honeypot -->
                <input type="text" name="pcb_hp" class="pcb-hp" tabindex="-1" autocomplete="off">

                <label class="pcb-field">
                    <span><?php esc_html_e( 'Name', 'insicore-chat-bubble' ); ?> *</span>
                    <input type="text" name="name" required>
                </label>
                <label class="pcb-field">
                    <span><?php esc_html_e( 'Email', 'insicore-chat-bubble' ); ?> *</span>
                    <input type="email" name="email" required>
                </label>
                <?php if ( $show_phone ) : ?>
                <label class="pcb-field">
                    <span><?php esc_html_e( 'Phone', 'insicore-chat-bubble' ); ?></span>
                    <input type="tel" name="phone">
                </label>
                <?php endif; ?>
                <?php if ( $show_message ) : ?>
                <label class="pcb-field">
                    <span><?php esc_html_e( 'Message', 'insicore-chat-bubble' ); ?> *</span>
                    <textarea name="message" rows="4" required></textarea>
                </label>
                <?php endif; ?>
                <div class="pcb-form-status" aria-live="polite"></div>
                <button type="submit" class="pcb-form-submit"><?php echo esc_html( $submit_label ); ?></button>
            </form>
        </div>
    </div>
    <?php endforeach; ?>

    <script>
    window.pcbFrontendConfig = <?php echo wp_json_encode( $config, JSON_HEX_TAG | JSON_HEX_AMP ); ?>;
    </script>
    <?php
}

/**
 * Render a single channel `<a>` (or button for form type) inside the menu.
 */
function pcb_render_channel_item( array $ch, $index, $show_labels, $is_preview ) {
    if ( empty( $ch['enabled'] ) && ! $is_preview ) return;

    $type  = $ch['type']  ?? '';
    $label = $ch['label'] ?? '';

    // Multi-agent rotation: pass all agents to the frontend JS for round-robin via localStorage.
    $active_url   = $ch['url'] ?? '';
    $active_label = $label;
    $agents_json  = null;
    if ( ! empty( $ch['agents_enabled'] ) && ! empty( $ch['agents'] ) && is_array( $ch['agents'] ) ) {
        $valid_agents = array_values( array_filter( $ch['agents'], static function( $a ) {
            return is_array( $a ) && ! empty( $a['value'] );
        } ) );
        if ( count( $valid_agents ) >= 2 ) {
            $preset_msg_tmp = (string) ( $ch['preset_message'] ?? '' );
            $agents_list    = [];
            foreach ( $valid_agents as $a ) {
                $agents_list[] = [
                    'url'   => pcb_normalize_channel_url( $type, $a['value'], $preset_msg_tmp ),
                    'label' => sanitize_text_field( $a['name'] ?? '' ),
                ];
            }
            // Use first agent as href fallback (for JS-disabled browsers).
            $active_url  = $agents_list[0]['url'];
            $agents_json = wp_json_encode( $agents_list, JSON_HEX_TAG | JSON_HEX_AMP );
        } elseif ( ! empty( $valid_agents ) ) {
            $active_url   = $valid_agents[0]['value'] ?? $active_url;
            $active_label = ! empty( $valid_agents[0]['name'] ) ? $valid_agents[0]['name'] : $active_label;
        }
    }

    $preset_msg = (string) ( $ch['preset_message'] ?? '' );
    $url        = ( $type === 'form' )
        ? '#'
        : pcb_normalize_channel_url( $type, $active_url, $preset_msg );
    $bg         = esc_attr( $ch['color'] ?? '#4a5568' );
    $hov        = esc_attr( $ch['hover'] ?? $bg );
    $cls        = 'pcb-menu-item' . ( $show_labels ? ' pcb-has-label' : '' );
    if ( $type === 'form' ) $cls .= ' pcb-form-trigger';
    $style      = "--pcb-index:{$index};--pcb-ch-color:{$bg};background:{$bg};--pcb-ch-hover:{$hov};";
    $is_form    = ( $type === 'form' );
    $attrs      = $is_form
        ? 'data-form-idx="' . (int) $index . '"'
        : 'target="_blank" rel="noopener noreferrer"';
    ?>
    <a href="<?php echo esc_url( $url ); ?>"
       class="<?php echo esc_attr( $cls ); ?>"
       <?php echo $attrs; ?>
       aria-label="<?php echo esc_attr( $active_label ); ?>"
       data-pcb-channel="<?php echo esc_attr( $type ); ?>"
       data-pcb-label="<?php echo esc_attr( $active_label ); ?>"
       <?php if ( $agents_json ) : ?>
       data-agents="<?php echo esc_attr( $agents_json ); ?>"
       data-agent-group="<?php echo (int) $index; ?>"
       <?php endif; ?>
       style="<?php echo esc_attr( $style ); ?>">
        <span class="pcb-icon"><?php echo wp_kses( $ch['icon'] ?? '', pcb_allowed_icon_tags() ); ?></span>
        <?php if ( $show_labels && $active_label ) : ?>
            <span class="pcb-ch-label"><?php echo esc_html( $active_label ); ?></span>
        <?php endif; ?>
    </a>
    <?php
}
