<?php

class NightingaleConant_SpecialItem_Block_Adminhtml_Specialitem_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
  public function __construct()
  {
      parent::__construct();
      $this->setId('specialitemGrid');
      $this->setDefaultSort('specialitem_id');
      $this->setDefaultDir('ASC');
      $this->setSaveParametersInSession(true);
  }

  protected function _prepareCollection()
  {
      $collection = Mage::getModel('specialitem/specialitem')->getCollection();
      $this->setCollection($collection);
      return parent::_prepareCollection();
  }

  protected function _prepareColumns()
  { 
      $this->addColumn('product_sku', array(
          'header'    => Mage::helper('specialitem')->__('Product SKU'),
          'align'     =>'right',
          'width'     => '50px',
          'index'     => 'product_sku',
      ));

      $this->addColumn('item_sku', array(
          'header'    => Mage::helper('specialitem')->__('Order Item SKU'),
          'align'     =>'left',
            'width'     => '250px',
          'index'     => 'item_sku',
      ));
      
      $this->addColumn('promo_code', array(
          'header'    => Mage::helper('specialitem')->__('Promo Code'),
          'align'     =>'left',
            'width'     => '250px',
          'index'     => 'promo_code',
      ));
      
      $this->addColumn('order_type', array(
          'header'    => Mage::helper('specialitem')->__('Order Type'),
          'align'     =>'left',
            'width'     => '250px',
          'index'     => 'order_type',
      ));
      
      
      $this->addColumn('customer_group', array(
          'header'    => Mage::helper('specialitem')->__('Customer Group'),
          'align'     =>'left',
            'width'     => '250px',
          'index'     => 'customer_group',
      ));
      

      $this->addColumn('active', array(
          'header'    => Mage::helper('specialitem')->__('Active'),
          'align'     => 'right',
          'width'     => '100px',
          'index'     => 'active',
          'type'      => 'options',
          'options'   => array(
              1 => 'Yes',
              0 => 'No',
          ),
      ));
      
        
	$this->addColumn('created_at', array(
          'header'    => Mage::helper('specialitem')->__('Created Date'),
          'align'     =>'right',
          'width'     => '150px',
          'index'     => 'created_at',
      ));
        
        $this->addColumn('updated_at', array(
          'header'    => Mage::helper('specialitem')->__('Last Modified Date'),
          'align'     =>'right',
          'width'     => '150px',
          'index'     => 'updated_at',
      ));
        

        $this->addColumn('action',
            array(
                'header'    =>  Mage::helper('specialitem')->__('Action'),
                'width'     => '100',
                'type'      => 'action',
                'getter'    => 'getId',
                'actions'   => array(
                    array(
                        'caption'   => Mage::helper('specialitem')->__('Edit'),
                        'url'       => array('base'=> '*/*/edit'),
                        'field'     => 'id'
                    )
                ),
                'filter'    => false,
                'sortable'  => false,
                'index'     => 'stores',
                'is_system' => true,
        ));
		
		$this->addExportType('*/*/exportCsv', Mage::helper('specialitem')->__('CSV'));
		$this->addExportType('*/*/exportXml', Mage::helper('specialitem')->__('XML'));
	  
      return parent::_prepareColumns();
  }

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('specialitem_id');
        $this->getMassactionBlock()->setFormFieldName('specialitem');

        $this->getMassactionBlock()->addItem('delete', array(
             'label'    => Mage::helper('specialitem')->__('Delete'),
             'url'      => $this->getUrl('*/*/massDelete'),
             'confirm'  => Mage::helper('specialitem')->__('Are you sure?')
        ));

        return $this;
    }

  public function getRowUrl($row)
  {
      return $this->getUrl('*/*/edit', array('id' => $row->getId()));
  }

}