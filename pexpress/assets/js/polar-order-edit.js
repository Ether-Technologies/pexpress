/**
 * Polar Order Edit - Main Entry Point
 * 
 * This file initializes all order edit modules.
 * Module files must be loaded in order before this file:
 * 1. order-edit/utils.js
 * 2. order-edit/api.js
 * 3. order-edit/select2.js
 * 4. order-edit/item-actions.js
 * 5. order-edit/add-product.js
 * 6. order-edit/forwarding.js
 * 7. order-edit/order-actions.js
 * 8. order-edit/history.js
 * 9. polar-order-edit.js (this file)
 * 
 * @package PExpress
 */
(function (window, $) {
    'use strict';

    /**
     * Main application controller
     */
    const App = {
        /**
         * Initialize all modules
         */
        init: function () {
            // Verify polarOrderEdit config is available
            if (typeof window.polarOrderEdit === 'undefined') {
                console.error('polarOrderEdit is not defined. Make sure the script is properly localized.');
                return;
            }

            // Verify jQuery and Select2 are available
            if (typeof $ === 'undefined') {
                console.error('jQuery is not loaded.');
                return;
            }

            // Verify modules are loaded
            const modules = ['Utils', 'API', 'Select2', 'ItemActions', 'AddProduct', 'Forwarding', 'Shipping', 'OrderActions', 'History'];
            const missingModules = modules.filter(function (module) {
                return !window.PolarOrderEdit || !window.PolarOrderEdit[module];
            });

            if (missingModules.length > 0) {
                console.error('Missing PolarOrderEdit modules:', missingModules);
                return;
            }

            // Initialize product search for existing elements
            window.PolarOrderEdit.Select2.init($('.polar-product-select, .wc-product-search'));

            // Initialize all modules
            window.PolarOrderEdit.ItemActions.init();
            window.PolarOrderEdit.AddProduct.init();
            window.PolarOrderEdit.History.init();
            window.PolarOrderEdit.Forwarding.init();
            if (window.PolarOrderEdit.Shipping) {
                window.PolarOrderEdit.Shipping.init();
            }
            window.PolarOrderEdit.OrderActions.init();
        }
    };

    // Initialize on document ready
    $(document).ready(function () {
        App.init();
    });

    // Export App to global namespace
    window.PolarOrderEdit = window.PolarOrderEdit || {};
    window.PolarOrderEdit.App = App;

})(window, jQuery);
