<?php

class NGC_Installment_Block_Adminhtml_Sales_Order_View_Tab_Payment
    extends Mage_Adminhtml_Block_Widget_Grid
    implements Mage_Adminhtml_Block_Widget_Tab_Interface
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('order_installment_payment');
        $this->setUseAjax(true);
    }

    /**
     * Retrieve collection class
     *
     * @return string
     */
    protected function _getCollectionClass()
    {
        return 'installment/master_collection';
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getResourceModel($this->_getCollectionClass())
            ->addFieldToSelect('id')
            ->addFieldToSelect('order_id')
            ->addFieldToSelect('installment_master_sequence_number')
            ->addFieldToSelect('installment_master_amount_due')
            ->addFieldToSelect('installment_master_amount_due_date')
            ->addFieldToSelect('installment_master_installment_authorized')
            ->addFieldToSelect('installment_master_installment_paid')
            ->addFieldToSelect('installment_master_suspend_installment')
            ->addFieldToSelect('installment_master_suspended_reason')
            ->addFieldToFilter('order_id', $this->getOrder()->getRealOrderId());

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('order_id', array(
            'header' => Mage::helper('installment')->__('Order ID'),
            'index' => 'order_id',
        ));

        $this->addColumn('sequence_number', array(
            'header' => Mage::helper('installment')->__('Sequence Number'),
            'index' => 'installment_master_sequence_number',
        ));

        $this->addColumn('amount_due', array(
            'header' => Mage::helper('installment')->__('Amount Due'),
            'index' => 'installment_master_amount_due',
            'type'  => 'number',
        ));

        $this->addColumn('amount_due_date', array(
            'header' => Mage::helper('installment')->__('Due Date'),
            'index' => 'installment_master_amount_due_date',
            'type'  => 'date',
        ));

        $this->addColumn('installment_authorized', array(
            'header' => Mage::helper('installment')->__('Installment Authorized'),
            'index' => 'installment_master_installment_authorized',
            'renderer' => 'NGC_Installment_Block_Adminhtml_Sales_Order_Renderer_Installment_Authorized',
        ));


        $this->addColumn('installment_paid', array(
            'header' => Mage::helper('installment')->__('Installment Paid'),
            'index' => 'installment_master_installment_paid',
            'renderer' => 'NGC_Installment_Block_Adminhtml_Sales_Order_Renderer_Installment_Paid',
        ));

        $this->addColumn('suspend_installment', array(
            'header' => Mage::helper('installment')->__('Suspend Installment'),
            'index' => 'installment_master_suspend_installment',
            'renderer' => 'NGC_Installment_Block_Adminhtml_Sales_Order_Renderer_Installment_Suspended',
        ));

        $this->addColumn('suspended_reason', array(
            'header' => Mage::helper('installment')->__('Suspended Reason'),
            'index' => 'installment_master_suspended_reason',
            'type'  => 'text',
        ));

        return parent::_prepareColumns();
    }

    /**
     * Retrieve order model instance
     *
     * @return Mage_Sales_Model_Order
     */
    public function getOrder()
    {
        return Mage::registry('current_order');
    }

    public function getRowUrl($row)
    {
        return $this->getUrl(
            '*/sales_order_payment/edit',
            array(
                'id'=> $row->getId()
            ));
    }

    public function getGridUrl()
    {
        return $this->getUrl('*/sales_order_payment/payment', array('_current' => true));
    }

    /**
     * ######################## TAB settings #################################
     */
    public function getTabLabel()
    {
        return Mage::helper('sales')->__('Installment Payments');
    }

    public function getTabTitle()
    {
        return Mage::helper('sales')->__('Installment Payments');
    }

    public function canShowTab()
    {
        return $this->getOrder()->getInstallmentTypeId();
    }

    public function isHidden()
    {
        return false;
    }
}