# Changelog

## 1.9.7

### Added
- Bulk AWB generate modal shows cross-border currency warnings and requires conversion disclaimer confirmation

### Fixed
- Bulk/estimate AWB now use the default pickup point for the active testing/production environment
- Cross-border locker (XL) AWB requests now send `lockerLastMile`

## 1.9.6

### Fixed
- PHP 8.4 compatibility: explicit nullable parameter types across the Magento module
- Authentication failures now log endpoint details and surface the last error in admin instead of failing silently
- Requires `sameday-courier/php-sdk` ^2.4.2 (PHP 8.4 nullable fixes and auth header alignment)

## 1.9.5

### Changed
- Bulgaria destinations now send EUR (not BGN) on all Sameday API requests (AWB create and cost estimation)

### Removed
- Checkout dual BGN/EUR conversion display for Bulgarian shipping rates

## 1.9.4

### Added
- Bulk AWB actions on Sales → Orders: Generate AWB, Remove AWB, and Clear Errors
- Sameday Feedback and Sameday Actions columns on the orders grid
- Sequential per-order processing with progress, results summary, and CSV export for bulk generate
- AWB number shown as a badge in the Feedback column; per-order action buttons in the Actions column
