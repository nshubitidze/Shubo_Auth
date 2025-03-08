<?php
declare(strict_types=1);

namespace Shubo\Auth\Plugin\Controller\Adminhtml;

use Magento\Framework\Registry;
use Shubo\Auth\Helper\ScopeHelper;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Sales\Controller\Adminhtml\Order as Subject;
use Magento\Framework\Controller\Result\RedirectFactory;

class Order
{
    /**
     * @param Registry $registry
     * @param RedirectFactory $redirectFactory
     * @param ManagerInterface $messageManager
     * @param ScopeHelper $scopeHelper
     */
    public function __construct(
        protected Registry $registry,
        protected RedirectFactory $redirectFactory,
        protected ManagerInterface $messageManager,
        protected ScopeHelper $scopeHelper
    ) {}

    /**
     * @param Subject $subject
     * @param $result
     * @return Redirect|mixed
     */
    public function afterExecute(Subject $subject, $result) {
        $order = $this->registry->registry('current_order');

        if ($order && !$this->scopeHelper->hasAccessToStore((int)$order->getStoreId())) {
            $this->messageManager->addErrorMessage(__('Order Does not exist.'));
            $resultRedirect = $this->redirectFactory->create();
            $resultRedirect->setPath('sales/*/');

            return $resultRedirect;
        }

        return $result;
    }
}
