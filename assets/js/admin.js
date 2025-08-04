/**
 * JavaScript pour l'interface d'administration Whise Integration
 */

jQuery(document).ready(function($) {
    
    // Gestion des états de chargement
    function setLoadingState(element, loading) {
        if (loading) {
            element.addClass('whise-loading');
            element.prop('disabled', true);
        } else {
            element.removeClass('whise-loading');
            element.prop('disabled', false);
        }
    }
    
    // Gestion des formulaires de synchronisation
    $('form[action*="whise_manual_sync"]').on('submit', function() {
        var button = $(this).find('button[type="submit"]');
        var originalText = button.text();
        
        setLoadingState(button, true);
        button.text('Synchronisation en cours...');
        
        // Timeout de sécurité (5 minutes)
        setTimeout(function() {
            setLoadingState(button, false);
            button.text(originalText);
        }, 300000);
    });
    
    // Gestion du test de connexion
    $('form[action*="whise_test_connection"]').on('submit', function() {
        var button = $(this).find('button[type="submit"]');
        var originalText = button.text();
        
        setLoadingState(button, true);
        button.text('Test en cours...');
        
        // Timeout de sécurité (30 secondes)
        setTimeout(function() {
            setLoadingState(button, false);
            button.text(originalText);
        }, 30000);
    });
    
    // Animation des statistiques
    function animateStats() {
        $('.stat-box h3, .stat-card h3').each(function() {
            var $this = $(this);
            var countTo = parseInt($this.text());
            
            $({ countNum: 0 }).animate({
                countNum: countTo
            }, {
                duration: 1000,
                easing: 'swing',
                step: function() {
                    $this.text(Math.floor(this.countNum));
                },
                complete: function() {
                    $this.text(countTo);
                }
            });
        });
    }
    
    // Lancer l'animation des stats au chargement
    animateStats();
    
    // Gestion de l'affichage/masquage des logs
    $('.log-controls button').on('click', function() {
        var logsContainer = $('#whise-logs');
        var isVisible = logsContainer.is(':visible');
        
        if (isVisible) {
            logsContainer.slideUp(300);
            $(this).text('Afficher les logs');
        } else {
            logsContainer.slideDown(300);
            $(this).text('Masquer les logs');
        }
    });
    
    // Auto-refresh des logs (optionnel)
    var autoRefreshLogs = false;
    var logsRefreshInterval;
    
    function toggleLogsAutoRefresh() {
        if (autoRefreshLogs) {
            clearInterval(logsRefreshInterval);
            autoRefreshLogs = false;
            $('#toggle-logs-refresh').text('Activer auto-refresh');
        } else {
            logsRefreshInterval = setInterval(function() {
                refreshLogs();
            }, 10000); // Refresh toutes les 10 secondes
            autoRefreshLogs = true;
            $('#toggle-logs-refresh').text('Désactiver auto-refresh');
        }
    }
    
    function refreshLogs() {
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'whise_get_logs',
                nonce: whise_ajax.nonce
            },
            success: function(response) {
                if (response.success) {
                    $('#whise-logs ul').html(response.data.logs);
                }
            }
        });
    }
    
    // Ajouter le bouton auto-refresh si nécessaire
    if ($('.log-controls').length) {
        $('.log-controls').append(
            '<button type="button" id="toggle-logs-refresh" class="button">Activer auto-refresh</button>'
        );
        
        $('#toggle-logs-refresh').on('click', toggleLogsAutoRefresh);
    }
    
    // Validation des champs API
    $('input[name="whise_api_username"], input[name="whise_api_password"], input[name="whise_client_id"]').on('blur', function() {
        var field = $(this);
        var value = field.val().trim();
        
        if (value === '') {
            field.addClass('error');
            field.after('<span class="error-message">Ce champ est requis</span>');
        } else {
            field.removeClass('error');
            field.siblings('.error-message').remove();
        }
    });
    
    // Validation du formulaire avant soumission
    $('form[action="options.php"]').on('submit', function(e) {
        var hasErrors = false;
        
        // Vérifier les champs requis
        $('input[name="whise_api_username"], input[name="whise_api_password"], input[name="whise_client_id"]').each(function() {
            var field = $(this);
            var value = field.val().trim();
            
            if (value === '') {
                field.addClass('error');
                if (!field.siblings('.error-message').length) {
                    field.after('<span class="error-message">Ce champ est requis</span>');
                }
                hasErrors = true;
            }
        });
        
        if (hasErrors) {
            e.preventDefault();
            $('html, body').animate({
                scrollTop: $('.error').first().offset().top - 100
            }, 500);
            
            // Afficher un message d'erreur
            if (!$('.whise-form-error').length) {
                $('.wrap h1').after(
                    '<div class="notice notice-error whise-form-error"><p>Veuillez corriger les erreurs dans le formulaire.</p></div>'
                );
            }
        }
    });
    
    // Tooltips pour les champs
    $('.form-table th').each(function() {
        var title = $(this).text();
        var helpText = getHelpText(title);
        
        if (helpText) {
            $(this).append(
                '<span class="dashicons dashicons-editor-help" title="' + helpText + '" style="margin-left: 5px; color: #666; cursor: help;"></span>'
            );
        }
    });
    
    function getHelpText(fieldTitle) {
        var helpTexts = {
            'Username Marketplace': 'Nom d\'utilisateur de votre compte Whise Marketplace',
            'Password Marketplace': 'Mot de passe de votre compte Whise Marketplace',
            'Client ID': 'Identifiant du client fourni par Whise',
            'Office ID': 'Identifiant du bureau (optionnel)',
            'Endpoint API Whise': 'URL de l\'API Whise (ne pas modifier sauf instruction)',
            'Fréquence de synchronisation': 'Fréquence de mise à jour automatique des propriétés',
            'Mode debug': 'Active des logs détaillés pour le diagnostic'
        };
        
        return helpTexts[fieldTitle] || null;
    }
    
    // Gestion des onglets (si implémenté plus tard)
    $('.whise-tabs-nav a').on('click', function(e) {
        e.preventDefault();
        
        var target = $(this).attr('href');
        
        // Masquer tous les contenus
        $('.whise-tab-content').hide();
        
        // Afficher le contenu cible
        $(target).show();
        
        // Mettre à jour la navigation
        $('.whise-tabs-nav a').removeClass('active');
        $(this).addClass('active');
    });
    
    // Export des données (fonctionnalité future)
    $('#export-properties').on('click', function(e) {
        e.preventDefault();
        
        if (confirm('Voulez-vous exporter toutes les propriétés au format CSV ?')) {
            var button = $(this);
            setLoadingState(button, true);
            button.text('Export en cours...');
            
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'whise_export_properties',
                    nonce: whise_ajax.nonce
                },
                success: function(response) {
                    if (response.success) {
                        // Créer un lien de téléchargement
                        var link = document.createElement('a');
                        link.href = 'data:text/csv;charset=utf-8,' + encodeURIComponent(response.data.csv);
                        link.download = 'whise-properties-' + new Date().toISOString().split('T')[0] + '.csv';
                        link.click();
                    } else {
                        alert('Erreur lors de l\'export : ' + response.data.message);
                    }
                },
                error: function() {
                    alert('Erreur lors de l\'export');
                },
                complete: function() {
                    setLoadingState(button, false);
                    button.text('Exporter les propriétés');
                }
            });
        }
    });
    
    // Notifications toast (optionnel)
    function showToast(message, type) {
        var toast = $('<div class="whise-toast whise-toast-' + type + '">' + message + '</div>');
        $('body').append(toast);
        
        toast.fadeIn(300).delay(3000).fadeOut(300, function() {
            $(this).remove();
        });
    }
    
    // Gestion des erreurs AJAX globales
    $(document).ajaxError(function(event, xhr, settings, error) {
        console.error('Erreur AJAX Whise:', error);
        showToast('Erreur de communication avec le serveur', 'error');
    });
    
    // Initialisation des tooltips
    if (typeof $.fn.tooltip !== 'undefined') {
        $('[title]').tooltip();
    }
    
    // Gestion du responsive
    function handleResponsive() {
        if ($(window).width() < 768) {
            $('.whise-actions').addClass('mobile');
        } else {
            $('.whise-actions').removeClass('mobile');
        }
    }
    
    $(window).on('resize', handleResponsive);
    handleResponsive();
    
    // Sauvegarde automatique des paramètres (optionnel)
    var saveTimeout;
    $('form[action="options.php"] input, form[action="options.php"] select').on('change', function() {
        clearTimeout(saveTimeout);
        
        saveTimeout = setTimeout(function() {
            showToast('Sauvegarde automatique...', 'info');
            
            $('form[action="options.php"]').submit();
        }, 2000);
    });
    
    // Confirmation pour les actions destructives
    $('button[onclick*="confirm"]').off('click').on('click', function(e) {
        var confirmMessage = $(this).attr('data-confirm') || 'Êtes-vous sûr de vouloir effectuer cette action ?';
        
        if (!confirm(confirmMessage)) {
            e.preventDefault();
            return false;
        }
    });
    
    // Amélioration de l'UX : focus sur le premier champ vide
    $('form[action="options.php"]').on('submit', function() {
        var firstEmptyField = $('input[required], input[name*="whise_api"]').filter(function() {
            return $(this).val().trim() === '';
        }).first();
        
        if (firstEmptyField.length) {
            firstEmptyField.focus();
        }
    });
    
    // Gestion des raccourcis clavier
    $(document).on('keydown', function(e) {
        // Ctrl/Cmd + S pour sauvegarder
        if ((e.ctrlKey || e.metaKey) && e.keyCode === 83) {
            e.preventDefault();
            $('form[action="options.php"]').submit();
        }
        
        // Ctrl/Cmd + R pour synchroniser
        if ((e.ctrlKey || e.metaKey) && e.keyCode === 82) {
            e.preventDefault();
            $('form[action*="whise_manual_sync"]').submit();
        }
    });
    
    // Indicateur de modification
    var originalFormData = $('form[action="options.php"]').serialize();
    
    $('form[action="options.php"]').on('change', function() {
        var currentFormData = $(this).serialize();
        
        if (currentFormData !== originalFormData) {
            $('.wrap h1').append('<span class="whise-badge whise-badge-warning" style="margin-left: 10px;">Modifications non sauvegardées</span>');
        } else {
            $('.whise-badge-warning').remove();
        }
    });
    
    // Sauvegarde automatique des modifications
    $('form[action="options.php"]').on('submit', function() {
        originalFormData = $(this).serialize();
        $('.whise-badge-warning').remove();
    });
    
    console.log('Whise Integration Admin JS chargé');
});
