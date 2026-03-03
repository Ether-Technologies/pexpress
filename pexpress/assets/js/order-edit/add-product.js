/**
 * Add Product module for Polar Order Edit
 * Handles inline add product form (simple dropdown, no search)
 * @module order-edit/add-product
 */
(function (window, $) {
    'use strict';

    const Utils = window.PolarOrderEdit.Utils;
    const API = window.PolarOrderEdit.API;

    const AddProduct = {
        $container: null,
        $tbody: null,

        /**
         * Initialize add product module
         */
        init: function () {
            this.$container = $('.polar-add-product-inline-section');

            if (!this.$container.length) {
                return;
            }

            const $table = this.$container.find('.polar-modal-products-table');
            this.$tbody = $table.find('tbody');

            this.bindEvents();
        },

        /**
         * Bind event handlers
         */
        bindEvents: function () {
            const self = this;

            this.$container.on('click', '.polar-add-row-btn', function (e) {
                e.preventDefault();
                self.addRow();
            });

            this.$container.on('click', '.polar-remove-row-btn', function (e) {
                e.preventDefault();
                self.removeRow($(this).closest('tr'));
            });

            this.$container.on('click', '.polar-submit-items-btn', function (e) {
                e.preventDefault();
                e.stopPropagation();
                self.submit($(this));
            });

            this.$container.on('submit', '.polar-add-product-form', function (e) {
                e.preventDefault();
                e.stopPropagation();
                self.$container.find('.polar-submit-items-btn').trigger('click');
                return false;
            });
        },

        /**
         * Add a new product row (clone first row)
         */
        addRow: function () {
            const $firstRow = this.$tbody.find('tr').first();
            if (!$firstRow.length) {
                return;
            }

            const index = this.$tbody.find('tr').length;
            const $newRow = $firstRow.clone();

            $newRow.attr('data-row-index', index);
            $newRow.find('.polar-product-dropdown').attr('id', 'polar-product-dropdown-' + index).val('');
            $newRow.find('.polar-modal-quantity-field').attr({ id: 'polar-quantity-' + index, min: 1, step: 1 }).val(1);
            $newRow.find('.polar-remove-row-btn').show();

            this.$tbody.append($newRow);
        },

        /**
         * Remove a product row
         */
        removeRow: function ($row) {
            if (this.$tbody.find('tr').length <= 1) {
                return;
            }

            const self = this;
            $row.fadeOut(200, function () {
                $(this).remove();
                if (self.$tbody.find('tr').length === 1) {
                    self.$tbody.find('.polar-remove-row-btn').hide();
                }
            });
        },

        /**
         * Submit the add product form
         */
        submit: function ($submitBtn) {
            if ($submitBtn.prop('disabled') || $submitBtn.hasClass('is-loading')) {
                return;
            }

            if (!this.$container || !this.$container.length) {
                this.$container = $('.polar-add-product-inline-section');
            }

            const formData = this.collectFormData();

            if (formData.item_id.length === 0) {
                alert(Utils.getI18n('selectProduct', 'Please select a product.'));
                return;
            }

            $submitBtn.prop('disabled', true).addClass('is-loading');
            this.processItems(formData, $submitBtn);
        },

        /**
         * Collect form data from all rows (simple dropdown .val())
         */
        collectFormData: function () {
            const formData = { item_id: [], item_qty: [] };
            const $tbody = this.$container.find('.polar-modal-products-table tbody');

            $tbody.find('tr').each(function () {
                const $row = $(this);
                const $select = $row.find('select.polar-product-dropdown');
                const $qty = $row.find('.polar-modal-quantity-field, input[name="item_qty"]');

                const productId = $select.length ? $select.val() : null;
                const quantity = $qty.length ? ($qty.val() || '1') : '1';

                if (productId && productId !== '' && productId !== '0') {
                    formData.item_id.push(String(productId));
                    formData.item_qty.push(String(quantity));
                }
            });

            return formData;
        },

        /**
         * Process items and add to order sequentially
         */
        processItems: function (formData, $submitBtn) {
            const self = this;
            const items = [];

            formData.item_id.forEach(function (id, index) {
                const productId = parseInt(id, 10);
                if (productId) {
                    const rawQty = formData.item_qty[index] || '1';
                    const quantity = parseInt(rawQty, 10) || 1;
                    items.push({ id: productId, qty: quantity });
                }
            });

            if (!items.length) {
                $submitBtn.prop('disabled', false).removeClass('is-loading');
                alert(Utils.getI18n('selectProduct', 'Please select a product.'));
                return;
            }

            const originalBtnText = $submitBtn.html();
            let hadError = false;

            const processNext = function (queue) {
                if (!queue.length) {
                    $submitBtn.prop('disabled', false).removeClass('is-loading');
                    if (!hadError) {
                        self.clearForm();
                        setTimeout(function () { window.location.reload(); }, 300);
                    } else {
                        $submitBtn.html(originalBtnText);
                    }
                    return;
                };

                const current = queue.shift();
                const remaining = queue.length + 1;
                $submitBtn.html('<span class="spinner is-active" style="float: none; margin: 0 8px 0 0;"></span>Adding ' + remaining + ' item' + (remaining > 1 ? 's' : '') + '...');

                API.addItem(current.id, current.qty, {
                    onSuccess: function () { processNext(queue); },
                    onError: function (response) {
                        hadError = true;
                        const message = (response && response.data && response.data.message) ? response.data.message : Utils.getI18n('addProductError', 'Error adding item.');
                        alert(message);
                        $submitBtn.prop('disabled', false).removeClass('is-loading').html(originalBtnText);
                    }
                });
            };

            processNext(items.slice());
        },

        /**
         * Clear the form after successful submission
         */
        clearForm: function () {
            this.$tbody.find('select.polar-product-dropdown').val('');
            this.$tbody.find('.polar-modal-quantity-field').val(1);

            const $rows = this.$tbody.find('tr');
            if ($rows.length > 1) {
                $rows.not(':first').remove();
                this.$tbody.find('tr:first .polar-remove-row-btn').hide();
            }
        }
    };

    window.PolarOrderEdit = window.PolarOrderEdit || {};
    window.PolarOrderEdit.AddProduct = AddProduct;

})(window, jQuery);
