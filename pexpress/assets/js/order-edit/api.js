/**
 * API module for Polar Order Edit
 * Handles all AJAX requests
 * @module order-edit/api
 */
(function (window, $) {
    'use strict';

    const Utils = window.PolarOrderEdit.Utils;

    const API = {
        /**
         * Make AJAX request
         * @param {string} action - WordPress AJAX action
         * @param {Object} data - Request data
         * @param {Object} callbacks - Callback functions
         * @returns {jqXHR} jQuery AJAX object
         */
        ajax: function (action, data, callbacks) {
            const options = callbacks || {};

            return $.ajax({
                url: window.polarOrderEdit.ajaxUrl,
                type: 'POST',
                dataType: 'json',
                data: Object.assign({
                    action: action,
                    nonce: window.polarOrderEdit.nonce,
                    order_id: window.polarOrderEdit.orderId
                }, data),
                beforeSend: options.beforeSend,
                success: function (response) {
                    if (response && response.success) {
                        if (typeof options.onSuccess === 'function') {
                            options.onSuccess(response);
                        }
                    } else {
                        if (typeof options.onError === 'function') {
                            options.onError(response || { data: { message: 'Unknown error' } });
                        }
                    }
                },
                error: function (xhr, status, error) {
                    const errorResponse = {
                        success: false,
                        data: {
                            message: (xhr && xhr.responseJSON && xhr.responseJSON.data && xhr.responseJSON.data.message)
                                ? xhr.responseJSON.data.message
                                : Utils.getI18n('genericError', 'An error occurred. Please try again.')
                        }
                    };
                    if (typeof options.onError === 'function') {
                        options.onError(errorResponse);
                    }
                },
                complete: options.onComplete
            });
        },

        /**
         * Add item to order
         * @param {number} productId - Product ID
         * @param {number} quantity - Quantity
         * @param {Object} callbacks - Callback functions
         * @returns {jqXHR}
         */
        addItem: function (productId, quantity, callbacks) {
            return this.ajax('polar_add_order_item', {
                product_id: productId,
                quantity: quantity
            }, callbacks);
        },

        /**
         * Remove item from order
         * @param {number} itemId - Item ID
         * @param {Object} callbacks - Callback functions
         * @returns {jqXHR}
         */
        removeItem: function (itemId, callbacks) {
            return this.ajax('polar_remove_order_item', { item_id: itemId }, callbacks);
        },

        /**
         * Update order item
         * @param {number} itemId - Item ID
         * @param {number} quantity - New quantity
         * @param {number|null} price - New price
         * @param {Object} callbacks - Callback functions
         * @returns {jqXHR}
         */
        updateItem: function (itemId, quantity, price, callbacks) {
            const data = { item_id: itemId };

            if (Number.isInteger(quantity)) {
                data.quantity = quantity;
            }

            if (price !== null && price !== undefined && isFinite(price)) {
                const decimals = Utils.getCurrencyDecimals();
                data.price = parseFloat(price.toFixed(decimals));
            }

            return this.ajax('polar_update_order_item', data, callbacks);
        },

        /**
         * Replace order item
         * @param {number} itemId - Item ID to replace
         * @param {number} newProductId - New product ID
         * @param {number} quantity - Quantity
         * @param {Object} callbacks - Callback functions
         * @returns {jqXHR}
         */
        replaceItem: function (itemId, newProductId, quantity, callbacks) {
            return this.ajax('polar_replace_order_item', {
                item_id: itemId,
                new_product_id: newProductId,
                quantity: quantity
            }, callbacks);
        },

        /**
         * Forward order to HR/SR
         * @param {number} orderId - Order ID
         * @param {string} note - Forward note
         * @param {Object} callbacks - Callback functions
         * @returns {jqXHR}
         */
        forwardToHR: function (orderId, note, callbacks) {
            return this.ajax('polar_forward_order_to_hr', {
                order_id: orderId,
                note: note
            }, callbacks);
        },

        /**
         * Revoke order from HR/SR
         * @param {number} orderId - Order ID
         * @param {Object} callbacks - Callback functions
         * @returns {jqXHR}
         */
        revokeFromHR: function (orderId, callbacks) {
            return this.ajax('polar_revoke_order_from_hr', { order_id: orderId }, callbacks);
        },

        /**
         * Update order shipping address
         * @param {number} orderId - Order ID
         * @param {Object} data - Shipping fields (shipping_first_name, shipping_address_1, etc.)
         * @param {Object} callbacks - Callback functions
         * @returns {jqXHR}
         */
        updateShippingAddress: function (orderId, data, callbacks) {
            return this.ajax('polar_update_shipping_address', Object.assign({ order_id: orderId }, data), callbacks);
        },

        /**
         * Confirm order
         * @param {number} orderId - Order ID
         * @param {string} nonce - Security nonce
         * @param {Object} callbacks - Callback functions
         * @returns {jqXHR}
         */
        confirmOrder: function (orderId, nonce, callbacks) {
            return $.ajax({
                url: window.polarOrderEdit.ajaxUrl,
                method: 'POST',
                dataType: 'json',
                data: {
                    action: 'polar_confirm_order',
                    nonce: nonce,
                    order_id: orderId
                },
                success: function (response) {
                    if (response && response.success) {
                        if (typeof callbacks.onSuccess === 'function') {
                            callbacks.onSuccess(response);
                        }
                    } else {
                        if (typeof callbacks.onError === 'function') {
                            callbacks.onError(response);
                        }
                    }
                },
                error: function () {
                    if (typeof callbacks.onError === 'function') {
                        callbacks.onError({ data: { message: 'Unable to confirm order.' } });
                    }
                },
                complete: callbacks.onComplete
            });
        },

        /**
         * Complete order
         * @param {number} orderId - Order ID
         * @param {string} nonce - Security nonce
         * @param {Object} callbacks - Callback functions
         * @returns {jqXHR}
         */
        completeOrder: function (orderId, nonce, callbacks) {
            return $.ajax({
                url: window.polarOrderEdit.ajaxUrl,
                method: 'POST',
                dataType: 'json',
                data: {
                    action: 'polar_complete_order',
                    nonce: nonce,
                    order_id: orderId
                },
                success: function (response) {
                    if (response && response.success) {
                        if (typeof callbacks.onSuccess === 'function') {
                            callbacks.onSuccess(response);
                        }
                    } else {
                        if (typeof callbacks.onError === 'function') {
                            callbacks.onError(response);
                        }
                    }
                },
                error: function () {
                    if (typeof callbacks.onError === 'function') {
                        callbacks.onError({ data: { message: 'Unable to complete order.' } });
                    }
                },
                complete: callbacks.onComplete
            });
        },

        /**
         * Cancel order with reason
         * @param {number} orderId - Order ID
         * @param {string} nonce - Security nonce
         * @param {string} reason - Cancellation reason
         * @param {Object} callbacks - Callback functions
         * @returns {jqXHR}
         */
        cancelOrder: function (orderId, nonce, reason, callbacks) {
            return $.ajax({
                url: window.polarOrderEdit.ajaxUrl,
                method: 'POST',
                dataType: 'json',
                data: {
                    action: 'polar_cancel_order',
                    nonce: nonce,
                    order_id: orderId,
                    reason: reason
                },
                success: function (response) {
                    if (response && response.success) {
                        if (typeof callbacks.onSuccess === 'function') {
                            callbacks.onSuccess(response);
                        }
                    } else {
                        if (typeof callbacks.onError === 'function') {
                            callbacks.onError(response);
                        }
                    }
                },
                error: function () {
                    if (typeof callbacks.onError === 'function') {
                        callbacks.onError({ data: { message: 'Unable to cancel order.' } });
                    }
                },
                complete: callbacks.onComplete
            });
        }
    };

    // Export to global namespace
    window.PolarOrderEdit = window.PolarOrderEdit || {};
    window.PolarOrderEdit.API = API;

})(window, jQuery);
