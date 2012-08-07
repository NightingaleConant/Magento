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
class AW_Sarp_Block_Adminhtml_Alerts_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
{

    protected function _prepareForm()
    {

        if (Mage::getSingleton('adminhtml/session')->getFormData()) {
            $data = Mage::getSingleton('adminhtml/session')->getFormData();
        } elseif ($this->getAlert()) {
            $data = $this->getAlert()->getData();
        }


        $form = new Varien_Data_Form(array(
                                          'id' => 'edit_form',
                                          'action' => $this->getUrl('*/*/save', array('id' => $this->getRequest()->getParam('id'))),
                                          'method' => 'post',
                                          'enctype' => 'multipart/form-data'
                                     )
        );

        $fieldset = $form->addFieldset('alert_details', array('legend' => $this->__('Alert Details')));
        $fieldset->addField('id', 'hidden', array(
                                                 'required' => false,
                                                 'name' => 'id'
                                            ));


        $fieldset->addField('name', 'text', array(
                                                 'required' => true,
                                                 'name' => 'name',
                                                 'label' => 'Name'
                                            ));

        // Status field
        $fieldset->addField('status', 'select', array(
                                                     'required' => true,
                                                     'name' => 'status',
                                                     'label' => 'Status',
                                                     'options' => Mage::getModel('sarp/source_alert_status')->getGridOptions()
                                                ));

        $fieldset->addField('type', 'select', array(
                                                   'required' => true,
                                                   'name' => 'type',
                                                   'label' => 'Event Type',
                                                   'onchange' => 'switchTemplates($(this).getValue())',
                                                   'options' => Mage::getModel('sarp/source_alert_type')->getGridOptions()
                                              ));


        $fieldset->addField('recipient', 'select', array(
                                                        'required' => true,
                                                        'name' => 'recipient',
                                                        'onchange' => 'switchTemplates($F(\'type\'))',
                                                        'label' => 'Recipient',
                                                        'options' => Mage::getModel('sarp/source_alert_recipient')->getGridOptions()
                                                   ));


        $selectIsAfter = new Varien_Data_Form_Element_Select(array(
                                                                  'name' => 'time_is_after',
                                                                  'style' => 'width:120px',
                                                                  'options' => array(
                                                                      '0' => $this->__('Before'),
                                                                      '1' => $this->__('After')
                                                                  )
                                                             ));

        $selectIsAfter->setId('time_is_after')
                ->setRenderer(Mage::getBlockSingleton('sarp/adminhtml_widget_form_renderer_element'))
                ->setForm($form)
                ->setValue(@$data['time_is_after']);


        $selectMultiplier = new Varien_Data_Form_Element_Select(array(
                                                                     'name' => 'time_multiplier',
                                                                     'style' => 'width:120px',
                                                                     'options' => Mage::getModel('sarp/source_alert_multiplier')->getGridOptions()
                                                                ));

        $selectMultiplier->setId('time_multiplier')
                ->setRenderer(Mage::getBlockSingleton('sarp/adminhtml_widget_form_renderer_element'))
                ->setForm($form)
                ->setValue(@$data['time_multiplier']);


        $fieldset->addField('time_amount', 'text', array(
                                                        'required' => true,
                                                        'name' => 'time_amount',
                                                        'label' => 'Notify',
                                                        'style' => 'width:30px',
                                                        'after_element_html' => $selectMultiplier->toHtml() . $selectIsAfter->toHtml()
                                                   ));

        $fieldset->addField('store_ids', 'multiselect', array(
                                                             'required' => true,
                                                             'name' => 'store_ids',
                                                             'label' => 'Store',
                                                             'values' => Mage::getSingleton('adminhtml/system_store')->getStoreValuesForForm(false, true)
                                                        ));


        $fieldset->addField('email_template_date_start', 'select', array(
                                                                        'label' => $this->__('Template'),
                                                                        'note' => '',
                                                                        'class' => 'template_selector',
                                                                        'name' => 'email_template',
                                                                        'values' => Mage::getModel('adminhtml/system_config_source_email_template')->setPath('sarp/template/firstdelivery')->toOptionArray()));

        $fieldset->addField('email_template_activation', 'select', array(
                                                                        'label' => $this->__('Template'),
                                                                        'note' => '',
                                                                        'class' => 'template_selector',
                                                                        'name' => 'email_template',
                                                                        'values' => Mage::getModel('adminhtml/system_config_source_email_template')->setPath('sarp/template/activation')->toOptionArray()));

        $fieldset->addField('email_template_delivery', 'select', array(
                                                                      'label' => $this->__('Template'),
                                                                      'note' => '',
                                                                      'class' => 'template_selector',
                                                                      'name' => 'email_template',
                                                                      'values' => Mage::getModel('adminhtml/system_config_source_email_template')->setPath('sarp/template/delivery')->toOptionArray()));

        $fieldset->addField('email_template_date_expire', 'select', array(
                                                                         'label' => $this->__('Template'),
                                                                         'note' => '',
                                                                         'class' => 'template_selector',
                                                                         'name' => 'email_template',
                                                                         'values' => Mage::getModel('adminhtml/system_config_source_email_template')->setPath('sarp/template/expiredate')->toOptionArray()));

        $fieldset->addField('email_template_new_subscription', 'select', array(
                                                                              'label' => $this->__('Template'),
                                                                              'note' => '',
                                                                              'class' => 'template_selector',
                                                                              'name' => 'email_template',
                                                                              'values' => Mage::getModel('adminhtml/system_config_source_email_template')->setPath('sarp/template/newsubscription')->toOptionArray()));

        $fieldset->addField('email_template_cancel_subscription', 'select', array(
                                                                                  'label' => $this->__('Template'),
                                                                                  'note' => '',
                                                                                  'class' => 'template_selector',
                                                                                  'name' => 'email_template',
                                                                                  'values' => Mage::getModel('adminhtml/system_config_source_email_template')->setPath('sarp/template/unsubscription')->toOptionArray()));

        $fieldset->addField('email_template_suspended', 'select', array(
                                                                                  'label' => $this->__('Template'),
                                                                                  'note' => '',
                                                                                  'class' => 'template_selector',
                                                                                  'name' => 'email_template',
                                                                                  'values' => Mage::getModel('adminhtml/system_config_source_email_template')->setPath('sarp/template/suspended')->toOptionArray()));

        $fieldset->addField('email_template_date_start_admin', 'select', array(
                                                                              'label' => $this->__('Template'),
                                                                              'note' => '',
                                                                              'class' => 'template_selector',
                                                                              'name' => 'email_template',
                                                                              'values' => Mage::getModel('adminhtml/system_config_source_email_template')->setPath('sarp/template/firstdelivery_admin')->toOptionArray()));

        $fieldset->addField('email_template_activation_admin', 'select', array(
                                                                              'label' => $this->__('Template'),
                                                                              'note' => '',
                                                                              'class' => 'template_selector',
                                                                              'name' => 'email_template',
                                                                              'values' => Mage::getModel('adminhtml/system_config_source_email_template')->setPath('sarp/template/activation_admin')->toOptionArray()));

        $fieldset->addField('email_template_delivery_admin', 'select', array(
                                                                            'label' => $this->__('Template'),
                                                                            'note' => '',
                                                                            'class' => 'template_selector',
                                                                            'name' => 'email_template',
                                                                            'values' => Mage::getModel('adminhtml/system_config_source_email_template')->setPath('sarp/template/delivery_admin')->toOptionArray()));

        $fieldset->addField('email_template_date_expire_admin', 'select', array(
                                                                               'label' => $this->__('Template'),
                                                                               'note' => '',
                                                                               'class' => 'template_selector',
                                                                               'name' => 'email_template',
                                                                               'values' => Mage::getModel('adminhtml/system_config_source_email_template')->setPath('sarp/template/expiredate_admin')->toOptionArray()));

        $fieldset->addField('email_template_new_subscription_admin', 'select', array(
                                                                                    'label' => $this->__('Template'),
                                                                                    'note' => '',
                                                                                    'class' => 'template_selector',
                                                                                    'name' => 'email_template',
                                                                                    'values' => Mage::getModel('adminhtml/system_config_source_email_template')->setPath('sarp/template/newsubscription_admin')->toOptionArray()));

        $fieldset->addField('email_template_cancel_subscription_admin', 'select', array(
                                                                                       'label' => $this->__('Template'),
                                                                                       'note' => '',
                                                                                       'class' => 'template_selector',
                                                                                       'name' => 'email_template',
                                                                                       'values' => Mage::getModel('adminhtml/system_config_source_email_template')->setPath('sarp/template/unsubscription_admin')->toOptionArray()));

        $fieldset->addField('email_template_suspended_admin', 'select', array(
                                                                                       'label' => $this->__('Template'),
                                                                                       'note' => '',
                                                                                       'class' => 'template_selector',
                                                                                       'name' => 'email_template',
                                                                                       'values' => Mage::getModel('adminhtml/system_config_source_email_template')->setPath('sarp/template/suspended_admin')->toOptionArray()));


        foreach (array('email_template_date_start',
                       'email_template_activation',
                       'email_template_delivery',
                       'email_template_date_expire',
                       'email_template_new_subscription',
                       'email_template_cancel_subscription',
                       'email_template_suspended_subscription',
                       'email_template_date_start_admin',
                       'email_template_activation_admin',
                       'email_template_delivery_admin',
                       'email_template_date_expire_admin',
                       'email_template_new_subscription_admin',
                       'email_template_cancel_subscription_admin',
                       'email_template_suspended_subscription_admin',
        ) as $id) {
            $data[$id] = @$data['email_template'];
        }


        $form->setValues($data);

        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }


}
