# Magento 2 / Adobe Commerce Asynchronous Email Sending

A Magento 2 module that queues all outgoing emails for asynchronous processing via cron jobs, significantly improving store performance by offloading email sending to background processes.

## Features

- **Performance Optimization**: Queues all outgoing emails instead of sending them synchronously
- **Admin Grid Interface**: Full-featured admin panel for managing email queue
- **Cron-based Processing**: Sends emails in batches via configurable cron jobs
- **Configurable Limits**: Control how many emails are sent per cron execution
- **Queue Management**: Automatic cleanup of successfully sent and failed emails
- **Mass Actions**: Delete or resend multiple emails at once from admin
- **Email Preview**: View email content directly in admin panel via iframe
- **Debug Mode**: Detailed logging for troubleshooting email issues
- **Symfony Mailer Support**: Fully compatible with Magento 2.4.8+ and Symfony Mailer

## Requirements

- **Magento**: 2.4.8 or higher
- **PHP**: 8.3 or 8.4
- **Composer**: Latest version

## Installation

### Via Composer (Recommended)

```bash
composer require hryvinskyi/magento2-asynchronous-email-sending
bin/magento module:enable Hryvinskyi_AsynchronousEmailSending
bin/magento setup:upgrade
bin/magento setup:di:compile
bin/magento cache:clean
```

### Manual Installation

1. Create the directory structure: `app/code/Hryvinskyi/AsynchronousEmailSending`
2. Copy all module files to the directory
3. Run the following commands:

```bash
bin/magento module:enable Hryvinskyi_AsynchronousEmailSending
bin/magento setup:upgrade
bin/magento setup:di:compile
bin/magento cache:clean
```

## Configuration

Navigate to: **Stores > Configuration > System > Hryvinskyi Asynchronous Email Sending**

### General Settings

| Setting | Description | Default |
|---------|-------------|---------|
| **Enable** | Enable/disable asynchronous email sending | No |
| **Sending Limit** | Maximum number of emails to send per cron execution | 10 |
| **Clear Success After (Days)** | Days to keep successfully sent emails before cleanup | 7 |
| **Clear Errors After (Days)** | Days to keep failed emails before cleanup | 30 |
| **Debug Mode** | Enable detailed logging to `var/log/hryvinskyi_asynchronous_email_sending.log` | No |

## Admin Grid Interface

Navigate to: **System > Async Email Queue**

The admin grid provides a comprehensive interface for managing queued emails:

### Grid Columns

| Column | Description |
|--------|-------------|
| **ID** | Unique email identifier |
| **Status** | Email status with colored badges (Pending, Sent, Error) |
| **Subject** | Email subject line |
| **Created At** | When the email was queued |
| **Sent At** | When the email was sent (if applicable) |
| **Actions** | View, Resend, Delete |

### Available Actions

#### Mass Actions
- **Delete**: Remove selected emails from the queue
- **Resend**: Reset selected emails to pending status for reprocessing

#### Row Actions
- **View**: Display email details including headers and content preview
- **Resend**: Queue individual email for resending
- **Delete**: Remove individual email from queue

### Email View Page

The view page displays:
- Email metadata (ID, status, timestamps)
- Parsed email headers (From, To, Subject, etc.)
- Email content preview in an iframe
- Action buttons: Back to List, Resend, Delete

### ACL Permissions

| Resource | Description |
|----------|-------------|
| `Hryvinskyi_AsynchronousEmailSending::email_queue` | Access to email queue menu |
| `Hryvinskyi_AsynchronousEmailSending::email_queue_view` | View email queue and details |
| `Hryvinskyi_AsynchronousEmailSending::email_queue_delete` | Delete emails from queue |
| `Hryvinskyi_AsynchronousEmailSending::email_queue_resend` | Resend emails |

## How It Works

### 1. Email Capture
When your Magento store attempts to send an email:
- The module intercepts the `TransportInterface::sendMessage()` call via a plugin
- Extracts the raw email message (headers + body)
- Stores it in the `hryvinskyi_asynchronous_email_sending` database table with status = `0` (pending)

### 2. Queue Processing
The cron job `hryvinskyi_asynchronous_sending_send_emails` runs periodically:
- Fetches up to N pending emails (configured via "Sending Limit")
- Parses the raw message to extract headers and body
- Reconstructs the email using Magento's mail framework
- Sends the email via configured SMTP settings
- Updates status to `1` (success) or `2` (failed)

## Logging

When debug mode is enabled, the module logs to:
```
var/log/hryvinskyi_asynchronous_email_sending.log
```

Log entries include:
- Queued email details
- Sending attempts and results
- Parsing errors
- SMTP/transport errors

## Troubleshooting

### Emails Not Being Sent

1. **Verify cron is running**:
   ```bash
   bin/magento cron:run
   ```

2. **Check queue status**:
   ```sql
   SELECT status, COUNT(*) FROM hryvinskyi_asynchronous_email_sending GROUP BY status;
   ```
   Status: 0 = Pending, 1 = Sent, 2 = Failed

3. **Enable debug mode** and check logs at `var/log/hryvinskyi_asynchronous_email_sending.log`

4. **Verify SMTP configuration** in Magento admin

### Failed Emails (Status = 2)

Check the log file for specific error messages. Common issues:
- Invalid SMTP credentials
- Network connectivity problems
- Malformed email addresses
- Missing required headers

### Manual Queue Processing

You can manually trigger email sending using the CLI command:
```bash
bin/magento hryvinskyi:email:send
```

Or via cron:
```bash
bin/magento cron:run --group=default
```

## Compatibility

| Magento Version | Module Version | PHP Version | Status            |
|----------------|----------------|-------------|-------------------|
| 2.4.8+ | 1.1.0+ | 8.3, 8.4 | Fully Supported   |
| 2.4.6 - 2.4.7 | 1.0.x | 8.1, 8.2 | Use older version |
| < 2.4.6 | Not supported | - | Not compatible    |

## Changelog

See [CHANGELOG.md](CHANGELOG.md) for version history and updates.

## License

This module is licensed under the MIT License. See [LICENSE](LICENSE) for details.

## Author

**Volodymyr Hryvinskyi**
- Email: volodymyr@hryvinskyi.com
- GitHub: [@hryvinskyi](https://github.com/hryvinskyi)