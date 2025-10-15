<?php
/**
 * Copyright (c) 2020-2025. Volodymyr Hryvinskyi. All rights reserved.
 * Author: Volodymyr Hryvinskyi <volodymyr@hryvinskyi.com>
 * GitHub: https://github.com/hryvinskyi
 */

declare(strict_types=1);

namespace Hryvinskyi\AsynchronousEmailSending\Model;

use Exception;
use Hryvinskyi\AsynchronousEmailSending\Api\AsyncEmailRepositoryInterface;
use Hryvinskyi\AsynchronousEmailSending\Api\Data\AsyncEmailInterface;
use Hryvinskyi\AsynchronousEmailSending\Api\Data\AsyncEmailInterfaceFactory;
use Hryvinskyi\AsynchronousEmailSending\Api\Data\AsyncEmailSearchResultsInterface;
use Hryvinskyi\AsynchronousEmailSending\Api\Data\AsyncEmailSearchResultsInterfaceFactory;
use Hryvinskyi\AsynchronousEmailSending\Model\ResourceModel\AsyncEmail as AsyncEmailResource;
use Hryvinskyi\AsynchronousEmailSending\Model\ResourceModel\AsyncEmail\CollectionFactory;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SortOrder;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Mail\TransportInterface;

/**
 * Class AsyncEmailRepository
 *
 * @package Hryvinskyi\AsynchronousEmailSending\Model
 */
class AsyncEmailRepository implements AsyncEmailRepositoryInterface
{
    public function __construct(
        private readonly AsyncEmailResource $resource,
        private readonly AsyncEmailInterfaceFactory $entityFactory,
        private readonly CollectionFactory $collectionFactory,
        private readonly AsyncEmailSearchResultsInterfaceFactory $searchResultFactory,
        private readonly SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
    }

    /**
     * @inheritdoc
     */
    public function save(AsyncEmailInterface $asyncEmail): AsyncEmailInterface
    {
        try {
            $this->resource->save($asyncEmail);
        } catch (Exception $exception) {
            throw new CouldNotSaveException(__($exception->getMessage()));
        }

        return $asyncEmail;
    }

    /**
     * @inheritdoc
     */
    public function saveTransport(TransportInterface $transport): AsyncEmailInterface
    {
        try {
            $asyncEmail = $this->save($this->populateAsyncEmail($transport->getMessage()));
        } catch (Exception $exception) {
            throw new CouldNotSaveException(__($exception->getMessage()));
        }

        return $asyncEmail;
    }

    /**
     * @inheritdoc
     */
    public function getPendingItems(int $size): AsyncEmailSearchResultsInterface
    {
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter(AsyncEmailInterface::STATUS, 0)
            ->setPageSize($size)
            ->setCurrentPage(1)
            ->create();

        return $this->getList($searchCriteria);
    }

    /**
     * @inheritdoc
     */
    public function getById($asyncEmailId)
    {
        $asyncEmail = $this->entityFactory->create();
        $this->resource->load($asyncEmail, $asyncEmailId);
        if (!$asyncEmail->getId()) {
            throw new NoSuchEntityException(__('AsyncEmail with id "%1" does not exist.', $asyncEmailId));
        }

        return $asyncEmail;
    }

    /**
     * @inheritdoc
     */
    public function findById($asyncEmailId)
    {
        $asyncEmail = $this->entityFactory->create();
        $this->resource->load($asyncEmail, $asyncEmailId);

        if (!$asyncEmail->getId()) {
            return null;
        }

        return $asyncEmail;
    }

    /**
     * @inheritdoc
     */
    public function getList(SearchCriteriaInterface $searchCriteria): AsyncEmailSearchResultsInterface
    {
        $searchResult = $this->searchResultFactory->create();
        $searchResult->setSearchCriteria($searchCriteria);
        $collection = $this->collectionFactory->create();

        foreach ($searchCriteria->getFilterGroups() as $filterGroup) {
            foreach ($filterGroup->getFilters() as $filter) {
                $condition = $filter->getConditionType() ?: 'eq';
                $collection->addFieldToFilter($filter->getField(), [$condition => $filter->getValue()]);
            }
        }

        $sortOrders = $searchCriteria->getSortOrders();
        $searchResult->setTotalCount($collection->getSize());
        if ($sortOrders) {
            foreach ($sortOrders as $sortOrder) {
                $collection->addOrder(
                    $sortOrder->getField(),
                    ($sortOrder->getDirection() === SortOrder::SORT_ASC) ? 'ASC' : 'DESC'
                );
            }
        }
        $collection->setCurPage($searchCriteria->getCurrentPage());
        $collection->setPageSize($searchCriteria->getPageSize());

        return $searchResult->setItems($collection->getItems());
    }

    /**
     * @inheritdoc
     */
    public function delete(AsyncEmailInterface $asyncEmail)
    {
        try {
            $this->resource->delete($asyncEmail);
        } catch (Exception $exception) {
            throw new CouldNotDeleteException(__($exception->getMessage()));
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function deleteById($asyncEmailId)
    {
        return $this->delete($this->getById($asyncEmailId));
    }


    /**
     * @param \Magento\Framework\Mail\MessageInterface|object $message
     *
     * @return AsyncEmailInterface
     * @throw LocalizedException
     */
    private function populateAsyncEmail($message): AsyncEmailInterface
    {
        /** @var AsyncEmailInterface $entity */
        $entity = $this->entityFactory->create();
        $bodyObject = $message->getBody();

        $rawContent = false;

        if (method_exists($message, 'getRawMessage')) {
            $rawContent = $message->getRawMessage();
        }

        if (method_exists($bodyObject, 'getRawContent')) {
            $rawContent = $bodyObject->getRawContent();
        }

        if ($rawContent === false) {
            throw new LocalizedException(__('Not found raw emil message content.'));
        }

        $subject = $message->getSubject();

        if (function_exists('imap_utf8')) {
            $subject = imap_utf8($subject);
        }

        return $entity->setRawMessage((string)$rawContent)->setSubject($subject);
    }

    /**
     * Clear old emails by status
     *
     * @param int $days Number of days to keep
     * @param int $status Status to clear (AsyncEmailInterface::STATUS_ERROR or AsyncEmailInterface::STATUS_SENT)
     *
     * @throws \Hryvinskyi\AsynchronousEmailSending\Exception\InvalidStatusException
     */
    public function clear(int $days, int $status): void
    {
        $this->resource->clear($days, $status);
    }
}
