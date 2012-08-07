<?php
/**
* @author Amasty Team
* @copyright Amasty
* @package Amasty_Orderattr
*/
class Amasty_Orderattr_Adminhtml_ManageController extends Mage_Adminhtml_Controller_Action
{
    protected $_entityTypeId;

    public function preDispatch()
    {
        parent::preDispatch();
        $this->_entityTypeId = Mage::getModel('eav/entity')->setType('order')->getTypeId();
    }

    protected function _initAction()
    {
        if($this->getRequest()->getParam('popup')) {
            $this->loadLayout('popup');
        } else {
            $this->loadLayout()
                ->_setActiveMenu('sales/orderattr')
                ->_addBreadcrumb(Mage::helper('sales')->__('Sales'), Mage::helper('sales')->__('Sales'))
                ->_addBreadcrumb(Mage::helper('amorderattr')->__('Manage Order Attributes'), Mage::helper('amorderattr')->__('Manage Order Attributes'))
            ;
        }
        return $this;
    }

    public function indexAction()
    {
        $this->_initAction()
            ->_addContent($this->getLayout()->createBlock('amorderattr/adminhtml_order_attribute'))
            ->renderLayout();
    }

    public function newAction()
    {
        $this->_forward('edit');
    }

    public function editAction()
    {
        $id = $this->getRequest()->getParam('attribute_id');
        $model = Mage::getModel('catalog/entity_attribute');

        if ($id) {
            $model->load($id);
            
            if ('time' == $model->getNote())
            {
            	$model->setFrontendInput('datetime');
            }
            
            if (! $model->getId()) {
                Mage::getSingleton('adminhtml/session')->addError(Mage::helper('catalog')->__('This attribute no longer exists'));
                $this->_redirect('*/*/');
                return;
            }

            // entity type check
            if ($model->getEntityTypeId() != $this->_entityTypeId) {
                Mage::getSingleton('adminhtml/session')->addError(Mage::helper('catalog')->__('You cannot edit this attribute'));
                $this->_redirect('*/*/');
                return;
            }
        }

        // set entered data if was error when we do save
        $data = Mage::getSingleton('adminhtml/session')->getAttributeData(true);
        if (! empty($data)) {
            $model->setData($data);
        }

        Mage::register('entity_attribute', $model);

        $this->_initAction()
            ->_addBreadcrumb($id ? Mage::helper('amorderattr')->__('Edit Order Attribute') : Mage::helper('amorderattr')->__('New Order Attribute'), $id ? Mage::helper('amorderattr')->__('Edit Order Attribute') : Mage::helper('amorderattr')->__('New Order Attribute'))
            ->_addContent($this->getLayout()->createBlock('amorderattr/adminhtml_order_attribute_edit')->setData('action', $this->getUrl('*/catalog_product_attribute/save')))
            ->_addLeft($this->getLayout()->createBlock('amorderattr/adminhtml_order_attribute_edit_tabs'))
            ->_addJs(
                $this->getLayout()->createBlock('adminhtml/template')
                    ->setIsPopup((bool)$this->getRequest()->getParam('popup'))
                    ->setTemplate('amorderattr/attribute/js.phtml')
            )
            ->renderLayout();
    }

    public function validateAction()
    {
        $response = new Varien_Object();
        $response->setError(false);

        $attributeCode  = $this->getRequest()->getParam('attribute_code');
        $attributeId    = $this->getRequest()->getParam('attribute_id');
        $attribute = Mage::getModel('catalog/entity_attribute')
            ->loadByCode($this->_entityTypeId, $attributeCode);

        if ($attribute->getId() && !$attributeId) {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('catalog')->__('Attribute with the same code already exists'));
            $this->_initLayoutMessages('adminhtml/session');
            $response->setError(true);
            $response->setMessage($this->getLayout()->getMessagesBlock()->getGroupedHtml());
        }

        $this->getResponse()->setBody($response->toJson());
    }

    public function saveAction()
    {
        if ($data = $this->getRequest()->getPost()) {
            $redirectBack   = $this->getRequest()->getParam('back', false);
            $model = Mage::getModel('eav/entity_attribute');
            
//            $model = Mage::getModel('catalog/entity_attribute');
            /* @var $model Mage_Catalog_Model_Entity_Attribute */

            if ($id = $this->getRequest()->getParam('attribute_id')) {

                $model->load($id);

                // entity type check
                if ($model->getEntityTypeId() != $this->_entityTypeId) {
                    Mage::getSingleton('adminhtml/session')->addError(Mage::helper('catalog')->__('You cannot update this attribute'));
                    Mage::getSingleton('adminhtml/session')->setAttributeData($data);
                    $this->_redirect('*/*/');
                    return;
                }

                $data['attribute_code'] = $model->getAttributeCode();
                $data['is_user_defined'] = $model->getIsUserDefined();
                $data['frontend_input'] = $model->getFrontendInput();
                $data['note'] = $model->getNote();
            } else 
            {
            	if ('datetime' == $data['frontend_input'])
            	{
            		$data['frontend_input'] = 'date';
            		$data['note'] = 'time';		
            	}
            }

            if (!isset($data['is_configurable'])) {
                $data['is_configurable'] = 0;
            }

            if (is_null($model->getIsUserDefined()) || $model->getIsUserDefined() != 0) {
                $data['backend_type'] = $model->getBackendTypeByInput($data['frontend_input']);
            }

            $defaultValueField = $model->getDefaultValueByInput($data['frontend_input']);
            if ($defaultValueField) {
                $data['default_value'] = $this->getRequest()->getParam($defaultValueField);
            }

            if(!isset($data['apply_to'])) {
                $data['apply_to'] = array();
            }

            if ('boolean' == $data['frontend_input'])
            {
                $data['frontend_input'] = 'select';
                $data['source_model'] = 'eav/entity_attribute_source_boolean';
            }
            
            if ('multiselect' == $data['frontend_input'])
            {
                $data['source_model'] = 'eav/entity_attribute_source_table';
            }
                
            $data['store_ids'] = '0';
            
            if ($data['stores'])
            {
                if (is_array($data['stores']))
                {
                    $data['store_ids'] = implode(',', $data['stores']);
                } else 
                {
                    $data['store_ids'] = $data['stores'];
                }
                unset($data['stores']);
            }
                
            /**
             * @todo need specify relations for properties
             */
            if (isset($data['frontend_input']) && $data['frontend_input'] == 'multiselect') {
                $data['backend_model'] = 'eav/entity_attribute_backend_array';
            }
            
            $model->addData($data);

            if (!$id) {
                $model->setEntityTypeId($this->_entityTypeId);
                $model->setIsUserDefined(1);
            }
            
            if($this->getRequest()->getParam('set') && $this->getRequest()->getParam('group')) {
                // For creating product attribute on product page we need specify attribute set and group
                $model->setAttributeSetId($this->getRequest()->getParam('set'));
                $model->setAttributeGroupId($this->getRequest()->getParam('group'));
            }

            try {
            	
//            	d($model->getData(), 1);
            	
                $model->save();
                
                if (!$this->getRequest()->getParam('attribute_id'))
                {
                	$fieldType = $model->getFrontendInput();
                    if ('time' == $model->getNote())
                    {
                    	$fieldType = 'datetime';
                    }
                    // adding field to attributes table
                    Mage::getModel('amorderattr/attribute')->addAttributeField($model->getAttributeCode(), $fieldType);
                    Mage::helper('amorderattr')->clearCache();
                }
                
                // saving Show on Manage Customers Grid and Show on Billing During Checkout
                /*$configuration = array(
                    'is_filterable_in_search'   => Mage::app()->getRequest()->getPost('is_filterable_in_search'),
                    'used_in_product_listing'   => Mage::app()->getRequest()->getPost('used_in_product_listing'),
                );
                $model->getResource()->saveAttributeConfiguration($model->getId(), $configuration);*/
                
                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('amorderattr')->__('Order attribute was successfully saved'));

                /**
                 * Clear translation cache because attribute labels are stored in translation
                 */
                Mage::app()->cleanCache(array(Mage_Core_Model_Translate::CACHE_TAG));
                Mage::getSingleton('adminhtml/session')->setAttributeData(false);
                if ($this->getRequest()->getParam('popup')) {
                    $this->_redirect('adminhtml/catalog_product/addAttribute', array(
                        'id'       => $this->getRequest()->getParam('product'),
                        'attribute'=> $model->getId(),
                        '_current' => true
                    ));
                } elseif ($redirectBack) {
                    $this->_redirect('*/*/edit', array('attribute_id' => $model->getId(),'_current'=>true));
                } else {
                    $this->_redirect('*/*/', array());
                }
                return;
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                Mage::getSingleton('adminhtml/session')->setAttributeData($data);
                $this->_redirect('*/*/edit', array('_current' => true));
                return;
            }
        }
        $this->_redirect('*/*/');
    }

    public function deleteAction()
    {
        if ($id = $this->getRequest()->getParam('attribute_id')) {
            $model = Mage::getModel('eav/entity_attribute');

            // entity type check
            $model->load($id);
            if ($model->getEntityTypeId() != $this->_entityTypeId) {
                Mage::getSingleton('adminhtml/session')->addError(Mage::helper('catalog')->__('You cannot delete this attribute'));
                $this->_redirect('*/*/');
                return;
            }

            try {
                $attributeCode = $model->getAttributeCode();
                $model->delete();
                Mage::getModel('amorderattr/attribute')->dropAttributeField($attributeCode);
                Mage::helper('amorderattr')->clearCache();
                
                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('amorderattr')->__('Order attribute was successfully deleted'));
                $this->_redirect('*/*/');
                return;
            }
            catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                $this->_redirect('*/*/edit', array('attribute_id' => $this->getRequest()->getParam('attribute_id')));
                return;
            }
        }
        Mage::getSingleton('adminhtml/session')->addError(Mage::helper('catalog')->__('Unable to find an attribute to delete'));
        $this->_redirect('*/*/');
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('catalog/attributes/attributes');
    }
}
