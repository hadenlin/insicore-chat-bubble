<?php
/**
 * Admin script / style enqueuing + JS i18n strings (wp_localize_script).
 */

if ( ! defined( 'ABSPATH' ) ) exit;

add_action( 'admin_enqueue_scripts', 'pcb_admin_assets' );

function pcb_admin_assets( $hook ) {
    // Builder page: slug pcb-builder (submenu of omnichat-bubble).
    $is_builder = is_string( $hook ) && str_ends_with( $hook, 'pcb-builder' );

    if ( ! $is_builder ) return;

    wp_enqueue_media();
    wp_enqueue_style( 'pcb-admin-style',         PCB_PLUGIN_URL . 'assets/css/style.css',         [], '3.0.0' );
    wp_enqueue_style( 'pcb-admin-builder-style', PCB_PLUGIN_URL . 'assets/css/admin-builder.css', [], '3.0.0' );

    if ( $is_builder ) {
        wp_enqueue_script( 'pcb-admin-script', PCB_PLUGIN_URL . 'assets/js/admin-builder.js', [ 'jquery' ], '3.0.0', true );

        wp_localize_script( 'pcb-admin-script', 'pcbL10n', [
            // Save button
            'saving'         => __( 'Saving??,                    'insicore-chat-bubble' ),
            'saved'          => __( '??Settings saved',          'insicore-chat-bubble' ),

            // Channel card
            'labelPlaceholder'     => __( 'Label',                    'insicore-chat-bubble' ),
            'customIconLabel'      => __( 'Custom Icon (URL or SVG)', 'insicore-chat-bubble' ),
            'customIconPlaceholder'=> __( 'https://??or <svg??',     'insicore-chat-bubble' ),
            'customChannelLabel'   => __( 'Custom',                   'insicore-chat-bubble' ),
            'urlLabel'             => __( 'URL / Phone / ID',         'insicore-chat-bubble' ),
            'colorLabel'           => __( 'Color',                    'insicore-chat-bubble' ),
            'hoverLabel'           => __( 'Hover',                    'insicore-chat-bubble' ),
            'presetMsgLabel'       => __( 'Pre-filled Message (optional)', 'insicore-chat-bubble' ),
            'presetMsgPlaceholder' => __( 'Hi! I have a question about {page_title}', 'insicore-chat-bubble' ),
            'presetMsgHint'        => __( 'Tokens: {page_title}, {page_url}, {site_name}', 'insicore-chat-bubble' ),
            'agentsToggle'         => __( 'Round-robin multiple contacts', 'insicore-chat-bubble' ),
            'agentLabel'           => __( 'Name',                     'insicore-chat-bubble' ),
            'agentValue'           => __( 'Phone / URL / ID',          'insicore-chat-bubble' ),
            'addAgent'             => __( '+ Add another contact',     'insicore-chat-bubble' ),
            'removeAgent'          => __( 'Remove',                    'insicore-chat-bubble' ),
            'formBuilderLabel'     => __( 'Contact Form Fields',       'insicore-chat-bubble' ),
            'formTitleLabel'       => __( 'Form Title',                'insicore-chat-bubble' ),
            'formSubmitLabel'      => __( 'Submit Button Text',        'insicore-chat-bubble' ),
            'formFieldsNote'       => __( 'Name, Email are required. Phone & Message are optional add-ons.', 'insicore-chat-bubble' ),

            // Visibility rule picker
            'noResults'  => __( 'No results found', 'insicore-chat-bubble' ),
            'allOf'      => __( 'All',              'insicore-chat-bubble' ),
            'otherPages' => [
                'home'   => __( 'Homepage',       'insicore-chat-bubble' ),
                '404'    => __( '404 Page',       'insicore-chat-bubble' ),
                'search' => __( 'Search Results', 'insicore-chat-bubble' ),
            ],
            'typeLabels'     => [
                'pages'      => __( 'Pages',             'insicore-chat-bubble' ),
                'posts'      => __( 'Posts',             'insicore-chat-bubble' ),
                'categories' => __( 'Categories',        'insicore-chat-bubble' ),
                'tags'       => __( 'Tags',              'insicore-chat-bubble' ),
                'taxonomies' => __( 'Custom Taxonomies', 'insicore-chat-bubble' ),
                'archives'   => __( 'Archives',          'insicore-chat-bubble' ),
                'others'     => __( 'Other Pages',       'insicore-chat-bubble' ),
            ],

            // URL placeholder hints per channel type
            'urlPlaceholders' => [
                'whatsapp'    => __( 'Phone number, e.g. 886912345678',      'insicore-chat-bubble' ),
                'line'        => __( 'LINE ID, e.g. mylineid',               'insicore-chat-bubble' ),
                'messenger'   => __( 'Facebook page name, e.g. mypagename',  'insicore-chat-bubble' ),
                'telegram'    => __( 'Telegram username, e.g. @username',    'insicore-chat-bubble' ),
                'instagram'   => __( 'Instagram handle, e.g. @username',     'insicore-chat-bubble' ),
                'wechat'      => __( 'WeChat ID',                            'insicore-chat-bubble' ),
                'phone'       => __( 'Phone number, e.g. +886912345678',     'insicore-chat-bubble' ),
                'sms'         => __( 'Phone number, e.g. +886912345678',     'insicore-chat-bubble' ),
                'email'       => __( 'Email address, e.g. hello@example.com','insicore-chat-bubble' ),
                'discord'     => __( 'Discord user ID or invite URL',         'insicore-chat-bubble' ),
                'viber'       => __( 'Phone number, e.g. +886912345678',     'insicore-chat-bubble' ),
                'zalo'        => __( 'Zalo phone number or username',         'insicore-chat-bubble' ),
                'tiktok'      => __( 'TikTok username, e.g. @username',      'insicore-chat-bubble' ),
                'linkedin'    => __( 'LinkedIn handle, e.g. johndoe',         'insicore-chat-bubble' ),
                'twitter'     => __( 'X/Twitter username, e.g. @username',   'insicore-chat-bubble' ),
                'skype'       => __( 'Skype username',                        'insicore-chat-bubble' ),
                'snapchat'    => __( 'Snapchat username',                     'insicore-chat-bubble' ),
                'custom_link' => __( 'https://??(full URL)',                  'insicore-chat-bubble' ),
                'custom'      => __( 'https://??(full URL)',                  'insicore-chat-bubble' ),
                'form'        => __( 'Not used ??opens in-page form',          'insicore-chat-bubble' ),
            ],

            // Preset-message support flag per channel type (enables that detail row)
            'presetMsgTypes' => [ 'whatsapp', 'sms', 'email' ],
            // Multi-agent support flag per channel type
            'agentsTypes'    => [ 'whatsapp', 'sms', 'phone', 'email', 'line', 'telegram', 'messenger' ],

            // Admin URL for AJAX
            'ajaxUrl'   => admin_url( 'admin-ajax.php' ),
            'nonce'     => wp_create_nonce( 'pcb_admin' ),
        ] );
    }
}
