<?php
/**
 * Decide whether the bubble should render on the current request.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * @param array $options  Settings row from the DB.
 * @return bool           True if the bubble should render on this request.
 */
function pcb_should_show_bubble( array $options ) {
    $rules = json_decode( $options['visibility_rules'] ?? '[]', true );
    if ( ! is_array( $rules ) || empty( $rules ) ) {
        return true; // No rules defined ??show everywhere.
    }

    foreach ( $rules as $rule ) {
        if ( empty( $rule['enabled'] ) ) continue;
        if ( pcb_rule_matches( $rule ) ) return true;
    }
    return false;
}

/**
 * Does a single rule match the current query?
 */
function pcb_rule_matches( array $rule ) {
    $type       = $rule['type']    ?? '';
    $targets    = $rule['targets'] ?? [];
    $target_ids = array_map( 'strval', array_column( $targets, 'id' ) );
    $has_all    = in_array( 'all', $target_ids, true );

    switch ( $type ) {
        case 'pages':
            return is_page() && ( $has_all || in_array( (string) get_the_ID(), $target_ids, true ) );

        case 'posts':
            return is_single() && ! is_page()
                && ( $has_all || in_array( (string) get_the_ID(), $target_ids, true ) );

        case 'categories':
            if ( ! is_category() ) return false;
            return pcb_queried_term_matches( $target_ids, $has_all );

        case 'tags':
            if ( ! is_tag() ) return false;
            return pcb_queried_term_matches( $target_ids, $has_all );

        case 'taxonomies':
            if ( ! is_tax() ) return false;
            return pcb_queried_term_matches( $target_ids, $has_all );

        case 'archives':
            if ( ! ( is_archive() || is_category() || is_tag() || is_tax() ) ) return false;
            return pcb_queried_term_matches( $target_ids, $has_all );

        case 'others':
            if ( $has_all ) {
                return is_front_page() || is_404() || is_search();
            }
            if ( ( is_front_page() || is_home() ) && in_array( 'home',   $target_ids, true ) ) return true;
            if ( is_404()                         && in_array( '404',    $target_ids, true ) ) return true;
            if ( is_search()                      && in_array( 'search', $target_ids, true ) ) return true;
            return false;
    }

    return false;
}

/**
 * Shared helper for category/tag/tax/archive rules: the current queried term
 * must either match one of the target IDs, or targets must include "all".
 */
function pcb_queried_term_matches( array $target_ids, $has_all ) {
    if ( $has_all ) return true;
    $obj = get_queried_object();
    return $obj && isset( $obj->term_id )
        && in_array( (string) $obj->term_id, $target_ids, true );
}
