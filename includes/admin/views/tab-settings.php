<?php
/**
 * "Settings" tab ??behavior triggers (when the bubble appears).
 *
 * Available vars from parent scope: $options
 */

if ( ! defined( 'ABSPATH' ) ) exit;

$opt = fn( $k, $d = '' ) => pcb_opt( $options, $k, $d );
?>
<div class="pcb-tab-panel" data-panel="settings">

    <!-- ?€?€ Behavior Triggers ?€?€ -->
    <div class="pcb-sidebar-group">
        <h4 class="pcb-group-title"><?php esc_html_e( 'When to Show', 'insicore-chat-bubble' ); ?></h4>
        <p class="pcb-option-hint" style="margin:0 0 14px;">
            <?php esc_html_e( 'Leave all off to show immediately. If any trigger is on, the bubble stays hidden until it fires.', 'insicore-chat-bubble' ); ?>
        </p>
        <div class="pcb-side-form-grid">
            <div class="pcb-form-row span-2">
                <div class="pcb-option-row">
                    <div><label><?php esc_html_e( 'Scroll Trigger', 'insicore-chat-bubble' ); ?></label>
                    <span class="pcb-option-hint"><?php esc_html_e( 'Show after visitor scrolls past a % of the page.', 'insicore-chat-bubble' ); ?></span></div>
                    <label class="pcb-switch-mini">
                        <input type="checkbox" name="pcb_settings[trigger_scroll_enabled]" value="1" <?php checked( ! empty( $opt( 'trigger_scroll_enabled' ) ) ); ?>>
                        <span class="pcb-slider-mini"></span>
                    </label>
                </div>
            </div>
            <div class="pcb-form-row span-2">
                <label><?php esc_html_e( 'Scroll Percentage', 'insicore-chat-bubble' ); ?></label>
                <input type="number" name="pcb_settings[trigger_scroll_percent]" value="<?php echo esc_attr( $opt( 'trigger_scroll_percent', 30 ) ); ?>" min="5" max="100">
            </div>

            <div class="pcb-form-row span-2">
                <div class="pcb-option-row">
                    <div><label><?php esc_html_e( 'Time Trigger', 'insicore-chat-bubble' ); ?></label>
                    <span class="pcb-option-hint"><?php esc_html_e( 'Show after N seconds on the page.', 'insicore-chat-bubble' ); ?></span></div>
                    <label class="pcb-switch-mini">
                        <input type="checkbox" name="pcb_settings[trigger_time_enabled]" value="1" <?php checked( ! empty( $opt( 'trigger_time_enabled' ) ) ); ?>>
                        <span class="pcb-slider-mini"></span>
                    </label>
                </div>
            </div>
            <div class="pcb-form-row span-2">
                <label><?php esc_html_e( 'Seconds', 'insicore-chat-bubble' ); ?></label>
                <input type="number" name="pcb_settings[trigger_time_seconds]" value="<?php echo esc_attr( $opt( 'trigger_time_seconds', 10 ) ); ?>" min="1" max="600">
            </div>

            <div class="pcb-form-row span-2">
                <div class="pcb-option-row">
                    <div><label><?php esc_html_e( 'Exit Intent', 'insicore-chat-bubble' ); ?></label>
                    <span class="pcb-option-hint"><?php esc_html_e( 'Show when the visitor moves to leave.', 'insicore-chat-bubble' ); ?></span></div>
                    <label class="pcb-switch-mini">
                        <input type="checkbox" name="pcb_settings[trigger_exit_intent_enabled]" value="1" <?php checked( ! empty( $opt( 'trigger_exit_intent_enabled' ) ) ); ?>>
                        <span class="pcb-slider-mini"></span>
                    </label>
                </div>
            </div>
        </div>
    </div>


    <!-- ?€?€ Contact Form ?€?€ -->
    <div class="pcb-sidebar-group">
        <h4 class="pcb-group-title"><?php esc_html_e( 'Contact Form', 'insicore-chat-bubble' ); ?></h4>
        <div class="pcb-side-form-grid">
            <div class="pcb-form-row span-2">
                <label><?php esc_html_e( 'Notification Email', 'insicore-chat-bubble' ); ?></label>
                <input type="email" name="pcb_settings[form_notify_email]" value="<?php echo esc_attr( $opt( 'form_notify_email' ) ); ?>" placeholder="<?php echo esc_attr( get_option( 'admin_email' ) ); ?>">
                <span class="pcb-option-hint"><?php esc_html_e( 'Leave blank to use the site admin email.', 'insicore-chat-bubble' ); ?></span>
            </div>
            <div class="pcb-form-row span-2">
                <label><?php esc_html_e( 'Success Message', 'insicore-chat-bubble' ); ?></label>
                <input type="text" name="pcb_settings[form_success_message]" value="<?php echo esc_attr( $opt( 'form_success_message' ) ); ?>" placeholder="<?php esc_attr_e( 'Thanks! We will get back to you soon.', 'insicore-chat-bubble' ); ?>">
            </div>
        </div>
    </div>

</div>
