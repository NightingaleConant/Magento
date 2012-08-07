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
class AW_Sarp_Block_Adminhtml_Subscriptions_Edit_Tab_Main extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form();
        $this->setForm($form);

        $fieldset = $form->addFieldset('main_details', array('legend' => $this->__('Main Details')));
        $fieldset->addField('id', 'hidden', array(
                                                 'required' => false,
                                                 'name' => 'id'
                                            ));


        if (
            $this->getSubscription()->getStatus() == AW_Sarp_Model_Subscription::STATUS_CANCELED &&
            $this->getSubscription()->getOrder()->getPayment()->getMethod() == 'paypal_direct'
        ) {
            $label = '<br/><span style="color:red;">' . $this->__("Subscription with this payment method can't be re-activated") . "</span>";
            $disabled = true;
        } else {
            $label = false;
            $disabled = false;
        }

        $fieldset->addField('status', 'select', array(
                                                     'required' => true,
                                                     'name' => 'status',
                                                     'after_element_html' => $label,
                                                     'disabled' => $disabled,
                                                     'label' => 'Status',
                                                     'options' => Mage::getModel('sarp/source_subscription_status')->getGridOptions()

                                                ));


        $fieldset->addField('period_type', 'select', array(
                                                          'name' => 'period_type',
                                                          'disabled' => true,
                                                          'label' => 'Period',
                                                          'options' => Mage::getModel('sarp/source_subscription_periods')->getGridOptions()

                                                     ));


        if (Mage::getSingleton('adminhtml/session')->getFormData()) {
            $data = Mage::getSingleton('adminhtml/session')->getFormData();
            $form->setValues($data);
        } elseif ($this->getSubscription()) {
            $form->setValues($this->getSubscription()->getData());
        }

        return parent::_prepareForm();
    }
}
