<?php
/**
 * aheadWorks Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://ecommerce.aheadworks.com/AW-LICENSE-COMMUNITY.txt
 * 
 * =================================================================
 *                 MAGENTO EDITION USAGE NOTICE
 * =================================================================
 * This package designed for Magento COMMUNITY edition
 * aheadWorks does not guarantee correct work of this extension
 * on any other Magento edition except Magento COMMUNITY edition.
 * aheadWorks does not provide extension support in case of
 * incorrect edition usage.
 * =================================================================
 *
 * @category   AW
 * @package    AW_Sarp
 * @copyright  Copyright (c) 2010-2011 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE-COMMUNITY.txt
 */
class AW_Sarp_AlertsController extends Mage_Adminhtml_Controller_Action
{

    public function indexAction()
    {
        $this->_initAction()
                ->_setActiveMenu('catalog')
                ->_addContent($this->getLayout()->createBlock('sarp/adminhtml_alerts'))
                ->renderLayout();
    }

    /**
     * Draw edit form for period
     * @return
     */
    public function editAction()
    {
        $Alert = Mage::getModel('sarp/alert')->load($this->getRequest()->getParam('id'));
        $Alert->setStoreIds(explode(',', $Alert->getStoreIds()));
        $this->_initAction();
        $_title = is_null($Alert->getId()) ? 'Create New Alert':'Edit Alert';
        $this->_title($this->__($_title));
        $this
                ->_addContent($this->getLayout()->createBlock('sarp/adminhtml_alerts_edit')->setAlert($Alert))
                ->renderLayout();
    }

    /**
     * Draw edit form for period
     * @return
     */
    public function newAction()
    {
        $this->_forward('edit');
    }


    protected function _initAction()
    {
        $this->_title($this->__('Subscriptions Alerts'));
        $this->loadLayout()
                ->_setActiveMenu('sarp');
        return $this;
    }

    /**
     * Saves subscription
     * @return
     */
    public function saveAction()
    {
        $Alert = Mage::getModel('sarp/alert')->load($this->getRequest()->getParam('id'));
        try {
            $Alert->setData($this->getRequest()->getPost());
            $Alert->setStoreIds(implode(',', $this->getRequest()->getParam('store_ids')));
            //$Period->validate();
            if (!$Alert->getId()) {
                $Alert->setId(null);
            }
            $Alert->save();
            Mage::getSingleton('adminhtml/session')->addSuccess("Alert successfully saved");
        } catch (AW_Sarp_Exception $E) {
            Mage::getSingleton('adminhtml/session')->addError($E->getMessage());

        }
        $this->_redirect('*/*');
    }

    public function deleteAction()
    {
        try {
            $Alert = Mage::getModel('sarp/alert')->load($this->getRequest()->getParam('id'));
            if ($Alert->getId()) {
                $Alert->delete();

            } else {
                throw new AW_Sarp_Exception("Can't delete alert that doesn't exist");
            }
            Mage::getSingleton('adminhtml/session')->addSuccess("Alert successfully deleted");
            $this->_redirect('*/alerts/');

        } catch (AW_Sarp_Exception $E) {
            Mage::getSingleton('adminhtml/session')->addError($E->getMessage());
            $this->_redirectReferer();
        }

    }

    protected function _title($text = null, $resetIfExists = true)
    {
        if (!Mage::helper('sarp')->checkVersion('1.4.0.0')) {
            return $this;
        }
        return parent::_title($text, $resetIfExists);
    }

}