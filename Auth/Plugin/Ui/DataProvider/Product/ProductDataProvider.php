<?php
declare(strict_types=1);

namespace Shubo\Auth\Plugin\Ui\DataProvider\Product;

use Magento\Catalog\Ui\DataProvider\Product\ProductDataProvider as Subject;
use Magento\Backend\Model\Auth\Session;

class ProductDataProvider
{
    /**
     * @param Session $authSession
     */
    public function __construct(
        protected Session $authSession,
    ) {}

    /**
     * @param Subject $subject
     * @param $result
     * @return mixed
     */
    public function afterGetCollection(Subject $subject, $result)
    {
        $adminUser = $this->authSession->getUser();

        if ($storeId = $adminUser->getRole()->getStoreId()) {
            $result->addStoreFilter($storeId);
        }

        return $result;
    }
}
