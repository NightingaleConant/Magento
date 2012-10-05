<?php

class NightingaleConant_Authors_Block_Adminhtml_Authors_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
  protected function _prepareForm()
  {
      $form = new Varien_Data_Form();
      $this->setForm($form);
      $fieldset = $form->addFieldset('authors_form', array('legend'=>Mage::helper('authors')->__('Create New Author')));
     
      $fieldset->addField('first_name', 'text', array(
          'label'     => Mage::helper('authors')->__('First Name'),
          'class'     => 'required-entry',
          'required'  => true,
          'name'      => 'first_name',
      ));
      
      $fieldset->addField('last_name', 'text', array(
          'label'     => Mage::helper('authors')->__('Last Name'),
          'class'     => 'required-entry',
          'required'  => true,
          'name'      => 'last_name',
      ));
      
      $fieldset->addField('display_name', 'text', array(
          'label'     => Mage::helper('authors')->__('Display Name'),
          'class'     => 'required-entry',
          'required'  => true,
          'name'      => 'display_name',
      ));
      
      $fieldset->addField('bio', 'text', array(
          'label'     => Mage::helper('authors')->__('Bio'),
          'required'  => false,
          'name'      => 'bio',
      ));
      
      
      $fieldset->addField('image', 'image', array(
          'label'     => Mage::helper('authors')->__('Author Image'),
          'required'  => false,
          'name'      => 'image',
	  ));
      
      $fieldset->addField('meta_title', 'text', array(
          'label'     => Mage::helper('authors')->__('Meta Title'),
          'required'  => false,
          'name'      => 'meta_title',
      ));
      
      $fieldset->addField('meta_description', 'text', array(
          'label'     => Mage::helper('authors')->__('Meta Description'),
          'required'  => false,
          'name'      => 'meta_description',
      ));
      
      $fieldset->addField('meta_keywords', 'text', array(
          'label'     => Mage::helper('authors')->__('Meta Keywords'),
          'required'  => false,
          'name'      => 'meta_keywords',
      ));
      
      $fieldset->addField('author_link_text', 'text', array(
          'label'     => Mage::helper('authors')->__('Author Link Text'),
          'required'  => true,
          'name'      => 'author_link_text',
         
      ));
      
      $fieldset->addField('author_page_title', 'text', array(
          'label'     => Mage::helper('authors')->__('Author Page Title'),
          'required'  => true,
          'name'      => 'author_page_title',
      ));
      
      $fieldset->addField('bio_link_text', 'text', array(
          'label'     => Mage::helper('authors')->__('Bio Link Text'),
          'required'  => true,
          'name'      => 'bio_link_text',
         
      ));
      
      $fieldset->addField('bio_page_title', 'text', array(
          'label'     => Mage::helper('authors')->__('Bio Page Title'),
          'required'  => true,
          'name'      => 'bio_page_title',
      ));
            
      $fieldset->addField('active', 'select', array(
          'label'     => Mage::helper('authors')->__('Active'),
          'name'      => 'active',
          'values'    => array(              
              array(
                  'value'     => 0,
                  'label'     => Mage::helper('authors')->__('No'),
              ),              
              array(
                  'value'     => 1,
                  'label'     => Mage::helper('authors')->__('Yes'),
              ),
          ),
      ));
      
      
      Mage::log(Mage::getModel('authors/authors')->toOptionArray());
     
      if ( Mage::getSingleton('adminhtml/session')->getAuthorsData() )
      {
          $form->setValues(Mage::getSingleton('adminhtml/session')->getAuthorsData());
          Mage::getSingleton('adminhtml/session')->setAuthorsData(null);
      } elseif ( Mage::registry('authors_data') ) {
          $form->setValues(Mage::registry('authors_data')->getData());
      }
      
      if($form->getElement('author_link_text')->getValue() != "") 
      {
         $form->getElement('author_link_text')->setReadonly(true); 
         $form->getElement('author_link_text')->setNote("read-only");
      }
      if($form->getElement('bio_link_text')->getValue() != "") 
      {
          $form->getElement('bio_link_text')->setReadonly(true);
          $form->getElement('bio_link_text')->setNote("read-only");
      }
	  return parent::_prepareForm();
  }
  
}