# Changelog

All notable changes to the Magento 2 Asynchronous Email Sending module will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.1.0] - 2026-01-31

### Added
- **Admin Grid Interface**: New admin panel for managing queued emails
  - Grid listing at **System > Async Email Queue** with sortable columns
  - View email details including parsed headers and email content preview (iframe)
  - Mass actions: Delete and Resend multiple emails at once
  - Individual row actions: View, Resend, Delete
  - Status filters: Pending, Sent, Error with colored badges
  - Date range filters for Created At and Sent At columns
  - Full-text search across email subjects
- ACL resources for granular permission control (view, delete, resend)
- Console command `hryvinskyi:email:send` for manual queue processing
- Symfony Mailer support for Magento 2.4.8+ compatibility
- PHP 8.3 and 8.4 support with modern language features
- Readonly classes for immutability across all service classes
- Constructor property promotion for cleaner code
- Typed class constants (PHP 8.3 feature) for configuration paths
- Enhanced email parsing for Symfony Mailer raw messages
- Improved error handling and logging throughout the module

### Changed
- **BREAKING**: Minimum PHP version is now 8.3 (was 8.1)
- **BREAKING**: Minimum Magento version is now 2.4.8
- Migrated from Laminas Mail to Symfony Mailer
- Refactored `EmailSenderHandler` for Symfony Mailer compatibility
- Updated all classes to use constructor property promotion
- Converted all service classes to readonly for better immutability
- Improved PHPDoc comments across all classes
- Updated module version to 1.1.0 in `module.xml`
- Enhanced code quality following SOLID principles

### Removed
- Removed deprecated Zend Framework dependencies
- Removed Laminas Mail dependencies
- Removed support for PHP 8.1 and 8.2
- Removed support for Magento versions below 2.4.8

### Fixed
- Email header parsing for better compatibility with Symfony Mailer
- Proper handling of quoted-printable encoded email bodies
- UTF-8 email subject decoding using IMAP functions
- Email address parsing with support for "Name <email@example.com>" format
- Exception handling in email sending process

### Technical Details
- All classes now use `declare(strict_types=1)` for type safety
- Implemented proper separation of concerns with focused service classes
- Enhanced error logging with detailed context information
- Improved code documentation and inline comments

## [1.0.2] - 2020-XX-XX

### Fixed
- Minor bug fixes and improvements
- Compatibility updates for Magento 2.3.x and 2.4.x (pre-2.4.8)

## [1.0.1] - 2020-XX-XX

### Added
- Initial release with basic asynchronous email sending functionality
- Queue management for pending, sent, and failed emails
- Cron jobs for sending queued emails and cleanup
- Configuration options for sending limits and cleanup schedules
- Debug mode for detailed logging

### Features
- Plugin to intercept TransportInterface::sendMessage()
- Database table for storing queued emails
- Support for Laminas Mail (Magento 2.3.3+)
- Support for Zend Mail (Magento 2.2.x - 2.3.2)

---

## Upgrade Guide

### Upgrading from 1.0.x to 1.1.0

**Important**: This is a major version upgrade with breaking changes. Please read carefully before upgrading.

#### Prerequisites
1. **PHP Version**: Ensure your server is running PHP 8.3 or 8.4
2. **Magento Version**: Ensure you're running Magento 2.4.8 or higher
3. **Backup**: Always backup your database before upgrading

#### Upgrade Steps

1. **Update Composer Requirements**:
   ```bash
   composer require hryvinskyi/magento2-asynchronous-email-sending:^1.1.0
   ```

2. **Run Magento Upgrade**:
   ```bash
   bin/magento setup:upgrade
   bin/magento setup:di:compile
   bin/magento cache:clean
   ```

3. **Clear Existing Queue** (Optional but Recommended):
   ```sql
   TRUNCATE TABLE hryvinskyi_asynchronous_email_sending;
   ```
   This ensures all emails in the queue will be processed with the new Symfony Mailer logic.

4. **Test Email Functionality**:
   - Send a test email from your Magento admin
   - Verify it appears in the queue: `SELECT * FROM hryvinskyi_asynchronous_email_sending WHERE status = 0;`
   - Run cron manually: `bin/magento cron:run`
   - Verify email was sent: `SELECT * FROM hryvinskyi_asynchronous_email_sending WHERE status = 1;`

#### Breaking Changes

| Change | Impact | Action Required |
|--------|--------|-----------------|
| PHP 8.3+ Required | High | Upgrade PHP to 8.3 or 8.4 |
| Magento 2.4.8+ Required | High | Upgrade Magento to 2.4.8+ |
| Symfony Mailer | Medium | No action needed (automatic) |
| Readonly Classes | Low | No action needed (internal change) |

#### Compatibility Notes

- **Magento 2.4.6 - 2.4.7**: Continue using version 1.0.x
- **PHP 8.1 - 8.2**: Continue using version 1.0.x with Magento 2.4.6+
- **Magento < 2.4.6**: Module is not compatible

---

## Support

For issues, questions, or contributions:
- GitHub Issues: [Report an issue](https://github.com/hryvinskyi/magento2-asynchronous-email-sending/issues)
- Email: volodymyr@hryvinskyi.com

## License

This module is open-source software licensed under the MIT License.