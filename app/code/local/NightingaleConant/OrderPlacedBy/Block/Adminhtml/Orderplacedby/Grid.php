<?php

class NightingaleConant_OrderPlacedBy_Block_Adminhtml_Orderplacedby_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    //protected $_columnGroupBy = 'period';

    public function __construct()
    {
        parent::__construct();

          $this->setId('orderplacedbyGrid');
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
            $orderplacedby = ltrim($data[2], 'orderplacedby=');
        }
        
        if (($from == null || $to == null)) {
            $this->setCountTotals(false);
            $this->setCountSubTotals(false);
            return parent::_prepareCollection();
        }
        
        $coll = Mage::getModel('sales/order')->getCollection()
                ->addFieldToSelect('entity_id','order_id')
                ->addFieldToSelect('increment_id')
                ->addFieldToSelect('store_name')
                ->addFieldToSelect('created_at')
                ->addFieldToSelect('subtotal')
                ->addFieldToSelect('grand_total')
                ->addFieldToSelect('status')
                ->addFieldToFilter('created_at', array('from'=>$from, 'to'=>$to));
        
        $coll->getSelect()->joinLeft('sales_flat_order_address as oab',
                'main_table.billing_address_id = oab.entity_id',
                array('firstname as bill_firstname','lastname as bill_lastname'));
        
        $coll->getSelect()->joinLeft('sales_flat_order_address as oas',
                'main_table.shipping_address_id = oas.entity_id',
                array('firstname as ship_firstname','lastname as ship_lastname'));
        
        if($storeIds)
            $coll->addFieldToFilter('store_id', array('in'=>$storeIds));
        
        $this->setCollection($coll);
        //Mage::log($coll->getSelect()->__toString());
        return parent::_prepareCollection();
        
    }

    protected function _prepareColumns()
    {
        
        $this->addColumn('increment_id', array(
            'header'        => Mage::helper('sales')->__('Order #'),
            'index'         => 'increment_id',
            'type'          => 'text',
            'width'         => 100,
            'sortable'      => false,
        ));
        
        $this->addColumn('store_name', array(
            'header'        => Mage::helper('sales')->__('Purchased From'),
            'index'         => 'store_name',
            'type'          => 'text',
            'width'         => 100,
            'sortable'      => false,
        ));
        
        $this->addColumn('created_at', array(
            'header'        => Mage::helper('sales')->__('Purchased On'),
            'index'         => 'created_at',
            'type'          => 'date',
            'width'         => 100,
            'sortable'      => false,
        ));
        
        $this->addColumn('bill_to_name', array(
            'header'        => Mage::helper('sales')->__('Bill To Name'),
            'index'         => 'bill_to_name',
            'type'          => 'text',
            'width'         => 100,
            'sortable'      => false,
            'renderer'      => 'orderplacedby/renderer_billtoname',
        ));
        
        $this->addColumn('ship_to_name', array(
            'header'        => Mage::helper('sales')->__('Ship To Name'),
            'index'         => 'ship_to_name',
            'type'          => 'text',
            'width'         => 100,
            'sortable'      => false,
            'renderer'      => 'orderplacedby/renderer_shiptoname',
        ));
        
        $this->addColumn('subtotal', array(
            'header'        => Mage::helper('sales')->__('Subtotal'),
            'index'         => 'subtotal',
            'type'          => 'currency',
            'width'         => 100,
            'sortable'      => false,
            'align'         => 'right',
        ));
        
        $this->addColumn('grand_total', array(
            'header'        => Mage::helper('sales')->__('Grand Total'),
            'index'         => 'grand_total',
            'type'          => 'currency',
            'width'         => 100,
            'sortable'      => false,
            'align'         => 'right',
        ));
                
        $this->addColumn('status', array(
            'header'        => Mage::helper('sales')->__('Status'),
            'index'         => 'status',
            'type'          => 'options',
            'width'         => 100,
            'sortable'      => false,
            'options'       => Mage::getSingleton('sales/order_config')->getStatuses(),
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