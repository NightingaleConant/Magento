<?php
/**
* @author Amasty Team
* @copyright Amasty
* @package Amasty_Orderattr
*/
class Amasty_Orderattr_Block_Adminhtml_Order_Attribute_Edit_Tab_Main extends Mage_Adminhtml_Block_Widget_Form
{

    protected function _prepareForm()
    {
        $model = Mage::registry('entity_attribute');
        
        if (!Mage::app()->isSingleStoreMode()) {
            $model->setData('stores', explode(',', $model->getData('store_ids')));
        }

        $form = new Varien_Data_Form(array(
            'id' => 'edit_form',
            'action' => $this->getData('action'),
            'method' => 'post'
        ));

        $disableAttributeFields = array(
//            'sku'       => array(
//                'is_global',
//                'is_unique',
//            ),
//            'url_key'   => array(
//                'is_unique',
//            ),
//            'status'    => array(
//                'is_configurable'
//            )
        );

        $rewriteAttributeValue = array(
            'status'    => array(
                'is_configurable' => 0
            )
        );

        $fieldset = $form->addFieldset('base_fieldset',
            array('legend'=>Mage::helper('catalog')->__('Attribute Properties'))
        );
        if ($model->getAttributeId()) {
            $fieldset->addField('attribute_id', 'hidden', array(
                'name' => 'attribute_id',
            ));
        }

        $this->_addElementTypes($fieldset);

        $yesno = array(
            array(
                'value' => 0,
                'label' => Mage::helper('catalog')->__('No')
            ),
            array(
                'value' => 1,
                'label' => Mage::helper('catalog')->__('Yes')
            ));

        $fieldset->addField('attribute_code', 'text', array(
            'name'  => 'attribute_code',
            'label' => Mage::helper('catalog')->__('Attribute Code'),
            'title' => Mage::helper('catalog')->__('Attribute Code'),
            'note'  => Mage::helper('catalog')->__('For internal use. Must be unique with no spaces'),
            'class' => 'validate-code',
            'required' => true,
        ));
        
        if (!Mage::app()->isSingleStoreMode()) {
            $fieldset->addField('stores', 'multiselect', array(
                'name'      => 'stores[]',
                'label'     => Mage::helper('cms')->__('Store View'),
                'title'     => Mage::helper('cms')->__('Store View'),
                'required'  => true,
                'values'    => Mage::getSingleton('adminhtml/system_store')->getStoreValuesForForm(false, true),
            ));
        }
        else {
            $fieldset->addField('stores', 'hidden', array(
                'name'      => 'stores[]',
                'value'     => Mage::app()->getStore(true)->getId()
            ));
            $model->setStoreId(Mage::app()->getStore(true)->getId());
        }

        $scopes = array(
            Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE =>Mage::helper('catalog')->__('Store View'),
            Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_WEBSITE =>Mage::helper('catalog')->__('Website'),
            Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL =>Mage::helper('catalog')->__('Global'),
        );

        if ($model->getAttributeCode() == 'status' || $model->getAttributeCode() == 'tax_class_id') {
            unset($scopes[Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE]);
        }

        /*$fieldset->addField('is_global', 'select', array(
            'name'  => 'is_global',
            'label' => Mage::helper('catalog')->__('Scope'),
            'title' => Mage::helper('catalog')->__('Scope'),
            'note'  => Mage::helper('catalog')->__('Declare attribute value saving scope'),
//            'class' => 'no-display',
            'values'=> $scopes
        ));*/

        $inputTypes = array(
            array(
                'value' => 'text',
                'label' => Mage::helper('catalog')->__('Text Field')
            ),
            array(
                'value' => 'textarea',
                'label' => Mage::helper('catalog')->__('Text Area')
            ),
            array(
                'value' => 'date',
                'label' => Mage::helper('catalog')->__('Date')
            ),
            array(
                'value' => 'datetime',
                'label' => Mage::helper('catalog')->__('Date With Time')
            ),
            /*array(
                'value' => 'boolean',
                'label' => Mage::helper('catalog')->__('Yes/No')
            ),*/
            /*array(
                'value' => 'multiselect',
                'label' => Mage::helper('catalog')->__('Multiple Select')
            ),*/
            array(
                'value' => 'select',
                'label' => Mage::helper('catalog')->__('Dropdown')
            ),
            /*array(
                'value' => 'price',
                'label' => Mage::helper('catalog')->__('Price')
            ),
            array(
                'value' => 'gallery',
                'label' => Mage::helper('catalog')->__('Gallery')
            ),
            array(
                'value' => 'media_image',
                'label' => Mage::helper('catalog')->__('Media Image')
            ),*/
        );

        $response = new Varien_Object();
        $response->setTypes(array());
//      do not need additional types  Mage::dispatchEvent('adminhtml_product_attribute_types', array('response'=>$response));

        $_disabledTypes = array();
        $_hiddenFields = array();
        foreach ($response->getTypes() as $type) {
            $inputTypes[] = $type;
            if (isset($type['hide_fields'])) {
                $_hiddenFields[$type['value']] = $type['hide_fields'];
            }
            if (isset($type['disabled_types'])) {
                $_disabledTypes[$type['value']] = $type['disabled_types'];
            }
        }
        //Mage::register('attribute_type_hidden_fields', $_hiddenFields);
        //Mage::register('attribute_type_disabled_types', $_disabledTypes);


        $fieldset->addField('frontend_input', 'select', array(
            'name' => 'frontend_input',
            'label' => Mage::helper('catalog')->__('Catalog Input Type for Store Owner'),
            'title' => Mage::helper('catalog')->__('Catalog Input Type for Store Owner'),
            'value' => 'text',
            'values'=> $inputTypes
        ));

        $fieldset->addField('default_value_text', 'text', array(
            'name' => 'default_value_text',
            'label' => Mage::helper('catalog')->__('Default value'),
            'title' => Mage::helper('catalog')->__('Default value'),
            'value' => $model->getDefaultValue(),
        ));

        $fieldset->addField('default_value_yesno', 'select', array(
            'name' => 'default_value_yesno',
            'label' => Mage::helper('catalog')->__('Default value'),
            'title' => Mage::helper('catalog')->__('Default value'),
            'values' => $yesno,
            'value' => $model->getDefaultValue(),
        ));

        $dateFormatIso = Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT);
        $fieldset->addField('default_value_date', 'date', array(
            'name'   => 'default_value_date',
            'label'  => Mage::helper('catalog')->__('Default value'),
            'title'  => Mage::helper('catalog')->__('Default value'),
            'image'  => $this->getSkinUrl('images/grid-cal.gif'),
            'value'  => $model->getDefaultValue(),
            'format'       => $dateFormatIso
        ));

        $fieldset->addField('default_value_textarea', 'textarea', array(
            'name' => 'default_value_textarea',
            'label' => Mage::helper('catalog')->__('Default value'),
            'title' => Mage::helper('catalog')->__('Default value'),
            'value' => $model->getDefaultValue(),
        ));
        
        $fieldset->addField('is_visible_on_front', 'select', array(
            'name'      => 'is_visible_on_front',
            'label'     => Mage::helper('catalog')->__('Visible on Front-end'),
            'title'     => Mage::helper('catalog')->__('Visible on Front-end'),
            'values'    => $yesno,
        ));

        /*$fieldset->addField('is_unique', 'select', array(
            'name' => 'is_unique',
            'label' => Mage::helper('catalog')->__('Unique Value'),
            'title' => Mage::helper('catalog')->__('Unique Value (not shared with other products)'),
            'note'  => Mage::helper('catalog')->__('Not shared with other products'),
            'values' => $yesno,
        ));*/

        $fieldset->addField('is_required', 'select', array(
            'name' => 'is_required',
            'label' => Mage::helper('catalog')->__('Values Required'),
            'title' => Mage::helper('catalog')->__('Values Required'),
            'values' => $yesno,
        ));

        $fieldset->addField('frontend_class', 'select', array(
            'name'  => 'frontend_class',
            'label' => Mage::helper('catalog')->__('Input Validation'),
            'title' => Mage::helper('catalog')->__('Input Validation'),
            'values'=>  array(
                array(
                    'value' => '',
                    'label' => Mage::helper('catalog')->__('None')
                ),
                array(
                    'value' => 'validate-number',
                    'label' => Mage::helper('catalog')->__('Decimal Number')
                ),
                array(
                    'value' => 'validate-digits',
                    'label' => Mage::helper('catalog')->__('Integer Number')
                ),
                array(
                    'value' => 'validate-email',
                    'label' => Mage::helper('catalog')->__('Email')
                ),
                array(
                    'value' => 'validate-url',
                    'label' => Mage::helper('catalog')->__('Url')
                ),
                array(
                    'value' => 'validate-alpha',
                    'label' => Mage::helper('catalog')->__('Letters')
                ),
                array(
                    'value' => 'validate-alphanum',
                    'label' => Mage::helper('catalog')->__('Letters(a-zA-Z) or Numbers(0-9)')
                ),
            )
        ));
/*
        $fieldset->addField('use_in_super_product', 'select', array(
            'name' => 'use_in_super_product',
            'label' => Mage::helper('catalog')->__('Apply To Configurable/Grouped Product'),
            'values' => $yesno,
        )); */

        /*$fieldset->addField('apply_to', 'apply', array(
            'name'        => 'apply_to[]',
            'label'       => Mage::helper('catalog')->__('Apply To'),
            'values'      => Mage_Catalog_Model_Product_Type::getOptions(),
            'mode_labels' => array(
                'all'     => Mage::helper('catalog')->__('All Product Types'),
                'custom'  => Mage::helper('catalog')->__('Selected Product Types')
            ),
            'required'    => true
        ));*/

        /*$fieldset->addField('is_configurable', 'select', array(
            'name' => 'is_configurable',
            'label' => Mage::helper('catalog')->__('Use To Create Configurable Product'),
            'values' => $yesno,
        ));*/
        
        // -----


        // frontend properties fieldset
        $fieldset = $form->addFieldset('front_fieldset', array('legend'=>Mage::helper('amorderattr')->__('Attribute Configuration')));

        $fieldset->addField('checkout_step', 'select', array(
            'name' => 'checkout_step',
            'label' => Mage::helper('amorderattr')->__('Show On Checkout Step'),
            'title' => Mage::helper('amorderattr')->__('Show On Checkout Step'),
            'values'=>  array(
                array(
                    'value' => '2',
                    'label' => Mage::helper('amorderattr')->__('2. Billing Information')
                ),
                array(
                    'value' => '3',
                    'label' => Mage::helper('amorderattr')->__('3. Shipping Information')
                ),
                array(
                    'value' => '4',
                    'label' => Mage::helper('amorderattr')->__('4. Shipping Method')
                ),
                array(
                    'value' => '5',
                    'label' => Mage::helper('amorderattr')->__('5. Payment Information')
                ),
            )
        ));
        
        $fieldset->addField('sorting_order', 'text', array(
            'name'  => 'sorting_order',
            'label' => Mage::helper('catalog')->__('Display Sorting Order'),
            'title' => Mage::helper('catalog')->__('Display Sorting Order'),
            'note'  => Mage::helper('catalog')->__('Numeric, used in front-end to sort attributes'),
        ));
        
        $fieldset->addField('save_selected', 'select', array(
            'name' => 'save_selected',
            'label' => Mage::helper('amorderattr')->__('Save Entered Value For Future Checkout'),
            'title' => Mage::helper('amorderattr')->__('Save Entered Value For Future Checkout'),
            'note'  => Mage::helper('catalog')->__('If set to "Yes", previously entered value will be used during checkout. Works for registered customers only.'),
            'values' => $yesno,
        ));
        
        $fieldset->addField('show_on_grid', 'select', array(
            'name' => 'show_on_grid',
            'label' => Mage::helper('amorderattr')->__('Show on Orders Grid'),
            'title' => Mage::helper('amorderattr')->__('Show on Orders Grid'),
            'values' => $yesno,
        ));
        
        $fieldset->addField('include_pdf', 'select', array(
            'name' => 'include_pdf',
            'label' => Mage::helper('amorderattr')->__('Include Into PDF Documents'),
            'title' => Mage::helper('amorderattr')->__('Include Into PDF Documents'),
            'values' => $yesno,
        ));
        
        $fieldset->addField('apply_default', 'select', array(
            'name' => 'apply_default',
            'label' => $this->__('Automatically Apply Default Value'),
            'title' => $this->__('Automatically Apply Default Value'),
        	'note'  => $this->__('If set to "Yes", the default value will be automatically applied for each order if attribute value is not entered or not visible at the frontend.'),
            'values' => $yesno,
        ));
        
        if ($model->getId()) {
            $form->getElement('attribute_code')->setDisabled(1);
            $form->getElement('frontend_input')->setDisabled(1);

//            if (isset($disableAttributeFields[$model->getAttributeCode()])) {
//                foreach ($disableAttributeFields[$model->getAttributeCode()] as $field) {
//                    $form->getElement($field)->setDisabled(1);
//                }
//            }
        }
//        if (!$model->getIsUserDefined() && $model->getId()) {
//            $form->getElement('is_unique')->setDisabled(1);
//        }

        $form->addValues($model->getData());

        if ($model->getId() && isset($rewriteAttributeValue[$model->getAttributeCode()])) {
            foreach ($rewriteAttributeValue[$model->getAttributeCode()] as $field => $value) {
                $form->getElement($field)->setValue($value);
            }
        }

       // $form->getElement('apply_to')->setSize(5);

        if ($applyTo = $model->getApplyTo()) {
            $applyTo = is_array($applyTo) ? $applyTo : explode(',', $applyTo);
            //$form->getElement('apply_to')->setValue($applyTo);
        } else {
            //$form->getElement('apply_to')->addClass('no-display ignore-validate');
        }

        $this->setForm($form);

        return parent::_prepareForm();
    }

    protected function _getAdditionalElementTypes()
    {
        return array(
            'apply' => Mage::getConfig()->getBlockClassName('adminhtml/catalog_product_helper_form_apply')
        );
    }

}
