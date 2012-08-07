<?php
/**
* @author Amasty Team
* @copyright Amasty
* @package Amasty_Orderattr
*/
class Amasty_Orderattr_Adminhtml_OrderController extends Mage_Adminhtml_Controller_Action
{
	protected function _initAction()
    {
        $this->loadLayout()
            ->_setActiveMenu('sales/orderattr')
            ->_addBreadcrumb(Mage::helper('sales')->__('Sales'), Mage::helper('sales')->__('Sales'))
            ->_addBreadcrumb(Mage::helper('amorderattr')->__('Edit Order Attributes'), Mage::helper('amorderattr')->__('Edit Order Attributes'))
        ;
        return $this;
    }
    
    public function editAction()
    {
    	$orderId = $this->getRequest()->getParam('order_id');
		$order   = Mage::getModel('sales/order')->load($orderId);
		
		
    	$orderAttributes = Mage::getModel('amorderattr/attribute');
    	$orderAttributes->load($order->getId(), 'order_id');
    	
    	
    	Mage::register('current_order', $order);
    	Mage::register('order_attributes', $orderAttributes);
    	
    	$this->_initAction()
    	     ->_addContent($this->getLayout()->createBlock('amorderattr/adminhtml_order_view_attribute_edit')->setData('action', $this->getUrl('*/*/save', array('order_id' => $orderId))))
             ->renderLayout();
    }
    
	public function validateAction()
    {
        $response = new Varien_Object();
        $response->setError(false);
        $this->getResponse()->setBody($response->toJson());
    }
    
    public function saveAction()
    {
    	$orderId 		 = $this->getRequest()->getParam('order_id');
    	$data        	 = $this->getRequest()->getPost();
    	$orderAttributes = Mage::getModel('amorderattr/attribute');
    	$orderAttributes->load($orderId, 'order_id');
    	if ($data)
    	{
    	    if (!$orderAttributes->getOrderId())
    	    {
    	        $orderAttributes->setOrderId($orderId);
    	    }
    	    foreach ($data as $key => $val)
    	    {
    	        if ($val)
    	        {
    	            $orderAttributes->setData($key, $val);
    	        }
    	    }
    		try {
    			$orderAttributes->save();
    			$this->_getSession()->addSuccess(Mage::helper('sales')->__('The order attributes have been updated.'));
                $this->_redirect('adminhtml/sales_order/view', array('order_id' => $orderId));
                return;
    		} catch (Mage_Core_Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            } catch (Exception $e) {
                $this->_getSession()->addException(
                    $e,
                    Mage::helper('amorderattr')->__('An error occurred while updating the order attributes.')
                );
            }
    	} else 
    	{
    		$this->_redirect('adminhtml/sales_order');
    	}
    }
}
