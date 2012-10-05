<?php

class NightingaleConant_Promocodes_Block_Adminhtml_Promocodes_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();
                 
        $this->_objectId = 'id';
        $this->_blockGroup = 'promocodes';
        $this->_controller = 'adminhtml_promocodes';
        $this->_removeButton('delete');
        $this->_removeButton('reset');
        
        $this->_updateButton('save', 'label', Mage::helper('promocodes')->__('Save'));
        		
        $this->_addButton('saveandcontinue', array(
            'label'     => Mage::helper('adminhtml')->__('Save And Continue Edit'),
            'onclick'   => 'saveAndContinueEdit()',
            'class'     => 'save',
        ), -100);

        $this->_formScripts[] = "
            function toggleEditor() {
                if (tinyMCE.getInstanceById('promocodes_content') == null) {
                    tinyMCE.execCommand('mceAddControl', false, 'promocodes_content');
                } else {
                    tinyMCE.execCommand('mceRemoveControl', false, 'promocodes_content');
                }
            }

            function saveAndContinueEdit(){
                editForm.submit($('edit_form').action+'back/edit/');
            }
        ";
    }

    public function getHeaderText()
    {
        if( Mage::registry('promocodes_data') && Mage::registry('promocodes_data')->getCkcEntityId() ) {
            return Mage::helper('promocodes')->__("Edit Promo code '%s'", $this->htmlEscape(Mage::registry('promocodes_data')->getCkcKeyCode()));
        } else {
            return Mage::helper('promocodes')->__('Add New Promocodes');
        }
    }
}
