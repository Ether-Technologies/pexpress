/**
 * Order Actions module for Polar Order Edit
 * Handles confirm and complete order actions
 * @module order-edit/order-actions
 */
(function (window, $) {
    'use strict';

    const API = window.PolarOrderEdit.API;

    const OrderActions = {
        /**
         * Initialize order actions
         */
        init: function () {
            this.bindConfirmEvent();
            this.bindCompleteEvent();
        },

        /**
         * Bind confirm order event
         */
        bindConfirmEvent: function () {
            $(document)
                .off('click.polarConfirm', '.polar-confirm-order')
                .on('click.polarConfirm', '.polar-confirm-order', function (e) {
                    e.preventDefault();
                    e.stopPropagation();

                    const $button = $(this);
                    const orderId = parseInt($button.data('order-id'), 10);
                    const nonce = $button.data('nonce');
                    const $feedback = $('.polar-action-feedback');

                    if (!orderId || !nonce) {
                        return;
                    }

                    $button.prop('disabled', true).addClass('is-loading');
                    $feedback.removeClass('is-error is-success').text('Confirming order...');

                    API.confirmOrder(orderId, nonce, {
                        onSuccess: function (response) {
                            $feedback.addClass('is-success').text((response.data && response.data.message) || 'Order confirmed successfully.');
                            $button.replaceWith('<p class="polar-action-status"><span class="dashicons dashicons-yes-alt" style="color: #46b450;"></span> Order Confirmed</p>');

                            // Enable the "Order Complete" button if it exists
                            const $completeButton = $('.polar-complete-order');
                            if ($completeButton.length) {
                                $completeButton.prop('disabled', false).attr('title', '');
                            }

                            setTimeout(function () {
                                $feedback.removeClass('is-success').text('');
                            }, 3000);
                        },
                        onError: function (response) {
                            const message = (response && response.data && response.data.message)
                                ? response.data.message
                                : 'Unable to confirm order. Please try again.';
                            $feedback.addClass('is-error').text(message);
                        },
                        onComplete: function () {
                            $button.prop('disabled', false).removeClass('is-loading');
                        }
                    });
                });
        },

        /**
         * Bind complete order event
         */
        bindCompleteEvent: function () {
            $(document)
                .off('click.polarComplete', '.polar-complete-order')
                .on('click.polarComplete', '.polar-complete-order', function (e) {
                    e.preventDefault();
                    e.stopPropagation();

                    const $button = $(this);
                    
                    // Prevent action if button is disabled
                    if ($button.prop('disabled')) {
                        return;
                    }
                    
                    const orderId = parseInt($button.data('order-id'), 10);
                    const nonce = $button.data('nonce');
                    const $feedback = $('.polar-action-feedback');

                    if (!orderId || !nonce) {
                        return;
                    }

                    if (!confirm('Are you sure you want to mark this order as completed?')) {
                        return;
                    }

                    $button.prop('disabled', true).addClass('is-loading');
                    $feedback.removeClass('is-error is-success').text('Completing order...');

                    API.completeOrder(orderId, nonce, {
                        onSuccess: function (response) {
                            $feedback.addClass('is-success').text((response.data && response.data.message) || 'Order completed successfully.');
                            $button.replaceWith('<p class="polar-action-status" style="margin-top: 10px;"><span class="dashicons dashicons-yes-alt" style="color: #46b450;"></span> Order Completed</p>');

                            setTimeout(function () {
                                $feedback.removeClass('is-success').text('');
                            }, 3000);
                        },
                        onError: function (response) {
                            const message = (response && response.data && response.data.message)
                                ? response.data.message
                                : 'Unable to complete order. Please try again.';
                            $feedback.addClass('is-error').text(message);
                        },
                        onComplete: function () {
                            $button.prop('disabled', false).removeClass('is-loading');
                        }
                    });
                });
        }
    };

    // Export to global namespace
    window.PolarOrderEdit = window.PolarOrderEdit || {};
    window.PolarOrderEdit.OrderActions = OrderActions;

})(window, jQuery);
