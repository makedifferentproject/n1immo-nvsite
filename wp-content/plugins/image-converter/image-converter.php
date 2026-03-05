<?php
/**
 * Plugin Name: Image Converter - WebP & AVIF
 * Plugin URI: https://associationabir.org
 * Description: Convertit automatiquement les images JPG/PNG en WebP et AVIF avec gestion de progression et statistiques.
 * Version: 1.0.0
 * Author: Association ABIR
 * Author URI: https://associationabir.org
 * License: GPL v2 or later
 * Text Domain: image-converter
 */

// Sécurité - Empêcher l'accès direct
if (!defined('ABSPATH')) {
    exit;
}

// Définir les constantes
define('IMAGE_CONVERTER_VERSION', '1.0.0');
define('IMAGE_CONVERTER_PATH', plugin_dir_path(__FILE__));
define('IMAGE_CONVERTER_URL', plugin_dir_url(__FILE__));

/**
 * Classe principale du plugin
 */
class Image_Converter_Plugin {
    
    private static $instance = null;
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        // Hooks d'activation/désactivation
        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));
        
        // Actions et filtres
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
        
        // AJAX actions
        add_action('wp_ajax_ic_convert_webp', array($this, 'ajax_convert_webp'));
        add_action('wp_ajax_ic_convert_avif', array($this, 'ajax_convert_avif'));
        add_action('wp_ajax_ic_get_stats', array($this, 'ajax_get_stats'));
        add_action('wp_ajax_ic_reset_progress', array($this, 'ajax_reset_progress'));
        
        // Cron hooks
        add_action('ic_webp_conversion_cron', array($this, 'cron_convert_webp'));
        add_action('ic_avif_conversion_cron', array($this, 'cron_convert_avif'));
        
        // Hook pour les tâches cron externes (via URL)
        add_action('init', array($this, 'handle_external_cron'));
    }
    
    /**
     * Activation du plugin
     */
    public function activate() {
        // Créer les tables de progression si nécessaire
        $this->create_tables();
        
        // Planifier les tâches cron (désactivées par défaut)
        // L'utilisateur peut les activer depuis les réglages
    }
    
    /**
     * Désactivation du plugin
     */
    public function deactivate() {
        // Supprimer les tâches cron
        wp_clear_scheduled_hook('ic_webp_conversion_cron');
        wp_clear_scheduled_hook('ic_avif_conversion_cron');
    }
    
    /**
     * Créer les tables de suivi
     */
    private function create_tables() {
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();
        
        $table_name = $wpdb->prefix . 'image_converter_progress';
        
        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            file_path varchar(500) NOT NULL,
            format varchar(10) NOT NULL,
            status varchar(20) NOT NULL,
            error_message text,
            attempts int(11) DEFAULT 0,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            UNIQUE KEY file_format (file_path, format),
            KEY status (status),
            KEY format (format)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
    
    /**
     * Ajouter le menu dans l'administration
     */
    public function add_admin_menu() {
        add_menu_page(
            'Image Converter',
            'Image Converter',
            'manage_options',
            'image-converter',
            array($this, 'render_admin_page'),
            'dashicons-images-alt2',
            65
        );
        
        add_submenu_page(
            'image-converter',
            'Statistiques',
            'Statistiques',
            'manage_options',
            'image-converter',
            array($this, 'render_admin_page')
        );
        
        add_submenu_page(
            'image-converter',
            'Compatibilité',
            'Compatibilité',
            'manage_options',
            'image-converter-compatibility',
            array($this, 'render_compatibility_page')
        );
        
        add_submenu_page(
            'image-converter',
            'Configuration',
            'Configuration',
            'manage_options',
            'image-converter-configuration',
            array($this, 'render_configuration_page')
        );
        
        add_submenu_page(
            'image-converter',
            'Réglages',
            'Réglages',
            'manage_options',
            'image-converter-settings',
            array($this, 'render_settings_page')
        );
    }
    
    /**
     * Charger les scripts admin
     */
    public function enqueue_admin_scripts($hook) {
        if (strpos($hook, 'image-converter') === false) {
            return;
        }
        
        wp_enqueue_style(
            'image-converter-admin',
            IMAGE_CONVERTER_URL . 'assets/admin.css',
            array(),
            IMAGE_CONVERTER_VERSION
        );
        
        wp_enqueue_script(
            'image-converter-admin',
            IMAGE_CONVERTER_URL . 'assets/admin.js',
            array('jquery'),
            IMAGE_CONVERTER_VERSION,
            true
        );
        
        wp_localize_script('image-converter-admin', 'imageConverterData', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('image_converter_nonce')
        ));
    }
    
    /**
     * Afficher la page principale
     */
    public function render_admin_page() {
        if (!current_user_can('manage_options')) {
            return;
        }
        
        $stats = $this->get_statistics();
        
        include IMAGE_CONVERTER_PATH . 'templates/admin-page.php';
    }
    
    /**
     * Afficher la page de réglages
     */
    public function render_settings_page() {
        if (!current_user_can('manage_options')) {
            return;
        }
        
        // Sauvegarder les réglages
        if (isset($_POST['ic_save_settings']) && check_admin_referer('ic_settings_nonce')) {
            update_option('ic_webp_quality', intval($_POST['ic_webp_quality']));
            update_option('ic_avif_quality', intval($_POST['ic_avif_quality']));
            update_option('ic_webp_cron_enabled', isset($_POST['ic_webp_cron_enabled']));
            update_option('ic_avif_cron_enabled', isset($_POST['ic_avif_cron_enabled']));
            update_option('ic_cron_schedule', sanitize_text_field($_POST['ic_cron_schedule']));
            
            // Mettre à jour les tâches cron
            $this->update_cron_schedules();
            
            echo '<div class="notice notice-success"><p>Réglages sauvegardés avec succès !</p></div>';
        }
        
        include IMAGE_CONVERTER_PATH . 'templates/settings-page.php';
    }
    
    /**
     * Afficher la page de compatibilité
     */
    public function render_compatibility_page() {
        if (!current_user_can('manage_options')) {
            return;
        }
        
        $compatibility = $this->check_compatibility();
        
        include IMAGE_CONVERTER_PATH . 'templates/compatibility-page.php';
    }
    
    /**
     * Afficher la page de configuration
     */
    public function render_configuration_page() {
        if (!current_user_can('manage_options')) {
            return;
        }
        
        // Générer les URLs pour les tâches cron
        $site_url = get_site_url();
        $nonce = wp_create_nonce('image_converter_cron');
        
        $cron_urls = array(
            'webp' => add_query_arg(
                array('ic_cron' => 'webp', 'nonce' => $nonce),
                $site_url
            ),
            'avif' => add_query_arg(
                array('ic_cron' => 'avif', 'nonce' => $nonce),
                $site_url
            )
        );
        
        include IMAGE_CONVERTER_PATH . 'templates/configuration-page.php';
    }
    
    /**
     * Helper pour afficher les icônes de statut
     */
    public function render_status_icon($status) {
        switch ($status) {
            case 'ok':
                return '<span class="ic-status-icon ic-status-ok">✓</span>';
            case 'warning':
                return '<span class="ic-status-icon ic-status-warning">⚠</span>';
            case 'error':
                return '<span class="ic-status-icon ic-status-error">✗</span>';
            case 'unknown':
                return '<span class="ic-status-icon ic-status-unknown">?</span>';
            default:
                return '';
        }
    }
    
    /**
     * Obtenir les statistiques complètes
     */
    public function get_statistics() {
        global $wpdb;
        $table = $wpdb->prefix . 'image_converter_progress';
        
        // Compter toutes les images JPG/PNG
        $upload_dir = wp_upload_dir();
        $base_dirs = array(
            $upload_dir['basedir'],
            WP_CONTENT_DIR . '/themes',
            WP_CONTENT_DIR . '/plugins'
        );
        
        $total_images = 0;
        foreach ($base_dirs as $dir) {
            $total_images += $this->count_images_recursive($dir);
        }
        
        // Statistiques WebP
        $webp_stats = array(
            'total_images' => $total_images,
            'converted' => $wpdb->get_var("SELECT COUNT(*) FROM $table WHERE format='webp' AND status='converted'"),
            'errors' => $wpdb->get_var("SELECT COUNT(*) FROM $table WHERE format='webp' AND status='error'"),
            'remaining' => 0
        );
        $webp_stats['remaining'] = $total_images - ($webp_stats['converted'] + $webp_stats['errors']);
        
        // Statistiques AVIF
        $avif_stats = array(
            'total_images' => $total_images,
            'converted' => $wpdb->get_var("SELECT COUNT(*) FROM $table WHERE format='avif' AND status='converted'"),
            'errors' => $wpdb->get_var("SELECT COUNT(*) FROM $table WHERE format='avif' AND status='error'"),
            'remaining' => 0
        );
        $avif_stats['remaining'] = $total_images - ($avif_stats['converted'] + $avif_stats['errors']);
        
        // Statistiques par dossier
        $folder_stats = array(
            'uploads' => $this->get_folder_stats($upload_dir['basedir']),
            'themes' => $this->get_folder_stats(WP_CONTENT_DIR . '/themes'),
            'plugins' => $this->get_folder_stats(WP_CONTENT_DIR . '/plugins')
        );
        
        return array(
            'webp' => $webp_stats,
            'avif' => $avif_stats,
            'folders' => $folder_stats
        );
    }
    
    /**
     * Compter les images récursivement
     */
    private function count_images_recursive($dir) {
        if (!is_dir($dir)) {
            return 0;
        }
        
        $count = 0;
        $items = @scandir($dir);
        
        if ($items === false) {
            return 0;
        }
        
        foreach ($items as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }
            
            $path = $dir . '/' . $item;
            
            if (is_dir($path)) {
                $count += $this->count_images_recursive($path);
            } else {
                $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
                if (in_array($ext, array('jpg', 'jpeg', 'png'))) {
                    $count++;
                }
            }
        }
        
        return $count;
    }
    
    /**
     * Obtenir les stats d'un dossier
     */
    private function get_folder_stats($dir) {
        global $wpdb;
        $table = $wpdb->prefix . 'image_converter_progress';
        
        $total = $this->count_images_recursive($dir);
        
        $webp_converted = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $table WHERE file_path LIKE %s AND format='webp' AND status='converted'",
            $dir . '%'
        ));
        
        $avif_converted = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $table WHERE file_path LIKE %s AND format='avif' AND status='converted'",
            $dir . '%'
        ));
        
        return array(
            'total' => $total,
            'webp_converted' => $webp_converted,
            'avif_converted' => $avif_converted
        );
    }
    
    /**
     * AJAX - Convertir en WebP
     */
    public function ajax_convert_webp() {
        check_ajax_referer('image_converter_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Permission refusée');
        }
        
        $batch_size = 10; // Convertir 10 images par appel AJAX
        $quality = get_option('ic_webp_quality', 85);
        
        $result = $this->convert_batch('webp', $batch_size, $quality);
        
        wp_send_json_success($result);
    }
    
    /**
     * AJAX - Convertir en AVIF
     */
    public function ajax_convert_avif() {
        check_ajax_referer('image_converter_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Permission refusée');
        }
        
        $batch_size = 10;
        $quality = get_option('ic_avif_quality', 85);
        
        $result = $this->convert_batch('avif', $batch_size, $quality);
        
        wp_send_json_success($result);
    }
    
    /**
     * AJAX - Obtenir les statistiques
     */
    public function ajax_get_stats() {
        check_ajax_referer('image_converter_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Permission refusée');
        }
        
        $stats = $this->get_statistics();
        wp_send_json_success($stats);
    }
    
    /**
     * AJAX - Réinitialiser la progression
     */
    public function ajax_reset_progress() {
        check_ajax_referer('image_converter_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Permission refusée');
        }
        
        global $wpdb;
        $table = $wpdb->prefix . 'image_converter_progress';
        
        $format = sanitize_text_field($_POST['format']);
        
        if ($format === 'all') {
            $wpdb->query("TRUNCATE TABLE $table");
        } else {
            $wpdb->delete($table, array('format' => $format));
        }
        
        wp_send_json_success('Progression réinitialisée');
    }
    
    /**
     * Convertir un lot d'images
     */
    private function convert_batch($format, $batch_size, $quality) {
        global $wpdb;
        $table = $wpdb->prefix . 'image_converter_progress';
        
        $upload_dir = wp_upload_dir();
        $dirs = array(
            'uploads' => $upload_dir['basedir'],
            'themes' => WP_CONTENT_DIR . '/themes',
            'plugins' => WP_CONTENT_DIR . '/plugins'
        );
        
        $converted = 0;
        $errors = 0;
        $skipped = 0;
        
        foreach ($dirs as $section => $dir) {
            if ($converted >= $batch_size) {
                break;
            }
            
            $result = $this->process_directory($dir, $format, $quality, $batch_size - $converted, $section);
            $converted += $result['converted'];
            $errors += $result['errors'];
            $skipped += $result['skipped'];
        }
        
        return array(
            'converted' => $converted,
            'errors' => $errors,
            'skipped' => $skipped,
            'remaining' => $this->count_remaining_images($format)
        );
    }
    
    /**
     * Traiter un répertoire
     */
    private function process_directory($dir, $format, $quality, $max_files, $section) {
        if (!is_dir($dir)) {
            return array('converted' => 0, 'errors' => 0, 'skipped' => 0);
        }
        
        global $wpdb;
        $table = $wpdb->prefix . 'image_converter_progress';
        
        $converted = 0;
        $errors = 0;
        $skipped = 0;
        
        $items = @scandir($dir);
        if ($items === false) {
            return array('converted' => 0, 'errors' => 0, 'skipped' => 0);
        }
        
        foreach ($items as $item) {
            if ($converted >= $max_files) {
                break;
            }
            
            if ($item === '.' || $item === '..') {
                continue;
            }
            
            $path = $dir . '/' . $item;
            
            if (is_dir($path)) {
                $result = $this->process_directory($path, $format, $quality, $max_files - $converted, $section);
                $converted += $result['converted'];
                $errors += $result['errors'];
                $skipped += $result['skipped'];
                continue;
            }
            
            $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
            if (!in_array($ext, array('jpg', 'jpeg', 'png'))) {
                continue;
            }
            
            // Vérifier si déjà traité
            $existing = $wpdb->get_row($wpdb->prepare(
                "SELECT * FROM $table WHERE file_path = %s AND format = %s",
                $path, $format
            ));
            
            if ($existing && $existing->status === 'converted') {
                $skipped++;
                continue;
            }
            
            // Vérifier si le fichier de sortie existe déjà
            $output_ext = '.' . $format;
            if (file_exists($path . $output_ext)) {
                // Marquer comme converti
                $wpdb->replace($table, array(
                    'file_path' => $path,
                    'format' => $format,
                    'status' => 'converted'
                ));
                $skipped++;
                continue;
            }
            
            // Convertir l'image
            if ($this->convert_image($path, $format, $quality)) {
                $wpdb->replace($table, array(
                    'file_path' => $path,
                    'format' => $format,
                    'status' => 'converted'
                ));
                $converted++;
            } else {
                $attempts = $existing ? $existing->attempts + 1 : 1;
                $wpdb->replace($table, array(
                    'file_path' => $path,
                    'format' => $format,
                    'status' => 'error',
                    'error_message' => 'Échec de la conversion',
                    'attempts' => $attempts
                ));
                $errors++;
            }
        }
        
        return array('converted' => $converted, 'errors' => $errors, 'skipped' => $skipped);
    }
    
    /**
     * Convertir une image
     */
    private function convert_image($path, $format, $quality) {
        $imageInfo = @getimagesize($path);
        if ($imageInfo === false) {
            return false;
        }
        
        $mimeType = $imageInfo['mime'];
        
        try {
            switch ($mimeType) {
                case 'image/jpeg':
                    $image = @imagecreatefromjpeg($path);
                    break;
                case 'image/png':
                    $image = @imagecreatefrompng($path);
                    if ($image) {
                        imagepalettetotruecolor($image);
                        imagealphablending($image, true);
                        imagesavealpha($image, true);
                    }
                    break;
                default:
                    return false;
            }
            
            if ($image === false) {
                return false;
            }
            
            $outputPath = $path . '.' . $format;
            
            if ($format === 'webp') {
                $success = @imagewebp($image, $outputPath, $quality);
            } elseif ($format === 'avif') {
                if (!function_exists('imageavif')) {
                    imagedestroy($image);
                    return false;
                }
                $success = @imageavif($image, $outputPath, $quality);
            } else {
                imagedestroy($image);
                return false;
            }
            
            imagedestroy($image);
            return $success;
            
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * Compter les images restantes à convertir
     */
    private function count_remaining_images($format) {
        global $wpdb;
        $table = $wpdb->prefix . 'image_converter_progress';
        
        $upload_dir = wp_upload_dir();
        $dirs = array(
            $upload_dir['basedir'],
            WP_CONTENT_DIR . '/themes',
            WP_CONTENT_DIR . '/plugins'
        );
        
        $total = 0;
        foreach ($dirs as $dir) {
            $total += $this->count_images_recursive($dir);
        }
        
        $converted = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $table WHERE format = %s AND status = 'converted'",
            $format
        ));
        
        return max(0, $total - $converted);
    }
    
    /**
     * Tâche cron - Conversion WebP
     */
    public function cron_convert_webp() {
        $quality = get_option('ic_webp_quality', 85);
        $this->convert_batch('webp', 50, $quality); // 50 images par exécution cron
    }
    
    /**
     * Tâche cron - Conversion AVIF
     */
    public function cron_convert_avif() {
        $quality = get_option('ic_avif_quality', 85);
        $this->convert_batch('avif', 50, $quality);
    }
    
    /**
     * Gérer les tâches cron externes (via URL)
     */
    public function handle_external_cron() {
        // Vérifier si c'est une requête cron
        if (!isset($_GET['ic_cron']) || !isset($_GET['nonce'])) {
            return;
        }
        
        // Vérifier le nonce
        if (!wp_verify_nonce($_GET['nonce'], 'image_converter_cron')) {
            wp_die('Nonce invalide', 'Erreur de sécurité', array('response' => 403));
        }
        
        $format = sanitize_text_field($_GET['ic_cron']);
        
        if ($format === 'webp') {
            $this->cron_convert_webp();
            echo json_encode(array('success' => true, 'message' => 'Conversion WebP exécutée'));
        } elseif ($format === 'avif') {
            $this->cron_convert_avif();
            echo json_encode(array('success' => true, 'message' => 'Conversion AVIF exécutée'));
        } else {
            wp_die('Format invalide', 'Erreur', array('response' => 400));
        }
        
        exit;
    }
    
    /**
     * Mettre à jour les planifications cron
     */
    private function update_cron_schedules() {
        // Supprimer les anciennes planifications
        wp_clear_scheduled_hook('ic_webp_conversion_cron');
        wp_clear_scheduled_hook('ic_avif_conversion_cron');
        
        $schedule = get_option('ic_cron_schedule', 'hourly');
        
        // Planifier WebP
        if (get_option('ic_webp_cron_enabled')) {
            if (!wp_next_scheduled('ic_webp_conversion_cron')) {
                wp_schedule_event(time(), $schedule, 'ic_webp_conversion_cron');
            }
        }
        
        // Planifier AVIF
        if (get_option('ic_avif_cron_enabled')) {
            if (!wp_next_scheduled('ic_avif_conversion_cron')) {
                wp_schedule_event(time() + 1800, $schedule, 'ic_avif_conversion_cron'); // Décaler de 30 min
            }
        }
    }
    
    /**
     * Vérifier la compatibilité du système
     */
    public function check_compatibility() {
        $compat = array(
            'php_version' => array(
                'current' => PHP_VERSION,
                'required' => '7.4.0',
                'recommended' => '8.1.0',
                'status' => version_compare(PHP_VERSION, '7.4.0', '>=') ? 'ok' : 'error',
                'message' => ''
            ),
            'gd_extension' => array(
                'installed' => extension_loaded('gd'),
                'status' => extension_loaded('gd') ? 'ok' : 'error',
                'message' => ''
            ),
            'webp_support' => array(
                'available' => function_exists('imagewebp'),
                'status' => function_exists('imagewebp') ? 'ok' : 'error',
                'message' => ''
            ),
            'avif_support' => array(
                'available' => function_exists('imageavif'),
                'status' => function_exists('imageavif') ? 'ok' : 'warning',
                'message' => ''
            ),
            'memory_limit' => array(
                'current' => ini_get('memory_limit'),
                'recommended' => '256M',
                'status' => '',
                'message' => ''
            ),
            'max_execution_time' => array(
                'current' => ini_get('max_execution_time'),
                'recommended' => '300',
                'status' => '',
                'message' => ''
            ),
            'htaccess' => array(
                'configured' => false,
                'status' => 'warning',
                'message' => '',
                'details' => array()
            ),
            'mod_rewrite' => array(
                'enabled' => false,
                'status' => 'unknown',
                'message' => ''
            ),
            'mod_headers' => array(
                'enabled' => false,
                'status' => 'unknown',
                'message' => ''
            )
        );
        
        // Vérifier la version PHP
        if (version_compare(PHP_VERSION, '8.1.0', '>=')) {
            $compat['php_version']['status'] = 'ok';
            $compat['php_version']['message'] = 'PHP 8.1+ détecté - Support AVIF disponible';
        } elseif (version_compare(PHP_VERSION, '7.4.0', '>=')) {
            $compat['php_version']['status'] = 'warning';
            $compat['php_version']['message'] = 'PHP 7.4+ détecté - WebP disponible, AVIF nécessite PHP 8.1+';
        } else {
            $compat['php_version']['status'] = 'error';
            $compat['php_version']['message'] = 'Version PHP trop ancienne - Mise à jour requise';
        }
        
        // Vérifier GD
        if (!extension_loaded('gd')) {
            $compat['gd_extension']['message'] = 'Extension GD non installée - Requise pour la conversion d\'images';
        } else {
            $compat['gd_extension']['message'] = 'Extension GD installée et active';
        }
        
        // Vérifier WebP
        if (!function_exists('imagewebp')) {
            $compat['webp_support']['message'] = 'Support WebP non disponible - Recompiler GD avec libwebp';
        } else {
            $compat['webp_support']['message'] = 'Support WebP disponible';
        }
        
        // Vérifier AVIF
        if (!function_exists('imageavif')) {
            $compat['avif_support']['message'] = 'Support AVIF non disponible - PHP 8.1+ avec GD compilé avec libavif requis';
        } else {
            $compat['avif_support']['message'] = 'Support AVIF disponible';
        }
        
        // Vérifier la mémoire
        $memory_limit = ini_get('memory_limit');
        $memory_value = intval($memory_limit);
        if ($memory_value >= 256 || $memory_limit === '-1') {
            $compat['memory_limit']['status'] = 'ok';
            $compat['memory_limit']['message'] = 'Limite de mémoire suffisante';
        } else {
            $compat['memory_limit']['status'] = 'warning';
            $compat['memory_limit']['message'] = '256M ou plus recommandé pour le traitement d\'images';
        }
        
        // Vérifier le temps d'exécution
        $max_execution = intval(ini_get('max_execution_time'));
        if ($max_execution >= 300 || $max_execution === 0) {
            $compat['max_execution_time']['status'] = 'ok';
            $compat['max_execution_time']['message'] = 'Temps d\'exécution suffisant';
        } else {
            $compat['max_execution_time']['status'] = 'warning';
            $compat['max_execution_time']['message'] = '300 secondes ou plus recommandé';
        }
        
        // Vérifier le .htaccess
        $htaccess_check = $this->check_htaccess_config();
        $compat['htaccess'] = array_merge($compat['htaccess'], $htaccess_check);
        
        // Vérifier mod_rewrite (si Apache)
        if (function_exists('apache_get_modules')) {
            $modules = apache_get_modules();
            $compat['mod_rewrite']['enabled'] = in_array('mod_rewrite', $modules);
            $compat['mod_rewrite']['status'] = $compat['mod_rewrite']['enabled'] ? 'ok' : 'error';
            $compat['mod_rewrite']['message'] = $compat['mod_rewrite']['enabled'] 
                ? 'Module mod_rewrite activé' 
                : 'Module mod_rewrite requis pour .htaccess';
                
            $compat['mod_headers']['enabled'] = in_array('mod_headers', $modules);
            $compat['mod_headers']['status'] = $compat['mod_headers']['enabled'] ? 'ok' : 'warning';
            $compat['mod_headers']['message'] = $compat['mod_headers']['enabled'] 
                ? 'Module mod_headers activé' 
                : 'Module mod_headers recommandé pour les en-têtes HTTP';
        } else {
            $compat['mod_rewrite']['status'] = 'unknown';
            $compat['mod_rewrite']['message'] = 'Impossible de vérifier (fonction apache_get_modules non disponible)';
            $compat['mod_headers']['status'] = 'unknown';
            $compat['mod_headers']['message'] = 'Impossible de vérifier (fonction apache_get_modules non disponible)';
        }
        
        return $compat;
    }
    
    /**
     * Vérifier la configuration .htaccess
     */
    private function check_htaccess_config() {
        $htaccess_path = ABSPATH . '.htaccess';
        $result = array(
            'configured' => false,
            'status' => 'error',
            'message' => '',
            'details' => array(
                'file_exists' => false,
                'file_writable' => false,
                'has_avif_rules' => false,
                'has_webp_rules' => false,
                'has_mime_types' => false,
                'has_headers' => false,
                'rules_before_wordpress' => false
            )
        );
        
        // Vérifier si le fichier existe
        if (!file_exists($htaccess_path)) {
            $result['message'] = 'Fichier .htaccess non trouvé à la racine du site';
            $result['status'] = 'error';
            return $result;
        }
        
        $result['details']['file_exists'] = true;
        
        // Vérifier si le fichier est accessible en écriture
        $result['details']['file_writable'] = is_writable($htaccess_path);
        
        // Lire le contenu
        $content = @file_get_contents($htaccess_path);
        if ($content === false) {
            $result['message'] = 'Impossible de lire le fichier .htaccess';
            $result['status'] = 'error';
            return $result;
        }
        
        // Vérifier les règles AVIF
        if (strpos($content, 'image/avif') !== false && strpos($content, '.avif') !== false) {
            $result['details']['has_avif_rules'] = true;
        }
        
        // Vérifier les règles WebP
        if (strpos($content, 'image/webp') !== false && strpos($content, '.webp') !== false) {
            $result['details']['has_webp_rules'] = true;
        }
        
        // Vérifier les types MIME
        if (strpos($content, 'AddType image/avif .avif') !== false || 
            strpos($content, 'AddType image/webp .webp') !== false) {
            $result['details']['has_mime_types'] = true;
        }
        
        // Vérifier les en-têtes HTTP
        if (strpos($content, 'mod_headers') !== false && strpos($content, 'Header') !== false) {
            $result['details']['has_headers'] = true;
        }
        
        // Vérifier si les règles sont avant WordPress
        $wp_start = strpos($content, '# BEGIN WordPress');
        if ($wp_start !== false) {
            $avif_pos = strpos($content, 'image/avif');
            $webp_pos = strpos($content, 'image/webp');
            
            if (($avif_pos !== false && $avif_pos < $wp_start) || 
                ($webp_pos !== false && $webp_pos < $wp_start)) {
                $result['details']['rules_before_wordpress'] = true;
            }
        } else {
            // Pas de bloc WordPress détecté, considérer comme OK
            $result['details']['rules_before_wordpress'] = true;
        }
        
        // Déterminer le statut global
        $has_rules = $result['details']['has_avif_rules'] || $result['details']['has_webp_rules'];
        
        if ($has_rules && $result['details']['rules_before_wordpress']) {
            $result['configured'] = true;
            $result['status'] = 'ok';
            $result['message'] = 'Configuration .htaccess détectée et correctement placée';
        } elseif ($has_rules && !$result['details']['rules_before_wordpress']) {
            $result['status'] = 'warning';
            $result['message'] = 'Règles détectées mais mal positionnées (doivent être AVANT # BEGIN WordPress)';
        } else {
            $result['status'] = 'warning';
            $result['message'] = 'Configuration .htaccess non détectée - Les images converties ne seront pas servies automatiquement';
        }
        
        return $result;
    }
}

// Initialiser le plugin
Image_Converter_Plugin::get_instance();