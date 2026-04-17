<?php
/**
 * Normalize a channel URL based on its type.
 *
 * Users may paste either a full URL ("https://wa.me/??) or just an identifier
 * ("+1 555 1234"). We massage bare identifiers into proper deep-link URLs.
 *
 * Supports preset message / page-context templating for supported channels
 * (currently WhatsApp + SMS) via the $preset_message argument. Recognised
 * tokens: {page_title}, {page_url}, {site_name}.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

function pcb_normalize_channel_url( $type, $url, $preset_message = '' ) {
    $url = trim( (string) $url );
    if ( $url === '' || $url === '#' ) return '#';

    $digits_only = str_replace( [ '+', ' ', '-', '(', ')' ], '', $url );
    $encoded_msg = pcb_build_preset_message( $preset_message );

    // If the link is already a full protocol URL, keep it ??but still allow
    // WhatsApp deep-links to pick up the ?text= query when a template is set.
    $is_full_url = (
           str_starts_with( $url, 'http' )
        || str_starts_with( $url, 'tel:' )
        || str_starts_with( $url, 'sms:' )
        || str_starts_with( $url, 'mailto:' )
        || str_starts_with( $url, 'line://' )
        || str_starts_with( $url, 'tg://' )
        || str_starts_with( $url, 'weixin://' )
    );

    if ( $is_full_url ) {
        if ( $encoded_msg && ( $type === 'whatsapp' || str_contains( $url, 'wa.me' ) ) ) {
            $sep = str_contains( $url, '?' ) ? '&' : '?';
            return $url . $sep . 'text=' . $encoded_msg;
        }
        return $url;
    }

    switch ( $type ) {
        case 'phone':
            return is_numeric( $digits_only ) ? 'tel:' . str_replace( [ ' ', '-', '(', ')' ], '', $url ) : $url;

        case 'sms':
            if ( ! is_numeric( $digits_only ) ) return $url;
            $num = str_replace( [ ' ', '-', '(', ')' ], '', $url );
            return $encoded_msg ? "sms:{$num}?body={$encoded_msg}" : "sms:{$num}";

        case 'whatsapp':
            if ( ! is_numeric( $digits_only ) ) return $url;
            $base = 'https://wa.me/' . $digits_only;
            return $encoded_msg ? $base . '?text=' . $encoded_msg : $base;

        case 'line':
            return 'https://line.me/ti/p/~' . ltrim( $url, '~@' );

        case 'messenger':
            return 'https://m.me/' . ltrim( $url, '@' );

        case 'telegram':
            return 'https://t.me/' . ltrim( $url, '@' );

        case 'instagram':
            return 'https://ig.me/m/' . ltrim( $url, '@' );

        case 'wechat':
            return 'weixin://dl/chat?' . $url;

        case 'email':
            if ( ! str_contains( $url, '@' ) ) return $url;
            return $encoded_msg ? 'mailto:' . $url . '?body=' . $encoded_msg : 'mailto:' . $url;

        case 'discord':
            if ( str_contains( $url, 'discord' ) ) return $url;
            return 'https://discord.com/users/' . ltrim( $url, '@' );

        case 'viber':
            return is_numeric( $digits_only ) ? 'viber://chat?number=' . urlencode( '+' . $digits_only ) : $url;

        case 'zalo':
            return is_numeric( $digits_only ) ? 'https://zalo.me/' . $digits_only : 'https://zalo.me/' . $url;

        case 'tiktok':
            return 'https://www.tiktok.com/@' . ltrim( $url, '@' );

        case 'linkedin':
            return 'https://linkedin.com/in/' . ltrim( $url, '@/' );

        case 'twitter':
            return 'https://twitter.com/' . ltrim( $url, '@' );

        case 'skype':
            return 'skype:' . $url . '?chat';

        case 'snapchat':
            return 'https://snapchat.com/add/' . ltrim( $url, '@' );
    }

    return $url;
}

/**
 * Render a preset-message template by substituting known tokens, then
 * URL-encode it for use in query strings. Returns '' when no template.
 */
function pcb_build_preset_message( $template ) {
    $template = trim( (string) $template );
    if ( $template === '' ) return '';

    $title = '';
    $url   = '';
    if ( function_exists( 'is_singular' ) && is_singular() ) {
        $title = get_the_title();
        $url   = get_permalink();
    } else {
        $url = home_url( wp_unslash( $_SERVER['REQUEST_URI'] ?? '/' ) );
    }

    $replacements = [
        '{page_title}' => $title,
        '{page_url}'   => $url,
        '{site_name}'  => get_bloginfo( 'name' ),
    ];
    $rendered = strtr( $template, $replacements );

    return rawurlencode( $rendered );
}

/**
 * Allowed HTML tags for SVG channel icons (used by wp_kses).
 */
function pcb_allowed_icon_tags() {
    static $tags = null;
    if ( $tags !== null ) return $tags;

    $tags = [
        'svg'      => [ 'viewbox' => true, 'fill' => true, 'xmlns' => true, 'width' => true, 'height' => true, 'class' => true ],
        'path'     => [ 'd' => true, 'fill' => true, 'stroke' => true, 'stroke-width' => true, 'stroke-linecap' => true, 'stroke-linejoin' => true ],
        'circle'   => [ 'cx' => true, 'cy' => true, 'r' => true, 'fill' => true, 'stroke' => true, 'stroke-width' => true ],
        'rect'     => [ 'x' => true, 'y' => true, 'width' => true, 'height' => true, 'rx' => true, 'ry' => true, 'fill' => true ],
        'line'     => [ 'x1' => true, 'y1' => true, 'x2' => true, 'y2' => true, 'stroke' => true, 'stroke-width' => true, 'stroke-linecap' => true ],
        'polyline' => [ 'points' => true, 'fill' => true, 'stroke' => true, 'stroke-width' => true, 'stroke-linecap' => true, 'stroke-linejoin' => true ],
        'g'        => [ 'fill' => true, 'stroke' => true, 'transform' => true ],
        'img'      => [ 'src' => true, 'alt' => true, 'class' => true ],
    ];
    return $tags;
}
