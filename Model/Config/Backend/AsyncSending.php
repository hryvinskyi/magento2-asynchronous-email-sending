<?php
/**
 * Copyright (c) 2020. Volodymyr Hryvinskyi.  All rights reserved.
 * @author: <mailto:volodymyr@hryvinskyi.com>
 * @github: <https://github.com/hryvinskyi>
 */

declare(strict_types=1);

namespace Hryvinskyi\AsynchronousEmailSending\Model\Config\Backend;

use Hryvinskyi\AsynchronousEmailSending\Model\Config;
use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\App\Config\Value;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;

/**
 * Class AsyncSending
 */
class AsyncSending extends Value
{
    /**
     * @var WriterInterface
     */
    private $writer;

    /**
     * AsyncSending constructor.
     *
     * @param Context $context
     * @param Registry $registry
     * @param ScopeConfigInterface $config
     * @param TypeListInterface $cacheTypeList
     * @param WriterInterface $writer
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        ScopeConfigInterface $config,
        TypeListInterface $cacheTypeList,
        WriterInterface $writer,
        ?AbstractResource $resource = null,
        ?AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $registry,
            $config,
            $cacheTypeList,
            $resource,
            $resourceCollection,
            $data
        );

        $this->writer = $writer;
    }

    /**
     * @return $this|AsyncSending
     */
    public function afterSave()
    {
        if ($this->isValueChanged()) {
            $this->writer->save(Config::XML_CONF_SALES_EMAIL_ASYNC_SENDING, !$this->getValue());

            $state = $this->getValue() ? 'disabled' : 'enabled';

            $this->_eventManager->dispatch(
                $this->_eventPrefix . '_sales_email_general_async_sending_' . $state,
                $this->_getEventData()
            );
        }

        return $this;
    }
}
