<?php
$wp_load = dirname(__FILE__, 4) . '/wp-load.php';
if (file_exists($wp_load)) {
    require_once($wp_load);
    if (function_exists('spm_execute_ttfb_check')) {
        echo "[" . date('Y-m-d H:i:s') . "] Scan en cours...\n";
        echo spm_execute_ttfb_check() . "\n";
    }
} else {
    die("Erreur de chemin wp-load.php\n");
}