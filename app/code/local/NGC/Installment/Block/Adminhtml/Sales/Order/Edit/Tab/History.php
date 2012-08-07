<?php
class NGC_Installment_Block_Adminhtml_Sales_Order_Edit_Tab_History
    extends Mage_Adminhtml_Block_Widget_Grid
    implements Mage_Adminhtml_Block_Widget_Tab_Interface
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('order_installment_payment_history');
        $this->setUseAjax(true);
    }

    /**
     * Retrieve collection class
     *
     * @return string
     */
    protected function _getCollectionClass()
    {
        return 'installment/transaction_collection';
    }

    protected function _prepareCollection()
    {
        $seqNum = Mage::registry('current_installment_payment')->getInstallmentMasterSequenceNumber();

        $collection = Mage::getResourceModel($this->_getCollectionClass())
            ->addFieldToSelect('id')
            ->addFieldToSelect('order_id')
            ->addFieldToSelect('installment_transaction_transaction_sequence_number')
            ->addFieldToSelect('installment_transaction_authorize_amount')
            ->addFieldToSelect('installment_transaction_authorize_attempt_date')
            ->addFieldToSelect('installment_transaction_authorization_code')
            ->addFieldToSelect('installment_transaction_decline_code')
            ->addFieldToSelect('installment_transaction_decline_transaction_message')
            ->addFieldToFilter('installment_transaction_sequence_number', $seqNum)
            ->addFieldToFilter('order_id', $this->getOrderId());

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('order_id', array(
            'header' => Mage::helper('installment')->__('Order ID'),
            'index' => 'order_id',
        ));

        $this->addColumn('transaction_sequence_number', array(
            'header' => Mage::helper('installment')->__('Transaction Sequence Number'),
            'index' => 'installment_transaction_transaction_sequence_number',
            'type'  => 'number',
        ));

        $this->addColumn('authorize_amount', array(
            'header' => Mage::helper('installment')->__('Authorize Amount'),
            'index' => 'installment_transaction_authorize_amount',
            'type'  => 'number',
        ));

        $this->addColumn('authorize_attempt_date', array(
            'header' => Mage::helper('installment')->__('Authorize Attempt Date'),
            'index' => 'installment_transaction_authorize_attempt_date',
            'type'  => 'date',
        ));

        $this->addColumn('authorization_code', array(
            'header' => Mage::helper('installment')->__('Authorization Code'),
            'index' => 'installment_transaction_authorization_code',
            'type'  => 'number',
        ));

        $this->addColumn('decline_code', array(
            'header' => Mage::helper('installment')->__('Decline Code'),
            'index' => 'installment_transaction_decline_code',
            'type'  => 'number',
        ));

        $this->addColumn('decline_transaction_message', array(
            'header' => Mage::helper('installment')->__('Decline Transaction Messsage'),
            'index' => 'installment_transaction_decline_transaction_message',
            'type'  => 'number',
        ));

        return parent::_prepareColumns();
    }

    /**
     * Retrieve order model instance
     *
     * @return Mage_Sales_Model_Order
     */
    public function getOrderId()
    {
        return Mage::registry('current_installment_payment')->getOrderId();
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
        return true;
    }

    public function isHidden()
    {
        return false;
    }
}