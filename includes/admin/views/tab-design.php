<?php
/**
 * "Design" tab ??all visual configuration, organized by feature group:
 *
 *   1. Main Bubble        ??colors, custom icon, theme preset, animation
 *   2. Layout & Position  ??size, corner, offsets (responsive)
 *   3. Greeting Message   ??the chat-style popup (delay/autohide/colors)
 *   4. Notification Badge ??red-dot indicator
 *   5. Channel Menu       ??item labels toggle
 *   6. Custom CSS         ??advanced overrides
 *
 * Available vars from parent scope: $options
 */

if ( ! defined( 'ABSPATH' ) ) exit;

$opt = fn( $k, $d = '' ) => pcb_opt( $options, $k, $d );

$hover_val     = $opt( 'bubble_hover_color', '' );
$hover_display = $hover_val ?: $opt( 'bubble_color', '#0073aa' );
$themes        = pcb_get_theme_presets();

$anims = [
    'none'   => __( '??None ??, 'insicore-chat-bubble' ),
    'pulse'  => __( 'Pulse',    'insicore-chat-bubble' ),
    'bounce' => __( 'Bounce',   'insicore-chat-bubble' ),
    'float'  => __( 'Float',    'insicore-chat-bubble' ),
    'shake'  => __( 'Shake',    'insicore-chat-bubble' ),
    'zoom'   => __( 'Zoom',     'insicore-chat-bubble' ),
    'tada'   => __( 'Tada',     'insicore-chat-bubble' ),
    'rotate' => __( 'Rotate',   'insicore-chat-bubble' ),
    'glow'   => __( 'Glow',     'insicore-chat-bubble' ),
];
?>
<div class="pcb-tab-panel" data-panel="design">

    <!-- ?€?€ 1. Main Bubble ?€?€ -->
    <div class="pcb-sidebar-group">
        <h4 class="pcb-group-title"><?php esc_html_e( 'Main Bubble', 'insicore-chat-bubble' ); ?></h4>
        <div class="pcb-side-form-grid">
            <div class="pcb-form-row">
                <label><?php esc_html_e( 'Main Color', 'insicore-chat-bubble' ); ?></label>
                <div class="pcb-color-row" onclick="this.querySelector('input[type=color]').click()">
                    <input type="color" name="pcb_settings[bubble_color]" value="<?php echo esc_attr( $opt( 'bubble_color', '#0073aa' ) ); ?>">
                    <span class="pcb-color-hex"><?php echo esc_html( strtoupper( $opt( 'bubble_color', '#0073AA' ) ) ); ?></span>
                </div>
            </div>
            <div class="pcb-form-row">
                <label><?php esc_html_e( 'Hover Color', 'insicore-chat-bubble' ); ?></label>
                <div class="pcb-color-row" onclick="this.querySelector('input[type=color]').click()">
                    <input type="color" name="pcb_settings[bubble_hover_color]" value="<?php echo esc_attr( $hover_display ); ?>" data-default-from="pcb_settings[bubble_color]">
                    <span class="pcb-color-hex"><?php echo esc_html( strtoupper( $hover_display ) ); ?></span>
                </div>
            </div>
            <div class="pcb-form-row span-2">
                <label><?php esc_html_e( 'Custom Icon', 'insicore-chat-bubble' ); ?></label>
                <div class="pcb-upload-row">
                    <input type="text" id="pcb_custom_svg" name="pcb_settings[custom_svg]" value="<?php echo esc_attr( $opt( 'custom_svg' ) ); ?>" placeholder="https://...">
                    <button type="button" class="button pcb-upload-button" data-target="pcb_custom_svg"><?php esc_html_e( 'Upload', 'insicore-chat-bubble' ); ?></button>
                </div>
                <span class="pcb-option-hint"><?php esc_html_e( 'Leave blank to use the default chat icon.', 'insicore-chat-bubble' ); ?></span>
            </div>
            <div class="pcb-form-row span-2">
                <label><?php esc_html_e( 'Style', 'insicore-chat-bubble' ); ?></label>
                <select name="pcb_settings[theme_preset]">
                    <?php foreach ( $themes as $slug => $def ) : ?>
                    <option value="<?php echo esc_attr( $slug ); ?>" <?php selected( $opt( 'theme_preset' ), $slug ); ?>><?php echo esc_html( $def['label'] ); ?></option>
                    <?php endforeach; ?>
                </select>
                <span class="pcb-option-hint"><?php esc_html_e( 'Applies a polished look on top of your colors.', 'insicore-chat-bubble' ); ?></span>
            </div>
            <div class="pcb-form-row span-2">
                <label><?php esc_html_e( 'Idle Animation', 'insicore-chat-bubble' ); ?></label>
                <select name="pcb_settings[animation_type]">
                    <?php foreach ( $anims as $val => $lbl ) : ?>
                    <option value="<?php echo esc_attr( $val ); ?>" <?php selected( $opt( 'animation_type', 'none' ), $val ); ?>><?php echo esc_html( $lbl ); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
    </div>

    <!-- ?€?€ 2. Layout & Position (responsive) ?€?€ -->
    <div class="pcb-resp-section">
        <div class="pcb-resp-header">
            <h4 class="pcb-group-title" style="margin:0;border:none;padding:0;"><?php esc_html_e( 'Layout & Position', 'insicore-chat-bubble' ); ?></h4>
            <div class="pcb-resp-toggle">
                <button type="button" class="pcb-resp-btn active" data-resp="desktop">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="3" width="20" height="14" rx="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/></svg>
                    <?php esc_html_e( 'Desktop', 'insicore-chat-bubble' ); ?>
                </button>
                <button type="button" class="pcb-resp-btn" data-resp="mobile">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="5" y="2" width="14" height="20" rx="2"/><circle cx="12" cy="18" r="1" fill="currentColor"/></svg>
                    <?php esc_html_e( 'Mobile', 'insicore-chat-bubble' ); ?>
                </button>
            </div>
        </div>

        <div class="pcb-resp-panel active" data-resp-panel="desktop">
            <div class="pcb-side-form-grid" style="margin-top:10px;">
                <div class="pcb-form-row">
                    <label><?php esc_html_e( 'Position', 'insicore-chat-bubble' ); ?></label>
                    <select name="pcb_settings[corner]">
                        <option value="right" <?php selected( $opt( 'corner', 'right' ), 'right' ); ?>><?php esc_html_e( 'Right', 'insicore-chat-bubble' ); ?></option>
                        <option value="left"  <?php selected( $opt( 'corner', 'right' ), 'left' ); ?>><?php esc_html_e( 'Left', 'insicore-chat-bubble' ); ?></option>
                    </select>
                </div>
                <div class="pcb-form-row">
                    <label><?php esc_html_e( 'Size (px)', 'insicore-chat-bubble' ); ?></label>
                    <input type="number" name="pcb_settings[button_size]" value="<?php echo esc_attr( $opt( 'button_size', 60 ) ); ?>" placeholder="60" min="36" max="120">
                </div>
                <div class="pcb-form-row">
                    <label><?php esc_html_e( 'Radius (px)', 'insicore-chat-bubble' ); ?></label>
                    <input type="number" name="pcb_settings[border_radius]" value="<?php echo esc_attr( $opt( 'border_radius', '' ) ); ?>" placeholder="circle">
                </div>
                <div class="pcb-form-row">
                    <label><?php esc_html_e( 'Bottom Offset', 'insicore-chat-bubble' ); ?></label>
                    <input type="number" name="pcb_settings[offset_bottom]" value="<?php echo esc_attr( $opt( 'offset_bottom', 30 ) ); ?>">
                </div>
                <div class="pcb-form-row span-2">
                    <label><?php esc_html_e( 'Side Offset', 'insicore-chat-bubble' ); ?></label>
                    <input type="number" name="pcb_settings[offset_side]" value="<?php echo esc_attr( $opt( 'offset_side', 30 ) ); ?>">
                </div>
            </div>
        </div>

        <div class="pcb-resp-panel" data-resp-panel="mobile">
            <p class="pcb-option-hint" style="margin:10px 0 8px;"><?php esc_html_e( 'Leave blank to inherit the desktop value. Breakpoint: ??768px.', 'insicore-chat-bubble' ); ?></p>
            <div class="pcb-side-form-grid">
                <div class="pcb-form-row">
                    <label><?php esc_html_e( 'Position', 'insicore-chat-bubble' ); ?></label>
                    <select name="pcb_settings[corner_mobile]">
                        <option value=""      <?php selected( $opt( 'corner_mobile', '' ), '' ); ?>><?php esc_html_e( '??inherit ??, 'insicore-chat-bubble' ); ?></option>
                        <option value="right" <?php selected( $opt( 'corner_mobile', '' ), 'right' ); ?>><?php esc_html_e( 'Right', 'insicore-chat-bubble' ); ?></option>
                        <option value="left"  <?php selected( $opt( 'corner_mobile', '' ), 'left' ); ?>><?php esc_html_e( 'Left', 'insicore-chat-bubble' ); ?></option>
                    </select>
                </div>
                <div class="pcb-form-row">
                    <label><?php esc_html_e( 'Size', 'insicore-chat-bubble' ); ?></label>
                    <input type="number" name="pcb_settings[button_size_mobile]" value="<?php echo esc_attr( $opt( 'button_size_mobile', '' ) ); ?>" placeholder="<?php echo esc_attr( $opt( 'button_size', 60 ) ); ?>">
                </div>
                <div class="pcb-form-row">
                    <label><?php esc_html_e( 'Radius', 'insicore-chat-bubble' ); ?></label>
                    <input type="number" name="pcb_settings[border_radius_mobile]" value="<?php echo esc_attr( $opt( 'border_radius_mobile', '' ) ); ?>" placeholder="<?php echo esc_attr( $opt( 'border_radius', '' ) ?: 'circle' ); ?>">
                </div>
                <div class="pcb-form-row">
                    <label><?php esc_html_e( 'Bottom', 'insicore-chat-bubble' ); ?></label>
                    <input type="number" name="pcb_settings[offset_bottom_mobile]" value="<?php echo esc_attr( $opt( 'offset_bottom_mobile', '' ) ); ?>" placeholder="<?php echo esc_attr( $opt( 'offset_bottom', 30 ) ); ?>">
                </div>
                <div class="pcb-form-row span-2">
                    <label><?php esc_html_e( 'Side', 'insicore-chat-bubble' ); ?></label>
                    <input type="number" name="pcb_settings[offset_side_mobile]" value="<?php echo esc_attr( $opt( 'offset_side_mobile', '' ) ); ?>" placeholder="<?php echo esc_attr( $opt( 'offset_side', 30 ) ); ?>">
                </div>
            </div>
        </div>
    </div>

    <!-- ?€?€ 3. Greeting Message ?€?€ -->
    <div class="pcb-sidebar-group">
        <h4 class="pcb-group-title"><?php esc_html_e( 'Greeting Message', 'insicore-chat-bubble' ); ?></h4>
        <p class="pcb-option-hint" style="margin:0 0 12px;"><?php esc_html_e( 'A chat-style bubble next to the main button that invites visitors to click.', 'insicore-chat-bubble' ); ?></p>
        <div class="pcb-side-form-grid">
            <div class="pcb-form-row span-2">
                <div class="pcb-option-row">
                    <label><?php esc_html_e( 'Enable', 'insicore-chat-bubble' ); ?></label>
                    <label class="pcb-switch-mini">
                        <input type="checkbox" name="pcb_settings[msgbubble_enabled]" value="1" <?php checked( ! empty( $opt( 'msgbubble_enabled' ) ) ); ?>>
                        <span class="pcb-slider-mini"></span>
                    </label>
                </div>
            </div>
            <div class="pcb-form-row span-2">
                <label><?php esc_html_e( 'Message Text', 'insicore-chat-bubble' ); ?></label>
                <input type="text" name="pcb_settings[msgbubble_text]" value="<?php echo esc_attr( $opt( 'msgbubble_text' ) ); ?>" placeholder="<?php esc_attr_e( 'Hi! Need help? Chat with us ??', 'insicore-chat-bubble' ); ?>">
            </div>
            <div class="pcb-form-row">
                <label><?php esc_html_e( 'Delay (sec)', 'insicore-chat-bubble' ); ?></label>
                <input type="number" name="pcb_settings[msgbubble_delay]" value="<?php echo esc_attr( $opt( 'msgbubble_delay', 3 ) ); ?>" min="0" max="120">
            </div>
            <div class="pcb-form-row">
                <label><?php esc_html_e( 'Auto-hide (sec)', 'insicore-chat-bubble' ); ?></label>
                <input type="number" name="pcb_settings[msgbubble_autohide]" value="<?php echo esc_attr( $opt( 'msgbubble_autohide', 0 ) ); ?>" min="0" max="600" placeholder="0 = never">
            </div>
            <div class="pcb-form-row">
                <label><?php esc_html_e( 'BG Color', 'insicore-chat-bubble' ); ?></label>
                <div class="pcb-color-row" onclick="this.querySelector('input[type=color]').click()">
                    <input type="color" name="pcb_settings[msgbubble_bg_color]" value="<?php echo esc_attr( $opt( 'msgbubble_bg_color', '#ffffff' ) ); ?>">
                    <span class="pcb-color-hex"><?php echo esc_html( strtoupper( $opt( 'msgbubble_bg_color', '#FFFFFF' ) ) ); ?></span>
                </div>
            </div>
            <div class="pcb-form-row">
                <label><?php esc_html_e( 'Text Color', 'insicore-chat-bubble' ); ?></label>
                <div class="pcb-color-row" onclick="this.querySelector('input[type=color]').click()">
                    <input type="color" name="pcb_settings[msgbubble_text_color]" value="<?php echo esc_attr( $opt( 'msgbubble_text_color', '#1a202c' ) ); ?>">
                    <span class="pcb-color-hex"><?php echo esc_html( strtoupper( $opt( 'msgbubble_text_color', '#1A202C' ) ) ); ?></span>
                </div>
            </div>
        </div>
    </div>

    <!-- ?€?€ 4. Notification Badge ?€?€ -->
    <div class="pcb-sidebar-group">
        <h4 class="pcb-group-title"><?php esc_html_e( 'Notification Badge', 'insicore-chat-bubble' ); ?></h4>
        <p class="pcb-option-hint" style="margin:0 0 12px;"><?php esc_html_e( 'A small red-dot indicator on the main button to grab attention.', 'insicore-chat-bubble' ); ?></p>
        <div class="pcb-side-form-grid">
            <div class="pcb-form-row span-2">
                <div class="pcb-option-row">
                    <label><?php esc_html_e( 'Enable', 'insicore-chat-bubble' ); ?></label>
                    <label class="pcb-switch-mini">
                        <input type="checkbox" name="pcb_settings[badge_enabled]" value="1" <?php checked( ! empty( $opt( 'badge_enabled' ) ) ); ?>>
                        <span class="pcb-slider-mini"></span>
                    </label>
                </div>
            </div>
            <div class="pcb-form-row span-2">
                <label><?php esc_html_e( 'Badge Text', 'insicore-chat-bubble' ); ?></label>
                <input type="text" name="pcb_settings[badge_text]" value="<?php echo esc_attr( $opt( 'badge_text', '1' ) ); ?>" maxlength="6" placeholder="1">
            </div>
            <div class="pcb-form-row">
                <label><?php esc_html_e( 'BG Color', 'insicore-chat-bubble' ); ?></label>
                <div class="pcb-color-row" onclick="this.querySelector('input[type=color]').click()">
                    <input type="color" name="pcb_settings[badge_bg_color]" value="<?php echo esc_attr( $opt( 'badge_bg_color', '#ef4444' ) ); ?>">
                    <span class="pcb-color-hex"><?php echo esc_html( strtoupper( $opt( 'badge_bg_color', '#EF4444' ) ) ); ?></span>
                </div>
            </div>
            <div class="pcb-form-row">
                <label><?php esc_html_e( 'Text Color', 'insicore-chat-bubble' ); ?></label>
                <div class="pcb-color-row" onclick="this.querySelector('input[type=color]').click()">
                    <input type="color" name="pcb_settings[badge_text_color]" value="<?php echo esc_attr( $opt( 'badge_text_color', '#ffffff' ) ); ?>">
                    <span class="pcb-color-hex"><?php echo esc_html( strtoupper( $opt( 'badge_text_color', '#FFFFFF' ) ) ); ?></span>
                </div>
            </div>
        </div>
    </div>

    <!-- ?€?€ 5. Channel Menu ?€?€ -->
    <div class="pcb-sidebar-group">
        <h4 class="pcb-group-title"><?php esc_html_e( 'Channel Menu', 'insicore-chat-bubble' ); ?></h4>
        <div class="pcb-option-row">
            <div>
                <label><?php esc_html_e( 'Show Channel Labels', 'insicore-chat-bubble' ); ?></label>
                <span class="pcb-option-hint"><?php esc_html_e( 'Display names next to channel icons when expanded.', 'insicore-chat-bubble' ); ?></span>
            </div>
            <label class="pcb-switch-mini">
                <input type="checkbox" name="pcb_settings[show_labels]" value="1" <?php checked( ! empty( $opt( 'show_labels' ) ) ); ?>>
                <span class="pcb-slider-mini"></span>
            </label>
        </div>
    </div>

    <!-- ?€?€ 6. Custom CSS ?€?€ -->
    <div class="pcb-sidebar-group">
        <h4 class="pcb-group-title"><?php esc_html_e( 'Custom CSS', 'insicore-chat-bubble' ); ?></h4>
        <p class="pcb-option-hint" style="margin:0 0 10px;"><?php esc_html_e( 'Advanced ??rules are appended to the plugin stylesheet.', 'insicore-chat-bubble' ); ?></p>
        <textarea name="pcb_settings[custom_css]" rows="6" class="pcb-css-editor" placeholder=".pcb-main-bubble { /* your rules */ }"><?php echo esc_textarea( $opt( 'custom_css' ) ); ?></textarea>
    </div>

</div>
