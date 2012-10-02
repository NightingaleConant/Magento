<?php

class NightingaleConant_HeldOrders_Block_Adminhtml_Heldorders_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    //protected $_columnGroupBy = 'period';

    public function __construct()
    {
        parent::__construct();

          $this->setId('heldordersGrid');
          $this->setDefaultSort('created_at');
          $this->setDefaultDir('ASC');
          $this->setSaveParametersInSession(true);
    }

    protected function _prepareCollection()
    {
        $filter = $this->getRequest()->getParam('filter');
        $storeIds = $this->getRequest()->getParam('store_ids');
        
        if($filter) {
            $data = explode("&", urldecode(base64_decode($filter)));
            $from = ltrim($data[0], 'from=');
            $to = ltrim($data[1], 'to=');
        }
        
        if (($from == null || $to == null)) {
            $this->setCountTotals(false);
            $this->setCountSubTotals(false);
            return parent::_prepareCollection();
        }
        
        $coll = Mage::getModel('sales/order')->getCollection()
            ->addFieldToSelect('entity_id','order_id')
            ->addFieldToSelect('created_at')->addFieldToSelect('subtotal')
            ->addFieldToSelect('shipping_amount')
            ->addFieldToFilter('created_at', array('from'=>$from, 'to'=>$to));
        
        $coll->getSelect()->joinLeft('amasty_amorderattr_order_attribute as amasty',
                'amasty.order_id = main_table.entity_id', 
                array('assist_sales_rep_number', 'order_hold_code'));
        
        $coll->getSelect()->joinLeft('sales_flat_order_address as oa',
                'main_table.billing_address_id = oa.entity_id',
                array('firstname','lastname','telephone'));
        
        if($storeIds)
            $coll->addFieldToFilter('store_id', array('in'=>$storeIds));
        
        $this->setCollection($coll);
        //Mage::log($coll->getSelect()->__toString());
        return parent::_prepareCollection();
        
    }

    protected function _prepareColumns()
    {
        
        $this->addColumn('assist_sales_rep_number', array(
            'header'        => Mage::helper('sales')->__('Rep #'),
            'index'         => 'assist_sales_rep_number',
            'type'          => 'text',
            'width'         => 100,
            'sortable'      => true,
        ));
        
        $this->addColumn('order_id', array(
            'header'        => Mage::helper('sales')->__('Order #'),
            'index'         => 'order_id',
            'type'          => 'text',
            'width'         => 100,
            'sortable'      => true,
        ));
        
        $this->addColumn('created_at', array(
            'header'        => Mage::helper('sales')->__('Order Date'),
            'index'         => 'created_at',
            'type'          => 'date',
            'width'         => 100,
            'sortable'      => true,
        ));
        
        $this->addColumn('order_hold_code', array(
            'header'        => Mage::helper('sales')->__('Hold Code'),
            'index'         => 'order_hold_code',
            'type'          => 'text',
            'width'         => 100,
            'sortable'      => true,
        ));
        
        $this->addColumn('net_order_amt', array(
            'header'        => Mage::helper('sales')->__('Net Order Amt'),
            'index'         => 'net_order_amt',
            'type'          => 'currency',
            'width'         => 100,
            'sortable'      => true,
            'align'         => 'right',
            'renderer'      => 'heldorders/renderer_netorderamt',
        ));
        
        $this->addColumn('firstname', array(
            'header'        => Mage::helper('sales')->__('First Name'),
            'index'         => 'firstname',
            'type'          => 'text',
            'width'         => 100,
            'sortable'      => true,
        ));
        
        $this->addColumn('lastname', array(
            'header'        => Mage::helper('sales')->__('Last Name'),
            'index'         => 'lastname',
            'type'          => 'text',
            'width'         => 100,
            'sortable'      => true,            
        ));
        
        $this->addColumn('telephone', array(
            'header'        => Mage::helper('sales')->__('Telephone'),
            'index'         => 'telephone',
            'type'          => 'text',
            'width'         => 100,
            'sortable'      => true,
        ));
        
        
        $this->addExportType('*/*/exportCsv', Mage::helper('adminhtml')->__('CSV'));
        $this->addExportType('*/*/exportXml', Mage::helper('adminhtml')->__('XML'));

        return parent::_prepareColumns();
    }
    

      public function getRowUrl($row)
      {
          return false;
      }
}