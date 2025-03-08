<?php
declare(strict_types=1);

namespace Shubo\Auth\Plugin\Controller\Adminhtml\Invoice\AbstractInvoice;

use Shubo\Auth\Helper\ScopeHelper;
use Magento\Framework\Message\ManagerInterface;
use Magento\Sales\Api\InvoiceRepositoryInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Backend\Model\View\Result\ForwardFactory;
use Magento\Sales\Controller\Adminhtml\Invoice\AbstractInvoice\View as Subject;

class View
{
    /**
     * @param InvoiceRepositoryInterface $invoiceRepository
     * @param ForwardFactory $resultForwardFactory
     * @param ManagerInterface $messageManager
     * @param ScopeHelper $scopeHelper
     */
    public function __construct(
        protected InvoiceRepositoryInterface $invoiceRepository,
        protected ForwardFactory $resultForwardFactory,
        protected ManagerInterface $messageManager,
        protected ScopeHelper $scopeHelper
    ) {}

    /**
     * @param Subject $subject
     * @param $result
     * @return Redirect|mixed
     */
    public function afterExecute(Subject $subject, $result)
    {
        $invoice = $this->invoiceRepository->get($subject->getRequest()->getParam('invoice_id'));

        if (!$invoice || !$this->scopeHelper->hasAccessToStore((int)$invoice->getStoreId())) {
            $this->messageManager->addErrorMessage(__('Invoice Does not exist.'));
            $resultForward = $this->resultForwardFactory->create();
            $resultForward->forward('noroute');

            return $resultForward;
        }

        return $result;
    }
}
