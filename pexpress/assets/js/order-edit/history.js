/**
 * History module for Polar Order Edit
 * Handles modification history toggle
 * @module order-edit/history
 */
(function (window, $) {
    'use strict';

    const History = {
        /**
         * Initialize history module
         */
        init: function () {
            $(document)
                .off('click.polarHistory', '.polar-toggle-history')
                .on('click.polarHistory', '.polar-toggle-history', function (e) {
                    e.preventDefault();

                    const $toggle = $(this);
                    const $container = $toggle.closest('.polar-order-item');
                    let $content = $container.find('.polar-history-content').first();

                    if (!$content.length) {
                        $content = $toggle.closest('.polar-order-edit-dashboard').find('.polar-history-content').first();
                    }

                    if ($content.length) {
                        if ($content.hasClass('is-hidden')) {
                            $content.removeClass('is-hidden').hide();
                        }
                        $content.slideToggle(200, function () {
                            $content.toggleClass('is-hidden', !$content.is(':visible'));
                        });
                        $toggle.toggleClass('dashicons-arrow-down-alt2 dashicons-arrow-up-alt2');
                    }
                });
        }
    };

    // Export to global namespace
    window.PolarOrderEdit = window.PolarOrderEdit || {};
    window.PolarOrderEdit.History = History;

})(window, jQuery);
