<?php
/**
 * Copyright (c) 2026. Volodymyr Hryvinskyi. All rights reserved.
 * Author: Volodymyr Hryvinskyi <volodymyr@hryvinskyi.com>
 * GitHub: https://github.com/hryvinskyi
 */

declare(strict_types=1);

namespace Hryvinskyi\AsynchronousEmailSending\Console\Command;

use Hryvinskyi\AsynchronousEmailSending\Model\EmailSenderHandlerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Console command to send queued emails.
 */
class SendEmailsCommand extends Command
{
    /**
     * @param EmailSenderHandlerInterface $emailSenderHandler
     * @param string|null $name
     */
    public function __construct(
        private readonly EmailSenderHandlerInterface $emailSenderHandler,
        ?string $name = null
    ) {
        parent::__construct($name);
    }

    /**
     * @inheritDoc
     */
    protected function configure(): void
    {
        $this->setName('hryvinskyi:email:send');
        $this->setDescription('Process and send queued asynchronous emails');
        parent::configure();
    }

    /**
     * @inheritDoc
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('<info>Processing queued emails...</info>');

        try {
            $this->emailSenderHandler->sendEmails();
            $output->writeln('<info>Email queue processed successfully.</info>');
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $output->writeln('<error>Error processing email queue: ' . $e->getMessage() . '</error>');
            return Command::FAILURE;
        }
    }
}
