<?php
/**
 * Copyright (c) 2020-2025. Volodymyr Hryvinskyi. All rights reserved.
 * Author: Volodymyr Hryvinskyi <volodymyr@hryvinskyi.com>
 * GitHub: https://github.com/hryvinskyi
 */

declare(strict_types=1);

namespace Hryvinskyi\AsynchronousEmailSending\Model\Service;

use Hryvinskyi\AsynchronousEmailSending\Api\Message\ParsedMessageInterface;
use Hryvinskyi\AsynchronousEmailSending\Api\Message\ParsedMessagePartInterface;
use Hryvinskyi\AsynchronousEmailSending\Api\Service\MessagePopulatorInterface;
use Hryvinskyi\AsynchronousEmailSending\Model\MailMessage;
use Hryvinskyi\AsynchronousEmailSending\Model\MailMessageFactory;
use InvalidArgumentException;
use Magento\Framework\Mail\MimeMessageInterfaceFactory;
use Symfony\Component\Mime\Header\Headers;
use Symfony\Component\Mime\Message;
use Symfony\Component\Mime\Part\AbstractPart;
use Symfony\Component\Mime\Part\DataPart;
use Symfony\Component\Mime\Part\Multipart\MixedPart;
use Symfony\Component\Mime\Part\TextPart;

/**
 * Service responsible for creating MailMessage instances from parsed messages
 */
class MessagePopulator implements MessagePopulatorInterface
{
    private const DEFAULT_CHARSET = 'utf-8';
    private const DEFAULT_SUBTYPE = 'html';
    private const TEMP_EMAIL = 'dummy@temp.local';

    public function __construct(
        private readonly MailMessageFactory $mailMessageFactory,
        private readonly MimeMessageInterfaceFactory $mimeMessageFactory,
    ) {
    }

    /**
     * @inheritDoc
     */
    public function createMailMessage(ParsedMessageInterface $parsedMessage): MailMessage
    {
        $this->validateHeaders($parsedMessage->getHeaders());

        $bodyPart = $this->createBodyPart($parsedMessage->getParts());
        $symfonyMessage = new Message($parsedMessage->getHeaders(), $bodyPart);

        return $this->buildMailMessage($symfonyMessage);
    }

    /**
     * Validate required headers
     *
     * @param Headers $headers
     * @throws InvalidArgumentException
     */
    private function validateHeaders(Headers $headers): void
    {
        if (!$headers->has('To')) {
            throw new InvalidArgumentException('Email message must have at least one "To" addressee');
        }
    }

    /**
     * Create body part from parsed message parts
     *
     * @param ParsedMessagePartInterface[] $parsedParts
     * @return AbstractPart
     */
    private function createBodyPart(array $parsedParts): AbstractPart
    {
        $symfonyParts = $this->convertParsedPartsToSymfonyParts($parsedParts);

        return $this->combineSymfonyParts($symfonyParts);
    }

    /**
     * Convert parsed message parts to Symfony parts
     *
     * @param ParsedMessagePartInterface[] $parsedParts
     * @return AbstractPart[]
     */
    private function convertParsedPartsToSymfonyParts(array $parsedParts): array
    {
        $symfonyParts = [];

        foreach ($parsedParts as $parsedPart) {
            $symfonyParts[] = $this->createSymfonyPart($parsedPart);
        }

        return $symfonyParts;
    }

    /**
     * Create appropriate Symfony part based on parsed part type
     *
     * @param ParsedMessagePartInterface $parsedPart
     * @return AbstractPart
     */
    private function createSymfonyPart(ParsedMessagePartInterface $parsedPart): AbstractPart
    {
        if ($parsedPart->isAttachment()) {
            return $this->createAttachmentPart($parsedPart);
        }

        if ($parsedPart->isHtml()) {
            return $this->createHtmlPart($parsedPart);
        }

        if ($parsedPart->isText()) {
            return $this->createTextPart($parsedPart);
        }

        // Default to HTML for unknown content types
        return $this->createDefaultPart($parsedPart);
    }

    /**
     * Create attachment part
     *
     * @param ParsedMessagePartInterface $parsedPart
     * @return DataPart
     */
    private function createAttachmentPart(ParsedMessagePartInterface $parsedPart): DataPart
    {
        return new DataPart(
            $parsedPart->getContent(),
            $parsedPart->getFilename(),
            $parsedPart->getContentType()
        );
    }

    /**
     * Create HTML text part
     *
     * @param ParsedMessagePartInterface $parsedPart
     * @return TextPart
     */
    private function createHtmlPart(ParsedMessagePartInterface $parsedPart): TextPart
    {
        return new TextPart(
            $parsedPart->getContent(),
            $parsedPart->getCharset(),
            'html'
        );
    }

    /**
     * Create plain text part
     *
     * @param ParsedMessagePartInterface $parsedPart
     * @return TextPart
     */
    private function createTextPart(ParsedMessagePartInterface $parsedPart): TextPart
    {
        return new TextPart(
            $parsedPart->getContent(),
            $parsedPart->getCharset(),
            'plain'
        );
    }

    /**
     * Create default part (HTML)
     *
     * @param ParsedMessagePartInterface $parsedPart
     * @return TextPart
     */
    private function createDefaultPart(ParsedMessagePartInterface $parsedPart): TextPart
    {
        return new TextPart(
            $parsedPart->getContent(),
            $parsedPart->getCharset(),
            self::DEFAULT_SUBTYPE
        );
    }

    /**
     * Combine Symfony parts into appropriate structure
     *
     * @param AbstractPart[] $symfonyParts
     * @return AbstractPart
     */
    private function combineSymfonyParts(array $symfonyParts): AbstractPart
    {
        return match (count($symfonyParts)) {
            0 => $this->createEmptyBodyPart(),
            1 => $symfonyParts[0],
            default => new MixedPart(...$symfonyParts)
        };
    }

    /**
     * Create empty body part as fallback
     *
     * @return TextPart
     */
    private function createEmptyBodyPart(): TextPart
    {
        return new TextPart('', self::DEFAULT_CHARSET, self::DEFAULT_SUBTYPE);
    }

    /**
     * Build final MailMessage with Symfony message injected
     *
     * @param Message $symfonyMessage
     * @return MailMessage
     */
    private function buildMailMessage(Message $symfonyMessage): MailMessage
    {
        // Create a minimal MailMessage with temporary data
        // The actual message content comes from the Symfony message
        $mailMessage = $this->mailMessageFactory->create([
            'body' => $this->mimeMessageFactory->create(['parts' => []]),
            'to' => [['email' => self::TEMP_EMAIL, 'name' => '']]
        ]);

        // Inject the properly constructed Symfony message
        $mailMessage->setSymfonyMessage($symfonyMessage);

        return $mailMessage;
    }
}