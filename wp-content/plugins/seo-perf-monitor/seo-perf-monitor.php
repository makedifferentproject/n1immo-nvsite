<?php
/*
Plugin Name: SEO TTFB Monitor (Multi-URL)
Description: Surveillance du TTFB local avec distinction des courbes par URL.
Version: 4.0
Author: Gemini
*/

if (!defined('ABSPATH')) exit;

/**
 * 1. BASE DE DONNÉES
 */
register_activation_hook(__FILE__, 'spm_ttfb_install');
function spm_ttfb_install() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'spm_ttfb_stats';
    $charset_collate = $wpdb->get_charset_collate();
    $sql = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        time datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
        url text NOT NULL,
        ttfb float NOT NULL,
        PRIMARY KEY  (id)
    ) $charset_collate;";
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}

/**
 * 2. MENU ET RÉGLAGES
 */
add_action('admin_menu', function() {
    add_menu_page('SEO TTFB', 'SEO TTFB', 'manage_options', 'spm-ttfb', 'spm_ttfb_dashboard', 'dashicons-performance');
    add_submenu_page('spm-ttfb', 'Réglages', 'Réglages', 'manage_options', 'spm-ttfb-settings', 'spm_ttfb_settings_page');
});

add_action('admin_init', function() {
    register_setting('spm_ttfb_group', 'spm_urls');
});

function spm_ttfb_settings_page() {
    $cron_path = plugin_dir_path(__FILE__) . 'cron-task.php';
    ?>
    <div class="wrap">
        <h1>Réglages TTFB Monitor</h1>
        <div class="notice notice-info">
            <p><strong>Commande Cron Serveur :</strong><br>
            <code>0 2 * * * php <?php echo esc_html($cron_path); ?> > /dev/null 2>&1</code></p>
        </div>
        <form method="post" action="options.php">
            <?php settings_fields('spm_ttfb_group'); ?>
            <table class="form-table">
                <tr>
                    <th>URLs à surveiller (une par ligne)</th>
                    <td><textarea name="spm_urls" rows="5" class="large-text"><?php echo esc_textarea(get_option('spm_urls')); ?></textarea></td>
                </tr>
            </table>
            <?php submit_button(); ?>
        </form>
    </div>
    <?php
}

/**
 * 3. LOGIQUE TTFB (CURL)
 */
function spm_execute_ttfb_check() {
    $urls_raw = get_option('spm_urls');
    if (empty($urls_raw)) return "Aucune URL.";
    $urls = explode("\n", str_replace("\r", "", $urls_raw));
    global $wpdb;
    $table_name = $wpdb->prefix . 'spm_ttfb_stats';
    $count = 0;

    foreach ($urls as $url) {
        $url = trim($url);
        if (empty($url)) continue;
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_exec($ch);
        $ttfb = curl_getinfo($ch, CURLINFO_STARTTRANSFER_TIME);
        curl_close($ch);

        if ($ttfb > 0) {
            $wpdb->insert($table_name, ['url' => $url, 'ttfb' => round($ttfb * 1000, 2)]);
            $count++;
        }
    }
    return "$count URL(s) testée(s).";
}

add_action('wp_ajax_spm_test_ttfb', function() {
    check_ajax_referer('spm_nonce');
    wp_send_json_success(spm_execute_ttfb_check());
});

/**
 * 4. DASHBOARD AVEC GRAPHIQUE MULTI-COURBES
 */
function spm_ttfb_dashboard() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'spm_ttfb_stats';
    
    // Récupérer les données des 7 derniers jours
    $raw_data = $wpdb->get_results("SELECT * FROM $table_name WHERE time > DATE_SUB(NOW(), INTERVAL 7 DAY) ORDER BY time ASC");

    $labels = [];
    $datasets = [];
    $colors = ['#2271b1', '#d63638', '#2ba245', '#ed8116', '#9b51e0', '#11d1e8'];
    $url_color_map = [];
    $color_index = 0;

    foreach ($raw_data as $row) {
        $date = date('d/m H:i', strtotime($row->time));
        if (!in_array($date, $labels)) $labels[] = $date;

        if (!isset($datasets[$row->url])) {
            $datasets[$row->url] = [
                'label' => $row->url,
                'data' => [],
                'borderColor' => $colors[$color_index % count($colors)],
                'fill' => false,
                'tension' => 0.1
            ];
            $color_index++;
        }
        $datasets[$row->url]['data'][] = $row->ttfb;
    }

    ?>
    <div class="wrap">
        <h1>Analyse comparative du TTFB</h1>
        <button id="run-test" class="button button-primary">⚡ Lancer un test maintenant</button>
        <span id="loader" class="spinner" style="float:none"></span>
        
        <div style="background:#fff; padding:20px; margin-top:20px; border:1px solid #ccd0d4;">
            <canvas id="ttfbChart" height="120"></canvas>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
    jQuery(document).ready(function($) {
        new Chart(document.getElementById('ttfbChart'), {
            type: 'line',
            data: {
                labels: <?php echo json_encode($labels); ?>,
                datasets: <?php echo json_encode(array_values($datasets)); ?>
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { 
                        display: true, 
                        position: 'bottom',
                        labels: { usePointStyle: true, padding: 20 }
                    },
                    tooltip: { mode: 'index', intersect: false }
                },
                scales: {
                    y: { beginAtZero: true, title: { display: true, text: 'Temps (ms)' } }
                }
            }
        });

        $('#run-test').click(function() {
            $(this).prop('disabled', true);
            $('#loader').addClass('is-active');
            $.post(ajaxurl, { action: 'spm_test_ttfb', _ajax_nonce: '<?php echo wp_create_nonce("spm_nonce"); ?>' }, function() {
                location.reload();
            });
        });
    });
    </script>
    <?php
}