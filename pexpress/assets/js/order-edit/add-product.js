/**
 * Add Product module for Polar Order Edit
 * Handles inline add product form
 * @module order-edit/add-product
 */
(function (window, $) {
    'use strict';

    const Utils = window.PolarOrderEdit.Utils;
    const API = window.PolarOrderEdit.API;
    const Select2 = window.PolarOrderEdit.Select2;

    const AddProduct = {
        $container: null,
        $tbody: null,
        rowTemplate: null,

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
            this.rowTemplate = this.$tbody.data('row');

            Select2.init(this.$container.find('.wc-product-search'));
            this.bindEvents();
        },

        /**
         * Bind event handlers
         */
        bindEvents: function () {
            const self = this;

            // Add row button
            this.$container.on('click', '.polar-add-row-btn', function (e) {
                e.preventDefault();
                self.addRow();
            });

            // Remove row button
            this.$container.on('click', '.polar-remove-row-btn', function (e) {
                e.preventDefault();
                self.removeRow($(this).closest('tr'));
            });

            // Submit button
            this.$container.on('click', '.polar-submit-items-btn', function (e) {
                e.preventDefault();
                e.stopPropagation();
                self.submit($(this));
            });

            // Form submit
            this.$container.on('submit', '.polar-add-product-form', function (e) {
                e.preventDefault();
                e.stopPropagation();
                self.$container.find('.polar-submit-items-btn').trigger('click');
                return false;
            });
        },

        /**
         * Add a new product row
         */
        addRow: function () {
            if (!this.rowTemplate) {
                return;
            }

            const index = this.$tbody.find('tr').length;
            const newRowHtml = this.rowTemplate.replace(/\{index\}/g, index.toString());
            const $newRow = $(newRowHtml);

            this.$tbody.append($newRow);

            // Show remove buttons if > 1 row
            if (this.$tbody.find('tr').length > 1) {
                this.$tbody.find('.polar-remove-row-btn').show();
            }

            Select2.init($newRow.find('.wc-product-search'));
            $newRow.find('.polar-modal-quantity-field').attr({ min: 1, step: 1 }).val(1);
        },

        /**
         * Remove a product row
         * @param {jQuery} $row - Row to remove
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
         * @param {jQuery} $submitBtn - Submit button
         */
        submit: function ($submitBtn) {
            if ($submitBtn.prop('disabled') || $submitBtn.hasClass('is-loading')) {
                return;
            }

            // Ensure we have fresh container reference
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
         * Collect form data from all rows
         * @returns {Object} Form data with item_id and item_qty arrays
         */
        collectFormData: function () {
            const formData = {
                item_id: [],
                item_qty: []
            };

            // Always get fresh reference to tbody
            const $tbody = this.$container.find('.polar-modal-products-table tbody');

            $tbody.find('tr').each(function () {
                const $row = $(this);
                const $productSelect = $row.find('select.wc-product-search');
                const $quantityInput = $row.find('.polar-modal-quantity-field, input[name="item_qty"]');

                let productId = null;

                if ($productSelect.length) {
                    // Method 1: Try data attribute first (most reliable for Chromium browsers)
                    productId = $productSelect.data('selected-product-id') || $productSelect.attr('data-selected-product-id');

                    // Method 2: Try Select2 data API - ensure we access it correctly
                    if (!productId && $productSelect.data('select2')) {
                        try {
                            // Use Select2's data method - works in both Firefox and Chromium when properly synced
                            const select2Data = $productSelect.select2('data');
                            if (select2Data && select2Data.length > 0) {
                                const selectedItem = Array.isArray(select2Data) ? select2Data[0] : select2Data;
                                if (selectedItem && selectedItem.id) {
                                    productId = String(selectedItem.id);
                                }
                            }
                        } catch (err) {
                            // Silently handle error
                        }
                    }

                    // Method 3: Try Select2's internal state (fallback for Chromium)
                    if (!productId && $productSelect.data('select2')) {
                        try {
                            const select2Instance = $productSelect.data('select2');
                            // Access Select2's internal data storage
                            if (select2Instance && select2Instance.data && select2Instance.data.length > 0) {
                                productId = String(select2Instance.data[0].id);
                            }
                        } catch (err) {
                            // Silently handle error
                        }
                    }

                    // Method 4: Force sync and try val() - ensure Select2 updates underlying select
                    if (!productId) {
                        // Force Select2 to sync its value to the underlying select element
                        if ($productSelect.data('select2')) {
                            try {
                                // Trigger change to ensure sync
                                $productSelect.trigger('change.select2');
                                // Small delay to allow sync (Chromium sometimes needs this)
                                const currentVal = $productSelect.val();
                                if (currentVal) {
                                    productId = String(currentVal);
                                }
                            } catch (err) {
                                // Silently handle error
                            }
                        } else {
                            // No Select2 instance, try direct val()
                            productId = $productSelect.val();
                        }
                    }

                    // Method 5: Try native DOM value
                    if (!productId && $productSelect[0]) {
                        productId = $productSelect[0].value;
                    }

                    // Method 6: Try finding selected option in DOM
                    if (!productId) {
                        const $selectedOption = $productSelect.find('option:selected');
                        if ($selectedOption.length && $selectedOption.val()) {
                            productId = String($selectedOption.val());
                        }
                    }

                    // Ensure productId is a string and valid
                    if (productId) {
                        productId = String(productId).trim();
                        if (productId === '' || productId === '0' || productId === 'null' || productId === 'undefined') {
                            productId = null;
                        }
                    }
                }

                const quantity = $quantityInput.length ? ($quantityInput.val() || '1') : '1';

                if (productId && productId !== '' && productId !== '0') {
                    formData.item_id.push(String(productId));
                    formData.item_qty.push(String(quantity));
                }
            });
            return formData;
        },

        /**
         * Process items and add to order sequentially
         * @param {Object} formData - Form data
         * @param {jQuery} $submitBtn - Submit button
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
                        setTimeout(function () {
                            window.location.reload();
                        }, 300);
                    } else {
                        $submitBtn.html(originalBtnText);
                    }
                    return;
                }

                const current = queue.shift();
                const remaining = queue.length + 1;

                $submitBtn.html('<span class="spinner is-active" style="float: none; margin: 0 8px 0 0;"></span>Adding ' + remaining + ' item' + (remaining > 1 ? 's' : '') + '...');

                API.addItem(current.id, current.qty, {
                    onSuccess: function () {
                        processNext(queue);
                    },
                    onError: function (response) {
                        hadError = true;
                        const message = (response && response.data && response.data.message)
                            ? response.data.message
                            : Utils.getI18n('addProductError', 'Error adding item.');
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
            this.$tbody.find('.wc-product-search').each(function () {
                const $select = $(this);
                // Clear data attribute used for Chrome compatibility
                $select.data('selected-product-id', null);
                $select.removeAttr('data-selected-product-id');

                if ($select.data('select2')) {
                    $select.val(null).trigger('change');
                }
            });

            this.$tbody.find('.polar-modal-quantity-field').val(1);

            const $rows = this.$tbody.find('tr');
            if ($rows.length > 1) {
                $rows.not(':first').remove();
                this.$tbody.find('tr:first .polar-remove-row-btn').hide();
            }
        }
    };

    // Export to global namespace
    window.PolarOrderEdit = window.PolarOrderEdit || {};
    window.PolarOrderEdit.AddProduct = AddProduct;

})(window, jQuery);
