<?php

class NightingaleConant_SpecialItem_Block_Adminhtml_Specialitem_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();
                 
        $this->_objectId = 'id';
        $this->_blockGroup = 'specialitem';
        $this->_controller = 'adminhtml_specialitem';
        
        $this->_updateButton('save', 'label', Mage::helper('specialitem')->__('Save Associate Item'));
        $this->_updateButton('delete', 'label', Mage::helper('specialitem')->__('Delete Associate Item'));
		
        $this->_addButton('saveandcontinue', array(
            'label'     => Mage::helper('adminhtml')->__('Save And Continue Edit'),
            'onclick'   => 'saveAndContinueEdit()',
            'class'     => 'save',
        ), -100);

        $this->_formScripts[] = "
            function toggleEditor() {
                if (tinyMCE.getInstanceById('specialitem_content') == null) {
                    tinyMCE.execCommand('mceAddControl', false, 'specialitem_content');
                } else {
                    tinyMCE.execCommand('mceRemoveControl', false, 'specialitem_content');
                }
            }

            function saveAndContinueEdit(){
                editForm.submit($('edit_form').action+'back/edit/');
            }
        ";
    }

    public function getHeaderText()
    {
        if( Mage::registry('specialitem_data') && Mage::registry('specialitem_data')->getSpecialItemId() ) {
            return Mage::helper('specialitem')->__("Edit Associate Item '%s %s'", $this->htmlEscape(Mage::registry('specialitem_data')->getFirstName()), $this->htmlEscape(Mage::registry('specialitem_data')->getLastName()));
        } else {
            return Mage::helper('specialitem')->__('Add New Associate Item');
        }
    }
}
