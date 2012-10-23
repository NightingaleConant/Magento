<?php

class NightingaleConant_SalesTaxReport_Block_Adminhtml_Salestaxreport_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();

          $this->setId('salestaxreportGrid');
          $this->setDefaultSort('main_table.created_at');
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
        
        $coll = Mage::getModel('sales/order_shipment')->getCollection()
            ->addFieldToSelect('entity_id','shipment_id')
            ->addFieldToSelect('order_id')
            ->addFieldToSelect('created_at','ship_date');
            
        
        $coll->getSelect()->joinLeft('sales_flat_order as so', 'main_table.order_id = so.entity_id', 
                    array('increment_id as order_increment_id', 'grand_total', 'tax_amount', 'order_currency_code', 'store_name', 'customer_id', 'store_id'));
        $coll->getSelect()->joinLeft('sales_flat_order_address as soa', 'so.billing_address_id = soa.entity_id', 
                    array('region_id', 'country_id'));
        $coll->getSelect()->joinLeft('sales_flat_invoice as si', 'main_table.order_id = si.order_id', array('entity_id as invoice_id'));
        $coll->getSelect()->where('so.tax_amount is NOT NULL AND so.tax_amount > 0 AND region_id > 0');
        $coll->addFieldToFilter('main_table.created_at', array('from'=>$from, 'to'=>$to));
        
        if($storeIds)
            $coll->addFieldToFilter('so.store_id', array('in'=>$storeIds));
        
        //$coll->getSelect()->group('main_table.created_at');
        $coll->getSelect()->order('main_table.created_at desc');
        $coll->getSelect()->order('country_id desc');
        $coll->getSelect()->order('region_id asc');
        
        
        $this->setCollection($coll);
        //Mage::log($coll->getSelect()->__toString());
        return parent::_prepareCollection();
        
    }

    protected function _prepareColumns()
    {
        
        $this->addColumn('store_id', array(
            'header'        => Mage::helper('sales')->__('Company'),
            'index'         => 'store_id',
            'type'          => 'text',
            'width'         => 100,
            'sortable'      => false,
        ));
        
        $this->addColumn('order_currency_code', array(
            'header'        => Mage::helper('sales')->__('Currency Code'),
            'index'         => 'order_currency_code',
            'type'          => 'text',
            'width'         => 100,
            'sortable'      => false,
        ));
        
        $this->addColumn('region_id', array(
            'header'        => Mage::helper('sales')->__('Tax Code'),
            'index'         => 'region_id',
            'type'          => 'text',
            'width'         => 100,
            'sortable'      => false,
            'align'         => 'right',
            'renderer'      => 'salestaxreport/renderer_taxcode',
        ));
        
        $this->addColumn('customer_id', array(
            'header'        => Mage::helper('sales')->__('Entity Number'),
            'index'         => 'customer_id',
            'type'          => 'text',
            'width'         => 100,
            'sortable'      => false,
        ));
        
        $this->addColumn('invoice_id', array(
            'header'        => Mage::helper('sales')->__('Invoice #'),
            'index'         => 'invoice_id',
            'type'          => 'text',
            'width'         => 100,
            'sortable'      => false,
        ));
        
        $this->addColumn('order_id', array(
            'header'        => Mage::helper('sales')->__('Order #'),
            'index'         => 'order_id',
            'type'          => 'text',
            'width'         => 100,
            'sortable'      => false,
        ));
        
        $this->addColumn('grand_total', array(
            'header'        => Mage::helper('sales')->__('Order Total'),
            'index'         => 'grand_total',
            'type'          => 'currency',
            'width'         => 100,
            'sortable'      => false,
        ));
        
        $this->addColumn('tax_amount', array(
            'header'        => Mage::helper('sales')->__('Tax Amount'),
            'index'         => 'tax_amount',
            'type'          => 'currency',
            'width'         => 100,
            'sortable'      => false,            
        ));
        
        $this->addColumn('ship_date', array(
            'header'        => Mage::helper('sales')->__('Ship Date'),
            'index'         => 'ship_date',
            'type'          => 'date',
            'width'         => 100,
            'sortable'      => false,
        ));
        
        $this->addExportType('*/*/exportPdf', Mage::helper('adminhtml')->__('PDF'));
        $this->addExportType('*/*/exportCsv', Mage::helper('adminhtml')->__('CSV'));
        $this->addExportType('*/*/exportXml', Mage::helper('adminhtml')->__('XML'));

        return parent::_prepareColumns();
    }
    

      public function getRowUrl($row)
      {
          return false;
      }
      
      public function getPdfFile(){
        $this->_isExport = true;
        $this->_prepareGrid();
        $this->getCollection()->getSelect()->limit();
        $this->getCollection()->setPageSize(0);
        $this->getCollection()->load();
        $this->_afterLoadCollection();

        $pdf = new Zend_Pdf();
        $page = new Zend_Pdf_Page(Zend_Pdf_Page::SIZE_A4_LANDSCAPE);
        $font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_TIMES);
        $page->setFont($font, 12);
        $width = $page->getWidth();
        $i=20; $col = array(); $header_arr = array();
        foreach ($this->_columns as $column) {            
            
            if($column->getData('renderer') && $column->getData('renderer') != "") {
                $renderer = $column->getData('renderer');
            } else $renderer = false;
            
            $col[$column->getIndex()] = array('width' => round($column->getWidth() / 1.2) , 'render' => $renderer);
            if (!$column->getIsSystem()) {
                
                $header = $column->getExportHeader();
                $header_arr[] = array('header'=>$header,'width'=>"$i");
                //$page->drawText($header, $i, $page->getHeight()-20);
                
                $i+= round($column->getWidth() / 1.2);
            }
        }
        
        //Mage::log($col);
        $height = $page->getHeight() - 30; $j=0;$totl = 0;
        foreach($this->getCollection() as $coll) {
            $i=20;
            $data = $coll->getData();            
            $sorted = $this->sortArrayByArray($data, $col);
            
            if($totl % 20 == 0) {                
                $page = $pdf->newPage(Zend_Pdf_Page::SIZE_A4_LANDSCAPE);
                $font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_TIMES);
                $page->setFont($font, 12);
                $totl = 0; $height = $page->getHeight() - 30;
                $width = $page->getWidth();
                $pdf->pages[] = $page;
                
                foreach($header_arr as $header) {                
                    $page->drawText($header['header'], $header['width'], $height);                
                }
            }
            
            $height -= 20; $totl++; 
            foreach($sorted as $key=>$value) {                
                if($col[$key]['render'] != "") {
                    $value = $this->getLayout()->createBlock($col[$key]['render'])->render($coll);                    
                }
                $page->drawText($value, $i, $height);
                $i += $col[$key]['width'];
            }
            
            $j++;
            //if($j == 50) break;
        }
        
        
        //$pdf->pages[] = $page;
        return $pdf->render();
    }
    
    private function sortArrayByArray($array,$orderArray) {
        $ordered = array();
        foreach($orderArray as $key=>$value) {
            if(array_key_exists($key,$array)) {
                    //array_push($ordered, array($key => $array[$key]));
                    $ordered[$key] = $array[$key];
                    unset($array[$key]);
            }
        }
        //Mage::log($ordered);
        return $ordered;// + $array;
    }


}