<?php


class NGC_Installment_Block_Adminhtml_Type_Edit_Tab_Properties
    extends Mage_Adminhtml_Block_Widget_Form
    implements Mage_Adminhtml_Block_Widget_Tab_Interface
{
    /**
     * Prepare form for render
     */
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form();

        $installment = Mage::registry('current_installment_type');

        $fieldset = $form->addFieldset('base_fieldset', array('legend'=>Mage::helper('installment')->__('Installment Type Information')));

        $validateClass = sprintf('required-entry validate-length maximum-length-%d',
            NGC_Installment_Model_Type::INSTALLMENT_TYPE_MAX_LENGTH);

        $fieldset->addField('installment_type_id', 'text',
            array(
                'name'  => 'type_id',
                'label' => Mage::helper('installment')->__('Installment Type Id'),
                'title' => Mage::helper('installment')->__('Installment Type Id'),
                'note'  => Mage::helper('installment')->__('Maximum length must be less then %s symbols',
                    NGC_Installment_Model_Type::INSTALLMENT_TYPE_MAX_LENGTH),
                'class' => $validateClass,
                'required' => true,
            )
        );

        $validateClass = sprintf('required-entry validate-length maximum-length-%d',
            NGC_Installment_Model_Type::INSTALLMENT_DESC_MAX_LENGTH);

        $fieldset->addField('installment_type_description', 'textarea',
            array(
                'name'  => 'description',
                'label' => Mage::helper('installment')->__('Description'),
                'title' => Mage::helper('installment')->__('Description'),
                'note'  => Mage::helper('installment')->__('Maximum length must be less then %s symbols',
                    NGC_Installment_Model_Type::INSTALLMENT_DESC_MAX_LENGTH),
                'class' => $validateClass,
                'required' => true,
            )
        );

        $fieldset->addField('installment_type_plan_active', 'select',
            array(
                'name'      => 'plan_active',
                'label'     => Mage::helper('installment')->__('This plan is'),
                'title'     => Mage::helper('installment')->__('Plan Status'),
                'required'  => true,
                'class'     => 'input-select',
                'options'   => array('1' => Mage::helper('adminhtml')->__('Active'), '0' => Mage::helper('adminhtml')->__('Inactive')),
            )
        );

        $fieldset->addField('installment_type_auto_adjust', 'select',
            array(
                'name'      => 'auto_adjust',
                'label'     => Mage::helper('installment')->__('Automatically adjust future installments'),
                'title'     => Mage::helper('installment')->__('Auto Adjust'),
                'note'      => Mage::helper('installment')->__('If an installment payment is soft declined, automatically adjust future installment payment due dates if future authorization successful'),
                'required'  => true,
                'class'     => 'input-select',
                'options'   => array('1' => Mage::helper('adminhtml')->__('Yes'), '0' => Mage::helper('adminhtml')->__('No')),
            )
        );

        $fieldset->addField('installment_type_plan_default', 'select',
            array(
                'name'      => 'plan_default',
                'label'     => Mage::helper('installment')->__('Default Plan'),
                'title'     => Mage::helper('installment')->__('Plan Status'),
                'required'  => true,
                'class'     => 'input-select',
                'options'   => array('1' => Mage::helper('adminhtml')->__('Yes'), '0' => Mage::helper('adminhtml')->__('No')),
            )
        );
        if (!is_null($installment->getId())) {
            // If edit add id
            $form->addField('id', 'hidden',
                array(
                    'name'  => 'id',
                    'value' => $installment->getId(),
                )
            );
        }

        if( Mage::getSingleton('adminhtml/session')->getInstallmentTypeData() ) {
            $form->addValues(Mage::getSingleton('adminhtml/session')->getInstallmentTypeData());
            Mage::getSingleton('adminhtml/session')->setInstallmentTypeData(null);
        } else {
            $form->addValues($installment->getData());
        }


//        $form->setUseContainer(true);
//        $form->setId('edit_form');
//        $form->setAction($this->getUrl('*/*/save'));
        $this->setForm($form);

        return parent::_prepareForm();
    }


    /**
     * Prepare label for tab
     *
     * @return string
     */
    public function getTabLabel()
    {
        return Mage::helper('installment')->__('Properties');
    }

    /**
     * Prepare title for tab
     *
     * @return string
     */
    public function getTabTitle()
    {
        return Mage::helper('installment')->__('Properties');
    }

    /**
     * Returns status flag about this tab can be shown or not
     *
     * @return true
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * Returns status flag about this tab hidden or not
     *
     * @return true
     */
    public function isHidden()
    {
        return false;
    }

    /**
     * Check permission for passed action
     *
     * @param string $action
     * @return bool
     */
    protected function _isAllowedAction($action)
    {
        return Mage::getSingleton('admin/session')->isAllowed('system/installment/type/' . $action);
    }
}