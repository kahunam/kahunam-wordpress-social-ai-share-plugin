/**
 * Admin JavaScript for AI Share Buttons
 */

jQuery(document).ready(function($) {
    'use strict';
    
    var aiShareAdmin = {
        
        init: function() {
            this.initSortable();
            this.initColorPicker();
            this.bindEvents();
            this.initTemplateVariables();
        },
        
        initSortable: function() {
            $('#ai-share-networks-list').sortable({
                handle: '.network-drag-handle',
                axis: 'y',
                update: function(event, ui) {
                    aiShareAdmin.saveNetworkOrder();
                }
            });
            
            $('#ai-share-prompts-list').sortable({
                handle: '.prompt-drag-handle',
                axis: 'y',
                update: function(event, ui) {
                    aiShareAdmin.savePromptOrder();
                }
            });
        },
        
        initColorPicker: function() {
            $('.ai-share-color-picker').wpColorPicker();
        },
        
        bindEvents: function() {
            // Network enable/disable toggle
            $(document).on('change', '.network-enable-toggle', this.toggleNetwork);
            
            // Add new network
            $('#add-new-network').on('click', this.showAddNetworkModal);
            
            // Edit network
            $(document).on('click', '.edit-network', this.showEditNetworkModal);
            
            // Delete network
            $(document).on('click', '.delete-network', this.deleteNetwork);
            
            // Save network form
            $('#save-network-form').on('submit', this.saveNetwork);
            
            // Add new prompt
            $('#add-new-prompt').on('click', this.showAddPromptModal);
            
            // Edit prompt
            $(document).on('click', '.edit-prompt', this.showEditPromptModal);
            
            // Delete prompt
            $(document).on('click', '.delete-prompt', this.deletePrompt);
            
            // Save prompt form
            $('#save-prompt-form').on('submit', this.savePrompt);
            
            // Save settings
            $('#ai-share-settings-form').on('submit', this.saveSettings);
            
            // Icon upload
            $('#upload-icon-button').on('click', this.openMediaUploader);
            
            // Hook location change
            $('#hook_location').on('change', this.toggleCustomHook);
            
            // Modal close
            $('.ai-share-modal-close').on('click', this.closeModal);
            
            // Export analytics
            $('#export-analytics').on('click', this.exportAnalytics);
            
            // Analytics date range
            $('#analytics-range').on('change', this.loadAnalytics);
        },
        
        initTemplateVariables: function() {
            $('.template-variable').on('click', function() {
                var variable = $(this).text();
                var $input = $(this).closest('.url-builder-helper').siblings('input, textarea');
                
                if ($input.length) {
                    var cursorPos = $input[0].selectionStart;
                    var currentVal = $input.val();
                    var newVal = currentVal.substring(0, cursorPos) + variable + currentVal.substring(cursorPos);
                    $input.val(newVal);
                    $input[0].setSelectionRange(cursorPos + variable.length, cursorPos + variable.length);
                    $input.focus();
                }
            });
        },
        
        toggleNetwork: function() {
            var $toggle = $(this);
            var networkId = $toggle.data('network-id');
            var enabled = $toggle.prop('checked');
            
            $toggle.closest('.ai-share-network-item').toggleClass('disabled', !enabled);
            
            // Save the change
            aiShareAdmin.saveNetworkStatus(networkId, enabled);
        },
        
        saveNetworkStatus: function(networkId, enabled) {
            $.ajax({
                url: aiShareButtons.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'ai_share_toggle_network',
                    nonce: aiShareButtons.nonce,
                    network_id: networkId,
                    enabled: enabled
                },
                success: function(response) {
                    if (!response.success) {
                        aiShareAdmin.showNotice('error', aiShareButtons.strings.saveError);
                    }
                }
            });
        },
        
        saveNetworkOrder: function() {
            var order = [];
            $('#ai-share-networks-list .ai-share-network-item').each(function() {
                order.push($(this).data('network-id'));
            });
            
            $.ajax({
                url: aiShareButtons.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'ai_share_reorder_networks',
                    nonce: aiShareButtons.nonce,
                    order: order
                },
                success: function(response) {
                    if (response.success) {
                        aiShareAdmin.showNotice('success', aiShareButtons.strings.saveSuccess);
                    } else {
                        aiShareAdmin.showNotice('error', aiShareButtons.strings.saveError);
                    }
                }
            });
        },
        
        showAddNetworkModal: function(e) {
            e.preventDefault();
            $('#network-modal-title').text('Add New Network');
            $('#network-form')[0].reset();
            $('#network-id').val('');
            $('#network-icon-preview').empty();
            $('#network-modal').show();
        },
        
        showEditNetworkModal: function(e) {
            e.preventDefault();
            var $item = $(this).closest('.ai-share-network-item');
            var networkData = $item.data('network');
            
            $('#network-modal-title').text('Edit Network');
            $('#network-id').val(networkData.id);
            $('#network-name').val(networkData.name);
            $('#network-type').val(networkData.type);
            $('#network-url-template').val(networkData.url_template);
            $('#network-default-prompt').val(networkData.default_prompt || '');
            $('#network-color').val(networkData.color).wpColorPicker('color', networkData.color);
            
            if (networkData.icon) {
                $('#network-icon-preview').html('<img src="' + networkData.icon_url + '" alt="">');
            }
            
            $('#network-modal').show();
        },
        
        deleteNetwork: function(e) {
            e.preventDefault();
            
            if (!confirm(aiShareButtons.strings.confirmDelete)) {
                return;
            }
            
            var $item = $(this).closest('.ai-share-network-item');
            var networkId = $item.data('network-id');
            
            $.ajax({
                url: aiShareButtons.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'ai_share_delete_network',
                    nonce: aiShareButtons.nonce,
                    network_id: networkId
                },
                success: function(response) {
                    if (response.success) {
                        $item.fadeOut(function() {
                            $(this).remove();
                        });
                        aiShareAdmin.showNotice('success', 'Network deleted successfully');
                    } else {
                        aiShareAdmin.showNotice('error', response.data || aiShareButtons.strings.saveError);
                    }
                }
            });
        },
        
        saveNetwork: function(e) {
            e.preventDefault();
            
            var formData = new FormData(this);
            formData.append('action', 'ai_share_save_network');
            formData.append('nonce', aiShareButtons.nonce);
            
            $.ajax({
                url: aiShareButtons.ajaxUrl,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    if (response.success) {
                        aiShareAdmin.showNotice('success', aiShareButtons.strings.saveSuccess);
                        $('#network-modal').hide();
                        // Reload the page to show updated network
                        location.reload();
                    } else {
                        aiShareAdmin.showNotice('error', response.data || aiShareButtons.strings.saveError);
                    }
                }
            });
        },
        
        showAddPromptModal: function(e) {
            e.preventDefault();
            $('#prompt-modal-title').text('Add New Prompt');
            $('#prompt-form')[0].reset();
            $('#prompt-id').val('');
            $('#prompt-modal').show();
        },
        
        showEditPromptModal: function(e) {
            e.preventDefault();
            var $item = $(this).closest('.ai-share-prompt-item');
            var promptData = $item.data('prompt');
            
            $('#prompt-modal-title').text('Edit Prompt');
            $('#prompt-id').val(promptData.id);
            $('#prompt-name').val(promptData.name);
            $('#prompt-text').val(promptData.prompt_text);
            
            // Set assigned services
            $('input[name="assigned_services[]"]').prop('checked', false);
            $.each(promptData.assigned_services, function(i, service) {
                $('input[name="assigned_services[]"][value="' + service + '"]').prop('checked', true);
            });
            
            $('#prompt-modal').show();
        },
        
        deletePrompt: function(e) {
            e.preventDefault();
            
            if (!confirm(aiShareButtons.strings.confirmDelete)) {
                return;
            }
            
            var $item = $(this).closest('.ai-share-prompt-item');
            var promptId = $item.data('prompt-id');
            
            $.ajax({
                url: aiShareButtons.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'ai_share_delete_prompt',
                    nonce: aiShareButtons.nonce,
                    prompt_id: promptId
                },
                success: function(response) {
                    if (response.success) {
                        $item.fadeOut(function() {
                            $(this).remove();
                        });
                        aiShareAdmin.showNotice('success', 'Prompt deleted successfully');
                    } else {
                        aiShareAdmin.showNotice('error', response.data || aiShareButtons.strings.saveError);
                    }
                }
            });
        },
        
        savePrompt: function(e) {
            e.preventDefault();
            
            var formData = $(this).serialize();
            
            $.ajax({
                url: aiShareButtons.ajaxUrl,
                type: 'POST',
                data: formData + '&action=ai_share_save_prompt&nonce=' + aiShareButtons.nonce,
                success: function(response) {
                    if (response.success) {
                        aiShareAdmin.showNotice('success', aiShareButtons.strings.saveSuccess);
                        $('#prompt-modal').hide();
                        // Reload the page to show updated prompt
                        location.reload();
                    } else {
                        aiShareAdmin.showNotice('error', response.data || aiShareButtons.strings.saveError);
                    }
                }
            });
        },
        
        saveSettings: function(e) {
            e.preventDefault();
            
            var $form = $(this);
            var $button = $form.find('button[type="submit"]');
            var originalText = $button.text();
            
            $button.prop('disabled', true).html(originalText + ' <span class="ai-share-spinner"></span>');
            
            $.ajax({
                url: aiShareButtons.ajaxUrl,
                type: 'POST',
                data: $form.serialize() + '&action=ai_share_save_settings&nonce=' + aiShareButtons.nonce,
                success: function(response) {
                    if (response.success) {
                        aiShareAdmin.showNotice('success', aiShareButtons.strings.saveSuccess);
                    } else {
                        aiShareAdmin.showNotice('error', aiShareButtons.strings.saveError);
                    }
                },
                complete: function() {
                    $button.prop('disabled', false).text(originalText);
                }
            });
        },
        
        openMediaUploader: function(e) {
            e.preventDefault();
            
            var mediaUploader;
            
            if (mediaUploader) {
                mediaUploader.open();
                return;
            }
            
            mediaUploader = wp.media({
                title: 'Choose Icon',
                button: {
                    text: 'Use this icon'
                },
                multiple: false,
                library: {
                    type: ['image']
                }
            });
            
            mediaUploader.on('select', function() {
                var attachment = mediaUploader.state().get('selection').first().toJSON();
                $('#network-icon').val(attachment.url);
                $('#network-icon-preview').html('<img src="' + attachment.url + '" alt="">');
            });
            
            mediaUploader.open();
        },
        
        toggleCustomHook: function() {
            var value = $(this).val();
            if (value === 'custom') {
                $('#custom-hook-row').show();
            } else {
                $('#custom-hook-row').hide();
            }
        },
        
        closeModal: function() {
            $(this).closest('.ai-share-modal').hide();
        },
        
        loadAnalytics: function() {
            var days = $('#analytics-range').val() || 30;
            
            $.ajax({
                url: aiShareButtons.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'ai_share_get_analytics',
                    nonce: aiShareButtons.nonce,
                    type: 'overview',
                    days: days
                },
                success: function(response) {
                    if (response.success) {
                        aiShareAdmin.renderAnalytics(response.data);
                    }
                }
            });
        },
        
        renderAnalytics: function(data) {
            // This would render charts and tables with the analytics data
            // For now, just console log
            console.log('Analytics data:', data);
        },
        
        exportAnalytics: function(e) {
            e.preventDefault();
            
            var format = $('#export-format').val();
            var dateStart = $('#export-date-start').val();
            var dateEnd = $('#export-date-end').val();
            
            $.ajax({
                url: aiShareButtons.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'ai_share_export_analytics',
                    nonce: aiShareButtons.nonce,
                    format: format,
                    date_start: dateStart,
                    date_end: dateEnd
                },
                success: function(response) {
                    if (response.success) {
                        // Create download link
                        var blob = new Blob([response.data.data], { type: 'text/csv' });
                        var url = window.URL.createObjectURL(blob);
                        var a = document.createElement('a');
                        a.href = url;
                        a.download = response.data.filename;
                        a.click();
                        window.URL.revokeObjectURL(url);
                    }
                }
            });
        },
        
        showNotice: function(type, message) {
            var $notice = $('<div class="ai-share-notice ' + type + '">' + message + '</div>');
            $('.ai-share-buttons-admin').prepend($notice);
            
            setTimeout(function() {
                $notice.fadeOut(function() {
                    $(this).remove();
                });
            }, 3000);
        }
    };
    
    // Initialize
    aiShareAdmin.init();
});