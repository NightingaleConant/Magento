<?php

class NightingaleConant_SpecialItem_Block_Adminhtml_Specialitem_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
  protected function _prepareForm()
  {
      $form = new Varien_Data_Form();
      $this->setForm($form);
      $fieldset = $form->addFieldset('specialitem_form', array('legend'=>Mage::helper('specialitem')->__('Create New Associate Item')));
     
      $fieldset->addField('product_sku', 'text', array(
          'label'     => Mage::helper('specialitem')->__('Product SKU'),
          'class'     => 'required-entry',
          'required'  => true,
          'name'      => 'product_sku',
      ));
      
      $fieldset->addField('item_sku', 'text', array(
          'label'     => Mage::helper('specialitem')->__('Order Item SKU'),
          //'class'     => 'required-entry',
          'required'  => false,
          'name'      => 'item_sku',
      ));
      
      $fieldset->addField('promo_code', 'text', array(
          'label'     => Mage::helper('specialitem')->__('Promo Code'),
          //'class'     => 'required-entry',
          'required'  => false,
          'name'      => 'promo_code',
      ));
      
      $fieldset->addField('order_type', 'text', array(
          'label'     => Mage::helper('specialitem')->__('Order Type'),
          //'class'     => 'required-entry',
          'required'  => false,
          'name'      => 'order_type',
      ));
      
      $fieldset->addField('customer_group', 'text', array(
          'label'     => Mage::helper('specialitem')->__('Customer Group'),
          //'class'     => 'required-entry',
          'required'  => false,
          'name'      => 'customer_group',
      ));
                        
      $fieldset->addField('active', 'select', array(
          'label'     => Mage::helper('specialitem')->__('Active'),
          'name'      => 'active',
          'values'    => array(              
              array(
                  'value'     => 0,
                  'label'     => Mage::helper('specialitem')->__('No'),
              ),              
              array(
                  'value'     => 1,
                  'label'     => Mage::helper('specialitem')->__('Yes'),
              ),
          ),
      ));
            
      if ( Mage::getSingleton('adminhtml/session')->getSpecialItemData() )
      {
          $form->setValues(Mage::getSingleton('adminhtml/session')->getSpecialItemData());
          Mage::getSingleton('adminhtml/session')->setSpecialItemData(null);
      } elseif ( Mage::registry('specialitem_data') ) {
          $form->setValues(Mage::registry('specialitem_data')->getData());
      }
      return parent::_prepareForm();
  }
  
}