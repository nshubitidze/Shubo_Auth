<?php
declare(strict_types=1);

namespace Shubo\Auth\Block\Role\Tab;

use Magento\Integration\Helper\Data;
use Magento\Framework\Acl\RootResource;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Backend\Block\Template\Context;
use Magento\Authorization\Model\Acl\AclRetriever;
use Magento\User\Block\Role\Tab\Edit as SourceEdit;
use Magento\Framework\Acl\AclResource\ProviderInterface;
use Magento\Authorization\Model\ResourceModel\Rules\CollectionFactory;
use Magento\Authorization\Model\ResourceModel\Role\CollectionFactory as RoleCollectionFactory;

class Edit extends SourceEdit
{
    public function __construct(
        protected RoleCollectionFactory $roleCollectionFactory,
        Context $context,
        AclRetriever $aclRetriever,
        RootResource $rootResource,
        CollectionFactory $rulesCollectionFactory,
        ProviderInterface $aclResourceProvider,
        Data $integrationData,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $aclRetriever,
            $rootResource,
            $rulesCollectionFactory,
            $aclResourceProvider,
            $integrationData, $data
        );
    }

    /**
     * @var string
     */
    protected $_template = 'Shubo_Auth::role/edit.phtml';

    /**
     * @return StoreInterface[]
     */
    public function getAvailableStores()
    {
        return $this->_storeManager->getStores();
    }

    /**
     * @return int
     */
    public function getSelectedStore(): int
    {
        $roleId = $this->_request->getParam('rid', false);

        if (!$roleId) return 0;

        $roleCollection = $this->roleCollectionFactory->create();
        $role = $roleCollection->getItemById($roleId);

        return (int)$role?->getStoreId();
    }
}
