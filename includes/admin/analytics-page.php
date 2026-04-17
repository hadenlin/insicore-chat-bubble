<?php
/**
 * Admin: Click Analytics page.
 * Registered as the top-level Contact Bubble page (via menu.php).
 * Uses standard WordPress admin UI - no custom CSS needed.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/* ── Reset handler ─────────────────────────────────────────── */
add_action( 'admin_post_pcb_analytics_reset', 'pcb_handle_analytics_reset' );
function pcb_handle_analytics_reset() {
    if ( ! current_user_can( 'manage_options' ) ) wp_die( __( 'Unauthorized', 'insicore-chat-bubble' ), 403 );
    check_admin_referer( 'pcb_analytics_reset' );

    global $wpdb;
    $wpdb->query( 'TRUNCATE TABLE ' . pcb_get_events_table() );
    wp_safe_redirect( admin_url( 'admin.php?page=insicore-chat-bubble&reset=1' ) );
    exit;
}

/* ── AJAX: return all analytics data as JSON ───────────────── */
add_action( 'wp_ajax_pcb_analytics_data', 'pcb_ajax_analytics_data' );
function pcb_ajax_analytics_data() {
    if ( ! current_user_can( 'manage_options' ) ) wp_send_json_error( 'Unauthorized', 403 );
    check_ajax_referer( 'pcb_analytics_nonce' );

    $days = max( 1, min( 365, absint( $_POST['days'] ?? 30 ) ) );

    wp_send_json_success( [
        'days'       => $days,
        'totals'     => pcb_analytics_totals( $days ),
        'by_channel' => pcb_analytics_by_channel( $days ),
        'daily'      => pcb_analytics_daily_series( $days ),
        'top_pages'  => pcb_analytics_top_pages( $days, 10 ),
    ] );
}

/* ── Page render ───────────────────────────────────────────── */
function pcb_render_analytics_page() {
    $days       = max( 1, min( 365, absint( $_GET['days'] ?? 30 ) ) );
    $totals     = pcb_analytics_totals( $days );
    $by_channel = pcb_analytics_by_channel( $days );
    $daily      = pcb_analytics_daily_series( $days );
    $top_pages  = pcb_analytics_top_pages( $days, 10 );

    $reset_url = esc_url( admin_url( 'admin-post.php' ) );
    $ch_max    = ! empty( $by_channel ) ? max( array_column( $by_channel, 'clicks' ) ) : 1;

    // JSON for initial chart render.
    $init_labels = wp_json_encode( array_column( $daily, 'date' ) );
    $init_values = wp_json_encode( array_map( 'intval', array_column( $daily, 'clicks' ) ) );
    ?>
    <div class="wrap pcb-admin-wrap">

        <h1><?php esc_html_e( 'Contact Bubble Analytics', 'insicore-chat-bubble' ); ?></h1>

        <?php if ( ! empty( $_GET['reset'] ) ) : ?>
            <div class="notice notice-success is-dismissible"><p><?php esc_html_e( 'Analytics data has been reset.', 'insicore-chat-bubble' ); ?></p></div>
        <?php endif; ?>

        <!-- KPI Cards -->
        <div class="pcb-kpi-grid" id="pcb-kpi-grid" style="margin-top:20px;max-width:100%;">
            <div class="pcb-kpi-card">
                <div class="pcb-kpi-label"><?php esc_html_e( 'Total Clicks', 'insicore-chat-bubble' ); ?></div>
                <div class="pcb-kpi-value" id="pcb-kpi-clicks"><?php echo number_format( (int) $totals['total_clicks'] ); ?></div>
            </div>
            <div class="pcb-kpi-card">
                <div class="pcb-kpi-label"><?php esc_html_e( 'Bubble Opens', 'insicore-chat-bubble' ); ?></div>
                <div class="pcb-kpi-value" id="pcb-kpi-opens"><?php echo number_format( (int) $totals['total_opens'] ); ?></div>
            </div>
            <div class="pcb-kpi-card">
                <div class="pcb-kpi-label"><?php esc_html_e( 'Form Submissions', 'insicore-chat-bubble' ); ?></div>
                <div class="pcb-kpi-value" id="pcb-kpi-forms"><?php echo number_format( (int) $totals['total_forms'] ); ?></div>
            </div>
        </div>

        <!-- Chart Panel: range buttons live here -->
        <div class="pcb-panel">
            <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px;margin-bottom:16px;">
                <h2 style="margin:0;font-size:14px;font-weight:700;color:#0f172a;"><?php esc_html_e( 'Daily Clicks', 'insicore-chat-bubble' ); ?></h2>
                <div class="pcb-range-row" id="pcb-range-row">
                    <?php foreach ( [ 1, 7, 30, 90, 365 ] as $r ) :
                        $cls   = $r === $days ? 'button button-primary' : 'button';
                        $label = $r === 1
                            ? __( 'Today', 'insicore-chat-bubble' )
                            : sprintf( _n( 'Last %d day', 'Last %d days', $r, 'insicore-chat-bubble' ), $r );
                    ?>
                        <button type="button" class="<?php echo esc_attr( $cls ); ?> pcb-range-btn" data-days="<?php echo (int) $r; ?>">
                            <?php echo esc_html( $label ); ?>
                        </button>
                    <?php endforeach; ?>
                </div>
            </div>
            <!-- Fixed-height wrapper prevents collapse when no data -->
            <div id="pcb-chart-wrap" style="position:relative;height:220px;">
                <canvas id="pcb-daily-chart" style="display:block;width:100%;height:100%;"></canvas>
            </div>
        </div>

        <!-- Two columns -->
        <div class="pcb-two-col">

            <div class="pcb-panel">
                <h2 style="margin:0 0 16px;font-size:14px;font-weight:700;color:#0f172a;"><?php esc_html_e( 'Clicks by Channel', 'insicore-chat-bubble' ); ?></h2>
                <div id="pcb-channels-list">
                    <?php pcb_render_channel_rows( $by_channel ); ?>
                </div>
            </div>

            <div class="pcb-panel">
                <h2 style="margin:0 0 16px;font-size:14px;font-weight:700;color:#0f172a;"><?php esc_html_e( 'Top Pages by Clicks', 'insicore-chat-bubble' ); ?></h2>
                <div id="pcb-pages-list">
                    <?php pcb_render_page_rows( $top_pages ); ?>
                </div>
            </div>

        </div>

        <!-- Reset -->
        <div style="margin-top:8px;text-align:right;">
            <form method="post" action="<?php echo esc_url( $reset_url ); ?>" style="display:inline;" onsubmit="return confirm('<?php echo esc_js( __( 'Reset ALL analytics data? This cannot be undone.', 'insicore-chat-bubble' ) ); ?>');">
                <input type="hidden" name="action" value="pcb_analytics_reset">
                <?php wp_nonce_field( 'pcb_analytics_reset' ); ?>
                <button class="button button-link-delete"><?php esc_html_e( 'Reset analytics data', 'insicore-chat-bubble' ); ?></button>
            </form>
        </div>

    </div>

    <script>
    (function(){
        var ajaxUrl  = <?php echo wp_json_encode( admin_url( 'admin-ajax.php' ) ); ?>;
        var nonce    = <?php echo wp_json_encode( wp_create_nonce( 'pcb_analytics_nonce' ) ); ?>;
        var noData   = <?php echo wp_json_encode( __( 'No data for this period', 'insicore-chat-bubble' ) ); ?>;
        var noChData = <?php echo wp_json_encode( __( 'No data yet.', 'insicore-chat-bubble' ) ); ?>;
        var noPgData = <?php echo wp_json_encode( __( 'No page data yet.', 'insicore-chat-bubble' ) ); ?>;
        var loadingTxt = <?php echo wp_json_encode( __( 'Loading...', 'insicore-chat-bubble' ) ); ?>;
        var activeDays = <?php echo (int) $days; ?>;

        /* ── Chart ──────────────────────────────────────────── */
        var canvas = document.getElementById('pcb-daily-chart');
        var ctx    = canvas.getContext('2d');
        var pad    = { top:16, right:20, bottom:36, left:44 };

        function drawChart(labels, values) {
            var wrap = document.getElementById('pcb-chart-wrap');
            var W = wrap.offsetWidth || 860;
            var H = wrap.offsetHeight || 220;
            canvas.width  = W;
            canvas.height = H;
            var cw = W - pad.left - pad.right;
            var ch = H - pad.top  - pad.bottom;

            ctx.clearRect(0, 0, W, H);
            ctx.font = '11px -apple-system,BlinkMacSystemFont,sans-serif';

            var hasData = values.some(function(v){ return v > 0; });
            if (!hasData) {
                ctx.fillStyle = '#94a3b8';
                ctx.textAlign = 'center';
                ctx.font = '13px -apple-system,BlinkMacSystemFont,sans-serif';
                ctx.fillText(noData, W / 2, H / 2);
                ctx.textAlign = 'left';
                return;
            }

            var maxV = Math.max.apply(null, values) || 1;
            var step = Math.max(1, Math.floor(labels.length / 6));
            var lenDivisor = Math.max(1, labels.length - 1);

            function xPos(i) { return pad.left + (i / lenDivisor) * cw; }

            function drawBase() {
                ctx.font = '11px -apple-system,BlinkMacSystemFont,sans-serif';
                ctx.strokeStyle = '#e2e8f0'; ctx.lineWidth = 1;
                for (var g = 0; g <= 4; g++) {
                    var gy = pad.top + ch - (g / 4) * ch;
                    ctx.beginPath(); ctx.moveTo(pad.left, gy); ctx.lineTo(pad.left + cw, gy); ctx.stroke();
                    ctx.fillStyle = '#94a3b8'; ctx.fillText(Math.round((g / 4) * maxV), 4, gy + 4);
                }
                for (var i = 0; i < labels.length; i += step) {
                    var lx = xPos(i);
                    ctx.fillStyle = '#94a3b8'; ctx.fillText(labels[i].slice(5), lx - 14, H - 8);
                }
            }

            function drawLine() {
                // Fill.
                ctx.beginPath();
                for (var i = 0; i < values.length; i++) {
                    var x = xPos(i);
                    var y = pad.top  + ch - (values[i] / maxV) * ch;
                    i === 0 ? ctx.moveTo(x, y) : ctx.lineTo(x, y);
                }
                ctx.lineTo(xPos(values.length - 1), pad.top + ch);
                ctx.lineTo(pad.left,      pad.top + ch);
                ctx.closePath();
                ctx.fillStyle = 'rgba(59,130,246,0.1)'; ctx.fill();
                // Line.
                ctx.beginPath(); ctx.strokeStyle = '#3b82f6'; ctx.lineWidth = 2;
                for (var i = 0; i < values.length; i++) {
                    var x = xPos(i);
                    var y = pad.top  + ch - (values[i] / maxV) * ch;
                    i === 0 ? ctx.moveTo(x, y) : ctx.lineTo(x, y);
                }
                ctx.stroke();
                // Dots.
                ctx.fillStyle = '#3b82f6';
                for (var i = 0; i < values.length; i++) {
                    var x = xPos(i);
                    var y = pad.top  + ch - (values[i] / maxV) * ch;
                    ctx.beginPath(); ctx.arc(x, y, values.length > 60 ? 2 : 3, 0, 2*Math.PI); ctx.fill();
                }
            }

            drawBase(); drawLine();

            // Tooltip.
            canvas.onmousemove = function(e) {
                var rect = canvas.getBoundingClientRect();
                var mx  = (e.clientX - rect.left) * (W / rect.width);
                var idx = Math.round((mx - pad.left) / cw * lenDivisor);
                idx = Math.max(0, Math.min(values.length - 1, idx));

                canvas.width = W; canvas.height = H;
                drawBase(); drawLine();

                var tx = xPos(idx);
                var ty = pad.top  + ch - (values[idx] / maxV) * ch;

                // Vertical guide.
                ctx.beginPath(); ctx.strokeStyle = 'rgba(59,130,246,0.3)'; ctx.lineWidth = 1;
                ctx.setLineDash([4,3]); ctx.moveTo(tx, pad.top); ctx.lineTo(tx, pad.top + ch); ctx.stroke();
                ctx.setLineDash([]);

                // Tooltip box.
                var tip = labels[idx] + ': ' + values[idx];
                ctx.font = '11px -apple-system,BlinkMacSystemFont,sans-serif';
                var tw  = ctx.measureText(tip).width + 16;
                var tipX = Math.min(tx - tw/2, W - tw - 4); tipX = Math.max(4, tipX);
                var tipY = ty - 36; if (tipY < 4) tipY = ty + 12;
                ctx.fillStyle = '#1e293b';
                ctx.beginPath();
                ctx.roundRect ? ctx.roundRect(tipX, tipY, tw, 24, 4) : ctx.rect(tipX, tipY, tw, 24);
                ctx.fill();
                ctx.fillStyle = '#fff'; ctx.fillText(tip, tipX + 8, tipY + 16);

                // Active dot.
                ctx.beginPath(); ctx.fillStyle = '#fff';
                ctx.arc(tx, ty, 5, 0, 2*Math.PI); ctx.fill();
                ctx.beginPath(); ctx.fillStyle = '#3b82f6';
                ctx.arc(tx, ty, 4, 0, 2*Math.PI); ctx.fill();
            };
            canvas.onmouseleave = function() {
                canvas.width = W; canvas.height = H; drawBase(); drawLine();
            };
        }

        /* Initial draw */
        drawChart(<?php echo $init_labels; ?>, <?php echo $init_values; ?>);

        /* ── Range buttons → AJAX ──────────────────────────── */
        document.getElementById('pcb-range-row').addEventListener('click', function(e) {
            var btn = e.target.closest('.pcb-range-btn');
            if (!btn) return;
            var days = parseInt(btn.dataset.days, 10);
            if (days === activeDays) return;

            // Update button state.
            document.querySelectorAll('.pcb-range-btn').forEach(function(b) {
                b.classList.remove('button-primary');
            });
            btn.classList.add('button-primary');
            activeDays = days;

            // Loading state on chart.
            var wrap = document.getElementById('pcb-chart-wrap');
            var W = wrap.offsetWidth || 860;
            var H = wrap.offsetHeight || 220;
            canvas.width = W; canvas.height = H;
            ctx.clearRect(0, 0, W, H);
            ctx.fillStyle = '#94a3b8';
            ctx.font = '13px -apple-system,BlinkMacSystemFont,sans-serif';
            ctx.textAlign = 'center';
            ctx.fillText(loadingTxt, W/2, H/2);
            ctx.textAlign = 'left';

            var body = new URLSearchParams({ action: 'pcb_analytics_data', _ajax_nonce: nonce, days: days });
            fetch(ajaxUrl, { method: 'POST', credentials: 'same-origin', body: body })
                .then(function(r){ return r.json(); })
                .then(function(res) {
                    if (!res.success) return;
                    var d = res.data;

                    // KPI.
                    document.getElementById('pcb-kpi-clicks').textContent = d.totals.total_clicks.toLocaleString();
                    document.getElementById('pcb-kpi-opens').textContent  = d.totals.total_opens.toLocaleString();
                    document.getElementById('pcb-kpi-forms').textContent  = d.totals.total_forms.toLocaleString();

                    // Chart.
                    var labels = d.daily.map(function(r){ return r.date; });
                    var values = d.daily.map(function(r){ return parseInt(r.clicks, 10); });
                    drawChart(labels, values);

                    // Channel list.
                    var chMax = d.by_channel.reduce(function(m,c){ return Math.max(m, parseInt(c.clicks,10)); }, 1);
                    var chHtml = '';
                    if (!d.by_channel.length) {
                        chHtml = '<p style="color:#94a3b8;font-size:13px;margin:0;">' + noChData + '</p>';
                    } else {
                        d.by_channel.forEach(function(c) {
                            var pct = parseInt(c.clicks,10) / chMax * 100;
                            chHtml += '<div class="pcb-ch-row">'
                                + '<div class="pcb-ch-name">' + escHtml(c.channel_label || c.channel_type) + '<span>' + escHtml(c.channel_type) + '</span></div>'
                                + '<div class="pcb-ch-bar"><div class="pcb-ch-fill" style="width:' + pct.toFixed(1) + '%"></div></div>'
                                + '<div class="pcb-ch-count">' + parseInt(c.clicks,10).toLocaleString() + '</div>'
                                + '</div>';
                        });
                    }
                    document.getElementById('pcb-channels-list').innerHTML = chHtml;

                    // Pages list.
                    var pgHtml = '';
                    if (!d.top_pages.length) {
                        pgHtml = '<p style="color:#94a3b8;font-size:13px;margin:0;">' + noPgData + '</p>';
                    } else {
                        d.top_pages.forEach(function(p) {
                            pgHtml += '<div class="pcb-ch-row">'
                                + '<div class="pcb-ch-name" style="min-width:0;flex:1;overflow:hidden;">'
                                + '<a href="' + escAttr(p.page_url) + '" target="_blank" style="color:#3b82f6;text-decoration:none;font-size:13px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;display:block;" title="' + escAttr(p.page_url) + '">' + escHtml(p.page_url) + '</a>'
                                + '</div>'
                                + '<div class="pcb-ch-count" style="flex-shrink:0;margin-left:12px;">' + parseInt(p.clicks,10).toLocaleString() + '</div>'
                                + '</div>';
                        });
                    }
                    document.getElementById('pcb-pages-list').innerHTML = pgHtml;
                });
        });

        function escHtml(s) {
            return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
        }
        function escAttr(s) { return escHtml(s); }
    })();
    </script>
    <?php
}

/* ── HTML helpers (called from PHP initial render) ─────────── */
function pcb_render_channel_rows( $by_channel ) {
    if ( empty( $by_channel ) ) {
        echo '<p style="color:#94a3b8;font-size:13px;margin:0;">' . esc_html__( 'No data yet.', 'insicore-chat-bubble' ) . '</p>';
        return;
    }
    $ch_max = max( array_column( $by_channel, 'clicks' ) );
    foreach ( $by_channel as $c ) :
        $pct = (int) $c['clicks'] / max( 1, $ch_max ) * 100;
        ?>
        <div class="pcb-ch-row">
            <div class="pcb-ch-name">
                <?php echo esc_html( $c['channel_label'] ?: $c['channel_type'] ); ?>
                <span><?php echo esc_html( $c['channel_type'] ); ?></span>
            </div>
            <div class="pcb-ch-bar"><div class="pcb-ch-fill" style="width:<?php echo esc_attr( $pct ); ?>%"></div></div>
            <div class="pcb-ch-count"><?php echo number_format( (int) $c['clicks'] ); ?></div>
        </div>
        <?php
    endforeach;
}

function pcb_render_page_rows( $top_pages ) {
    if ( empty( $top_pages ) ) {
        echo '<p style="color:#94a3b8;font-size:13px;margin:0;">' . esc_html__( 'No page data yet.', 'insicore-chat-bubble' ) . '</p>';
        return;
    }
    foreach ( $top_pages as $p ) :
        ?>
        <div class="pcb-ch-row">
            <div class="pcb-ch-name" style="min-width:0;flex:1;overflow:hidden;">
                <a href="<?php echo esc_url( $p['page_url'] ); ?>" target="_blank" style="color:#3b82f6;text-decoration:none;font-size:13px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;display:block;" title="<?php echo esc_attr( $p['page_url'] ); ?>">
                    <?php echo esc_html( $p['page_url'] ); ?>
                </a>
            </div>
            <div class="pcb-ch-count" style="flex-shrink:0;margin-left:12px;"><?php echo number_format( (int) $p['clicks'] ); ?></div>
        </div>
        <?php
    endforeach;
}
