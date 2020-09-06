<?php
/**
 * Copyright (c) 2020. Volodymyr Hryvinskyi.  All rights reserved.
 * @author: <mailto:volodymyr@hryvinskyi.com>
 * @github: <https://github.com/hryvinskyi>
 */

declare(strict_types=1);

namespace Hryvinskyi\AsynchronousEmailSending\Api;

use Hryvinskyi\AsynchronousEmailSending\Api\Data\AsyncEmailInterface;
use Hryvinskyi\AsynchronousEmailSending\Api\Data\AsyncEmailSearchResultsInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Mail\TransportInterface;

/**
 * Interface AsyncEmailRepositoryInterface
 *
 * @package Hryvinskyi\AsynchronousEmailSending\Api
 */
interface AsyncEmailRepositoryInterface
{
    /**
     * Save AsyncEmail
     *
     * @param AsyncEmailInterface $asyncEmail
     *
     * @return AsyncEmailInterface
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    public function save(AsyncEmailInterface $asyncEmail): AsyncEmailInterface;

    /**
     * Save transport email to queue
     *
     * @param TransportInterface $transport
     *
     * @return AsyncEmailInterface
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    public function saveTransport(TransportInterface $transport): AsyncEmailInterface;

    /**
     * @param int $size
     *
     * @return AsyncEmailSearchResultsInterface
     */
    public function getPendingItems(int $size): AsyncEmailSearchResultsInterface;

    /**
     * Get AsyncEmail by id.
     *
     * @param int $entity_id
     *
     * @return AsyncEmailInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getById(int $entity_id);

    /**
     * Find AsyncEmail by id.
     *
     * @param int $entity_id
     *
     * @return AsyncEmailInterface|null
     */
    public function findById(int $entity_id);

    /**
     * Retrieve AsyncEmail matching the specified criteria.
     *
     * @param SearchCriteriaInterface $searchCriteria
     *
     * @return AsyncEmailSearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(SearchCriteriaInterface $searchCriteria): AsyncEmailSearchResultsInterface;

    /**
     * Delete AsyncEmail
     *
     * @param AsyncEmailInterface $asyncEmail
     *
     * @return bool
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     */
    public function delete(AsyncEmailInterface $asyncEmail);

    /**
     * Delete AsyncEmail by ID.
     *
     * @param int $asyncEmailId
     *
     * @return bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     */
    public function deleteById($asyncEmailId);
}
