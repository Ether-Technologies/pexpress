/**
 * Utility functions for Polar Order Edit
 * @module order-edit/utils
 */
(function (window) {
    'use strict';

    const Utils = {
        entityDecoder: document.createElement('textarea'),

        /**
         * Decode HTML entities in a string
         * @param {string} value - String with HTML entities
         * @returns {string} Decoded string
         */
        decodeHtmlEntities: function (value) {
            if (typeof value !== 'string') {
                return value || '';
            }
            this.entityDecoder.innerHTML = value;
            return this.entityDecoder.value || value;
        },

        /**
         * Normalize currency output by replacing non-breaking spaces
         * @param {string} value - Currency string
         * @returns {string} Normalized string
         */
        normalizeCurrencyOutput: function (value) {
            if (typeof value !== 'string') {
                return value;
            }
            return value.replace(/\u00a0/g, ' ').trim();
        },

        /**
         * Format amount as currency
         * @param {number} amount - Amount to format
         * @returns {string} Formatted currency string
         */
        formatCurrency: function (amount) {
            if (!isFinite(amount)) {
                return '';
            }

            const currency = window.polarOrderEdit.currency || {};
            const decimals = typeof currency.decimals === 'number' ? currency.decimals : 2;
            const locale = currency.locale ? currency.locale.replace(/_/g, '-') : undefined;
            const formattedNumber = amount.toLocaleString(locale, {
                minimumFractionDigits: decimals,
                maximumFractionDigits: decimals,
            });
            const priceFormat = currency.price_format || '%1$s%2$s';
            const rawSymbol = currency.symbol || '$';
            const symbol = this.decodeHtmlEntities(rawSymbol);

            const formatted = priceFormat.replace('%1$s', symbol).replace('%2$s', formattedNumber);
            return this.normalizeCurrencyOutput(this.decodeHtmlEntities(formatted));
        },

        /**
         * Serialize form data to object
         * @param {jQuery} $form - jQuery form element
         * @returns {Object} Form data object
         */
        serializeFormData: function ($form) {
            const result = {};
            if (!$form || !$form.length) {
                return result;
            }

            const fields = $form.serializeArray();
            fields.forEach(function (field) {
                if (Object.prototype.hasOwnProperty.call(result, field.name)) {
                    if (!Array.isArray(result[field.name])) {
                        result[field.name] = [result[field.name]];
                    }
                    result[field.name].push(field.value);
                } else {
                    result[field.name] = field.value;
                }
            });

            $form.find('input[type="checkbox"]:not(:checked)').each(function () {
                const name = this.name;
                if (!name || Object.prototype.hasOwnProperty.call(result, name)) {
                    return;
                }
                result[name] = '';
            });

            return result;
        },

        /**
         * Get i18n string with fallback
         * @param {string} key - i18n key
         * @param {string} fallback - Fallback string
         * @returns {string} Translated or fallback string
         */
        getI18n: function (key, fallback) {
            return (window.polarOrderEdit.i18n && window.polarOrderEdit.i18n[key]) || fallback || '';
        },

        /**
         * Get currency decimals
         * @returns {number} Number of decimals
         */
        getCurrencyDecimals: function () {
            return (window.polarOrderEdit.currency && typeof window.polarOrderEdit.currency.decimals === 'number')
                ? window.polarOrderEdit.currency.decimals
                : 2;
        }
    };

    // Export to global namespace
    window.PolarOrderEdit = window.PolarOrderEdit || {};
    window.PolarOrderEdit.Utils = Utils;

})(window);
