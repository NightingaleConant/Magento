<?php

class NightingaleConant_Royalties_Block_Adminhtml_Royalties_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();
                 
        $this->_objectId = 'id';
        $this->_blockGroup = 'royalties';
        $this->_controller = 'adminhtml_royalties';
        
        $this->_updateButton('save', 'label', Mage::helper('royalties')->__('Save Royalty'));
        $this->_updateButton('delete', 'label', Mage::helper('royalties')->__('Delete Royalty'));
		
        $this->_addButton('saveandcontinue', array(
            'label'     => Mage::helper('adminhtml')->__('Save And Continue Edit'),
            'onclick'   => 'saveAndContinueEdit()',
            'class'     => 'save',
        ), -100);

        $this->_formScripts[] = "
            function toggleEditor() {
                if (tinyMCE.getInstanceById('royalties_content') == null) {
                    tinyMCE.execCommand('mceAddControl', false, 'royalties_content');
                } else {
                    tinyMCE.execCommand('mceRemoveControl', false, 'royalties_content');
                }
            }

            function saveAndContinueEdit(){
                editForm.submit($('edit_form').action+'back/edit/');
            }
        ";
    }

    public function getHeaderText()
    {
        $royalties_data = Mage::registry('royalties_data');
        if( $royalties_data && $royalties_data['royalty_rate_id'] ) {
            //Mage::log(Mage::registry('royalties_data')->getData());
            return Mage::helper('royalties')->__("Edit Royalty '%s for item %s'", $this->htmlEscape($royalties_data['AUTH']), $this->htmlEscape($royalties_data['ITEM']));
        } else {
            return Mage::helper('royalties')->__('Add New Royalty');
        }
    }
}
