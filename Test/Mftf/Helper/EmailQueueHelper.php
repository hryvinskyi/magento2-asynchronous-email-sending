<?php
/**
 * Copyright (c) 2026. Volodymyr Hryvinskyi. All rights reserved.
 * Author: Volodymyr Hryvinskyi <volodymyr@hryvinskyi.com>
 * GitHub: https://github.com/hryvinskyi
 */

declare(strict_types=1);

namespace Hryvinskyi\AsynchronousEmailSending\Test\Mftf\Helper;

use Magento\FunctionalTestingFramework\Helper\Helper;

/**
 * Helper for checking email queue status in MFTF tests.
 */
class EmailQueueHelper extends Helper
{
    private const TABLE_NAME = 'hryvinskyi_asynchronous_email_sending';
    private const STATUS_PENDING = 0;
    private const STATUS_SENT = 1;
    private const STATUS_ERROR = 2;

    /**
     * Gets database connection using Magento's resource connection.
     *
     * @return \PDO
     * @throws \Exception
     */
    private function getConnection(): \PDO
    {
        $magentoRoot = $this->getMagentoRoot();
        $envPath = $magentoRoot . '/app/etc/env.php';

        if (!file_exists($envPath)) {
            throw new \Exception("Magento env.php not found at: {$envPath}");
        }

        $env = include $envPath;
        $dbConfig = $env['db']['connection']['default'] ?? null;

        if (!$dbConfig) {
            throw new \Exception("Database configuration not found in env.php");
        }

        $dsn = sprintf(
            'mysql:host=%s;dbname=%s;charset=utf8',
            $dbConfig['host'],
            $dbConfig['dbname']
        );

        return new \PDO($dsn, $dbConfig['username'], $dbConfig['password'], [
            \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION
        ]);
    }

    /**
     * Gets Magento root directory path.
     *
     * @return string
     * @throws \Exception
     */
    private function getMagentoRoot(): string
    {
        // Try MAGENTO_BP environment variable (set by MFTF)
        $magentoRoot = getenv('MAGENTO_BP');
        if ($magentoRoot && is_dir($magentoRoot)) {
            return $magentoRoot;
        }

        // Try BP constant if defined
        if (defined('BP')) {
            return BP;
        }

        // Calculate from current file path
        // Current: app/code/Hryvinskyi/AsynchronousEmailSending/Test/Mftf/Helper/EmailQueueHelper.php
        // Need to go up 7 levels to reach Magento root
        $currentDir = __DIR__;
        $magentoRoot = dirname($currentDir, 7);

        if (is_file($magentoRoot . '/app/etc/env.php')) {
            return $magentoRoot;
        }

        // Try to find env.php by walking up directories
        $searchDir = $currentDir;
        for ($i = 0; $i < 10; $i++) {
            $searchDir = dirname($searchDir);
            if (is_file($searchDir . '/app/etc/env.php')) {
                return $searchDir;
            }
        }

        throw new \Exception("Unable to determine Magento root directory. Searched from: " . $currentDir);
    }

    /**
     * Asserts the count of pending emails in the queue.
     *
     * @param int $expectedCount Expected number of pending emails
     * @return void
     * @throws \Exception If count doesn't match
     */
    public function assertPendingEmailCount(int $expectedCount): void
    {
        $actualCount = $this->getEmailCountByStatus(self::STATUS_PENDING);

        if ($actualCount !== $expectedCount) {
            throw new \Exception(
                "Expected {$expectedCount} pending emails, but found {$actualCount}"
            );
        }
    }

    /**
     * Asserts that there is at least one pending email in the queue.
     *
     * @return void
     * @throws \Exception If no pending emails found
     */
    public function assertHasPendingEmails(): void
    {
        $count = $this->getEmailCountByStatus(self::STATUS_PENDING);

        if ($count === 0) {
            throw new \Exception("No pending emails found in queue");
        }
    }

    /**
     * Asserts that there are no pending emails in the queue.
     *
     * @return void
     * @throws \Exception If pending emails found
     */
    public function assertNoPendingEmails(): void
    {
        $count = $this->getEmailCountByStatus(self::STATUS_PENDING);

        if ($count > 0) {
            throw new \Exception("Expected no pending emails, but found {$count}");
        }
    }

    /**
     * Asserts the count of sent emails in the queue.
     *
     * @param int $expectedCount Expected number of sent emails
     * @return void
     * @throws \Exception If count doesn't match
     */
    public function assertSentEmailCount(int $expectedCount): void
    {
        $actualCount = $this->getEmailCountByStatus(self::STATUS_SENT);

        if ($actualCount !== $expectedCount) {
            throw new \Exception(
                "Expected {$expectedCount} sent emails, but found {$actualCount}"
            );
        }
    }

    /**
     * Asserts that there is at least one sent email in the queue.
     *
     * @return void
     * @throws \Exception If no sent emails found
     */
    public function assertHasSentEmails(): void
    {
        $count = $this->getEmailCountByStatus(self::STATUS_SENT);

        if ($count === 0) {
            throw new \Exception("No sent emails found in queue");
        }
    }

    /**
     * Asserts the total count of emails in the queue (any status).
     *
     * @param int $expectedCount Expected total number of emails
     * @return void
     * @throws \Exception If count doesn't match
     */
    public function assertTotalEmailCount(int $expectedCount): void
    {
        $actualCount = $this->getTotalEmailCount();

        if ($actualCount !== $expectedCount) {
            throw new \Exception(
                "Expected {$expectedCount} total emails, but found {$actualCount}"
            );
        }
    }

    /**
     * Asserts that the queue is empty.
     *
     * @return void
     * @throws \Exception If queue is not empty
     */
    public function assertQueueIsEmpty(): void
    {
        $count = $this->getTotalEmailCount();

        if ($count > 0) {
            throw new \Exception("Expected empty queue, but found {$count} emails");
        }
    }

    /**
     * Asserts that at least one email with the given subject pattern exists.
     *
     * @param string $subjectPattern Subject pattern to search for (supports SQL LIKE)
     * @param int|null $status Optional status filter
     * @return void
     * @throws \Exception If no matching email found
     */
    public function assertEmailExistsWithSubject(string $subjectPattern, ?int $status = null): void
    {
        $pdo = $this->getConnection();

        $sql = "SELECT COUNT(*) FROM " . self::TABLE_NAME . " WHERE subject LIKE :subject";
        $params = ['subject' => '%' . $subjectPattern . '%'];

        if ($status !== null) {
            $sql .= " AND status = :status";
            $params['status'] = $status;
        }

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $count = (int) $stmt->fetchColumn();

        if ($count === 0) {
            $statusText = $status !== null ? " with status {$status}" : "";
            throw new \Exception(
                "No email found with subject containing '{$subjectPattern}'{$statusText}"
            );
        }
    }

    /**
     * Asserts that a pending email exists with the given subject pattern.
     *
     * @param string $subjectPattern Subject pattern to search for
     * @return void
     * @throws \Exception If no matching pending email found
     */
    public function assertPendingEmailWithSubject(string $subjectPattern): void
    {
        $this->assertEmailExistsWithSubject($subjectPattern, self::STATUS_PENDING);
    }

    /**
     * Asserts that a sent email exists with the given subject pattern.
     *
     * @param string $subjectPattern Subject pattern to search for
     * @return void
     * @throws \Exception If no matching sent email found
     */
    public function assertSentEmailWithSubject(string $subjectPattern): void
    {
        $this->assertEmailExistsWithSubject($subjectPattern, self::STATUS_SENT);
    }

    /**
     * Clears all emails from the queue.
     *
     * @return void
     */
    public function clearEmailQueue(): void
    {
        $pdo = $this->getConnection();
        $pdo->exec("TRUNCATE TABLE " . self::TABLE_NAME);
    }

    /**
     * Clears emails with specific status from the queue.
     *
     * @param int $status Status to clear (0=pending, 1=sent, 2=error)
     * @return void
     */
    public function clearEmailsByStatus(int $status): void
    {
        $pdo = $this->getConnection();
        $stmt = $pdo->prepare("DELETE FROM " . self::TABLE_NAME . " WHERE status = :status");
        $stmt->execute(['status' => $status]);
    }

    /**
     * Gets the count of emails by status.
     *
     * @param int $status Status to count
     * @return int
     */
    private function getEmailCountByStatus(int $status): int
    {
        $pdo = $this->getConnection();
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM " . self::TABLE_NAME . " WHERE status = :status");
        $stmt->execute(['status' => $status]);

        return (int) $stmt->fetchColumn();
    }

    /**
     * Gets the total count of emails in the queue.
     *
     * @return int
     */
    private function getTotalEmailCount(): int
    {
        $pdo = $this->getConnection();
        $stmt = $pdo->query("SELECT COUNT(*) FROM " . self::TABLE_NAME);

        return (int) $stmt->fetchColumn();
    }

    /**
     * Gets the count of pending emails (convenience method for tests).
     *
     * @return int
     */
    public function getPendingCount(): int
    {
        return $this->getEmailCountByStatus(self::STATUS_PENDING);
    }

    /**
     * Gets the count of sent emails (convenience method for tests).
     *
     * @return int
     */
    public function getSentCount(): int
    {
        return $this->getEmailCountByStatus(self::STATUS_SENT);
    }

    /**
     * Deletes a customer by email address.
     *
     * @param string $email Customer email address
     * @return void
     */
    public function deleteCustomerByEmail(string $email): void
    {
        $pdo = $this->getConnection();

        // Get customer entity_id
        $stmt = $pdo->prepare("SELECT entity_id FROM customer_entity WHERE email = :email");
        $stmt->execute(['email' => $email]);
        $customerId = $stmt->fetchColumn();

        if ($customerId) {
            // Delete customer (cascade will handle related tables)
            $stmt = $pdo->prepare("DELETE FROM customer_entity WHERE entity_id = :id");
            $stmt->execute(['id' => $customerId]);
        }
    }
}
