<?php
declare(strict_types=1);

namespace Shubo\Auth\Controller\Adminhtml\User\Role;

use Magento\Authorization\Model\Role;
use Magento\Framework\Acl\RootResource;
use Magento\Framework\App\ObjectManager;
use Magento\Security\Model\SecurityCookie;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Security\Model\AdminSessionsManager;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\AuthenticationException;
use Magento\User\Controller\Adminhtml\User\Role\SaveRole as SourceSaveRole;
use Magento\Framework\Controller\ResultFactory;
use Magento\Authorization\Model\Acl\Role\Group as RoleGroup;
use Magento\Authorization\Model\UserContextInterface;
use Magento\Framework\Exception\State\UserLockedException;

class SaveRole extends SourceSaveRole
{
    /**
     * @var SecurityCookie
     */
    protected $securityCookie;

    /**
     * @return Redirect
     */
    public function execute()
    {
        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);

        $rid = $this->getRequest()->getParam('role_id', false);
        $resource = $this->getRequest()->getParam('resource', false);
        $oldRoleUsers = $this->parseRequestVariable('in_role_user_old');
        $roleUsers = $this->parseRequestVariable('in_role_user');
        $isAll = $this->getRequest()->getParam('all');
        $storeId = (int) $this->getRequest()->getPost('store_id', 0);

        if ($isAll) {
            $resource = [$this->_objectManager->get(RootResource::class)->getId()];
        }

        $role = $this->_initRole('role_id');
        if (!$role->getId() && $rid) {
            $this->messageManager->addError(__('This role no longer exists.'));
            return $resultRedirect->setPath('adminhtml/*/');
        }

        try {
            $this->validateUser();
            $roleName = $this->_filterManager->removeTags($this->getRequest()->getParam('rolename', false));
            $role->setName($roleName)
                ->setPid($this->getRequest()->getParam('parent_id', false))
                ->setStoreId($storeId)
                ->setRoleType(RoleGroup::ROLE_TYPE)
                ->setUserType(UserContextInterface::USER_TYPE_ADMIN);
            if ($this->getRequest()->getParam('gws_is_all', false)) {
                $role->setGwsWebsites(null)->setGwsStoreGroups(null);
            }
            $this->_eventManager->dispatch(
                'admin_permissions_role_prepare_save',
                ['object' => $role, 'request' => $this->getRequest()]
            );
            $this->processPreviousUsers($role, $oldRoleUsers);
            $this->processCurrentUsers($role, $roleUsers);

            $role->save();
            $this->_rulesFactory->create()->setRoleId($role->getId())->setResources($resource)->saveRel();

            $this->messageManager->addSuccessMessage(__('You saved the role.'));
        } catch (UserLockedException $e) {
            $this->_auth->logout();
            $this->getSecurityCookie()->setLogoutReasonCookie(
                AdminSessionsManager::LOGOUT_REASON_USER_LOCKED
            );
            return $resultRedirect->setPath('*');
        } catch (AuthenticationException $e) {
            $this->messageManager->addErrorMessage(
                __('The password entered for the current user is invalid. Verify the password and try again.')
            );
            return $this->saveDataToSessionAndRedirect($role, $this->getRequest()->getPostValue(), $resultRedirect);
        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(__('An error occurred while saving this role.'));
        }

        return $resultRedirect->setPath('*/*/');
    }

    /**
     * Parse request value from string
     *
     * @param string $paramName
     * @return array
     */
    private function parseRequestVariable($paramName): array
    {
        $value = $this->getRequest()->getParam($paramName, '');
        // phpcs:ignore Magento2.Functions.DiscouragedFunction
        parse_str($value, $value);
        $value = array_keys($value);
        return $value;
    }

    /**
     * Processes users to be assigned to roles
     *
     * @param Role $role
     * @param array $roleUsers
     * @return $this
     */
    private function processCurrentUsers(Role $role, array $roleUsers): self
    {
        foreach ($roleUsers as $nRuid) {
            try {
                $this->_addUserToRole($nRuid, $role->getId());
            } catch (LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            }
        }

        return $this;
    }

    /**
     * Get security cookie
     *
     * @return SecurityCookie
     * @deprecated 100.1.0
     * @see we don't recommend this approach anymore
     */
    private function getSecurityCookie()
    {
        if (!($this->securityCookie instanceof SecurityCookie)) {
            return ObjectManager::getInstance()->get(SecurityCookie::class);
        }
        return $this->securityCookie;
    }
}
