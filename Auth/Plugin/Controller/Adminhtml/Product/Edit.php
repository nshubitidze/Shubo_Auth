<?php
declare(strict_types=1);

namespace Shubo\Auth\Plugin\Controller\Adminhtml\Product;

use Shubo\Auth\Helper\ScopeHelper;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Catalog\Controller\Adminhtml\Product\Builder;
use Magento\Catalog\Controller\Adminhtml\Product\Edit as Subject;

class Edit
{
    /**
     * @param Builder $productBuilder
     * @param RedirectFactory $redirectFactory
     * @param ManagerInterface $messageManager
     * @param ScopeHelper $scopeHelper
     */
    public function __construct(
        protected Builder $productBuilder,
        protected RedirectFactory $redirectFactory,
        protected ManagerInterface $messageManager,
        protected ScopeHelper $scopeHelper
    ) {}

    /**
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    public function afterExecute(Subject $subject, $result)
    {
        $product = $this->productBuilder->build($subject->getRequest());

        if (!$this->scopeHelper->hasAccessToWebsite($product->getWebsiteIds())) {
            $this->messageManager->addErrorMessage(__('This product doesn\'t exist.'));
            $resultRedirect = $this->redirectFactory->create();

            return $resultRedirect->setPath('catalog/*');
        }

        return $result;
    }
}
