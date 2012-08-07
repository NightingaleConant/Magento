<?php

class NGC_Installment_Block_Adminhtml_Sales_Order_Split_Form extends Mage_Adminhtml_Block_Widget_Form
{

    protected function _prepareForm()
    {
        $form = new Varien_Data_Form();

        $installment = Mage::registry('current_installment_payment');

        $fieldset = $form->addFieldset('base_fieldset', array('legend'=>Mage::helper('installment')->__('Original Installment Payment')));

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

        $fieldset->addField('installment_master_amount_due', 'text',
            array(
                'name'  => 'amount_due',
                'label' => Mage::helper('installment')->__('Amount Due'),
                'title' => Mage::helper('installment')->__('Amount Due'),
                'required' => true,
            )
        );


        $outputFormat = Mage::app()->getLocale()->getDateTimeFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT);
        $fieldset->addField('installment_master_amount_due_date', 'date',
            array(
                'name'  => 'amount_due_date',
                'label' => Mage::helper('installment')->__('Amount Due Date'),
                'title' => Mage::helper('installment')->__('Amount Due Date'),
                'class' => 'validate-date',
                'time'  => true,
                'image' => $this->getSkinUrl('images/grid-cal.gif'),
                'input_format' => Varien_Date::DATETIME_INTERNAL_FORMAT,
                'format'       => $outputFormat,
                'required'     => true,
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

        $fieldset = $form->addFieldset('new_installment_fieldset', array('legend'=>Mage::helper('installment')->__('New Installment Payment')));

        $fieldset->addField('new_order_id', 'text',
            array(
                'name'  => 'order_id',
                'label' => Mage::helper('installment')->__('Order Id'),
                'title' => Mage::helper('installment')->__('Order Id'),
                'readonly' => true,
                'value'     => $installment->getOrderId()
            )
        );

        $fieldset->addField('new_installment_master_amount_due', 'text',
            array(
                'name'  => 'new_amount_due',
                'label' => Mage::helper('installment')->__('Amount Due'),
                'title' => Mage::helper('installment')->__('Amount Due'),
                'required' => true,
            )
        );


        $outputFormat = Mage::app()->getLocale()->getDateTimeFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT);
        $fieldset->addField('new_installment_master_amount_due_date', 'date',
            array(
                'name'  => 'new_amount_due_date',
                'label' => Mage::helper('installment')->__('Amount Due Date'),
                'title' => Mage::helper('installment')->__('Amount Due Date'),
                'class' => 'validate-date',
                'time'  => true,
                'image' => $this->getSkinUrl('images/grid-cal.gif'),
                'input_format' => Varien_Date::DATETIME_INTERNAL_FORMAT,
                'format'       => $outputFormat,
                'required'     => true,
            )
        );

        if( Mage::getSingleton('adminhtml/session')->getInstallmentPaymentData() ) {
            $form->addValues(Mage::getSingleton('adminhtml/session')->getInstallmentPaymentData());
            Mage::getSingleton('adminhtml/session')->setInstallmentPaymentData(null);
        } else {
            $form->addValues($installment->getData());
        }


        $form->setUseContainer(true);
        $form->setId('edit_form');
        $form->setAction($this->getUrl('*/*/split'));
        $form->setPost(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }

}
