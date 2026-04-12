# Changelog

## [1.0.21] - 2026-04-12
### Added
- Dynamic discount integration pulling natively from `woo-discount-rules` dynamically on item addition.
- Minimum order value restriction (5000) for proceeding or forwarding tasks.

### Changed
- UI feedback elements added to order-edit templates outlining constraints dynamically.
- `ajax_add_order_item` and `ajax_update_order_item` updated with new comprehensive cart math logic.

### Fixed
- Fixed bug causing regular price to overlap discounted price when order lines were modified or added out-of-band by Agents.
