<?php
declare(strict_types=1);

namespace Shubo\Auth\Plugin\Controller\Adminhtml\Shipment\AbstractShipment;

use Magento\Backend\Model\Auth\Session;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Sales\Api\ShipmentRepositoryInterface;
use Magento\Backend\Model\View\Result\ForwardFactory;
use Magento\Sales\Controller\Adminhtml\Shipment\AbstractShipment\View as Subject;

class View
{
    /**
     * @param Session $authSession
     * @param ShipmentRepositoryInterface $shipmentRepository
     * @param ManagerInterface $messageManager
     * @param ForwardFactory $resultForwardFactory
     */
    public function __construct(
        protected Session $authSession,
        protected ShipmentRepositoryInterface $shipmentRepository,
        protected ManagerInterface $messageManager,
        protected ForwardFactory $resultForwardFactory
    ) {}

    /**
     * @param Subject $subject
     * @param $result
     * @return Redirect|mixed
     */
    public function afterExecute(Subject $subject, $result)
    {
        $adminUser = $this->authSession->getUser();
        $storeId = $adminUser->getRole()->getStoreId();

        if ($storeId) {
            $shipment = $this->shipmentRepository->get($subject->getRequest()->getParam('shipment_id'));

            if ($shipment && $storeId !== $shipment->getStoreId()) {
                $this->messageManager->addErrorMessage(__('Shipment Does not exist.'));
                $resultForward = $this->resultForwardFactory->create();
                $resultForward->forward('noroute');

                return $resultForward;
            }
        }

        return $result;
    }
}
