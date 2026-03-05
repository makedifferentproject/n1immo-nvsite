<?php
/**
 * Template: Page de réglages
 * Fichier: templates/settings-page.php
 */
if (!defined('ABSPATH')) exit;

$webp_quality = get_option('ic_webp_quality', 85);
$avif_quality = get_option('ic_avif_quality', 85);
$webp_cron_enabled = get_option('ic_webp_cron_enabled', false);
$avif_cron_enabled = get_option('ic_avif_cron_enabled', false);
$cron_schedule = get_option('ic_cron_schedule', 'hourly');
?>

<div class="wrap image-converter-wrap">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
    
    <form method="post" action="">
        <?php wp_nonce_field('ic_settings_nonce'); ?>
        
        <div class="ic-settings-container">
            <!-- Qualité des conversions -->
            <div class="ic-settings-section">
                <h2>⚙️ Qualité des conversions</h2>
                
                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label for="ic_webp_quality">Qualité WebP</label>
                        </th>
                        <td>
                            <input type="range" 
                                   id="ic_webp_quality" 
                                   name="ic_webp_quality" 
                                   min="0" 
                                   max="100" 
                                   value="<?php echo esc_attr($webp_quality); ?>"
                                   class="ic-quality-slider">
                            <span class="ic-quality-value"><?php echo esc_html($webp_quality); ?></span>
                            <p class="description">
                                0 = compression maximale (qualité faible), 100 = qualité maximale (fichier plus lourd).
                                <br>Recommandé : 80-90 pour un bon équilibre qualité/taille.
                            </p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">
                            <label for="ic_avif_quality">Qualité AVIF</label>
                        </th>
                        <td>
                            <input type="range" 
                                   id="ic_avif_quality" 
                                   name="ic_avif_quality" 
                                   min="0" 
                                   max="100" 
                                   value="<?php echo esc_attr($avif_quality); ?>"
                                   class="ic-quality-slider">
                            <span class="ic-quality-value"><?php echo esc_html($avif_quality); ?></span>
                            <p class="description">
                                AVIF offre une meilleure compression qu'WebP à qualité équivalente.
                                <br>Recommandé : 75-85 pour d'excellents résultats.
                            </p>
                        </td>
                    </tr>
                </table>
            </div>
            
            <!-- Tâches planifiées -->
            <div class="ic-settings-section">
                <h2>⏰ Tâches planifiées (Cron)</h2>
                
                <table class="form-table">
                    <tr>
                        <th scope="row">Conversion WebP automatique</th>
                        <td>
                            <label>
                                <input type="checkbox" 
                                       name="ic_webp_cron_enabled" 
                                       value="1" 
                                       <?php checked($webp_cron_enabled); ?>>
                                Activer la conversion automatique en WebP
                            </label>
                            <p class="description">
                                Les images seront converties automatiquement selon la fréquence choisie.
                            </p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">Conversion AVIF automatique</th>
                        <td>
                            <label>
                                <input type="checkbox" 
                                       name="ic_avif_cron_enabled" 
                                       value="1" 
                                       <?php checked($avif_cron_enabled); ?>>
                                Activer la conversion automatique en AVIF
                            </label>
                            <p class="description">
                                <?php if (!function_exists('imageavif')): ?>
                                    <span class="ic-warning">
                                        ⚠️ AVIF n'est pas supporté sur ce serveur (PHP 8.1+ avec GD et libavif requis).
                                    </span>
                                <?php else: ?>
                                    AVIF est supporté sur ce serveur.
                                <?php endif; ?>
                            </p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">
                            <label for="ic_cron_schedule">Fréquence d'exécution</label>
                        </th>
                        <td>
                            <select name="ic_cron_schedule" id="ic_cron_schedule">
                                <option value="hourly" <?php selected($cron_schedule, 'hourly'); ?>>
                                    Toutes les heures
                                </option>
                                <option value="twicedaily" <?php selected($cron_schedule, 'twicedaily'); ?>>
                                    Deux fois par jour
                                </option>
                                <option value="daily" <?php selected($cron_schedule, 'daily'); ?>>
                                    Une fois par jour
                                </option>
                            </select>
                            <p class="description">
                                Chaque exécution traite environ 50 images. Plus la fréquence est élevée, 
                                plus la conversion sera rapide, mais cela peut impacter les performances du serveur.
                            </p>
                        </td>
                    </tr>
                </table>
                
                <?php 
                $webp_next = wp_next_scheduled('ic_webp_conversion_cron');
                $avif_next = wp_next_scheduled('ic_avif_conversion_cron');
                ?>
                
                <?php if ($webp_cron_enabled || $avif_cron_enabled): ?>
                    <div class="ic-cron-info">
                        <h3>📅 Prochaines exécutions planifiées</h3>
                        <ul>
                            <?php if ($webp_next): ?>
                                <li>
                                    <strong>WebP:</strong> 
                                    <?php echo date_i18n('d/m/Y à H:i', $webp_next); ?>
                                    (dans <?php echo human_time_diff($webp_next); ?>)
                                </li>
                            <?php endif; ?>
                            
                            <?php if ($avif_next): ?>
                                <li>
                                    <strong>AVIF:</strong> 
                                    <?php echo date_i18n('d/m/Y à H:i', $avif_next); ?>
                                    (dans <?php echo human_time_diff($avif_next); ?>)
                                </li>
                            <?php endif; ?>
                        </ul>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Informations système -->
            <div class="ic-settings-section">
                <h2>🖥️ Informations système</h2>
                
                <table class="widefat">
                    <tbody>
                        <tr>
                            <td><strong>Version PHP</strong></td>
                            <td><?php echo PHP_VERSION; ?></td>
                            <td>
                                <?php if (version_compare(PHP_VERSION, '8.1.0', '>=')): ?>
                                    <span class="ic-status-ok">✓ Compatible AVIF</span>
                                <?php else: ?>
                                    <span class="ic-status-warning">⚠️ PHP 8.1+ requis pour AVIF</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        
                        <tr>
                            <td><strong>Extension GD</strong></td>
                            <td><?php echo extension_loaded('gd') ? 'Installée' : 'Non installée'; ?></td>
                            <td>
                                <?php if (extension_loaded('gd')): ?>
                                    <span class="ic-status-ok">✓ Activée</span>
                                <?php else: ?>
                                    <span class="ic-status-error">✗ Requise</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        
                        <tr>
                            <td><strong>Support WebP</strong></td>
                            <td><?php echo function_exists('imagewebp') ? 'Oui' : 'Non'; ?></td>
                            <td>
                                <?php if (function_exists('imagewebp')): ?>
                                    <span class="ic-status-ok">✓ Disponible</span>
                                <?php else: ?>
                                    <span class="ic-status-error">✗ Non disponible</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        
                        <tr>
                            <td><strong>Support AVIF</strong></td>
                            <td><?php echo function_exists('imageavif') ? 'Oui' : 'Non'; ?></td>
                            <td>
                                <?php if (function_exists('imageavif')): ?>
                                    <span class="ic-status-ok">✓ Disponible</span>
                                <?php else: ?>
                                    <span class="ic-status-warning">⚠️ Non disponible</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        
                        <tr>
                            <td><strong>Limite de mémoire PHP</strong></td>
                            <td><?php echo ini_get('memory_limit'); ?></td>
                            <td>
                                <?php 
                                $memory_limit = ini_get('memory_limit');
                                $memory_value = intval($memory_limit);
                                if ($memory_value >= 256): ?>
                                    <span class="ic-status-ok">✓ Suffisant</span>
                                <?php else: ?>
                                    <span class="ic-status-warning">⚠️ 256M recommandé</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        
                        <tr>
                            <td><strong>Temps d'exécution max</strong></td>
                            <td><?php echo ini_get('max_execution_time'); ?>s</td>
                            <td>
                                <?php 
                                $max_execution_time = intval(ini_get('max_execution_time'));
                                if ($max_execution_time >= 300 || $max_execution_time == 0): ?>
                                    <span class="ic-status-ok">✓ Suffisant</span>
                                <?php else: ?>
                                    <span class="ic-status-warning">⚠️ 300s recommandé</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            
            <!-- Configuration .htaccess -->
            <div class="ic-settings-section">
                <h2>📄 Configuration .htaccess</h2>
                <p>
                    Pour que WordPress serve automatiquement les images WebP et AVIF, 
                    ajoutez ce code dans votre fichier <code>.htaccess</code> à la racine du site,
                    <strong>AVANT</strong> les règles WordPress.
                </p>
                
                <div class="ic-code-block">
                    <button type="button" class="button ic-copy-htaccess">📋 Copier le code</button>
                    <pre id="ic-htaccess-code"># Image Converter - Servir AVIF et WebP automatiquement
<IfModule mod_rewrite.c>
  RewriteEngine On

  # Priorité 1: AVIF (meilleure compression)
  RewriteCond %{HTTP_ACCEPT} image/avif
  RewriteCond %{REQUEST_URI} \.(jpe?g|png)$ [NC]
  RewriteCond %{DOCUMENT_ROOT}%{REQUEST_URI}.avif -f
  RewriteRule ^(.+)\.(jpe?g|png)$ $1.$2.avif [T=image/avif,E=REQUEST_image,L]

  # Priorité 2: WebP (fallback)
  RewriteCond %{HTTP_ACCEPT} image/webp
  RewriteCond %{REQUEST_URI} \.(jpe?g|png)$ [NC]
  RewriteCond %{DOCUMENT_ROOT}%{REQUEST_URI}.webp -f
  RewriteRule ^(.+)\.(jpe?g|png)$ $1.$2.webp [T=image/webp,E=REQUEST_image,L]
</IfModule>

<IfModule mod_headers.c>
  <FilesMatch "\.(jpe?g|png)$">
    Header append Vary Accept
  </FilesMatch>
  <FilesMatch "\.avif$">
    Header set Content-Type "image/avif"
  </FilesMatch>
  <FilesMatch "\.webp$">
    Header set Content-Type "image/webp"
  </FilesMatch>
</IfModule>

<IfModule mod_mime.c>
  AddType image/avif .avif
  AddType image/webp .webp
</IfModule></pre>
                </div>
            </div>
        </div>
        
        <p class="submit">
            <input type="submit" 
                   name="ic_save_settings" 
                   class="button button-primary" 
                   value="Enregistrer les réglages">
        </p>
    </form>
</div>