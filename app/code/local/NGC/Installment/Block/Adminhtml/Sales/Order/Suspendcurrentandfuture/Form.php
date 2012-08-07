<?php

class NGC_Installment_Block_Adminhtml_Sales_Order_Suspendcurrentandfuture_Form extends Mage_Adminhtml_Block_Widget_Form
{

    protected function _prepareForm()
    {
        $form = new Varien_Data_Form();

        $installment = Mage::registry('current_installment_payment');

        $fieldset = $form->addFieldset('base_fieldset', array('legend'=>Mage::helper('installment')->__('Suspend Current and Future Installment Payments')));

        $validateClass = sprintf('validate-length maximum-length-%d',
            NGC_Installment_Model_Master::INSTALLMENT_MASTER_SUSPENDED_REASON_MAX_LENGTH);

        $fieldset->addField('installment_master_suspended_reason', 'textarea',
            array(
                'name'  => 'suspended_reason',
                'id'    => 'suspended_reason',
                'label' => Mage::helper('installment')->__('Suspended Reason'),
                'title' => Mage::helper('installment')->__('Suspended Reason'),
                'class' => $validateClass,
                'required' => true,
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

        if( Mage::getSingleton('adminhtml/session')->getInstallmentPaymentData() ) {
            $form->addValues(Mage::getSingleton('adminhtml/session')->getInstallmentPaymentData());
            Mage::getSingleton('adminhtml/session')->setInstallmentPaymentData(null);
        } else {
            $form->addValues($installment->getData());
        }


        $form->setUseContainer(true);
        $form->setId('edit_form');
        $form->setAction($this->getUrl('*/*/suspendcurrentandfuturepost'));
        $this->setForm($form);

        return parent::_prepareForm();
    }

}
