/**
 * Shipping Address module for Polar Order Edit
 * Handles edit/save shipping address
 * @module order-edit/shipping
 */
(function (window, $) {
    'use strict';

    const Utils = window.PolarOrderEdit.Utils;
    const API = window.PolarOrderEdit.API;

    const fieldIds = [
        'polar-shipping-first-name',
        'polar-shipping-last-name',
        'polar-shipping-company',
        'polar-shipping-address-1',
        'polar-shipping-address-2',
        'polar-shipping-city',
        'polar-shipping-state',
        'polar-shipping-postcode',
        'polar-shipping-country'
    ];

    const fieldToKey = {
        'polar-shipping-first-name': 'shipping_first_name',
        'polar-shipping-last-name': 'shipping_last_name',
        'polar-shipping-company': 'shipping_company',
        'polar-shipping-address-1': 'shipping_address_1',
        'polar-shipping-address-2': 'shipping_address_2',
        'polar-shipping-city': 'shipping_city',
        'polar-shipping-state': 'shipping_state',
        'polar-shipping-postcode': 'shipping_postcode',
        'polar-shipping-country': 'shipping_country'
    };

    const Shipping = {
        init: function () {
            this.bindEdit();
            this.bindCancel();
            this.bindSave();
        },

        bindEdit: function () {
            $(document)
                .off('click.polarShippingEdit', '.polar-edit-shipping-btn')
                .on('click.polarShippingEdit', '.polar-edit-shipping-btn', function () {
                    $('.polar-shipping-view').hide();
                    $('.polar-shipping-edit-form').show();
                    $('.polar-edit-shipping-btn').prop('disabled', true);
                });
        },

        bindCancel: function () {
            $(document)
                .off('click.polarShippingCancel', '.polar-cancel-shipping-btn')
                .on('click.polarShippingCancel', '.polar-cancel-shipping-btn', function () {
                    $('.polar-shipping-edit-form').hide();
                    $('.polar-shipping-view').show();
                    $('.polar-edit-shipping-btn').prop('disabled', false);
                    $('.polar-shipping-feedback').removeClass('is-error is-success').text('');
                });
        },

        bindSave: function () {
            const self = this;
            $(document)
                .off('click.polarShippingSave', '.polar-save-shipping-btn')
                .on('click.polarShippingSave', '.polar-save-shipping-btn', function () {
                    const $btn = $(this);
                    const orderId = parseInt($btn.data('orderId'), 10);
                    if (!orderId) return;

                    const data = {};
                    fieldIds.forEach(function (id) {
                        const key = fieldToKey[id];
                        if (key) {
                            const $input = $('#' + id);
                            data[key] = $input.length ? $input.val() : '';
                        }
                    });

                    const $feedback = $('.polar-shipping-feedback');
                    $btn.prop('disabled', true);
                    $feedback.removeClass('is-error is-success').text(Utils.getI18n('shippingSaving', 'Saving...'));

                    API.updateShippingAddress(orderId, data, {
                        onSuccess: function (response) {
                            const res = response.data || {};
                            $feedback.addClass('is-success').text(res.message || Utils.getI18n('shippingSaved', 'Address updated.'));
                            if (res.formatted) {
                                $('.polar-shipping-display').html(res.formatted.replace(/\n/g, '<br>'));
                            }
                            $('.polar-shipping-edit-form').hide();
                            $('.polar-shipping-view').show();
                            $('.polar-edit-shipping-btn').prop('disabled', false);
                            setTimeout(function () {
                                $feedback.removeClass('is-success').text('');
                            }, 3000);
                        },
                        onError: function (response) {
                            const msg = (response && response.data && response.data.message)
                                ? response.data.message
                                : Utils.getI18n('genericError', 'An error occurred. Please try again.');
                            $feedback.addClass('is-error').text(msg);
                        },
                        onComplete: function () {
                            $btn.prop('disabled', false);
                        }
                    });
                });
        }
    };

    window.PolarOrderEdit = window.PolarOrderEdit || {};
    window.PolarOrderEdit.Shipping = Shipping;

})(window, jQuery);
