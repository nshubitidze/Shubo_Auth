<?php
declare(strict_types=1);

namespace Shubo\Auth\Plugin\Controller\Adminhtml\Shipment\AbstractShipment;

use Shubo\Auth\Helper\ScopeHelper;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Sales\Api\ShipmentRepositoryInterface;
use Magento\Backend\Model\View\Result\ForwardFactory;
use Magento\Sales\Controller\Adminhtml\Shipment\AbstractShipment\View as Subject;

class View
{
    /**
     * @param ShipmentRepositoryInterface $shipmentRepository
     * @param ManagerInterface $messageManager
     * @param ForwardFactory $resultForwardFactory
     * @param ScopeHelper $scopeHelper
     */
    public function __construct(
        protected ShipmentRepositoryInterface $shipmentRepository,
        protected ManagerInterface $messageManager,
        protected ForwardFactory $resultForwardFactory,
        protected ScopeHelper $scopeHelper
    ) {}

    /**
     * @param Subject $subject
     * @param $result
     * @return Redirect|mixed
     */
    public function afterExecute(Subject $subject, $result)
    {
        $shipment = $this->shipmentRepository->get($subject->getRequest()->getParam('shipment_id'));

        if (!$this->scopeHelper->hasAccessToStore((int)$shipment->getStoreId())) {
            $this->messageManager->addErrorMessage(__('Shipment Does not exist.'));
            $resultForward = $this->resultForwardFactory->create();
            $resultForward->forward('noroute');

            return $resultForward;
        }

        return $result;
    }
}
