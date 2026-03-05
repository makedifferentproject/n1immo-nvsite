<?php
/**
 * Template: Page de configuration
 * Fichier: templates/configuration-page.php
 */
if (!defined('ABSPATH')) exit;
?>

<div class="wrap image-converter-wrap">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
    
    <div class="ic-config-intro">
        <p>
            Cette page vous guide pour configurer le plugin Image Converter sur votre serveur.
            Vous y trouverez les instructions pour configurer le fichier <code>.htaccess</code>
            et les tâches cron pour automatiser les conversions.
        </p>
    </div>
    
    <!-- Configuration .htaccess -->
    <div class="ic-config-section">
        <h2>📄 Configuration du fichier .htaccess</h2>
        
        <div class="ic-config-description">
            <p>
                Le fichier <code>.htaccess</code> permet à Apache de servir automatiquement les versions 
                WebP et AVIF de vos images aux navigateurs compatibles, sans modifier votre code WordPress.
            </p>
            
            <div class="ic-config-benefits">
                <h3>✨ Avantages :</h3>
                <ul>
                    <li>🚀 <strong>Automatique</strong> : Aucune modification du code WordPress nécessaire</li>
                    <li>🎯 <strong>Intelligent</strong> : Sert AVIF aux navigateurs compatibles, sinon WebP, sinon JPG/PNG</li>
                    <li>⚡ <strong>Performant</strong> : Réduction de 30-50% de la taille des images</li>
                    <li>🔄 <strong>Transparent</strong> : Les anciennes images restent disponibles</li>
                </ul>
            </div>
        </div>
        
        <div class="ic-config-steps">
            <h3>📋 Instructions d'installation :</h3>
            
            <div class="ic-step">
                <div class="ic-step-number">1</div>
                <div class="ic-step-content">
                    <h4>Localiser le fichier .htaccess</h4>
                    <p>
                        Le fichier se trouve à la racine de votre installation WordPress :<br>
                        <code><?php echo esc_html(ABSPATH . '.htaccess'); ?></code>
                    </p>
                    <p class="ic-note">
                        <strong>Note :</strong> Si le fichier n'existe pas, créez-le. 
                        Assurez-vous qu'il soit au même niveau que <code>wp-config.php</code>
                    </p>
                </div>
            </div>
            
            <div class="ic-step">
                <div class="ic-step-number">2</div>
                <div class="ic-step-content">
                    <h4>Faire une sauvegarde</h4>
                    <p>
                        <strong>⚠️ Important :</strong> Avant toute modification, faites une copie de votre fichier .htaccess actuel.
                    </p>
                    <p>
                        Commande pour sauvegarder :<br>
                        <code>cp .htaccess .htaccess.backup</code>
                    </p>
                </div>
            </div>
            
            <div class="ic-step">
                <div class="ic-step-number">3</div>
                <div class="ic-step-content">
                    <h4>Ajouter le code <strong>AVANT</strong> les règles WordPress</h4>
                    <p>
                        Copiez le code ci-dessous et collez-le dans votre .htaccess 
                        <strong>AVANT</strong> la ligne <code># BEGIN WordPress</code>
                    </p>
                    
                    <div class="ic-code-block">
                        <button type="button" class="button ic-copy-code" data-target="htaccess-code">
                            📋 Copier le code
                        </button>
                        <pre id="htaccess-code"># ============================================================
# Image Converter - Servir AVIF et WebP automatiquement
# ============================================================

<IfModule mod_rewrite.c>
  RewriteEngine On

  # Priorité 1: AVIF (meilleure compression ~50%)
  RewriteCond %{HTTP_ACCEPT} image/avif
  RewriteCond %{REQUEST_URI} \.(jpe?g|png)$ [NC]
  RewriteCond %{DOCUMENT_ROOT}%{REQUEST_URI}.avif -f
  RewriteRule ^(.+)\.(jpe?g|png)$ $1.$2.avif [T=image/avif,E=REQUEST_image,L]

  # Priorité 2: WebP (fallback ~30-35%)
  RewriteCond %{HTTP_ACCEPT} image/webp
  RewriteCond %{REQUEST_URI} \.(jpe?g|png)$ [NC]
  RewriteCond %{DOCUMENT_ROOT}%{REQUEST_URI}.webp -f
  RewriteRule ^(.+)\.(jpe?g|png)$ $1.$2.webp [T=image/webp,E=REQUEST_image,L]
</IfModule>

# En-têtes HTTP pour les images
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

# Déclaration des types MIME
<IfModule mod_mime.c>
  AddType image/avif .avif
  AddType image/webp .webp
</IfModule>

# ============================================================
# FIN Image Converter
# ============================================================

# BEGIN WordPress
# Les règles WordPress commencent ici...
# END WordPress</pre>
                    </div>
                </div>
            </div>
            
            <div class="ic-step">
                <div class="ic-step-number">4</div>
                <div class="ic-step-content">
                    <h4>Tester la configuration</h4>
                    <p>
                        Après avoir ajouté le code, testez que votre site fonctionne toujours correctement.
                        Si vous rencontrez une erreur 500, restaurez votre sauvegarde et vérifiez que :
                    </p>
                    <ul>
                        <li>Les modules <code>mod_rewrite</code> et <code>mod_headers</code> sont activés</li>
                        <li>Le code est bien placé <strong>avant</strong> # BEGIN WordPress</li>
                        <li>Il n'y a pas de faute de syntaxe dans le code</li>
                    </ul>
                </div>
            </div>
        </div>
        
        <div class="ic-config-example">
            <h3>💡 Exemple de fichier .htaccess complet</h3>
            <div class="ic-code-block">
                <pre># Image Converter - À placer EN PREMIER

<IfModule mod_rewrite.c>
  RewriteEngine On
  RewriteCond %{HTTP_ACCEPT} image/avif
  RewriteCond %{REQUEST_URI} \.(jpe?g|png)$ [NC]
  RewriteCond %{DOCUMENT_ROOT}%{REQUEST_URI}.avif -f
  RewriteRule ^(.+)\.(jpe?g|png)$ $1.$2.avif [T=image/avif,E=REQUEST_image,L]

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
</IfModule>

# BEGIN WordPress
<IfModule mod_rewrite.c>
RewriteEngine On
RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]
RewriteBase /
RewriteRule ^index\.php$ - [L]
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule . /index.php [L]
</IfModule>
# END WordPress</pre>
            </div>
        </div>
    </div>
    
    <!-- Configuration Cron -->
    <div class="ic-config-section">
        <h2>⏰ Configuration des tâches Cron</h2>
        
        <div class="ic-config-description">
            <p>
                Les tâches cron permettent d'automatiser la conversion de vos images en arrière-plan.
                Vous pouvez utiliser soit le système cron de WordPress, soit le cron système de votre serveur.
            </p>
            
            <div class="ic-config-comparison">
                <div class="ic-comparison-item">
                    <h3>🔵 Cron WordPress (recommandé)</h3>
                    <p class="ic-pros">
                        ✅ Configuration simple dans les réglages du plugin<br>
                        ✅ Fonctionne sur tous les hébergements<br>
                        ✅ Aucune commande serveur nécessaire
                    </p>
                    <p class="ic-cons">
                        ⚠️ S'exécute uniquement quand quelqu'un visite le site<br>
                        ⚠️ Peut ralentir le chargement des pages
                    </p>
                    <p>
                        <a href="<?php echo admin_url('admin.php?page=image-converter-settings'); ?>" class="button button-primary">
                            Configurer dans Réglages
                        </a>
                    </p>
                </div>
                
                <div class="ic-comparison-item">
                    <h3>🟢 Cron système (meilleure performance)</h3>
                    <p class="ic-pros">
                        ✅ S'exécute à heure fixe, indépendamment du trafic<br>
                        ✅ N'impacte pas les performances du site<br>
                        ✅ Plus fiable pour les gros volumes
                    </p>
                    <p class="ic-cons">
                        ⚠️ Nécessite un accès SSH ou panneau d'hébergement<br>
                        ⚠️ Configuration manuelle requise
                    </p>
                    <p>
                        Voir les instructions ci-dessous ↓
                    </p>
                </div>
            </div>
        </div>
        
        <div class="ic-config-steps">
            <h3>📋 Configuration du Cron système</h3>
            
            <div class="ic-cron-method">
                <h4>Méthode 1 : Via SSH (ligne de commande)</h4>
                
                <div class="ic-step">
                    <div class="ic-step-number">1</div>
                    <div class="ic-step-content">
                        <h4>Se connecter en SSH</h4>
                        <p>
                            Connectez-vous à votre serveur via SSH :<br>
                            <code>ssh utilisateur@votreserveur.com</code>
                        </p>
                    </div>
                </div>
                
                <div class="ic-step">
                    <div class="ic-step-number">2</div>
                    <div class="ic-step-content">
                        <h4>Éditer la crontab</h4>
                        <p>
                            Ouvrez l'éditeur de crontab :<br>
                            <code>crontab -e</code>
                        </p>
                    </div>
                </div>
                
                <div class="ic-step">
                    <div class="ic-step-number">3</div>
                    <div class="ic-step-content">
                        <h4>Ajouter les tâches cron</h4>
                        <p>
                            Copiez et collez les lignes suivantes dans votre crontab :
                        </p>
                        
                        <div class="ic-code-block">
                            <button type="button" class="button ic-copy-code" data-target="cron-code">
                                📋 Copier les commandes
                            </button>
                            <pre id="cron-code"># Image Converter - Conversion WebP (toutes les heures)
0 * * * * curl -k "<?php echo esc_url($cron_urls['webp']); ?>" > /dev/null 2>&1

# Image Converter - Conversion AVIF (toutes les heures, décalé de 30 min)
30 * * * * curl -k "<?php echo esc_url($cron_urls['avif']); ?>" > /dev/null 2>&1</pre>
                        </div>
                        
                        <div class="ic-note">
                            <strong>💡 Explications :</strong>
                            <ul>
                                <li><code>0 * * * *</code> = Toutes les heures à la minute 0</li>
                                <li><code>30 * * * *</code> = Toutes les heures à la minute 30</li>
                                <li><code>curl -k</code> = Appel HTTP (le -k ignore les erreurs SSL)</li>
                                <li><code>> /dev/null 2>&1</code> = Supprime la sortie pour éviter les emails</li>
                            </ul>
                        </div>
                    </div>
                </div>
                
                <div class="ic-step">
                    <div class="ic-step-number">4</div>
                    <div class="ic-step-content">
                        <h4>Sauvegarder et quitter</h4>
                        <p>
                            Dans nano : <code>Ctrl + X</code>, puis <code>Y</code>, puis <code>Enter</code><br>
                            Dans vim : <code>:wq</code> puis <code>Enter</code>
                        </p>
                        <p>
                            Vérifier que les tâches sont bien enregistrées :<br>
                            <code>crontab -l</code>
                        </p>
                    </div>
                </div>
            </div>
            
            <div class="ic-cron-method">
                <h4>Méthode 2 : Via panneau d'hébergement (cPanel, Plesk, etc.)</h4>
                
                <div class="ic-step">
                    <div class="ic-step-number">1</div>
                    <div class="ic-step-content">
                        <h4>Accéder aux tâches cron</h4>
                        <p>
                            Dans votre panneau d'hébergement, cherchez une section nommée :
                        </p>
                        <ul>
                            <li>"Tâches cron" (cPanel)</li>
                            <li>"Scheduled Tasks" (Plesk)</li>
                            <li>"Planificateur de tâches"</li>
                        </ul>
                    </div>
                </div>
                
                <div class="ic-step">
                    <div class="ic-step-number">2</div>
                    <div class="ic-step-content">
                        <h4>Créer la première tâche (WebP)</h4>
                        <p><strong>Fréquence :</strong> Toutes les heures</p>
                        <p><strong>Commande :</strong></p>
                        <div class="ic-code-block">
                            <button type="button" class="button ic-copy-code" data-target="cron-webp">
                                📋 Copier
                            </button>
                            <pre id="cron-webp">curl -k "<?php echo esc_url($cron_urls['webp']); ?>"</pre>
                        </div>
                    </div>
                </div>
                
                <div class="ic-step">
                    <div class="ic-step-number">3</div>
                    <div class="ic-step-content">
                        <h4>Créer la seconde tâche (AVIF)</h4>
                        <p><strong>Fréquence :</strong> Toutes les heures (décalé de 30 minutes)</p>
                        <p><strong>Commande :</strong></p>
                        <div class="ic-code-block">
                            <button type="button" class="button ic-copy-code" data-target="cron-avif">
                                📋 Copier
                            </button>
                            <pre id="cron-avif">curl -k "<?php echo esc_url($cron_urls['avif']); ?>"</pre>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="ic-cron-frequencies">
            <h3>⏱️ Autres fréquences possibles</h3>
            <table class="widefat">
                <thead>
                    <tr>
                        <th>Fréquence</th>
                        <th>Syntaxe cron</th>
                        <th>Description</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><strong>Toutes les 30 minutes</strong></td>
                        <td><code>*/30 * * * *</code></td>
                        <td>Conversion rapide, mais charge serveur plus élevée</td>
                    </tr>
                    <tr>
                        <td><strong>Toutes les heures</strong></td>
                        <td><code>0 * * * *</code></td>
                        <td>Bon équilibre (recommandé)</td>
                    </tr>
                    <tr>
                        <td><strong>Toutes les 2 heures</strong></td>
                        <td><code>0 */2 * * *</code></td>
                        <td>Conversion plus lente mais charge minimale</td>
                    </tr>
                    <tr>
                        <td><strong>2 fois par jour</strong></td>
                        <td><code>0 2,14 * * *</code></td>
                        <td>À 2h et 14h - Pour sites peu actifs</td>
                    </tr>
                    <tr>
                        <td><strong>Une fois par jour</strong></td>
                        <td><code>0 3 * * *</code></td>
                        <td>À 3h du matin - Pour maintenance régulière</td>
                    </tr>
                </tbody>
            </table>
        </div>
        
        <div class="ic-cron-test">
            <h3>🧪 Tester les URLs de cron</h3>
            <p>
                Vous pouvez tester que les URLs fonctionnent en les appelant manuellement :
            </p>
            
            <div class="ic-test-urls">
                <div class="ic-test-url">
                    <strong>WebP :</strong><br>
                    <a href="<?php echo esc_url($cron_urls['webp']); ?>" target="_blank" class="button">
                        🧪 Tester WebP
                    </a>
                    <button type="button" class="button ic-copy-code-inline" data-text="<?php echo esc_url($cron_urls['webp']); ?>">
                        📋 Copier l'URL
                    </button>
                </div>
                
                <div class="ic-test-url">
                    <strong>AVIF :</strong><br>
                    <a href="<?php echo esc_url($cron_urls['avif']); ?>" target="_blank" class="button">
                        🧪 Tester AVIF
                    </a>
                    <button type="button" class="button ic-copy-code-inline" data-text="<?php echo esc_url($cron_urls['avif']); ?>">
                        📋 Copier l'URL
                    </button>
                </div>
            </div>
            
            <p class="ic-note">
                <strong>Note :</strong> Cliquer sur "Tester" lancera immédiatement une conversion de 50 images.
                Les URLs contiennent un nonce de sécurité qui est régénéré régulièrement.
            </p>
        </div>
    </div>
    
    <!-- Dépannage -->
    <div class="ic-config-section">
        <h2>🔧 Dépannage</h2>
        
        <div class="ic-troubleshooting">
            <div class="ic-trouble-item">
                <h3>❌ Erreur 500 après modification du .htaccess</h3>
                <p><strong>Solution :</strong></p>
                <ol>
                    <li>Restaurez votre sauvegarde : <code>mv .htaccess.backup .htaccess</code></li>
                    <li>Vérifiez que mod_rewrite et mod_headers sont activés</li>
                    <li>Contactez votre hébergeur si nécessaire</li>
                </ol>
            </div>
            
            <div class="ic-trouble-item">
                <h3>❌ Les images WebP/AVIF ne sont pas servies</h3>
                <p><strong>Vérifications :</strong></p>
                <ol>
                    <li>Les fichiers .webp et .avif existent-ils à côté des originaux ?</li>
                    <li>Le code .htaccess est-il bien placé AVANT # BEGIN WordPress ?</li>
                    <li>Testez avec Chrome DevTools (F12 → Network → Type)</li>
                    <li>Videz le cache de votre navigateur et CDN</li>
                </ol>
            </div>
            
            <div class="ic-trouble-item">
                <h3>❌ Les tâches cron ne s'exécutent pas</h3>
                <p><strong>Vérifications :</strong></p>
                <ol>
                    <li>Testez les URLs manuellement en cliquant sur les boutons ci-dessus</li>
                    <li>Vérifiez que curl est installé : <code>curl --version</code></li>
                    <li>Consultez les logs cron : <code>grep CRON /var/log/syslog</code></li>
                    <li>Vérifiez les permissions d'exécution de la crontab</li>
                </ol>
            </div>
            
            <div class="ic-trouble-item">
                <h3>❓ Comment savoir si ça fonctionne ?</h3>
                <p><strong>Méthode :</strong></p>
                <ol>
                    <li>Allez dans <a href="<?php echo admin_url('admin.php?page=image-converter'); ?>">Statistiques</a></li>
                    <li>Regardez le nombre d'images converties augmenter</li>
                    <li>Vérifiez les fichiers .webp/.avif dans wp-content/uploads</li>
                    <li>Testez dans Chrome DevTools (Network → voir le type d'image chargée)</li>
                </ol>
            </div>
        </div>
    </div>
</div>