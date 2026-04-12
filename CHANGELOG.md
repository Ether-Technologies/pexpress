# Changelog

## [1.0.23] - 2026-04-12
### Added
- Manual bundle breakdown mechanism in `ajax_add_order_item`. When a bundle product is added via the support portal, its constituent items are now programmatically added and linked to the parent, ensuring they display separately as in standard storefront orders.
- Fallback pricing mechanism: Added a flat **15% discount** fallback that activates if the `woo-discount-rules` calculation returns the regular price. This ensures "Actual Price" and "Discounted Price" always show a distinction for newly added or updated items.

### Fixed
- Improved `WC_Order_Item_Product` type safety in AJAX order manipulation handlers.
- Standardized unit price calculations across addition and update workflows.


## [1.0.22] - 2026-04-12
### Fixed
- Fixed issue where WooCommerce Discount Rules (WDR) failed to apply discounts to Bundle products and other items when added manually via the backend interface. WDR calculation now properly defaults to `$is_cart = false` to evaluate product pricing immediately rather than conditionally failing on empty cart contexts.
- Updated 100% discount evaluations by strict-checking against `false` rather than using `empty()`, allowing free ($0.00) bundles or items to successfully inherit discounts.

## [1.0.21] - 2026-04-12
### Added
- Dynamic discount integration pulling natively from `woo-discount-rules` dynamically on item addition.
- Minimum order value restriction (5000) for proceeding or forwarding tasks.

### Changed
- UI feedback elements added to order-edit templates outlining constraints dynamically.
- `ajax_add_order_item` and `ajax_update_order_item` updated with new comprehensive cart math logic.

### Fixed
- Fixed bug causing regular price to overlap discounted price when order lines were modified or added out-of-band by Agents.
