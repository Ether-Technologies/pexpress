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
            this.bindCancelEvent();
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
        },

        /**
         * Bind cancel order event and reason modal
         */
        bindCancelEvent: function () {
            const $modal = $('#polar-cancel-reason-modal');
            const $input = $('#polar-cancel-reason-input');
            const $submit = $modal.find('.polar-cancel-reason-submit');
            const $cancelBtn = $modal.find('.polar-cancel-reason-cancel');

            $(document)
                .off('click.polarCancel', '.polar-cancel-order')
                .on('click.polarCancel', '.polar-cancel-order', function (e) {
                    e.preventDefault();
                    e.stopPropagation();
                    const $btn = $(this);
                    $modal.data('cancel-order-id', $btn.data('order-id')).data('cancel-nonce', $btn.data('nonce'));
                    $input.val('');
                    $modal.css('display', 'flex').attr('aria-hidden', 'false');
                });

            $cancelBtn.on('click', function () {
                $modal.css('display', 'none').attr('aria-hidden', 'true');
                $input.val('');
            });

            $modal.on('click', function (e) {
                if (e.target === e.currentTarget) {
                    $modal.css('display', 'none').attr('aria-hidden', 'true');
                    $input.val('');
                }
            });

            $submit.on('click', function () {
                const reason = $input.val().trim();
                if (!reason) {
                    return;
                }
                const orderId = parseInt($modal.data('cancel-order-id'), 10);
                const nonce = $modal.data('cancel-nonce');
                if (!orderId || !nonce) {
                    return;
                }
                const $feedback = $('.polar-action-feedback');
                const $wrap = $('.polar-cancel-order-wrap');

                $submit.prop('disabled', true);
                $feedback.removeClass('is-error is-success').text('Cancelling order...');

                API.cancelOrder(orderId, nonce, reason, {
                    onSuccess: function (response) {
                        $modal.css('display', 'none').attr('aria-hidden', 'true');
                        $input.val('');
                        $feedback.addClass('is-success').text((response.data && response.data.message) || 'Order cancelled successfully.');
                        if ($wrap.length) {
                            $wrap.replaceWith('<p class="polar-action-status polar-action-cancelled" style="margin-top: 10px;"><span class="dashicons dashicons-warning" style="color: #d63638;"></span> Order Cancelled</p>');
                        }
                        const $statusBadge = $('.order-status');
                        if ($statusBadge.length) {
                            $statusBadge.removeClass().addClass('order-status status-cancelled').text('Cancelled');
                        }
                        setTimeout(function () {
                            $feedback.removeClass('is-success').text('');
                        }, 3000);
                    },
                    onError: function (response) {
                        const message = (response && response.data && response.data.message)
                            ? response.data.message
                            : 'Unable to cancel order. Please try again.';
                        $feedback.addClass('is-error').text(message);
                    },
                    onComplete: function () {
                        $submit.prop('disabled', false);
                    }
                });
            });
        }
    };

    // Export to global namespace
    window.PolarOrderEdit = window.PolarOrderEdit || {};
    window.PolarOrderEdit.OrderActions = OrderActions;

})(window, jQuery);
