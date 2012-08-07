<?php

class NGC_Installment_Block_Adminhtml_Sales_Order_Edit_Tab_Properties
    extends Mage_Adminhtml_Block_Widget_Form
    implements Mage_Adminhtml_Block_Widget_Tab_Interface
{

    protected function _prepareForm()
    {
        $form = new Varien_Data_Form();

        $installment = Mage::registry('current_installment_payment');

        $fieldset = $form->addFieldset('base_fieldset', array('legend'=>Mage::helper('installment')->__('Installment Payment Information')));

        $fieldset->addType('amount_due', 'NGC_Installment_Lib_Varien_Data_Form_Element_AmountDue');

        $fieldset->addField('order_id', 'text',
            array(
                'name'  => 'order_id',
                'label' => Mage::helper('installment')->__('Order Id'),
                'title' => Mage::helper('installment')->__('Order Id'),
                'readonly' => true,
            )
        );

        $fieldset->addField('installment_master_sequence_number', 'text',
            array(
                'name'  => 'sequence_number',
                'label' => Mage::helper('installment')->__('Sequence Number'),
                'title' => Mage::helper('installment')->__('Sequence Number'),
                'readonly' => true,
            )
        );
        $authPaid = ($installment->getInstallmentMasterInstallmentAuthorized() || $installment->getInstallmentMasterInstallmentPaid());

        $required = (!$authPaid) ? true : false;

        $option = array(
            'name'  => 'amount_due',
            'label' => Mage::helper('installment')->__('Amount Due'),
            'title' => Mage::helper('installment')->__('Amount Due'),
            'readonly' => true,
            'required' => $required,
        );

        $fieldset->addField('installment_master_amount_due', 'amount_due', $option);

        $outputFormat = Mage::app()->getLocale()->getDateTimeFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT);
        $fieldType = ($authPaid) ? 'text' : 'date';

        $option = array(
            'name'  => 'amount_due_date',
            'label' => Mage::helper('installment')->__('Amount Due Date'),
            'title' => Mage::helper('installment')->__('Amount Due Date'),
            'class' => 'validate-date',
            'image'  => $this->getSkinUrl('images/grid-cal.gif'),
            'input_format' => Varien_Date::DATETIME_INTERNAL_FORMAT,
            'format'       => $outputFormat,
            'time'         => true,
            'required' => $required,
        );

        if ($authPaid) {
            $option = array_merge($option, array('readonly' => true));
        }

        $fieldset->addField('installment_master_amount_due_date', $fieldType, $option);

        $fieldset->addField('installment_master_installment_authorized', 'select',
            array(
                'name'  => 'installment_authorized',
                'label' => Mage::helper('installment')->__('Installment Authorized'),
                'title' => Mage::helper('installment')->__('Installment Authorized'),
                'disabled' => 'disabled',
                'class'     => 'input-select',
                'options'   => array('1' => Mage::helper('adminhtml')->__('Yes'), '0' => Mage::helper('adminhtml')->__('No')),
            )
        );

        $fieldset->addField('installment_master_installment_paid', 'select',
            array(
                'name'  => 'installment_paid',
                'label' => Mage::helper('installment')->__('Installment Paid'),
                'title' => Mage::helper('installment')->__('Installment Paid'),
                'disabled' => 'disabled',
                'class'     => 'input-select',
                'options'   => array('1' => Mage::helper('adminhtml')->__('Yes'), '0' => Mage::helper('adminhtml')->__('No')),
            )
        );

        $disabled = ($authPaid) ? 'disabled' : '';
        $suspendInstallment = $fieldset->addField('installment_master_suspend_installment', 'select',
            array(
                'name'  => 'suspend_installment',
                'label' => Mage::helper('installment')->__('Suspend Installment'),
                'title' => Mage::helper('installment')->__('Suspend Installment'),
                'required'  => $required,
                'disabled'  =>  $disabled,
                'class'     => 'input-select',
                'options'   => array('1' => Mage::helper('adminhtml')->__('Yes'), '0' => Mage::helper('adminhtml')->__('No')),
                'onchange'   => 'modifySuspendedReasonElement(this)'
            )
        );

        $suspendInstallment->setAfterElementHtml('<script>
            function modifySuspendedReasonElement(selectElem){
                if(selectElem.value == 0){
                    $("installment_master_suspended_reason").readOnly=true;
                    $("installment_master_suspended_reason").removeClassName(\'required-entry\');
                    var label = $$(\'label[for="installment_master_suspended_reason"] span\');
                    $(label[0]).remove();
                } else {
                    $("installment_master_suspended_reason").readOnly=false;
                    $("installment_master_suspended_reason").addClassName(\'required-entry\');
                    var label = $$(\'label[for="installment_master_suspended_reason"]\');
                    var elem = new Element("span").addClassName("required").update(" *");

                    $(label[0]).insert({bottom: elem});
                }
            }
        </script>');

        $fieldset->addField('installment_master_suspended_reason', 'textarea',
            array(
                'name'  => 'suspended_reason',
                'id'    => 'suspended_reason',
                'label' => Mage::helper('installment')->__('Suspended Reason'),
                'title' => Mage::helper('installment')->__('Suspended Reason'),
                'readonly' => ($installment->getInstallmentMasterSuspendInstallment()) ? true : false
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

        $data = Mage::getSingleton('adminhtml/session')->getInstallmentPaymentData();
        if( Mage::getSingleton('adminhtml/session')->getInstallmentPaymentData() ) {
            $form->addValues(Mage::getSingleton('adminhtml/session')->getInstallmentPaymentData());
            Mage::getSingleton('adminhtml/session')->setInstallmentPaymentData(null);
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
