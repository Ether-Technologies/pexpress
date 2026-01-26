/**
 * Item Actions module for Polar Order Edit
 * Handles edit, remove, replace, and save item actions
 * @module order-edit/item-actions
 */
(function (window, $) {
    'use strict';

    const Utils = window.PolarOrderEdit.Utils;
    const API = window.PolarOrderEdit.API;

    const ItemActions = {
        /**
         * Initialize item actions
         */
        init: function () {
            this.bindEvents();
        },

        /**
         * Bind event handlers
         */
        bindEvents: function () {
            const self = this;

            $(document)
                .off('click.polarItemActions', '.polar-edit-item')
                .on('click.polarItemActions', '.polar-edit-item', function (e) {
                    e.preventDefault();
                    e.stopPropagation();
                    self.showEditor($(this).closest('tr'));
                });

            $(document)
                .off('click.polarItemActions', '.polar-remove-item')
                .on('click.polarItemActions', '.polar-remove-item', function (e) {
                    e.preventDefault();
                    e.stopPropagation();

                    if (!confirm(Utils.getI18n('confirmRemove', 'Are you sure you want to remove this item?'))) {
                        return;
                    }

                    const itemId = $(this).data('item-id');
                    if (itemId) {
                        self.remove(itemId);
                    }
                });

            $(document)
                .off('click.polarItemActions', '.polar-replace-item')
                .on('click.polarItemActions', '.polar-replace-item', function (e) {
                    e.preventDefault();
                    e.stopPropagation();

                    const itemId = $(this).data('item-id');
                    if (itemId) {
                        self.showReplaceModal(itemId);
                    }
                });

            $(document)
                .off('click.polarItemActions', '.polar-save-item')
                .on('click.polarItemActions', '.polar-save-item', function (e) {
                    e.preventDefault();
                    e.stopPropagation();
                    self.save($(this).closest('tr'));
                });

            $(document)
                .off('click.polarItemActions', '.polar-cancel-edit')
                .on('click.polarItemActions', '.polar-cancel-edit', function (e) {
                    e.preventDefault();
                    e.stopPropagation();
                    self.cancelEdit($(this).closest('tr'));
                });
        },

        /**
         * Show inline editor for item row
         * @param {jQuery} $row - Table row
         */
        showEditor: function ($row) {
            if ($row.hasClass('is-editing')) {
                return;
            }

            const self = this;

            // Cancel other editing rows
            $('.polar-order-item-row.is-editing').not($row).each(function () {
                self.cancelEdit($(this));
            });

            if (!$row.data('original-html')) {
                $row.data('original-html', $row.html());
            }

            $row.addClass('is-editing');

            const itemId = $row.data('item-id');
            const decimals = Utils.getCurrencyDecimals();

            const quantityAttr = parseFloat($row.data('quantity'));
            const priceAttr = parseFloat($row.data('unitPrice'));
            const totalAttr = parseFloat($row.data('lineTotal'));

            const fallbackQuantity = parseFloat($row.find('.item-quantity').first().text()) || 1;
            const quantity = Number.isFinite(quantityAttr) && quantityAttr > 0 ? quantityAttr : fallbackQuantity;

            const fallbackTotal = Number.isFinite(totalAttr) ? totalAttr : quantity * (parseFloat($row.find('.item-price').first().text().replace(/[^0-9.\-]/g, '')) || 0);
            const fallbackPrice = quantity ? fallbackTotal / quantity : 0;
            const unitPrice = Number.isFinite(priceAttr) ? priceAttr : fallbackPrice;
            const priceStep = decimals > 0 ? Math.pow(10, -decimals) : 1;

            const $quantityCell = $row.find('td.column-quantity').first();
            const $priceCell = $row.find('td.column-price').first();
            const $totalCell = $row.find('td.column-total').first();
            const $actionCell = $row.find('td.column-actions').first();

            // Remove duplicate cells
            $row.find('td.column-quantity').not($quantityCell).remove();
            $row.find('td.column-price').not($priceCell).remove();
            $row.find('td.column-total').not($totalCell).remove();
            $row.find('td.column-actions').not($actionCell).remove();

            $quantityCell.empty().append(
                $('<label>', { class: 'screen-reader-text', for: 'polar-edit-quantity-' + itemId, text: Utils.getI18n('quantityLabel', 'Quantity') }),
                $('<input>', { type: 'number', id: 'polar-edit-quantity-' + itemId, class: 'polar-edit-quantity', value: quantity, min: 1, step: 1, css: { width: '100px' } })
            );

            $priceCell.empty().append(
                $('<label>', { class: 'screen-reader-text', for: 'polar-edit-price-' + itemId, text: Utils.getI18n('priceLabel', 'Price') }),
                $('<input>', { type: 'number', id: 'polar-edit-price-' + itemId, class: 'polar-edit-price', value: Number.isFinite(unitPrice) ? unitPrice.toFixed(decimals) : (0).toFixed(decimals), min: 0, step: priceStep, css: { width: '120px' } })
            );

            const $totalPreview = $('<span>', { class: 'item-total-preview' });
            $totalCell.empty().append($totalPreview);

            $actionCell.empty().append(
                $('<div>', { class: 'polar-item-actions' }).append(
                    $('<button>', { type: 'button', class: 'button button-small button-primary polar-save-item', 'data-item-id': itemId, text: Utils.getI18n('saveItem', 'Save') }),
                    $('<button>', { type: 'button', class: 'button button-small polar-cancel-edit', text: Utils.getI18n('cancelEdit', 'Cancel') })
                )
            );

            const updatePreview = function () {
                const qty = parseFloat($quantityCell.find('.polar-edit-quantity').val()) || 0;
                const price = parseFloat($priceCell.find('.polar-edit-price').val()) || 0;
                $totalPreview.text(Utils.formatCurrency(qty * price));
            };

            $quantityCell.find('.polar-edit-quantity').on('input', updatePreview);
            $priceCell.find('.polar-edit-price').on('input', updatePreview);
            updatePreview();
        },

        /**
         * Cancel editing and restore original row
         * @param {jQuery} $row - Table row
         */
        cancelEdit: function ($row) {
            const originalHtml = $row.data('original-html');
            if (originalHtml) {
                $row.html(originalHtml);
            }
            $row.removeClass('is-editing');
            $row.removeData('original-html');
        },

        /**
         * Save item changes
         * @param {jQuery} $row - Table row
         */
        save: function ($row) {
            const itemId = $row.find('.polar-save-item').data('item-id') || $row.data('item-id');
            const quantityValue = $row.find('.polar-edit-quantity').val();
            const priceValue = $row.find('.polar-edit-price').val();

            const quantity = parseInt(quantityValue, 10);
            const price = priceValue === '' ? null : parseFloat(priceValue);

            if (!Number.isInteger(quantity) || quantity < 1) {
                alert(Utils.getI18n('invalidQuantity', 'Please enter a valid quantity.'));
                return;
            }

            if (price !== null && (!isFinite(price) || price < 0)) {
                alert(Utils.getI18n('invalidPrice', 'Please enter a valid price.'));
                return;
            }

            API.updateItem(itemId, quantity, price, {
                onSuccess: function () {
                    location.reload();
                },
                onError: function (response) {
                    alert((response && response.data && response.data.message) || 'Error updating item.');
                }
            });
        },

        /**
         * Remove item from order
         * @param {number} itemId - Item ID
         */
        remove: function (itemId) {
            API.removeItem(itemId, {
                onSuccess: function () {
                    location.reload();
                },
                onError: function (response) {
                    alert((response && response.data && response.data.message) || 'Error removing item.');
                }
            });
        },

        /**
         * Show replace item modal
         * @param {number} itemId - Item ID
         */
        showReplaceModal: function (itemId) {
            const newProductId = prompt('Enter new product ID:');
            if (newProductId) {
                const quantity = prompt('Enter quantity:', '1') || 1;
                API.replaceItem(itemId, newProductId, quantity, {
                    onSuccess: function () {
                        location.reload();
                    },
                    onError: function (response) {
                        alert((response && response.data && response.data.message) || 'Error replacing item.');
                    }
                });
            }
        }
    };

    // Export to global namespace
    window.PolarOrderEdit = window.PolarOrderEdit || {};
    window.PolarOrderEdit.ItemActions = ItemActions;

})(window, jQuery);
