<?php
/**
 * "Visibility" tab ??display rules controlling where the bubble appears.
 *
 * Available vars from parent scope: $options
 */

if ( ! defined( 'ABSPATH' ) ) exit;

$opt = fn( $k, $d = '' ) => pcb_opt( $options, $k, $d );

if ( ! function_exists( 'pcb_build_visibility_catalog' ) ) {
    function pcb_build_visibility_catalog() {
        $rules_data = [];

        foreach ( get_pages( [ 'number' => 200 ] ) as $p ) {
            $rules_data[] = [ 'id' => $p->ID, 'title' => $p->post_title, 'type' => 'pages' ];
        }
        foreach ( get_posts( [ 'numberposts' => 200, 'post_status' => 'publish' ] ) as $p ) {
            $rules_data[] = [ 'id' => $p->ID, 'title' => $p->post_title, 'type' => 'posts' ];
        }

        $term_types = [
            [ 'taxonomy' => 'category', 'type' => 'categories' ],
            [ 'taxonomy' => 'post_tag', 'type' => 'tags' ],
        ];
        foreach ( $term_types as $tt ) {
            $terms = get_terms( [ 'taxonomy' => $tt['taxonomy'], 'number' => 200, 'hide_empty' => false ] );
            if ( is_wp_error( $terms ) ) continue;
            foreach ( $terms as $t ) {
                $rules_data[] = [ 'id' => $t->term_id, 'title' => $t->name, 'type' => $tt['type'] ];
            }
        }

        foreach ( get_taxonomies( [ 'public' => true, '_builtin' => false ], 'objects' ) as $tax ) {
            $terms = get_terms( [ 'taxonomy' => $tax->name, 'number' => 200, 'hide_empty' => false ] );
            if ( is_wp_error( $terms ) ) continue;
            foreach ( $terms as $term ) {
                $rules_data[] = [ 'id' => $term->term_id, 'title' => $tax->label . ': ' . $term->name, 'type' => 'taxonomies' ];
            }
        }

        return $rules_data;
    }
}

$saved_rules = json_decode( $options['visibility_rules'] ?? '[]', true ) ?: [];
?>
<div class="pcb-tab-panel" data-panel="visibility">

    <div class="pcb-sidebar-group">
        <h4 class="pcb-group-title"><?php esc_html_e( 'Display Rules', 'insicore-chat-bubble' ); ?></h4>
        <p class="pcb-option-hint" style="margin:0 0 14px;">
            <?php esc_html_e( 'Leave empty to show on every page. Add rules to limit where the bubble appears.', 'insicore-chat-bubble' ); ?>
        </p>

        <div id="pcb-rule-repeater" class="pcb-vis-rules"></div>

        <div class="pcb-add-rule-wrap" style="margin-top:10px;">
            <button type="button" class="pcb-add-rule-btn" id="pcb-add-rule-trigger">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="currentColor"><path d="M19 13h-6v6h-2v-6H5v-2h6V5h2v6h6v2z"/></svg>
                <?php esc_html_e( 'Add Display Rule', 'insicore-chat-bubble' ); ?>
            </button>
        </div>
    </div>

    <!-- Hidden: populated by JS before form submit -->
    <input type="hidden" name="pcb_settings[visibility_rules]" id="pcb-visibility-rules-input" value="<?php echo esc_attr( $opt( 'visibility_rules', '[]' ) ); ?>">

</div>

<!-- Rule card template (hidden) -->
<div id="pcb-rule-template" style="display:none">
    <div class="pcb-vis-rule-card">
        <input type="checkbox" name="pcb_visibility_enabled[]" value="1" checked style="display:none">
        <button type="button" class="pcb-remove-rule" title="<?php esc_attr_e( 'Remove', 'insicore-chat-bubble' ); ?>">
            <svg width="8" height="8" viewBox="0 0 8 8" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><line x1="1" y1="1" x2="7" y2="7"/><line x1="7" y1="1" x2="1" y2="7"/></svg>
        </button>
        <div class="pcb-rule-header-row">
            <span class="pcb-rule-on-label"><?php esc_html_e( 'Show on', 'insicore-chat-bubble' ); ?></span>
            <select name="pcb_visibility_type[]" class="pcb-type-select">
                <option value="pages"><?php      esc_html_e( 'Pages',             'insicore-chat-bubble' ); ?></option>
                <option value="posts"><?php      esc_html_e( 'Posts',             'insicore-chat-bubble' ); ?></option>
                <option value="categories"><?php esc_html_e( 'Categories',        'insicore-chat-bubble' ); ?></option>
                <option value="tags"><?php       esc_html_e( 'Tags',              'insicore-chat-bubble' ); ?></option>
                <option value="taxonomies"><?php esc_html_e( 'Custom Taxonomies', 'insicore-chat-bubble' ); ?></option>
                <option value="archives"><?php   esc_html_e( 'Archives',          'insicore-chat-bubble' ); ?></option>
                <option value="others"><?php     esc_html_e( 'Other Pages',       'insicore-chat-bubble' ); ?></option>
            </select>
        </div>
        <div class="pcb-rule-target-section">
            <div class="pcb-search-wrap">
                <svg class="pcb-search-svg" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="8.5" cy="8.5" r="5.5"/><path stroke-linecap="round" d="m13.5 13.5 3 3"/></svg>
                <input type="text" class="pcb-search-input" placeholder="<?php esc_attr_e( 'Search or pick from list...', 'insicore-chat-bubble' ); ?>">
                <div class="pcb-dropdown-results"></div>
            </div>
            <div class="pcb-selected-tags"></div>
        </div>
        <input type="hidden" name="pcb_visibility_targets[]" value="">
    </div>
</div>

<script>
window.pcbSavedRules   = <?php echo wp_json_encode( $saved_rules,                    JSON_HEX_TAG | JSON_HEX_AMP ); ?>;
window.pcbAllRulesData = <?php echo wp_json_encode( pcb_build_visibility_catalog(), JSON_HEX_TAG | JSON_HEX_AMP ); ?>;
</script>
