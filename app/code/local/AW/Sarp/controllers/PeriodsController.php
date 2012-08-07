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
class AW_Sarp_PeriodsController extends Mage_Adminhtml_Controller_Action
{

    public function indexAction()
    {
        $this->_initAction()
                ->_setActiveMenu('catalog')
                ->_addContent($this->getLayout()->createBlock('sarp/adminhtml_periods'))
                ->renderLayout();
    }

    /**
     * Draw edit form for period
     * @return
     */
    public function editAction()
    {
        $Period = Mage::getModel('sarp/period')->load($this->getRequest()->getParam('id'));
        $this->_initAction();
        $_title = is_null($Period->getId()) ? 'Create New Period':'Edit Period';
        $this->_title($this->__($_title));
        $this
            ->_addContent($this->getLayout()->createBlock('sarp/adminhtml_periods_edit')->setPeriod($Period))
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

    /**
     * Saves subscription
     * @return
     */
    public function saveAction()
    {
        $Period = Mage::getModel('sarp/period')->load($this->getRequest()->getParam('id'));
        try {
            $Period->setData($this->getRequest()->getPost());
            $Period->validate();
            if (!$Period->getId()) {
                $Period->setId(null);
            }

            if ($this->getRequest()->getParam('payment_offset') < 0) $Period->setPaymentOffset(0);

            $Period->save();
            Mage::getSingleton('adminhtml/session')->addSuccess($this->__("Period successfully saved"));
        } catch (AW_Sarp_Exception $E) {
            Mage::getSingleton('adminhtml/session')->addError($this->__("Can not save period. %s", $this->__($E->getMessage())));
            return $this->_redirectReferer();
        }
        $this->_redirect('*/*');
    }

    public function deleteAction()
    {
        try {
            $Period = Mage::getModel('sarp/period')->load($this->getRequest()->getParam('id'));
            if ($Period->getId() && !$Period->getAffectedSubscriptions()->count()) {
                $Period->delete();

            } else {
                throw new AW_Sarp_Exception("Can't delete period that has subscriptions affected by it");
            }
            Mage::getSingleton('adminhtml/session')->addSuccess("Period successfully deleted");
            $this->_redirect('*/periods');

        } catch (AW_Sarp_Exception $E) {
            Mage::getSingleton('adminhtml/session')->addError($E->getMessage());
            $this->_redirectReferer();
        }

    }


    protected function _initAction()
    {
        $this->_title($this->__('Subscriptions Periods'));
        $this->loadLayout()
                ->_setActiveMenu('sarp');
        return $this;
    }

    protected function _title($text = null, $resetIfExists = true)
    {
        if (!Mage::helper('sarp')->checkVersion('1.4.0.0')) {
            return $this;
        }
        return parent::_title($text, $resetIfExists);
    }

}