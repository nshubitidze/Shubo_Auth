<?php
declare(strict_types=1);

namespace Shubo\Auth\Plugin\Controller\Adminhtml\Product;

use Magento\Backend\Model\Auth\Session;
use Magento\Store\Api\StoreRepositoryInterface;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Catalog\Controller\Adminhtml\Product\Builder;
use Magento\Catalog\Controller\Adminhtml\Product\Edit as Subject;

class Edit
{
    public function __construct(
        protected Session $authSession,
        protected Builder $productBuilder,
        protected StoreRepositoryInterface $storeRepository,
        protected RedirectFactory $redirectFactory,
        protected ManagerInterface $messageManager
    ) {}

    public function afterExecute(Subject $subject, $result)
    {
        $adminUser = $this->authSession->getUser();
        $storeId = $adminUser->getRole()->getStoreId();

        if ($storeId) {
            $websiteId = $this->storeRepository->getById($storeId)->getWebsiteId();
            $product = $this->productBuilder->build($subject->getRequest());

            if (!in_array($websiteId, $product->getWebsiteIds())) {
                $this->messageManager->addErrorMessage(__('This product doesn\'t exist.'));
                $resultRedirect = $this->redirectFactory->create();

                return $resultRedirect->setPath('catalog/*');
            }
        }

        return $result;
    }
}
