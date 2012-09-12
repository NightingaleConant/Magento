<?php

class NightingaleConant_Authors_Block_Adminhtml_Authors_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();
                 
        $this->_objectId = 'id';
        $this->_blockGroup = 'authors';
        $this->_controller = 'adminhtml_authors';
        
        $this->_updateButton('save', 'label', Mage::helper('authors')->__('Save Author'));
        $this->_updateButton('delete', 'label', Mage::helper('authors')->__('Delete Author'));
		
        $this->_addButton('saveandcontinue', array(
            'label'     => Mage::helper('adminhtml')->__('Save And Continue Edit'),
            'onclick'   => 'saveAndContinueEdit()',
            'class'     => 'save',
        ), -100);

        $this->_formScripts[] = "
            function toggleEditor() {
                if (tinyMCE.getInstanceById('authors_content') == null) {
                    tinyMCE.execCommand('mceAddControl', false, 'authors_content');
                } else {
                    tinyMCE.execCommand('mceRemoveControl', false, 'authors_content');
                }
            }

            function saveAndContinueEdit(){
                editForm.submit($('edit_form').action+'back/edit/');
            }
        ";
    }

    public function getHeaderText()
    {
        if( Mage::registry('authors_data') && Mage::registry('authors_data')->getAuthorId() ) {
            return Mage::helper('authors')->__("Edit Author '%s %s'", $this->htmlEscape(Mage::registry('authors_data')->getFirstName()), $this->htmlEscape(Mage::registry('authors_data')->getLastName()));
        } else {
            return Mage::helper('authors')->__('Add New Author');
        }
    }
}
