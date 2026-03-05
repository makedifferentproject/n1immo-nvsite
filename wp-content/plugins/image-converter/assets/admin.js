/**
 * Fichier: assets/admin.js
 * JavaScript pour le backoffice du plugin Image Converter
 */

(function($) {
    'use strict';
    
    const ImageConverter = {
        isProcessing: false,
        currentFormat: null,
        
        init: function() {
            this.bindEvents();
            this.updateQualitySliders();
        },
        
        bindEvents: function() {
            // Boutons de conversion
            $('.ic-convert-btn').on('click', this.startConversion.bind(this));
            
            // Boutons de réinitialisation
            $('.ic-reset-btn').on('click', this.resetProgress.bind(this));
            
            // Bouton d'actualisation des stats
            $('.ic-refresh-stats').on('click', this.refreshStats.bind(this));
            
            // Sliders de qualité
            $('.ic-quality-slider').on('input', this.updateQualityDisplay);
            
            // Bouton de copie .htaccess
            $('.ic-copy-htaccess').on('click', this.copyHtaccess);
        },
        
        /**
         * Démarrer la conversion
         */
        startConversion: function(e) {
            e.preventDefault();
            
            if (this.isProcessing) {
                this.showNotice('Une conversion est déjà en cours', 'warning');
                return;
            }
            
            const $button = $(e.currentTarget);
            const format = $button.data('format');
            
            if (!confirm(`Voulez-vous lancer la conversion en ${format.toUpperCase()} ?`)) {
                return;
            }
            
            this.currentFormat = format;
            this.isProcessing = true;
            
            $button.addClass('processing').prop('disabled', true);
            $button.html('<span class="ic-loading"></span> Conversion en cours...');
            
            this.clearLog();
            this.addLog(`Démarrage de la conversion ${format.toUpperCase()}...`, 'processing');
            
            // Lancer la conversion en boucle
            this.processNextBatch(format, $button);
        },
        
        /**
         * Traiter le prochain lot d'images
         */
        processNextBatch: function(format, $button) {
            const action = format === 'webp' ? 'ic_convert_webp' : 'ic_convert_avif';
            
            $.ajax({
                url: imageConverterData.ajax_url,
                type: 'POST',
                data: {
                    action: action,
                    nonce: imageConverterData.nonce
                },
                success: (response) => {
                    if (response.success) {
                        const data = response.data;
                        
                        // Afficher les résultats
                        if (data.converted > 0) {
                            this.addLog(`✓ ${data.converted} image(s) convertie(s)`, 'success');
                        }
                        
                        if (data.errors > 0) {
                            this.addLog(`✗ ${data.errors} erreur(s)`, 'error');
                        }
                        
                        if (data.skipped > 0) {
                            this.addLog(`⊘ ${data.skipped} image(s) ignorée(s)`, 'processing');
                        }
                        
                        // Actualiser les statistiques
                        this.refreshStats();
                        
                        // Si des images restent, continuer
                        if (data.remaining > 0 && (data.converted > 0 || data.skipped > 0)) {
                            this.addLog(`${data.remaining} image(s) restante(s)...`, 'processing');
                            setTimeout(() => {
                                this.processNextBatch(format, $button);
                            }, 1000);
                        } else {
                            // Conversion terminée
                            this.isProcessing = false;
                            $button.removeClass('processing').prop('disabled', false);
                            $button.html(`Convertir en ${format.toUpperCase()}`);
                            
                            if (data.remaining === 0) {
                                this.addLog('🎉 Conversion terminée !', 'success');
                                this.showNotice('Conversion terminée avec succès !', 'success');
                            } else {
                                this.addLog('⏸ Conversion interrompue', 'processing');
                                this.showNotice('Conversion interrompue. Relancez pour continuer.', 'info');
                            }
                        }
                    } else {
                        this.handleError('Erreur lors de la conversion', $button, format);
                    }
                },
                error: () => {
                    this.handleError('Erreur de communication avec le serveur', $button, format);
                }
            });
        },
        
        /**
         * Gérer les erreurs
         */
        handleError: function(message, $button, format) {
            this.isProcessing = false;
            $button.removeClass('processing').prop('disabled', false);
            $button.html(`Convertir en ${format.toUpperCase()}`);
            this.addLog(`✗ ${message}`, 'error');
            this.showNotice(message, 'error');
        },
        
        /**
         * Réinitialiser la progression
         */
        resetProgress: function(e) {
            e.preventDefault();
            
            const $button = $(e.currentTarget);
            const format = $button.data('format');
            
            let message = 'Voulez-vous vraiment réinitialiser la progression ';
            if (format === 'all') {
                message += 'pour WebP ET AVIF ?';
            } else {
                message += `pour ${format.toUpperCase()} ?`;
            }
            
            if (!confirm(message + '\n\nCette action ne supprime pas les fichiers convertis.')) {
                return;
            }
            
            $button.addClass('processing').prop('disabled', true);
            
            $.ajax({
                url: imageConverterData.ajax_url,
                type: 'POST',
                data: {
                    action: 'ic_reset_progress',
                    nonce: imageConverterData.nonce,
                    format: format
                },
                success: (response) => {
                    if (response.success) {
                        this.showNotice('Progression réinitialisée', 'success');
                        this.refreshStats();
                    } else {
                        this.showNotice('Erreur lors de la réinitialisation', 'error');
                    }
                    $button.removeClass('processing').prop('disabled', false);
                },
                error: () => {
                    this.showNotice('Erreur de communication', 'error');
                    $button.removeClass('processing').prop('disabled', false);
                }
            });
        },
        
        /**
         * Actualiser les statistiques
         */
        refreshStats: function() {
            $.ajax({
                url: imageConverterData.ajax_url,
                type: 'POST',
                data: {
                    action: 'ic_get_stats',
                    nonce: imageConverterData.nonce
                },
                success: (response) => {
                    if (response.success) {
                        // Recharger la page pour actualiser les stats
                        // (plus simple que de mettre à jour tous les éléments)
                        if (!ImageConverter.isProcessing) {
                            location.reload();
                        }
                    }
                }
            });
        },
        
        /**
         * Afficher une notification
         */
        showNotice: function(message, type) {
            const $notice = $('<div>')
                .addClass(`ic-notice ic-notice-${type}`)
                .text(message)
                .hide()
                .prependTo('.image-converter-wrap')
                .slideDown();
            
            setTimeout(() => {
                $notice.slideUp(() => {
                    $notice.remove();
                });
            }, 5000);
        },
        
        /**
         * Ajouter une ligne au log
         */
        addLog: function(message, type) {
            const $log = $('#ic-log-container');
            
            // Supprimer le message vide
            $log.find('.ic-log-empty').remove();
            
            const timestamp = new Date().toLocaleTimeString();
            const $item = $('<div>')
                .addClass(`ic-log-item ic-log-${type}`)
                .html(`<strong>[${timestamp}]</strong> ${message}`);
            
            $log.append($item);
            
            // Scroller vers le bas
            $log.scrollTop($log[0].scrollHeight);
        },
        
        /**
         * Vider le log
         */
        clearLog: function() {
            $('#ic-log-container').html('<p class="ic-log-empty">Démarrage...</p>');
        },
        
        /**
         * Mettre à jour l'affichage des sliders de qualité
         */
        updateQualityDisplay: function() {
            const $slider = $(this);
            const value = $slider.val();
            $slider.next('.ic-quality-value').text(value);
        },
        
        /**
         * Mettre à jour tous les sliders au chargement
         */
        updateQualitySliders: function() {
            $('.ic-quality-slider').each(function() {
                const $slider = $(this);
                const value = $slider.val();
                $slider.next('.ic-quality-value').text(value);
            });
        },
        
        /**
         * Copier le code .htaccess
         */
        copyHtaccess: function() {
            const code = $('#ic-htaccess-code').text();
            
            // Créer un élément temporaire
            const $temp = $('<textarea>')
                .val(code)
                .appendTo('body')
                .select();
            
            try {
                document.execCommand('copy');
                $(this).text('✓ Copié !');
                
                setTimeout(() => {
                    $(this).text('📋 Copier le code');
                }, 2000);
            } catch (err) {
                alert('Erreur lors de la copie. Veuillez copier manuellement.');
            }
            
            $temp.remove();
        }
    };
    
    // Initialiser au chargement du DOM
    $(document).ready(() => {
        ImageConverter.init();
        
        // Bouton de copie .htaccess dans la page de compatibilité
        $('.ic-copy-htaccess-compat').on('click', function() {
            const code = $('#ic-htaccess-code-compat').text();
            
            const $temp = $('<textarea>')
                .val(code)
                .appendTo('body')
                .select();
            
            try {
                document.execCommand('copy');
                $(this).text('✓ Copié !');
                
                setTimeout(() => {
                    $(this).text('📋 Copier le code');
                }, 2000);
            } catch (err) {
                alert('Erreur lors de la copie. Veuillez copier manuellement.');
            }
            
            $temp.remove();
        });
        
        // Boutons de copie génériques dans la page de configuration
        $('.ic-copy-code').on('click', function() {
            const targetId = $(this).data('target');
            const code = $('#' + targetId).text();
            
            const $temp = $('<textarea>')
                .val(code)
                .appendTo('body')
                .select();
            
            try {
                document.execCommand('copy');
                $(this).text('✓ Copié !');
                
                setTimeout(() => {
                    $(this).text('📋 Copier le code');
                }, 2000);
            } catch (err) {
                alert('Erreur lors de la copie. Veuillez copier manuellement.');
            }
            
            $temp.remove();
        });
        
        // Boutons de copie inline (pour les URLs)
        $('.ic-copy-code-inline').on('click', function() {
            const text = $(this).data('text');
            
            const $temp = $('<textarea>')
                .val(text)
                .appendTo('body')
                .select();
            
            try {
                document.execCommand('copy');
                const originalText = $(this).text();
                $(this).text('✓ Copié !');
                
                setTimeout(() => {
                    $(this).text(originalText);
                }, 2000);
            } catch (err) {
                alert('Erreur lors de la copie. Veuillez copier manuellement.');
            }
            
            $temp.remove();
        });
    });
    
})(jQuery);