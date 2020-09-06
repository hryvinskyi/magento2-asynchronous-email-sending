<?php
/**
 * Copyright (c) 2020. Volodymyr Hryvinskyi.  All rights reserved.
 * @author: <mailto:volodymyr@hryvinskyi.com>
 * @github: <https://github.com/hryvinskyi>
 */

declare(strict_types=1);

namespace Hryvinskyi\AsynchronousEmailSending\Block\System\Config\Form\Field;

use Hryvinskyi\AsynchronousEmailSending\Model\Config;
use Magento\Backend\Block\Template\Context;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;

/**
 * Class Disable
 */
class Disable extends Field
{
    /**
     * @var Config
     */
    private $config;

    /**
     * Disable constructor.
     *
     * @param Context $context
     * @param Config $config
     * @param array $data
     */
    public function __construct(
        Context $context,
        Config $config,
        array $data = []
    ) {
        parent::__construct($context, $data);

        $this->config = $config;
    }

    /**
     * Retrieve element HTML markup
     *
     * @param AbstractElement $element
     *
     * @return string
     */
    protected function _getElementHtml(AbstractElement $element)
    {
        if ($this->config->isEnabled()) {
            $element->setDisabled('disabled');
            $element->setComment(
                'This configuration is disabled because the <a href="' .
                $this->_urlBuilder->getUrl('adminhtml/system_config/edit/section/hryvinskyi_asynchronous_sending') .
                '">Hryvinskyi Asynchronous Email Sending</a> module is enabled.'
            );
        }

        return $element->getElementHtml();
    }
}
