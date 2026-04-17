<?php
/**
 * Preset visual themes. Selecting a theme injects a short CSS block on top
 * of the inline-generated CSS. The user can still tweak individual settings;
 * themes only override visuals that are specifically set in their recipes.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

function pcb_get_theme_presets() {
    static $themes = null;
    if ( $themes !== null ) return $themes;

    $themes = [
        ''         => [
            'label' => __( 'None', 'insicore-chat-bubble' ),
            'css'   => '',
        ],
        'modern'   => [
            'label' => __( 'Modern Shadow', 'insicore-chat-bubble' ),
            'css'   => '
                .pcb-main-bubble { box-shadow: 0 20px 40px -10px rgba(0,0,0,0.35), 0 8px 16px -8px rgba(0,0,0,0.25); }
                .pcb-menu-item   { box-shadow: 0 12px 28px -8px rgba(0,0,0,0.3); }
                .pcb-tooltip     { box-shadow: 0 8px 24px -6px rgba(0,0,0,0.25); border: none; }
            ',
        ],
        'glass'    => [
            'label' => __( 'Glass Morphism', 'insicore-chat-bubble' ),
            'css'   => '
                .pcb-main-bubble { backdrop-filter: blur(14px); background: rgba(255,255,255,0.18) !important; border: 1px solid rgba(255,255,255,0.4); color: #fff !important; }
                .pcb-menu-item   { backdrop-filter: blur(14px); border: 1px solid rgba(255,255,255,0.35); }
                .pcb-tooltip     { backdrop-filter: blur(14px); background: rgba(255,255,255,0.75) !important; border: 1px solid rgba(255,255,255,0.6); }
            ',
        ],
        'dark'     => [
            'label' => __( 'Dark Elegant', 'insicore-chat-bubble' ),
            'css'   => '
                .pcb-main-bubble { background: #111827 !important; box-shadow: 0 10px 30px rgba(0,0,0,0.6); }
                .pcb-tooltip     { background: #1f2937 !important; color: #f9fafb !important; border: none; }
            ',
        ],
        'soft'     => [
            'label' => __( 'Soft Pastel', 'insicore-chat-bubble' ),
            'css'   => '
                .pcb-main-bubble { box-shadow: 0 16px 30px -10px rgba(99,102,241,0.4); }
                .pcb-menu-item   { box-shadow: 0 10px 20px -8px rgba(99,102,241,0.35); }
                .pcb-tooltip     { background: #fef3c7 !important; color: #78350f !important; border: none; }
            ',
        ],
        'neon'     => [
            'label' => __( 'Neon Glow', 'insicore-chat-bubble' ),
            'css'   => '
                .pcb-main-bubble { box-shadow: 0 0 18px var(--pcb-primary-color), 0 0 32px var(--pcb-primary-color); }
                .pcb-menu-item   { box-shadow: 0 0 14px var(--pcb-ch-color, var(--pcb-primary-color)); }
            ',
        ],
        'squared'  => [
            'label' => __( 'Squared Corners', 'insicore-chat-bubble' ),
            'css'   => '
                .pcb-main-bubble, .pcb-menu-item, .pcb-menu-item.pcb-has-label { border-radius: 10px !important; }
                .pcb-tooltip     { border-radius: 6px !important; }
            ',
        ],
        'gradient' => [
            'label' => __( 'Vibrant Gradient', 'insicore-chat-bubble' ),
            'css'   => '
                .pcb-main-bubble { background: linear-gradient(135deg, var(--pcb-primary-color), var(--pcb-hover-color, #9333ea)) !important; }
            ',
        ],
        'flat'     => [
            'label' => __( 'Flat Clean', 'insicore-chat-bubble' ),
            'css'   => '
                .pcb-main-bubble, .pcb-menu-item { box-shadow: none !important; }
                .pcb-tooltip     { box-shadow: none !important; border: 1px solid rgba(0,0,0,0.1); }
            ',
        ],
    ];
    return $themes;
}
