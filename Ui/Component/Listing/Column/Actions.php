<?php
/**
 * Copyright (c) 2026. Volodymyr Hryvinskyi. All rights reserved.
 * Author: Volodymyr Hryvinskyi <volodymyr@hryvinskyi.com>
 * GitHub: https://github.com/hryvinskyi
 */

declare(strict_types=1);

namespace Hryvinskyi\AsynchronousEmailSending\Ui\Component\Listing\Column;

use Magento\Framework\Escaper;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

/**
 * Actions column for async email grid.
 */
class Actions extends Column
{
    private const string URL_PATH_VIEW = 'hryvinskyi_asyncemail/email/view';
    private const string URL_PATH_DELETE = 'hryvinskyi_asyncemail/email/delete';
    private const string URL_PATH_RESEND = 'hryvinskyi_asyncemail/email/resend';

    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param UrlInterface $urlBuilder
     * @param Escaper $escaper
     * @param array<string, mixed> $components
     * @param array<string, mixed> $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        private readonly UrlInterface $urlBuilder,
        private readonly Escaper $escaper,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * Prepare data source with actions.
     *
     * @param array<string, mixed> $dataSource
     * @return array<string, mixed>
     */
    public function prepareDataSource(array $dataSource): array
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as &$item) {
                if (isset($item['entity_id'])) {
                    $name = $this->getData('name');
                    $subject = $this->escaper->escapeHtml($item['subject'] ?? '');

                    $item[$name]['view'] = [
                        'href' => $this->urlBuilder->getUrl(
                            self::URL_PATH_VIEW,
                            ['entity_id' => $item['entity_id']]
                        ),
                        'label' => __('View')
                    ];

                    $item[$name]['resend'] = [
                        'href' => $this->urlBuilder->getUrl(
                            self::URL_PATH_RESEND,
                            ['entity_id' => $item['entity_id']]
                        ),
                        'label' => __('Resend'),
                        'confirm' => [
                            'title' => __('Resend Email'),
                            'message' => __('Are you sure you want to resend email "%1"?', $subject)
                        ]
                    ];

                    $item[$name]['delete'] = [
                        'href' => $this->urlBuilder->getUrl(
                            self::URL_PATH_DELETE,
                            ['entity_id' => $item['entity_id']]
                        ),
                        'label' => __('Delete'),
                        'confirm' => [
                            'title' => __('Delete Email'),
                            'message' => __('Are you sure you want to delete email "%1"?', $subject)
                        ],
                        'post' => true
                    ];
                }
            }
        }

        return $dataSource;
    }
}
