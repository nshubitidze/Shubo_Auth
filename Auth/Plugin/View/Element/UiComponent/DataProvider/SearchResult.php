<?php
declare(strict_types=1);

namespace Shubo\Auth\Plugin\View\Element\UiComponent\DataProvider;

use Magento\Backend\Model\Auth\Session;
use Magento\Framework\View\Element\UiComponent\DataProvider\SearchResult as Subject;

class SearchResult
{
    /**
     * @param Session $authSession
     */
    public function __construct(
        protected Session $authSession,
    ) {}

    /**
     * @param Subject $subject
     * @param bool $printQuery
     * @param bool $logQuery
     * @return array|false[]
     */
    public function beforeLoad(Subject $subject, bool $printQuery = false, bool $logQuery = false): array
    {
        $adminUser = $this->authSession->getUser();

        if ($storeId = $adminUser->getRole()->getStoreId()) {
            $subject->addFieldToFilter('store_id', $storeId);
        }

        return [$printQuery, $logQuery];
    }
}
