<?php
/**
 * Template: Page de compatibilité
 * Fichier: templates/compatibility-page.php
 */
if (!defined('ABSPATH')) exit;

$overall_status = 'ok';
foreach ($compatibility as $key => $check) {
    if (isset($check['status']) && $check['status'] === 'error') {
        $overall_status = 'error';
        break;
    }
    if (isset($check['status']) && $check['status'] === 'warning' && $overall_status !== 'error') {
        $overall_status = 'warning';
    }
}
?>

<div class="wrap image-converter-wrap">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
    
    <!-- Statut global -->
    <div class="ic-compatibility-overview">
        <?php if ($overall_status === 'ok'): ?>
            <div class="ic-compat-banner ic-compat-success">
                <span class="dashicons dashicons-yes-alt"></span>
                <div>
                    <h2>✅ Votre système est compatible</h2>
                    <p>Toutes les vérifications sont passées avec succès. Vous pouvez utiliser le plugin sans problème.</p>
                </div>
            </div>
        <?php elseif ($overall_status === 'warning'): ?>
            <div class="ic-compat-banner ic-compat-warning">
                <span class="dashicons dashicons-warning"></span>
                <div>
                    <h2>⚠️ Système partiellement compatible</h2>
                    <p>Certaines fonctionnalités peuvent nécessiter une configuration supplémentaire. Consultez les détails ci-dessous.</p>
                </div>
            </div>
        <?php else: ?>
            <div class="ic-compat-banner ic-compat-error">
                <span class="dashicons dashicons-dismiss"></span>
                <div>
                    <h2>❌ Problèmes de compatibilité détectés</h2>
                    <p>Votre système ne remplit pas toutes les conditions requises. Veuillez corriger les erreurs ci-dessous.</p>
                </div>
            </div>
        <?php endif; ?>
    </div>
    
    <!-- Vérifications PHP et Extensions -->
    <div class="ic-compat-section">
        <h2>🐘 PHP et Extensions</h2>
        
        <table class="widefat ic-compat-table">
            <thead>
                <tr>
                    <th style="width: 30%;">Composant</th>
                    <th style="width: 20%;">Valeur actuelle</th>
                    <th style="width: 20%;">Recommandé</th>
                    <th style="width: 10%;">Statut</th>
                    <th style="width: 20%;">Message</th>
                </tr>
            </thead>
            <tbody>
                <!-- Version PHP -->
                <tr>
                    <td><strong>Version PHP</strong></td>
                    <td><?php echo esc_html($compatibility['php_version']['current']); ?></td>
                    <td>
                        7.4+ (requis)<br>
                        <small>8.1+ (recommandé pour AVIF)</small>
                    </td>
                    <td>
                        <?php echo Image_Converter_Plugin::get_instance()->render_status_icon($compatibility['php_version']['status']); ?>
                    </td>
                    <td><?php echo esc_html($compatibility['php_version']['message']); ?></td>
                </tr>
                
                <!-- Extension GD -->
                <tr>
                    <td><strong>Extension GD</strong></td>
                    <td>
                        <?php echo $compatibility['gd_extension']['installed'] ? 'Installée' : 'Non installée'; ?>
                    </td>
                    <td>Requise</td>
                    <td>
                        <?php echo Image_Converter_Plugin::get_instance()->render_status_icon($compatibility['gd_extension']['status']); ?>
                    </td>
                    <td><?php echo esc_html($compatibility['gd_extension']['message']); ?></td>
                </tr>
                
                <!-- Support WebP -->
                <tr>
                    <td><strong>Support WebP</strong></td>
                    <td>
                        <?php echo $compatibility['webp_support']['available'] ? 'Disponible' : 'Non disponible'; ?>
                    </td>
                    <td>Requis</td>
                    <td>
                        <?php echo Image_Converter_Plugin::get_instance()->render_status_icon($compatibility['webp_support']['status']); ?>
                    </td>
                    <td><?php echo esc_html($compatibility['webp_support']['message']); ?></td>
                </tr>
                
                <!-- Support AVIF -->
                <tr>
                    <td><strong>Support AVIF</strong></td>
                    <td>
                        <?php echo $compatibility['avif_support']['available'] ? 'Disponible' : 'Non disponible'; ?>
                    </td>
                    <td>Optionnel (PHP 8.1+)</td>
                    <td>
                        <?php echo Image_Converter_Plugin::get_instance()->render_status_icon($compatibility['avif_support']['status']); ?>
                    </td>
                    <td><?php echo esc_html($compatibility['avif_support']['message']); ?></td>
                </tr>
                
                <!-- Limite mémoire -->
                <tr>
                    <td><strong>Limite de mémoire PHP</strong></td>
                    <td><?php echo esc_html($compatibility['memory_limit']['current']); ?></td>
                    <td>256M ou plus</td>
                    <td>
                        <?php echo Image_Converter_Plugin::get_instance()->render_status_icon($compatibility['memory_limit']['status']); ?>
                    </td>
                    <td><?php echo esc_html($compatibility['memory_limit']['message']); ?></td>
                </tr>
                
                <!-- Temps d'exécution -->
                <tr>
                    <td><strong>Temps d'exécution max</strong></td>
                    <td><?php echo esc_html($compatibility['max_execution_time']['current']); ?>s</td>
                    <td>300s ou plus</td>
                    <td>
                        <?php echo Image_Converter_Plugin::get_instance()->render_status_icon($compatibility['max_execution_time']['status']); ?>
                    </td>
                    <td><?php echo esc_html($compatibility['max_execution_time']['message']); ?></td>
                </tr>
            </tbody>
        </table>
    </div>
    
    <!-- Vérifications Apache -->
    <div class="ic-compat-section">
        <h2>🌐 Configuration Apache</h2>
        
        <table class="widefat ic-compat-table">
            <thead>
                <tr>
                    <th style="width: 30%;">Module</th>
                    <th style="width: 20%;">État</th>
                    <th style="width: 10%;">Statut</th>
                    <th style="width: 40%;">Message</th>
                </tr>
            </thead>
            <tbody>
                <!-- mod_rewrite -->
                <tr>
                    <td><strong>mod_rewrite</strong></td>
                    <td>
                        <?php 
                        if ($compatibility['mod_rewrite']['status'] === 'unknown') {
                            echo 'Inconnu';
                        } else {
                            echo $compatibility['mod_rewrite']['enabled'] ? 'Activé' : 'Désactivé';
                        }
                        ?>
                    </td>
                    <td>
                        <?php echo Image_Converter_Plugin::get_instance()->render_status_icon($compatibility['mod_rewrite']['status']); ?>
                    </td>
                    <td><?php echo esc_html($compatibility['mod_rewrite']['message']); ?></td>
                </tr>
                
                <!-- mod_headers -->
                <tr>
                    <td><strong>mod_headers</strong></td>
                    <td>
                        <?php 
                        if ($compatibility['mod_headers']['status'] === 'unknown') {
                            echo 'Inconnu';
                        } else {
                            echo $compatibility['mod_headers']['enabled'] ? 'Activé' : 'Désactivé';
                        }
                        ?>
                    </td>
                    <td>
                        <?php echo Image_Converter_Plugin::get_instance()->render_status_icon($compatibility['mod_headers']['status']); ?>
                    </td>
                    <td><?php echo esc_html($compatibility['mod_headers']['message']); ?></td>
                </tr>
            </tbody>
        </table>
        
        <?php if ($compatibility['mod_rewrite']['status'] === 'unknown' || $compatibility['mod_headers']['status'] === 'unknown'): ?>
            <div class="ic-notice ic-notice-info">
                <p>
                    <strong>ℹ️ Impossible de détecter les modules Apache</strong><br>
                    La fonction <code>apache_get_modules()</code> n'est pas disponible sur votre serveur.
                    Si vous utilisez Nginx, ignorez ces vérifications. Sinon, contactez votre hébergeur pour confirmer l'activation de ces modules.
                </p>
            </div>
        <?php endif; ?>
    </div>
    
    <!-- Vérification .htaccess -->
    <div class="ic-compat-section">
        <h2>📄 Configuration .htaccess</h2>
        
        <div class="ic-htaccess-status">
            <div class="ic-htaccess-summary">
                <?php echo Image_Converter_Plugin::get_instance()->render_status_icon($compatibility['htaccess']['status']); ?>
                <div>
                    <h3><?php echo esc_html($compatibility['htaccess']['message']); ?></h3>
                    <?php if ($compatibility['htaccess']['details']['file_exists']): ?>
                        <p>Fichier .htaccess trouvé à : <code><?php echo esc_html(ABSPATH . '.htaccess'); ?></code></p>
                    <?php endif; ?>
                </div>
            </div>
            
            <table class="widefat">
                <thead>
                    <tr>
                        <th style="width: 40%;">Vérification</th>
                        <th style="width: 20%;">État</th>
                        <th style="width: 40%;">Description</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Fichier .htaccess existe</td>
                        <td>
                            <?php echo $compatibility['htaccess']['details']['file_exists'] 
                                ? '<span class="ic-status-ok">✓ Oui</span>' 
                                : '<span class="ic-status-error">✗ Non</span>'; ?>
                        </td>
                        <td>Le fichier doit exister à la racine du site</td>
                    </tr>
                    
                    <tr>
                        <td>Fichier modifiable</td>
                        <td>
                            <?php echo $compatibility['htaccess']['details']['file_writable'] 
                                ? '<span class="ic-status-ok">✓ Oui</span>' 
                                : '<span class="ic-status-warning">⚠ Non</span>'; ?>
                        </td>
                        <td>Permissions d'écriture recommandées pour modifications</td>
                    </tr>
                    
                    <tr>
                        <td>Règles AVIF présentes</td>
                        <td>
                            <?php echo $compatibility['htaccess']['details']['has_avif_rules'] 
                                ? '<span class="ic-status-ok">✓ Oui</span>' 
                                : '<span class="ic-status-warning">⚠ Non</span>'; ?>
                        </td>
                        <td>Règles de réécriture pour AVIF</td>
                    </tr>
                    
                    <tr>
                        <td>Règles WebP présentes</td>
                        <td>
                            <?php echo $compatibility['htaccess']['details']['has_webp_rules'] 
                                ? '<span class="ic-status-ok">✓ Oui</span>' 
                                : '<span class="ic-status-warning">⚠ Non</span>'; ?>
                        </td>
                        <td>Règles de réécriture pour WebP</td>
                    </tr>
                    
                    <tr>
                        <td>Types MIME configurés</td>
                        <td>
                            <?php echo $compatibility['htaccess']['details']['has_mime_types'] 
                                ? '<span class="ic-status-ok">✓ Oui</span>' 
                                : '<span class="ic-status-warning">⚠ Non</span>'; ?>
                        </td>
                        <td>Déclaration des types MIME pour AVIF et WebP</td>
                    </tr>
                    
                    <tr>
                        <td>En-têtes HTTP configurés</td>
                        <td>
                            <?php echo $compatibility['htaccess']['details']['has_headers'] 
                                ? '<span class="ic-status-ok">✓ Oui</span>' 
                                : '<span class="ic-status-warning">⚠ Non</span>'; ?>
                        </td>
                        <td>En-têtes HTTP pour Content-Type et Vary</td>
                    </tr>
                    
                    <tr>
                        <td>Règles avant WordPress</td>
                        <td>
                            <?php echo $compatibility['htaccess']['details']['rules_before_wordpress'] 
                                ? '<span class="ic-status-ok">✓ Oui</span>' 
                                : '<span class="ic-status-error">✗ Non</span>'; ?>
                        </td>
                        <td>Les règles doivent être AVANT # BEGIN WordPress</td>
                    </tr>
                </tbody>
            </table>
        </div>
        
        <?php if ($compatibility['htaccess']['status'] !== 'ok'): ?>
            <div class="ic-htaccess-help">
                <h3>📝 Configuration recommandée</h3>
                <p>
                    Copiez ce code dans votre fichier <code>.htaccess</code> <strong>AVANT</strong> la ligne <code># BEGIN WordPress</code> :
                </p>
                
                <div class="ic-code-block">
                    <button type="button" class="button ic-copy-htaccess-compat">📋 Copier le code</button>
                    <pre id="ic-htaccess-code-compat"># Image Converter - Servir AVIF et WebP automatiquement
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
        <?php endif; ?>
    </div>
    
    <!-- Recommandations -->
    <div class="ic-compat-section">
        <h2>💡 Recommandations</h2>
        
        <div class="ic-recommendations">
            <?php if ($compatibility['php_version']['status'] !== 'ok'): ?>
                <div class="ic-recommendation ic-rec-important">
                    <span class="dashicons dashicons-warning"></span>
                    <div>
                        <h4>Mettre à jour PHP</h4>
                        <p>
                            Votre version de PHP (<?php echo PHP_VERSION; ?>) est obsolète. 
                            Mettez à jour vers PHP 8.1 ou supérieur pour bénéficier du support AVIF 
                            et des meilleures performances.
                        </p>
                        <p><strong>Action :</strong> Contactez votre hébergeur pour effectuer la mise à jour.</p>
                    </div>
                </div>
            <?php endif; ?>
            
            <?php if (!$compatibility['avif_support']['available']): ?>
                <div class="ic-recommendation ic-rec-info">
                    <span class="dashicons dashicons-info"></span>
                    <div>
                        <h4>Activer le support AVIF</h4>
                        <p>
                            AVIF offre une compression jusqu'à 50% meilleure que JPEG. 
                            Pour l'activer, vous avez besoin de PHP 8.1+ avec l'extension GD compilée avec libavif.
                        </p>
                        <p><strong>Action :</strong> Contactez votre hébergeur ou mettez à jour PHP.</p>
                    </div>
                </div>
            <?php endif; ?>
            
            <?php if ($compatibility['htaccess']['status'] !== 'ok'): ?>
                <div class="ic-recommendation ic-rec-important">
                    <span class="dashicons dashicons-admin-generic"></span>
                    <div>
                        <h4>Configurer .htaccess</h4>
                        <p>
                            Les règles .htaccess permettent à Apache de servir automatiquement 
                            les versions WebP et AVIF de vos images aux navigateurs compatibles.
                        </p>
                        <p><strong>Action :</strong> Copiez le code fourni ci-dessus dans votre fichier .htaccess.</p>
                    </div>
                </div>
            <?php endif; ?>
            
            <?php if ($compatibility['memory_limit']['status'] === 'warning'): ?>
                <div class="ic-recommendation ic-rec-info">
                    <span class="dashicons dashicons-performance"></span>
                    <div>
                        <h4>Augmenter la limite de mémoire</h4>
                        <p>
                            La conversion d'images volumineuses peut nécessiter plus de mémoire. 
                            256M ou plus est recommandé.
                        </p>
                        <p><strong>Action :</strong> Ajoutez <code>define('WP_MEMORY_LIMIT', '256M');</code> dans wp-config.php 
                        ou contactez votre hébergeur.</p>
                    </div>
                </div>
            <?php endif; ?>
            
            <?php if ($overall_status === 'ok'): ?>
                <div class="ic-recommendation ic-rec-success">
                    <span class="dashicons dashicons-yes-alt"></span>
                    <div>
                        <h4>✅ Tout est prêt !</h4>
                        <p>
                            Votre système est entièrement configuré. Vous pouvez maintenant :
                        </p>
                        <ul>
                            <li>Convertir vos images en WebP et AVIF</li>
                            <li>Configurer les tâches cron automatiques</li>
                            <li>Profiter d'un site plus rapide</li>
                        </ul>
                        <p>
                            <a href="<?php echo admin_url('admin.php?page=image-converter'); ?>" class="button button-primary">
                                Commencer la conversion
                            </a>
                        </p>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Bouton de recharge -->
    <div class="ic-compat-actions">
        <button type="button" class="button button-secondary" onclick="location.reload();">
            🔄 Relancer les vérifications
        </button>
    </div>
</div>