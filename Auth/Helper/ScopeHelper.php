<?php
declare(strict_types=1);

namespace Shubo\Auth\Helper;

use Magento\Backend\Model\Auth\Session;
use Magento\Store\Api\StoreRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;

class ScopeHelper
{
    protected const DEFAULT_STORES = [0, 1];
    protected const DEFAULT_WEBSITES = [0, 1];

    /**
     * @param StoreRepositoryInterface $storeRepository
     * @param Session $authSession
     */
    public function __construct(
        protected StoreRepositoryInterface $storeRepository,
        protected Session $authSession,
    ) {}

    /**
     * @param int|array $store
     * @return bool
     */
    public function hasAccessToStore(int|array $store): bool
    {
        $adminStore = $this->getAdminStore();

        if (in_array($adminStore, self::DEFAULT_STORES)) return true;

        if (is_array($store)) return in_array($adminStore, $store);

        return $adminStore === $store;
    }

    /**
     * @param int|array $website
     * @throws NoSuchEntityException
     * @return bool
     */
    public function hasAccessToWebsite(int|array $website): bool
    {
        $adminStore = $this->getAdminStore();
        $adminWebsiteId = $this->storeRepository->getById($adminStore)->getWebsiteId();

        if (in_array($adminWebsiteId, self::DEFAULT_WEBSITES)) return true;

        if (is_array($website)) return in_array($adminWebsiteId, $website);

        return $adminWebsiteId === $website;
    }

    /**
     * @return int
     */
    public function getAdminStore(): int
    {
        $adminUser = $this->authSession->getUser();

        return (int)$adminUser->getRole()->getStoreId();
    }
}
