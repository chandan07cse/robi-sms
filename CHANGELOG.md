# Changelog

All notable changes to the AdaReach SMS package will be documented in this file.

## [v2.0.0] - 2026-01-31

### 🎉 Major Release - PHP 7.4+ Support & Standalone Mode

This release adds full PHP 7.4+ support and introduces a standalone client that works without Laravel.

### Added

- **Standalone Client**: New `StandaloneClient` class for use without Laravel
  - Works with PHP 7.4, 8.0, 8.1, 8.2, and 8.3
  - cURL-based HTTP client (no Guzzle dependency required)
  - File-based token caching
  - Complete API feature parity with Laravel integration
  
- **PHP 7.4 Compatibility**: 
  - Replaced PHP 8.0+ `match` expressions with `switch` statements
  - Updated all code to be compatible with PHP 7.4+
  
- **Documentation**:
  - New `STANDALONE_USAGE.md` with comprehensive standalone documentation
  - New `examples/standalone-usage.php` with 8 detailed examples
  - New `test-standalone.php` for quick testing
  - Updated main `README.md` with dual-mode usage instructions

### Changed

- **composer.json**:
  - PHP requirement: `^7.4|^8.0|^8.1|^8.2|^8.3` (was `^8.1|^8.2|^8.3`)
  - Laravel dependencies moved to `suggest` section (optional)
  - Added `ext-curl` and `ext-json` requirements
  
- **Code Compatibility**:
  - `src/Models/Setting.php`: Replaced `match` with `switch`
  - `src/Http/Controllers/DashboardController.php`: Replaced `match` with `switch`

### Features

#### Standalone Client Features

```php
// Works in any PHP 7.4+ project
$client = new \AdaReach\Sms\StandaloneClient('username', 'password');

// Send single SMS
$client->sendSms('880XXXXXXXXXX', 'Hello!', 'Sender');

// Send bulk SMS
$client->sendSms(['880XXX...', '880YYY...'], 'Bulk message', 'Sender');

// Check balance
$client->checkBalance();

// Get delivery status
$client->getDeliveryStatus($messageId);

// Clear token cache
$client->clearCache();
```

### Backward Compatibility

✅ **Fully backward compatible** - All existing Laravel integration code continues to work without changes.

- Dashboard features remain unchanged
- Facade usage unchanged
- Laravel integration unchanged
- Database models unchanged
- Events unchanged

### Migration Guide

#### For Laravel Users

No action required. The package continues to work exactly as before.

#### For Non-Laravel Users

Install the package and use the new `StandaloneClient`:

```bash
composer require chandan07cse/robi-sms
```

See `STANDALONE_USAGE.md` for complete documentation.

### System Requirements

#### Laravel Integration
- PHP 8.1+ (unchanged)
- Laravel 10.x or 11.x (unchanged)
- MySQL/PostgreSQL
- Redis (optional)

#### Standalone Mode (New)
- PHP 7.4+
- cURL extension
- JSON extension
- Composer

### Files Changed

**New Files:**
- `src/StandaloneClient.php` - Standalone SMS client
- `STANDALONE_USAGE.md` - Standalone documentation
- `examples/standalone-usage.php` - Usage examples
- `test-standalone.php` - Test script
- `CHANGELOG.md` - This file

**Modified Files:**
- `composer.json` - Updated PHP version and dependencies
- `README.md` - Added standalone usage documentation
- `src/Models/Setting.php` - PHP 7.4 compatibility
- `src/Http/Controllers/DashboardController.php` - PHP 7.4 compatibility

### Testing

Tested on:
- ✅ PHP 7.4
- ✅ PHP 8.0
- ✅ PHP 8.1
- ✅ PHP 8.2
- ✅ PHP 8.3
- ✅ Laravel 10.x
- ✅ Laravel 11.x
- ✅ Standalone mode (no framework)

---

## [v1.x.x] - Previous Versions

### Features (Legacy)

- Laravel integration with dashboard
- Vue.js dashboard interface
- Database-backed settings
- SMS history and analytics
- Real-time updates with WebSocket
- Queue support
- Event broadcasting
- Encrypted credentials storage
- Password-protected dashboard
- CSRF token management

---

## Upgrade Guide

### From v1.x to v2.0

**For Laravel users:** No changes required. Update normally:

```bash
composer update chandan07cse/robi-sms
```

**For standalone users:** You can now use the package! See `STANDALONE_USAGE.md`.

### Breaking Changes

None. This release is fully backward compatible.

### Deprecations

None.

---

## Support

- **GitHub Issues**: https://github.com/chandan07cse/robi-sms/issues
- **Email**: chandan07cse@gmail.com
- **Documentation**: See README.md and STANDALONE_USAGE.md

## License

MIT License - see LICENSE file for details
