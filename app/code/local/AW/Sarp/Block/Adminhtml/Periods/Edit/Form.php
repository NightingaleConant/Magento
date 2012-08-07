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
class AW_Sarp_Block_Adminhtml_Periods_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
{

    protected function _prepareForm()
    {

        if (Mage::getSingleton('adminhtml/session')->getFormData()) {
            $data = Mage::getSingleton('adminhtml/session')->getFormData();
        } elseif ($this->getPeriod()) {
            $data = $this->getPeriod()->getData();
        }


        $form = new Varien_Data_Form(array(
                                          'id' => 'edit_form',
                                          'action' => $this->getUrl('*/*/save', array('id' => $this->getRequest()->getParam('id'))),
                                          'method' => 'post',
                                          'enctype' => 'multipart/form-data'
                                     )
        );

        $fieldset = $form->addFieldset('period_details', array('legend' => $this->__('Period Details')));
        $fieldset->addField('id', 'hidden', array(
                                                 'required' => false,
                                                 'name' => 'id'
                                            ));


        $fieldset->addField('name', 'text', array(
                                                 'required' => true,
                                                 'name' => 'name',
                                                 'label' => 'Name'
                                            ));


        $fieldset->addField('sort_order', 'text', array(
                                                       'required' => false,
                                                       'name' => 'sort_order',
                                                       'label' => 'Sort Order'
                                                  ));

        $select = new Varien_Data_Form_Element_Select(array(
                                                           'name' => 'period_type',
                                                           'style' => 'width:241px',
                                                           'options' => Mage::getModel('sarp/source_periods')->getGridOptions()
                                                      ));
        $select->setId('period_type')
                ->setRenderer(Mage::getBlockSingleton('sarp/adminhtml_widget_form_renderer_element'))
                ->setForm($form)
                ->setValue(@$data['period_type']);
        ;

        $fieldset->addField('period_value', 'text', array(
                                                         'required' => true,
                                                         'name' => 'period_value',
                                                         'label' => 'Repeat each',
                                                         'style' => 'width:30px',
                                                         'after_element_html' => $select->toHtml()
                                                    ));


        $selectE = new Varien_Data_Form_Element_Select(array(
                                                            'name' => 'expire_type',
                                                            'style' => 'width:241px',
                                                            'options' => Mage::getModel('sarp/source_periods')->getGridOptions()

                                                       ));
        $selectE->setId('expire_type')
                ->setRenderer(Mage::getBlockSingleton('sarp/adminhtml_widget_form_renderer_element'))
                ->setForm($form)
                ->setValue(@$data['expire_type']);

        $fieldset->addField('expire_value', 'text', array(
                                                         'required' => false,
                                                         'name' => 'expire_value',
                                                         'label' => 'Expires After',
                                                         'style' => 'width:30px',
                                                         'after_element_html' => $selectE->toHtml()
                                                    ));


        $fieldset->addField('excluded_weekdays', 'multiselect', array(
                                                                     'required' => false,
                                                                     'name' => 'excluded_weekdays',
                                                                     'label' => 'Exclude Weekdays',
                                                                     'values' => Mage::getModel('sarp/source_periods_weekdays')->getAllOptions()
                                                                ));

        $fieldset->addField('payment_offset', 'text', array(
                                                           'required' => false,
                                                           'name' => 'payment_offset',
                                                           'label' => 'Require payment before, days'
                                                      ));


        /*	$fieldset->addField('period_type', 'select', array(
              'required'  => true,
              'name'      => 'period_type',
              'label'		=> 'Repeat',
              'options'	=> Mage::getModel('sarp/source_periods')->getGridOptions(),
              'after_element_html' => 'ddd',
              'renderer'	=> 'pizda'
          ));
          */


        if (!isset($data['expire_value']) || ($data['expire_value'] == 0)) {
            $data['expire_value'] = '';
        }
        if (!isset($data['excluded_weekdays']) || !strlen($data['excluded_weekdays'])) {
            $data['excluded_weekdays'] = -1;
        }
        $form->setValues($data);

        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }


}
