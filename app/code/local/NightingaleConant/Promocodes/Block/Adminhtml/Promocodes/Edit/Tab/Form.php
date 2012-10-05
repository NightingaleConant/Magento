<?php

class NightingaleConant_Promocodes_Block_Adminhtml_Promocodes_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
  protected function _prepareForm()
  {
      $form = new Varien_Data_Form();
      $this->setForm($form);
      $fieldset = $form->addFieldset('promocodes_form', array('legend'=>Mage::helper('promocodes')->__('Create New Promocode')));
     
      
      $fieldset->addField('ckc_store_id', 'text', array(
          'label'     => Mage::helper('promocodes')->__('Store ID'),
          'class'     => 'required-entry',
          'required'  => true,
          'name'      => 'ckc_store_id',
      ));
      
      $fieldset->addField('ckc_company_code', 'text', array(
          'label'     => Mage::helper('promocodes')->__('Company Code'),
          'class'     => 'required-entry',
          'required'  => true,
          'name'      => 'ckc_company_code',
      ));
      
      $fieldset->addField('ckc_key_code', 'text', array(
          'label'     => Mage::helper('promocodes')->__('Key Code'),
          'class'     => 'required-entry',
          'required'  => true,
          'name'      => 'ckc_key_code',
      ));
      
      
      $fieldset->addField('ckc_offer_code', 'text', array(
          'label'     => Mage::helper('promocodes')->__('Offer Code'),
          'required'  => true,
          'name'      => 'ckc_offer_code',
      ));
      
      
      $fieldset->addField('ckc_key_code_name', 'text', array(
          'label'     => Mage::helper('promocodes')->__('Key Code Name'),
          'required'  => true,
          'name'      => 'ckc_key_code_name',
      ));
      
      $fieldset->addField('ckc_begin_date', 'text', array(
          'label'     => Mage::helper('promocodes')->__('Begin Date'),
          'required'  => true,
          'name'      => 'ckc_begin_date',
          //'image'  => Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_SKIN).'/adminhtml/default/default/images/grid-cal.gif',
        //'format' => 'yyyyMMdd',//Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT), 
      ));
      
      
      $fieldset->addField('ckc_end_date', 'text', array(
          'label'     => Mage::helper('promocodes')->__('End Date'),
          'required'  => true,
          //'format' =>   'MM-dd-yyyy',
          'name'      => 'ckc_end_date',
      ));
      
      $fieldset->addField('ckc_order_type', 'text', array(
          'label'     => Mage::helper('promocodes')->__('Order Type'),
          'required'  => true,
          'name'      => 'ckc_order_type',
      ));
      $fieldset->addField('ckc_order_type_code', 'text', array(
          'label'     => Mage::helper('promocodes')->__('Order Type Code'),
          'required'  => true,
          'name'      => 'ckc_order_type_code',
      ));
      $fieldset->addField('price_code', 'text', array(
          'label'     => Mage::helper('promocodes')->__('Price Code'),
          'required'  => false,
          'name'      => 'price_code',
      ));
      
     
      if ( Mage::getSingleton('adminhtml/session')->getPromocodesData() )
      {
          $form->setValues(Mage::getSingleton('adminhtml/session')->getPromocodesData());
          Mage::getSingleton('adminhtml/session')->setPromocodesData(null);
      } elseif ( Mage::registry('promocodes_data') ) {
          $form->setValues(Mage::registry('promocodes_data')->getData());
      }
	  return parent::_prepareForm();
  }
  
}