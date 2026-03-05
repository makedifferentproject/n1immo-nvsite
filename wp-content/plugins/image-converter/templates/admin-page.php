<?php
if (!defined('ABSPATH')) exit;
?>

<div class="wrap image-converter-wrap">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
    
    <div class="ic-dashboard">
        <!-- Statistiques Globales -->
        <div class="ic-stats-grid">
            <div class="ic-stat-card ic-webp">
                <h2>🎨 Conversion WebP</h2>
                <div class="ic-stat-number"><?php echo number_format($stats['webp']['converted']); ?></div>
                <div class="ic-stat-label">Images converties</div>
                <div class="ic-progress-bar">
                    <?php 
                    $webp_percent = $stats['webp']['total_images'] > 0 
                        ? ($stats['webp']['converted'] / $stats['webp']['total_images']) * 100 
                        : 0;
                    ?>
                    <div class="ic-progress-fill" style="width: <?php echo $webp_percent; ?>%"></div>
                </div>
                <div class="ic-stat-details">
                    <span>Restantes: <strong><?php echo number_format($stats['webp']['remaining']); ?></strong></span>
                    <span>Erreurs: <strong class="ic-error"><?php echo number_format($stats['webp']['errors']); ?></strong></span>
                </div>
                <button class="button button-primary ic-convert-btn" data-format="webp">
                    Convertir en WebP
                </button>
            </div>
            
            <div class="ic-stat-card ic-avif">
                <h2>🚀 Conversion AVIF</h2>
                <div class="ic-stat-number"><?php echo number_format($stats['avif']['converted']); ?></div>
                <div class="ic-stat-label">Images converties</div>
                <div class="ic-progress-bar">
                    <?php 
                    $avif_percent = $stats['avif']['total_images'] > 0 
                        ? ($stats['avif']['converted'] / $stats['avif']['total_images']) * 100 
                        : 0;
                    ?>
                    <div class="ic-progress-fill ic-avif-fill" style="width: <?php echo $avif_percent; ?>%"></div>
                </div>
                <div class="ic-stat-details">
                    <span>Restantes: <strong><?php echo number_format($stats['avif']['remaining']); ?></strong></span>
                    <span>Erreurs: <strong class="ic-error"><?php echo number_format($stats['avif']['errors']); ?></strong></span>
                </div>
                <button class="button button-primary ic-convert-btn" data-format="avif">
                    Convertir en AVIF
                </button>
            </div>
        </div>
        
        <!-- Statistiques par dossier -->
        <div class="ic-folder-stats">
            <h2>📊 Statistiques par dossier</h2>
            <table class="widefat ic-stats-table">
                <thead>
                    <tr>
                        <th>Dossier</th>
                        <th>Total Images</th>
                        <th>WebP</th>
                        <th>AVIF</th>
                        <th>Progression</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><strong>📤 Uploads</strong></td>
                        <td><?php echo number_format($stats['folders']['uploads']['total']); ?></td>
                        <td><?php echo number_format($stats['folders']['uploads']['webp_converted']); ?></td>
                        <td><?php echo number_format($stats['folders']['uploads']['avif_converted']); ?></td>
                        <td>
                            <?php 
                            $uploads_percent = $stats['folders']['uploads']['total'] > 0
                                ? (($stats['folders']['uploads']['webp_converted'] + $stats['folders']['uploads']['avif_converted']) 
                                   / ($stats['folders']['uploads']['total'] * 2)) * 100
                                : 0;
                            ?>
                            <div class="ic-mini-progress">
                                <div class="ic-mini-progress-fill" style="width: <?php echo $uploads_percent; ?>%"></div>
                            </div>
                            <span><?php echo number_format($uploads_percent, 1); ?>%</span>
                        </td>
                    </tr>
                    <tr>
                        <td><strong>🎨 Themes</strong></td>
                        <td><?php echo number_format($stats['folders']['themes']['total']); ?></td>
                        <td><?php echo number_format($stats['folders']['themes']['webp_converted']); ?></td>
                        <td><?php echo number_format($stats['folders']['themes']['avif_converted']); ?></td>
                        <td>
                            <?php 
                            $themes_percent = $stats['folders']['themes']['total'] > 0
                                ? (($stats['folders']['themes']['webp_converted'] + $stats['folders']['themes']['avif_converted']) 
                                   / ($stats['folders']['themes']['total'] * 2)) * 100
                                : 0;
                            ?>
                            <div class="ic-mini-progress">
                                <div class="ic-mini-progress-fill" style="width: <?php echo $themes_percent; ?>%"></div>
                            </div>
                            <span><?php echo number_format($themes_percent, 1); ?>%</span>
                        </td>
                    </tr>
                    <tr>
                        <td><strong>🔌 Plugins</strong></td>
                        <td><?php echo number_format($stats['folders']['plugins']['total']); ?></td>
                        <td><?php echo number_format($stats['folders']['plugins']['webp_converted']); ?></td>
                        <td><?php echo number_format($stats['folders']['plugins']['avif_converted']); ?></td>
                        <td>
                            <?php 
                            $plugins_percent = $stats['folders']['plugins']['total'] > 0
                                ? (($stats['folders']['plugins']['webp_converted'] + $stats['folders']['plugins']['avif_converted']) 
                                   / ($stats['folders']['plugins']['total'] * 2)) * 100
                                : 0;
                            ?>
                            <div class="ic-mini-progress">
                                <div class="ic-mini-progress-fill" style="width: <?php echo $plugins_percent; ?>%"></div>
                            </div>
                            <span><?php echo number_format($plugins_percent, 1); ?>%</span>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        
        <!-- Actions rapides -->
        <div class="ic-quick-actions">
            <h2>⚡ Actions rapides</h2>
            <div class="ic-actions-grid">
                <button class="button ic-reset-btn" data-format="webp">
                    🔄 Réinitialiser WebP
                </button>
                <button class="button ic-reset-btn" data-format="avif">
                    🔄 Réinitialiser AVIF
                </button>
                <button class="button ic-reset-btn" data-format="all">
                    🗑️ Tout réinitialiser
                </button>
                <button class="button ic-refresh-stats">
                    📊 Actualiser les stats
                </button>
            </div>
        </div>
        
        <!-- Log de conversion en temps réel -->
        <div class="ic-conversion-log">
            <h2>📝 Log de conversion</h2>
            <div id="ic-log-container" class="ic-log-content">
                <p class="ic-log-empty">Aucune conversion en cours...</p>
            </div>
        </div>
    </div>
</div>