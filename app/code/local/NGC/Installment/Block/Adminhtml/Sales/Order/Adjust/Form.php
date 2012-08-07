<?php

class NGC_Installment_Block_Adminhtml_Sales_Order_Adjust_Form extends Mage_Adminhtml_Block_Widget_Form
{

    protected function _prepareForm()
    {
        $form = new Varien_Data_Form();

        $installment = Mage::registry('current_installment_payment');

        $fieldset = $form->addFieldset('base_fieldset', array('legend'=>Mage::helper('installment')->__('Installment Payment')));

        $fieldset->addField('order_id', 'text',
            array(
                'name'  => 'order_id',
                'label' => Mage::helper('installment')->__('Order Id'),
                'title' => Mage::helper('installment')->__('Order Id'),
                'readonly' => true,
            )
        );

        $fieldset->addField('installment_master_amount_due', 'text',
            array(
                'name'  => 'amount_due',
                'label' => Mage::helper('installment')->__('Amount Due'),
                'title' => Mage::helper('installment')->__('Amount Due'),
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

        $data = Mage::getSingleton('adminhtml/session')->getInstallmentPaymentData();
        if( Mage::getSingleton('adminhtml/session')->getInstallmentPaymentData() ) {
            $form->addValues(Mage::getSingleton('adminhtml/session')->getInstallmentPaymentData());
            Mage::getSingleton('adminhtml/session')->setInstallmentPaymentData(null);
        } else {
            $form->addValues($installment->getData());
        }


        $form->setUseContainer(true);
        $form->setId('edit_form');
        $form->setAction($this->getUrl('*/*/adjust'));
        $form->setPost(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }

}
