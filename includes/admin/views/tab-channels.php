<?php
/**
 * "Channels" tab ??quick-add preset grid + active channel list.
 *
 * Available vars from parent scope:
 *   $options, $presets, $channels_json
 */

if ( ! defined( 'ABSPATH' ) ) exit;
?>
<div class="pcb-tab-panel active" data-panel="channels">

    <div class="pcb-sidebar-group">
        <h4 class="pcb-group-title"><?php esc_html_e( 'Quick Add', 'insicore-chat-bubble' ); ?></h4>
        <div class="pcb-side-app-grid">
            <?php foreach ( $presets as $type => $data ) : ?>
            <div class="pcb-app-tile"
                 data-type="<?php echo esc_attr( $type ); ?>"
                 data-label="<?php echo esc_attr( $data['label'] ); ?>"
                 data-color="<?php echo esc_attr( $data['color'] ); ?>"
                 data-hover="<?php echo esc_attr( $data['hover'] ); ?>"
                 data-icon="<?php echo esc_attr( $data['icon'] ); ?>"
                 title="<?php echo esc_attr( $data['label'] ); ?>">
                <div class="pcb-tile-icon" style="background:<?php echo esc_attr( $data['color'] ); ?>"><?php echo $data['icon']; ?></div>
                <span class="pcb-tile-label"><?php echo esc_html( $data['label'] ); ?></span>
            </div>
            <?php endforeach; ?>
            <div class="pcb-app-tile pcb-add-form-tile" title="<?php esc_attr_e( 'Add Contact Form', 'insicore-chat-bubble' ); ?>">
                <div class="pcb-tile-icon" style="background:#6366f1; color:#fff;">
                    <svg viewBox="0 0 24 24" fill="currentColor"><path d="M14 2H6c-1.1 0-1.99.9-1.99 2L4 20c0 1.1.89 2 1.99 2H18c1.1 0 2-.9 2-2V8l-6-6zm2 16H8v-2h8v2zm0-4H8v-2h8v2zm-3-5V3.5L18.5 9H13z"/></svg>
                </div>
                <span class="pcb-tile-label"><?php esc_html_e( 'Form', 'insicore-chat-bubble' ); ?></span>
            </div>
            <div class="pcb-app-tile pcb-add-custom-tile" title="<?php esc_attr_e( 'Add Custom', 'insicore-chat-bubble' ); ?>">
                <div class="pcb-tile-icon" style="background:#f1f5f9; color:#64748b;">
                    <svg viewBox="0 0 24 24" fill="currentColor"><path d="M19 13h-6v6h-2v-6H5v-2h6V5h2v6h6v2z"/></svg>
                </div>
                <span class="pcb-tile-label" style="color:#64748b"><?php esc_html_e( 'Custom', 'insicore-chat-bubble' ); ?></span>
            </div>
        </div>
    </div>

    <div class="pcb-sidebar-group">
        <h4 class="pcb-group-title"><?php esc_html_e( 'Active Channels', 'insicore-chat-bubble' ); ?></h4>
        <div id="pcb-active-stack" class="pcb-modern-stack"></div>
        <input type="hidden" name="pcb_settings[channels_data]" id="pcb_channels_data_hidden" value="<?php echo esc_attr( $channels_json ); ?>">
    </div>

</div>
