/**
 * AI Share Buttons - Admin JavaScript
 * Handles sortable platforms, media library picker, and custom networks
 */
(function($) {
    'use strict';

    $(document).ready(function() {
        initSortable();
        initMediaPicker();
        initLayoutOptions();
        initCustomNetworks();
    });

    /**
     * Make AI platforms sortable
     */
    function initSortable() {
        var $list = $('#kaais-ai-platforms');
        var $orderInput = $('#kaais-platform-order');

        if (!$list.length) return;

        $list.sortable({
            handle: '.drag-handle',
            axis: 'y',
            cursor: 'move',
            placeholder: 'kaais-platform-item ui-sortable-placeholder',
            update: function() {
                var order = [];
                $list.find('.kaais-platform-item').each(function() {
                    order.push($(this).data('id'));
                });
                $orderInput.val(order.join(','));
            }
        });
    }

    /**
     * Media library picker for custom network icons
     */
    function initMediaPicker() {
        $(document).on('click', '.kaais-select-icon', function(e) {
            e.preventDefault();

            var $button = $(this);
            var $row = $button.closest('.field-row');
            var $input = $row.find('.kaais-icon-url');
            var $preview = $row.find('.icon-preview');

            var frame = wp.media({
                title: kaaisAdmin.mediaTitle,
                button: { text: kaaisAdmin.mediaButton },
                multiple: false,
                library: { type: 'image' }
            });

            frame.on('select', function() {
                var attachment = frame.state().get('selection').first().toJSON();
                var url = attachment.sizes && attachment.sizes.thumbnail
                    ? attachment.sizes.thumbnail.url
                    : attachment.url;

                $input.val(url);
                $preview.html('<img src="' + url + '" alt="">');
            });

            frame.open();
        });

        // Update preview when URL is manually entered
        $(document).on('change', '.kaais-icon-url', function() {
            var $input = $(this);
            var $preview = $input.closest('.field-row').find('.icon-preview');
            var url = $input.val().trim();

            if (url) {
                $preview.html('<img src="' + url + '" alt="">');
            } else {
                $preview.empty();
            }
        });
    }

    /**
     * Layout option selection
     */
    function initLayoutOptions() {
        $('.kaais-layout-option input').on('change', function() {
            $('.kaais-layout-option').removeClass('selected');
            $(this).closest('.kaais-layout-option').addClass('selected');
        });
    }

    /**
     * Custom network add/remove
     */
    function initCustomNetworks() {
        var $container = $('#kaais-custom-networks');
        var $template = $('#kaais-network-template');
        var networkIndex = $container.find('.kaais-custom-network').length;

        // Add new network
        $('#kaais-add-network').on('click', function() {
            var html = $template.html().replace(/__INDEX__/g, networkIndex);
            $container.append(html);
            networkIndex++;
        });

        // Remove network
        $(document).on('click', '.kaais-remove-network', function() {
            $(this).closest('.kaais-custom-network').remove();
        });
    }

})(jQuery);
