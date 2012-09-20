<?php

class NightingaleConant_Royalties_Block_Adminhtml_Royalties_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
  protected function _prepareForm()
  {
      $form = new Varien_Data_Form();
      $this->setForm($form);
      $fieldset = $form->addFieldset('royalties_form', array('legend'=>Mage::helper('royalties')->__('Royalty Rate Form')));
     
      $fieldset->addField('STEP', 'text', array(
          'label'     => Mage::helper('royalties')->__('STEP'),
          'class'     => '',
          'required'  => false,
          'name'      => 'STEP',
      ));
      
      $fieldset->addField('AUTH', 'text', array(
          'label'     => Mage::helper('royalties')->__('AUTH'),
          'class'     => '',
          'required'  => false,
          'name'      => 'AUTH',
      ));
      
      $fieldset->addField('AUTH1', 'text', array(
          'label'     => Mage::helper('royalties')->__('AUTH1'),
          'class'     => '',
          'required'  => false,
          'name'      => 'AUTH1',
      ));
      
      $fieldset->addField('ITEM', 'text', array(
          'label'     => Mage::helper('royalties')->__('ITEM'),
          'class'     => '',
          'required'  => false,
          'name'      => 'ITEM',
      ));
      
      $fieldset->addField('PRICE', 'text', array(
          'label'     => Mage::helper('royalties')->__('PRICE'),
          'class'     => '',
          'required'  => false,
          'name'      => 'PRICE',
      ));
      
      $fieldset->addField('INTALP', 'text', array(
          'label'     => Mage::helper('royalties')->__('INTALP'),
          'class'     => '',
          'required'  => false,
          'name'      => 'INTALP',
      ));
      
      $fieldset->addField('INITdl', 'text', array(
          'label'     => Mage::helper('royalties')->__('INITdl'),
          'class'     => '',
          'required'  => false,
          'name'      => 'INITdl',
      ));
      
      $fieldset->addField('REORDP', 'text', array(
          'label'     => Mage::helper('royalties')->__('REORDP'),
          'class'     => '',
          'required'  => false,
          'name'      => 'REORDP',
      ));
      
      $fieldset->addField('REORdol', 'text', array(
          'label'     => Mage::helper('royalties')->__('REORdol'),
          'class'     => '',
          'required'  => false,
          'name'      => 'REORdol',
      ));
      
      $fieldset->addField('FNTPR', 'text', array(
          'label'     => Mage::helper('royalties')->__('FNTPR'),
          'class'     => '',
          'required'  => false,
          'name'      => 'FNTPR',
      ));
      
      $fieldset->addField('FINITdol', 'text', array(
          'label'     => Mage::helper('royalties')->__('FINITdol'),
          'class'     => '',
          'required'  => false,
          'name'      => 'FINITdol',
      ));
      
      $fieldset->addField('FRRPR', 'text', array(
          'label'     => Mage::helper('royalties')->__('FRRPR'),
          'class'     => '',
          'required'  => false,
          'name'      => 'FRRPR',
      ));
      
      $fieldset->addField('FREORdol', 'text', array(
          'label'     => Mage::helper('royalties')->__('FREORdol'),
          'class'     => '',
          'required'  => false,
          'name'      => 'FREORdol',
      ));
      
      $fieldset->addField('LVL1UN', 'text', array(
          'label'     => Mage::helper('royalties')->__('LVL1UN'),
          'class'     => '',
          'required'  => false,
          'name'      => 'LVL1UN',
      ));
      
      $fieldset->addField('VLV1PR', 'text', array(
          'label'     => Mage::helper('royalties')->__('VLV1PR'),
          'class'     => '',
          'required'  => false,
          'name'      => 'VLV1PR',
      ));
      
      $fieldset->addField('VLV2UN', 'text', array(
          'label'     => Mage::helper('royalties')->__('VLV2UN'),
          'class'     => '',
          'required'  => false,
          'name'      => 'VLV2UN',
      ));
      
      $fieldset->addField('LVL2PR', 'text', array(
          'label'     => Mage::helper('royalties')->__('LVL2PR'),
          'class'     => '',
          'required'  => false,
          'name'      => 'LVL2PR',
      ));
      
      $fieldset->addField('LVL3UN', 'text', array(
          'label'     => Mage::helper('royalties')->__('LVL3UN'),
          'class'     => '',
          'required'  => false,
          'name'      => 'LVL3UN',
      ));
      
      $fieldset->addField('VLV3PR', 'text', array(
          'label'     => Mage::helper('royalties')->__('VLV3PR'),
          'class'     => '',
          'required'  => false,
          'name'      => 'VLV3PR',
      ));
      
      $fieldset->addField('VENDNUM', 'text', array(
          'label'     => Mage::helper('royalties')->__('VENDNUM'),
          'class'     => '',
          'required'  => false,
          'name'      => 'VENDNUM',
      ));
      
      $fieldset->addField('DOMLIC', 'text', array(
          'label'     => Mage::helper('royalties')->__('DOMLIC'),
          'class'     => '',
          'required'  => false,
          'name'      => 'DOMLIC',
      ));
      
      $fieldset->addField('FORLIC', 'text', array(
          'label'     => Mage::helper('royalties')->__('FORLIC'),
          'class'     => '',
          'required'  => false,
          'name'      => 'FORLIC',
      ));
      
      $fieldset->addField('ADVANC', 'text', array(
          'label'     => Mage::helper('royalties')->__('ADVANC'),
          'class'     => '',
          'required'  => false,
          'name'      => 'ADVANC',
      ));
      
      $fieldset->addField('LFL1UN', 'text', array(
          'label'     => Mage::helper('royalties')->__('LFL1UN'),
          'class'     => '',
          'required'  => false,
          'name'      => 'LFL1UN',
      ));
      
      $fieldset->addField('VFV1PR', 'text', array(
          'label'     => Mage::helper('royalties')->__('VFV1PR'),
          'class'     => '',
          'required'  => false,
          'name'      => 'VFV1PR',
      ));
      
      $fieldset->addField('VFV2UN', 'text', array(
          'label'     => Mage::helper('royalties')->__('VFV2UN'),
          'class'     => '',
          'required'  => false,
          'name'      => 'VFV2UN',
      ));
      
      $fieldset->addField('LFL2PR', 'text', array(
          'label'     => Mage::helper('royalties')->__('LFL2PR'),
          'class'     => '',
          'required'  => false,
          'name'      => 'LFL2PR',
      ));
      
      $fieldset->addField('LFL3UN', 'text', array(
          'label'     => Mage::helper('royalties')->__('LFL3UN'),
          'class'     => '',
          'required'  => false,
          'name'      => 'LFL3UN',
      ));
      
      $fieldset->addField('VFV3PR', 'text', array(
          'label'     => Mage::helper('royalties')->__('VFV3PR'),
          'class'     => '',
          'required'  => false,
          'name'      => 'VFV3PR',
      ));
      
      $fieldset->addField('LFL4UN', 'text', array(
          'label'     => Mage::helper('royalties')->__('LFL4UN'),
          'class'     => '',
          'required'  => false,
          'name'      => 'LFL4UN',
      ));
      
      $fieldset->addField('VFV4PR', 'text', array(
          'label'     => Mage::helper('royalties')->__('VFV4PR'),
          'class'     => '',
          'required'  => false,
          'name'      => 'VFV4PR',
      ));
      
      $fieldset->addField('LVL4UN', 'text', array(
          'label'     => Mage::helper('royalties')->__('LVL4UN'),
          'class'     => '',
          'required'  => false,
          'name'      => 'LVL4UN',
      ));
      
      $fieldset->addField('VLV4PR', 'text', array(
          'label'     => Mage::helper('royalties')->__('VLV4PR'),
          'class'     => '',
          'required'  => false,
          'name'      => 'VLV4PR',
      ));
      
      $fieldset->addField('LIL1UN', 'text', array(
          'label'     => Mage::helper('royalties')->__('LIL1UN'),
          'class'     => '',
          'required'  => false,
          'name'      => 'LIL1UN',
      ));
      
      $fieldset->addField('VIV1PR', 'text', array(
          'label'     => Mage::helper('royalties')->__('VIV1PR'),
          'class'     => '',
          'required'  => false,
          'name'      => 'VIV1PR',
      ));
      
      $fieldset->addField('VIV2UN', 'text', array(
          'label'     => Mage::helper('royalties')->__('VIV2UN'),
          'class'     => '',
          'required'  => false,
          'name'      => 'VIV2UN',
      ));
      
      $fieldset->addField('LIL2PR', 'text', array(
          'label'     => Mage::helper('royalties')->__('LIL2PR'),
          'class'     => '',
          'required'  => false,
          'name'      => 'LIL2PR',
      ));
      
      $fieldset->addField('LIL3UN', 'text', array(
          'label'     => Mage::helper('royalties')->__('LIL3UN'),
          'class'     => '',
          'required'  => false,
          'name'      => 'LIL3UN',
      ));
      
      $fieldset->addField('VIV3PR', 'text', array(
          'label'     => Mage::helper('royalties')->__('VIV3PR'),
          'class'     => '',
          'required'  => false,
          'name'      => 'VIV3PR',
      ));
      
      $fieldset->addField('LIF1UN', 'text', array(
          'label'     => Mage::helper('royalties')->__('LIF1UN'),
          'class'     => '',
          'required'  => false,
          'name'      => 'LIF1UN',
      ));
      
      $fieldset->addField('VIF1PR', 'text', array(
          'label'     => Mage::helper('royalties')->__('VIF1PR'),
          'class'     => '',
          'required'  => false,
          'name'      => 'VIF1PR',
      ));
      
      $fieldset->addField('VIF2UN', 'text', array(
          'label'     => Mage::helper('royalties')->__('VIF2UN'),
          'class'     => '',
          'required'  => false,
          'name'      => 'VIF2UN',
      ));
      
      $fieldset->addField('LIF2PR', 'text', array(
          'label'     => Mage::helper('royalties')->__('LIF2PR'),
          'class'     => '',
          'required'  => false,
          'name'      => 'LIF2PR',
      ));
      
      $fieldset->addField('LIF3UN', 'text', array(
          'label'     => Mage::helper('royalties')->__('LIF3UN'),
          'class'     => '',
          'required'  => false,
          'name'      => 'LIF3UN',
      ));
      
      $fieldset->addField('VIF3PR', 'text', array(
          'label'     => Mage::helper('royalties')->__('VIF3PR'),
          'class'     => '',
          'required'  => false,
          'name'      => 'VIF3PR',
      ));
      
      $fieldset->addField('QUATER', 'text', array(
          'label'     => Mage::helper('royalties')->__('QUATER'),
          'class'     => '',
          'required'  => false,
          'name'      => 'QUATER',
      ));
      
      if ( Mage::getSingleton('adminhtml/session')->getRoyaltiesData() )
      {
          $form->setValues(Mage::getSingleton('adminhtml/session')->getRoyaltiesData());
          Mage::getSingleton('adminhtml/session')->setRoyaltiesData(null);
      } elseif ( Mage::registry('royalties_data') ) {
          $form->setValues(Mage::registry('royalties_data')->getData());
      }
      
      return parent::_prepareForm();
  }
  
}