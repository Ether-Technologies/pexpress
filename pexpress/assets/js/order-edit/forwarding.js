/**
 * Forwarding module for Polar Order Edit
 * Handles forward/revoke SR functionality
 * @module order-edit/forwarding
 */
(function (window, $) {
    'use strict';

    const Utils = window.PolarOrderEdit.Utils;
    const API = window.PolarOrderEdit.API;

    const Forwarding = {
        /**
         * Initialize forwarding module
         */
        init: function () {
            this.bindForwardEvent();
            this.bindRevokeEvent();
        },

        /**
         * Bind forward to SR event
         */
        bindForwardEvent: function () {
            $(document)
                .off('click.polarForward', '.polar-forward-to-hr')
                .on('click.polarForward', '.polar-forward-to-hr', function (e) {
                    e.preventDefault();
                    e.stopPropagation();

                    const $button = $(this);
                    if ($button.prop('disabled')) {
                        return;
                    }

                    const orderId = parseInt($button.data('orderId'), 10);
                    if (!orderId) {
                        return;
                    }

                    const $note = $('#polar-forward-note');
                    const note = $note.length ? $note.val() : '';
                    const $feedback = $('.polar-forward-feedback');
                    const $statusBadge = $('.forward-status-badge');

                    $button.prop('disabled', true).addClass('is-loading');
                    $feedback.removeClass('is-error is-success').text(Utils.getI18n('forwarding', 'Forwarding to SR...'));

                    API.forwardToHR(orderId, note, {
                        onSuccess: function (response) {
                            const data = response.data || {};
                            $feedback.addClass('is-success').text(data.message || Utils.getI18n('forwardSuccess', 'Order forwarded to SR.'));

                            if ($statusBadge.length) {
                                $statusBadge.removeClass('is-idle').addClass('is-pending').text(Utils.getI18n('awaitingAssignment', 'Awaiting SR Assignment'));
                            }

                            $button.text(Utils.getI18n('updateForwarding', 'Update Forwarding'));
                        },
                        onError: function (response) {
                            const message = (response && response.data && response.data.message)
                                ? response.data.message
                                : Utils.getI18n('forwardError', 'Unable to forward order. Please try again.');
                            $feedback.addClass('is-error').text(message);
                        },
                        onComplete: function () {
                            $button.prop('disabled', false).removeClass('is-loading');
                        }
                    });
                });
        },

        /**
         * Bind revoke from SR event
         */
        bindRevokeEvent: function () {
            $(document)
                .off('click.polarRevoke', '.polar-revoke-forward')
                .on('click.polarRevoke', '.polar-revoke-forward', function (e) {
                    e.preventDefault();
                    e.stopPropagation();

                    const $button = $(this);
                    const orderId = parseInt($button.data('orderId'), 10);

                    if (!orderId) {
                        return;
                    }

                    if (!confirm(Utils.getI18n('revokeConfirm', 'Are you sure you want to revoke this order from SR?'))) {
                        return;
                    }

                    const $feedback = $('.polar-forward-feedback');
                    const $statusBadge = $('.forward-status-badge');
                    const $forwardMeta = $('.forward-meta');
                    const $forwardButton = $('.polar-forward-to-hr');

                    $button.prop('disabled', true).addClass('is-loading');
                    $feedback.removeClass('is-error is-success').text(Utils.getI18n('revoking', 'Revoking from SR...'));

                    API.revokeFromHR(orderId, {
                        onSuccess: function () {
                            $statusBadge.removeClass('is-forwarded').addClass('is-idle').text(Utils.getI18n('notForwarded', 'Not Yet Forwarded'));
                            $forwardMeta.remove();
                            $forwardButton.prop('disabled', false).text(Utils.getI18n('forwardToHR', 'Forward to SR'));
                            $button.remove();
                            $feedback.addClass('is-success').text(Utils.getI18n('revokeSuccess', 'Order revoked from SR successfully.'));

                            setTimeout(function () {
                                $feedback.removeClass('is-success').text('');
                            }, 3000);
                        },
                        onError: function (response) {
                            const message = (response && response.data && response.data.message)
                                ? response.data.message
                                : Utils.getI18n('revokeError', 'Unable to revoke order. Please try again.');
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
    window.PolarOrderEdit.Forwarding = Forwarding;

})(window, jQuery);
