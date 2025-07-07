/**
 * Frontend JavaScript for AI Share Buttons
 */

(function($) {
    'use strict';
    
    var aiShareButtons = {
        
        init: function() {
            this.bindEvents();
            this.setupMobileLayout();
        },
        
        bindEvents: function() {
            var self = this;
            
            // Handle AI prompt selection
            $(document).on('click', '.ai-prompt-dropdown-item', function(e) {
                e.preventDefault();
                self.handlePromptClick($(this));
            });
            
            // Handle share button clicks for tracking
            $(document).on('click', '.ai-share-button-link', function(e) {
                var $button = $(this).closest('.ai-share-button');
                var networkId = $button.data('network');
                var networkType = $button.data('type');
                
                // Track click
                self.trackClick(networkId, null);
                
                // Handle special cases
                if (networkId === 'copy') {
                    e.preventDefault();
                    self.copyLink(this);
                    return false;
                } else if (networkId === 'print') {
                    // Print is handled by onclick attribute
                    return;
                } else if (networkType === 'ai' && $button.hasClass('has-dropdown')) {
                    // AI with dropdown - prevent default to show dropdown
                    e.preventDefault();
                    return false;
                }
            });
            
            // Mobile dropdown handling
            if (self.isMobile()) {
                $(document).on('click', '.ai-share-button.has-dropdown .ai-share-button-link', function(e) {
                    e.preventDefault();
                    var $button = $(this).closest('.ai-share-button');
                    self.toggleMobileDropdown($button);
                });
                
                // Close dropdown on outside click
                $(document).on('click', function(e) {
                    if (!$(e.target).closest('.ai-share-button').length) {
                        $('.ai-prompt-dropdown').hide();
                    }
                });
            }
        },
        
        handlePromptClick: function($item) {
            var promptId = $item.data('prompt-id');
            var promptText = $item.data('prompt-text');
            var urlTemplate = $item.data('url-template');
            var $button = $item.closest('.ai-share-button');
            var networkId = $button.data('network');
            
            // Process the URL
            var url = this.buildShareUrl(urlTemplate, promptText);
            
            // Track click
            this.trackClick(networkId, promptId);
            
            // Open in new window
            window.open(url, '_blank', 'width=600,height=400');
            
            // Close dropdown on mobile
            if (this.isMobile()) {
                $item.closest('.ai-prompt-dropdown').hide();
            }
        },
        
        buildShareUrl: function(template, promptText) {
            // Process template variables
            var url = template;
            
            // Get current post data from page
            var postUrl = window.location.href;
            var postTitle = document.title;
            
            // Replace variables
            url = url.replace(/{POST_URL}/g, postUrl);
            url = url.replace(/{POST_TITLE}/g, postTitle);
            url = url.replace(/{ENCODED_URL}/g, encodeURIComponent(postUrl));
            url = url.replace(/{ENCODED_TITLE}/g, encodeURIComponent(postTitle));
            
            // Handle prompt
            if (promptText && url.indexOf('{encoded_prompt}') !== -1) {
                // Process prompt text
                promptText = promptText.replace(/{POST_URL}/g, postUrl);
                promptText = promptText.replace(/{POST_TITLE}/g, postTitle);
                promptText = promptText.replace(/{ENCODED_URL}/g, encodeURIComponent(postUrl));
                promptText = promptText.replace(/{ENCODED_TITLE}/g, encodeURIComponent(postTitle));
                
                // Encode and replace
                url = url.replace(/{encoded_prompt}/g, encodeURIComponent(promptText));
            }
            
            return url;
        },
        
        copyLink: function(button) {
            var self = this;
            var url = window.location.href;
            
            // Try modern clipboard API first
            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(url).then(function() {
                    self.showCopyMessage(true);
                }).catch(function() {
                    self.fallbackCopy(url);
                });
            } else {
                self.fallbackCopy(url);
            }
        },
        
        fallbackCopy: function(text) {
            var self = this;
            var $temp = $('<textarea>');
            $('body').append($temp);
            $temp.val(text).select();
            
            try {
                var successful = document.execCommand('copy');
                self.showCopyMessage(successful);
            } catch (err) {
                self.showCopyMessage(false);
            }
            
            $temp.remove();
        },
        
        showCopyMessage: function(success) {
            var message = success ? aiShareButtonsFront.strings.copySuccess : aiShareButtonsFront.strings.copyError;
            
            // Remove existing message
            $('.ai-share-copy-message').remove();
            
            // Create and show new message
            var $message = $('<div class="ai-share-copy-message">' + message + '</div>');
            $('body').append($message);
            
            setTimeout(function() {
                $message.addClass('show');
            }, 10);
            
            setTimeout(function() {
                $message.removeClass('show');
                setTimeout(function() {
                    $message.remove();
                }, 300);
            }, 2000);
        },
        
        trackClick: function(networkId, promptId) {
            if (!aiShareButtonsFront.ajaxUrl) {
                return;
            }
            
            $.ajax({
                url: aiShareButtonsFront.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'ai_share_track_click',
                    nonce: aiShareButtonsFront.nonce,
                    post_id: aiShareButtonsFront.postId,
                    service_id: networkId,
                    prompt_id: promptId || ''
                },
                dataType: 'json'
            });
        },
        
        setupMobileLayout: function() {
            if (!this.isMobile()) {
                return;
            }
            
            var $container = $('.ai-share-buttons');
            var layout = $container.data('mobile-layout') || 'horizontal_scroll';
            
            if (layout === 'horizontal_scroll') {
                $container.addClass('mobile-horizontal-scroll');
            } else if (layout === 'stack') {
                $container.addClass('mobile-stack');
            }
        },
        
        toggleMobileDropdown: function($button) {
            var $dropdown = $button.find('.ai-prompt-dropdown');
            
            // Hide other dropdowns
            $('.ai-prompt-dropdown').not($dropdown).hide();
            
            // Toggle this dropdown
            $dropdown.toggle();
            
            // Position at bottom of screen on mobile
            if ($dropdown.is(':visible') && this.isMobile()) {
                $dropdown.css({
                    position: 'fixed',
                    bottom: 0,
                    left: 0,
                    right: 0,
                    top: 'auto'
                });
            }
        },
        
        isMobile: function() {
            return window.innerWidth <= 768;
        }
    };
    
    // Make copyLink available globally
    window.aiShareButtons = {
        copyLink: function(button) {
            aiShareButtons.copyLink(button);
            return false;
        }
    };
    
    // Initialize when DOM is ready
    $(document).ready(function() {
        aiShareButtons.init();
    });
    
})(jQuery);