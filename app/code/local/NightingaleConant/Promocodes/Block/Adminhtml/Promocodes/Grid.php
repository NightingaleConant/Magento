<?php

class NightingaleConant_Promocodes_Block_Adminhtml_Promocodes_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
  public function __construct()
  {
      parent::__construct();
      $this->setId('promocodesGrid');
      $this->setDefaultSort('ckc_entity_id');
      $this->setDefaultDir('ASC');
      $this->setSaveParametersInSession(true);
  }

  protected function _prepareCollection()
  {
      $collection = Mage::getModel('promocodes/promocodes')->getCollection();
      $this->setCollection($collection);
      return parent::_prepareCollection();
  }

  protected function _prepareColumns()
  { 
      $this->addColumn('ckc_entity_id', array(
          'header'    => Mage::helper('promocodes')->__('Entity ID'),
          'align'     =>'right',
          'width'     => '50px',
          'index'     => 'ckc_entity_id',
      ));

      /*$this->addColumn('ckc_store_id', array(
          'header'    => Mage::helper('promocodes')->__('Store ID'),
          'align'     =>'left',
            'width'     => '250px',
          'index'     => 'ckc_store_id',
      ));*/
      
      $this->addColumn('ckc_company_code', array(
          'header'    => Mage::helper('promocodes')->__('Company Code'),
          'align'     =>'left',
            'width'     => '250px',
          'index'     => 'ckc_company_code',
      ));
      
      $this->addColumn('ckc_key_code', array(
          'header'    => Mage::helper('promocodes')->__('Key Code'),
          'align'     =>'left',
            'width'     => '250px',
          'index'     => 'ckc_key_code',
      ));
      
      
      $this->addColumn('ckc_offer_code', array(
          'header'    => Mage::helper('promocodes')->__('Offer Code'),
          'align'     =>'left',
            'width'     => '250px',
          'index'     => 'ckc_offer_code',
      ));
      
      $this->addColumn('ckc_key_code_name', array(
          'header'    => Mage::helper('promocodes')->__('Key Code Name'),
          'align'     =>'left',
            'width'     => '250px',
          'index'     => 'ckc_key_code_name',
      ));
        
	$this->addColumn('ckc_begin_date', array(
          'header'    => Mage::helper('promocodes')->__('Begin Date'),
          'align'     =>'right',
          'width'     => '200px',
          'index'     => 'ckc_begin_date',
            'renderer' => 'promocodes/renderer_begin'
      ));

        
	$this->addColumn('ckc_end_date', array(
          'header'    => Mage::helper('promocodes')->__('End Date'),
          'align'     =>'right',
          'width'     => '200px',
          'index'     => 'end_date',
            'renderer' => 'promocodes/renderer_end'
      ));

              $this->addColumn('ckc_order_type', array(
          'header'    => Mage::helper('promocodes')->__('Order Type'),
          'align'     =>'left',
            'width'     => '250px',
          'index'     => 'ckc_order_type',
      ));
      
      
      $this->addColumn('ckc_order_type_code', array(
          'header'    => Mage::helper('promocodes')->__('Order Type Code'),
          'align'     =>'left',
            'width'     => '250px',
          'index'     => 'ckc_order_type_code',
      ));
      
      $this->addColumn('price_code', array(
          'header'    => Mage::helper('promocodes')->__('Price Code'),
          'align'     =>'left',
            'width'     => '250px',
          'index'     => 'price_code',
      ));


        $this->addColumn('action',
            array(
                'header'    =>  Mage::helper('promocodes')->__('Action'),
                'width'     => '100',
                'type'      => 'action',
                'getter'    => 'getId',
                'actions'   => array(
                    array(
                        'caption'   => Mage::helper('promocodes')->__('Edit'),
                        'url'       => array('base'=> '*/*/edit'),
                        'field'     => 'id'
                    )
                ),
                'filter'    => false,
                'sortable'  => false,
                'index'     => 'stores',
                'is_system' => true,
        ));
		
		$this->addExportType('*/*/exportCsv', Mage::helper('promocodes')->__('CSV'));
		$this->addExportType('*/*/exportXml', Mage::helper('promocodes')->__('XML'));
	  
      return parent::_prepareColumns();
  }

  public function getRowUrl($row)
  {
      return $this->getUrl('*/*/edit', array('id' => $row->getId()));
  }

}
