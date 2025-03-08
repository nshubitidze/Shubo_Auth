<?php
declare(strict_types=1);

namespace Shubo\Auth\Plugin\Controller\Adminhtml;

use Magento\Framework\Registry;
use Magento\Backend\Model\Auth\Session;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Sales\Controller\Adminhtml\Order as Subject;
use Magento\Framework\Controller\Result\RedirectFactory;

class Order
{
    /**
     * @param Registry $registry
     * @param Session $authSession
     * @param RedirectFactory $redirectFactory
     * @param ManagerInterface $messageManager
     */
    public function __construct(
        protected Registry $registry,
        protected Session $authSession,
        protected RedirectFactory $redirectFactory,
        protected ManagerInterface $messageManager
    ) {}

    /**
     * @param Subject $subject
     * @param $result
     * @return Redirect|mixed
     */
    public function afterExecute(Subject $subject, $result) {
        $adminUser = $this->authSession->getUser();
        $storeId = $adminUser->getRole()->getStoreId();

        if ($storeId) {
            $order = $this->registry->registry('current_order');

            if ($order && $storeId !== $order->getStoreId()) {
                $this->messageManager->addErrorMessage(__('Order Does not exist.'));
                $resultRedirect = $this->redirectFactory->create();
                $resultRedirect->setPath('sales/*/');

                return $resultRedirect;
            }
        }

        return $result;
    }
}
