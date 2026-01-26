/**
 * Select2 module for Polar Order Edit
 * Handles product search Select2 initialization
 * @module order-edit/select2
 */
(function (window, $) {
    'use strict';

    const Utils = window.PolarOrderEdit.Utils;

    const Select2Module = {
        /**
         * Initialize Select2 on target elements
         * @param {jQuery|string} target - Elements to initialize
         */
        init: function (target) {
            if (typeof $.fn.select2 === 'undefined') {
                return;
            }

            const $elements = target && target.jquery ? target : $(target);

            if (!$elements.length) {
                return;
            }

            const self = this;

            $elements.each(function () {
                const $select = $(this);

                if (!$select.length || $select.data('select2')) {
                    return;
                }

                const $dropdownParent = self.findDropdownParent($select);
                const placeholderText = $select.data('placeholder') || Utils.getI18n('searchProducts', 'Search for a product...');

                $select.select2({
                    width: '100%',
                    dropdownParent: $dropdownParent,
                    ajax: self.getAjaxConfig(),
                    minimumInputLength: 2,
                    placeholder: placeholderText,
                    allowClear: true
                });

                self.attachSelectHandler($select);
            });
        },

        /**
         * Find appropriate dropdown parent for Select2
         * @param {jQuery} $select - Select element
         * @returns {jQuery} Parent element
         */
        findDropdownParent: function ($select) {
            const selectors = [
                '.wc-backbone-modal-content',
                '.polar-add-product-inline-section',
                '.polar-add-item-section',
                '.polar-order-item'
            ];

            for (let i = 0; i < selectors.length; i++) {
                const $parent = $select.closest(selectors[i]);
                if ($parent.length) {
                    return $parent;
                }
            }

            return $(document.body);
        },

        /**
         * Get AJAX configuration for Select2
         * @returns {Object} AJAX config
         */
        getAjaxConfig: function () {
            return {
                url: window.polarOrderEdit.ajaxUrl,
                dataType: 'json',
                delay: 250,
                data: function (params) {
                    return {
                        action: 'polar_search_products',
                        term: params.term || '',
                        security: window.polarOrderEdit.wcSearchNonce || ''
                    };
                },
                processResults: function (data) {
                    const results = [];

                    if (data && Array.isArray(data)) {
                        data.forEach(function (item) {
                            if (item && item.id && item.text) {
                                results.push({ id: item.id, text: item.text });
                            }
                        });
                    } else if (data && data.success && Array.isArray(data.data)) {
                        data.data.forEach(function (item) {
                            if (item && item.id && item.text) {
                                results.push({ id: item.id, text: item.text });
                            }
                        });
                    } else if (data && typeof data === 'object') {
                        $.each(data, function (id, text) {
                            if (id && text) {
                                results.push({ id: id, text: text });
                            }
                        });
                    }

                    return { results: results };
                },
                cache: true
            };
        },

        /**
         * Attach select handler to ensure value is properly stored
         * @param {jQuery} $select - Select element
         */
        attachSelectHandler: function ($select) {
            $select.on('select2:select', function (e) {
                const data = e.params.data;

                if (data && data.id) {
                    let existingOption = $select.find('option[value="' + data.id + '"]');

                    if (existingOption.length === 0) {
                        const $option = $('<option></option>')
                            .attr('value', data.id)
                            .text(data.text || data.id);
                        $select.append($option);
                    } else {
                        // Ensure existing option is properly set
                        existingOption.prop('selected', true);
                    }

                    // Store in data attribute FIRST (most reliable for Chromium browsers)
                    $select.data('selected-product-id', data.id);
                    $select.attr('data-selected-product-id', data.id);

                    // Set value and ensure it's selected - do this AFTER setting data attribute
                    $select.val(data.id).prop('selected', true);

                    // Force Select2 to update its internal state - critical for Chromium browsers
                    if ($select.data('select2')) {
                        $select.trigger('change.select2');
                        // Force sync by accessing Select2's internal data
                        try {
                            const select2Instance = $select.data('select2');
                            if (select2Instance) {
                                select2Instance.trigger('select', {
                                    data: data
                                });
                            }
                        } catch (err) {
                            // Silently handle error
                        }
                    }
                }
            });

            // Handle clear event
            $select.on('select2:clear', function () {
                $select.data('selected-product-id', null);
                $select.removeAttr('data-selected-product-id');
            });
        }
    };

    // Export to global namespace
    window.PolarOrderEdit = window.PolarOrderEdit || {};
    window.PolarOrderEdit.Select2 = Select2Module;

})(window, jQuery);
