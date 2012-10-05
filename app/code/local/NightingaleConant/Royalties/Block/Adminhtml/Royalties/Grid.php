<?php

class NightingaleConant_Royalties_Block_Adminhtml_Royalties_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
  public function __construct()
  {
      parent::__construct();
      $this->setId('royaltiesGrid');
      $this->setDefaultSort('royalty_rate_id');
      $this->setDefaultDir('ASC');
      $this->setSaveParametersInSession(true);
  }

  protected function _prepareCollection()
  {
      $collection = Mage::getModel('royalties/rate')->getCollection();
      
      /* Tried without primary key...
      $variendata = new Varien_Object;
      $collection = new Varien_Data_Collection;
      $adapter = Mage::getSingleton('core/resource')->getConnection('core_write');
      $select = $adapter->select();
      $select->from('custom_royalty_rates');
      
      $data = $adapter->fetchAll($select);
      $id = 0;
      foreach($data as $k => $v) {
          //$data[$k]['royalties_id'] = $id;          
          $id++;
          Mage::log($data[$k]);
          if($id == 5) break;
          $d = $variendata->addData($data[$k]);
          $collection->addItem($d);
      }*/
      
      $this->setCollection($collection);      
      return parent::_prepareCollection();
  }

  protected function _prepareColumns()
  {
      /*$this->addColumn('royalty_rate_id', array(
          'header'    => Mage::helper('royalties')->__('Royalty Rate ID'),
          'align'     =>'right',
          'width'     => '50px',
          'index'     => 'royalty_rate_id',
      ));*/
      
      $this->addColumn('STEP', array(
          'header'    => Mage::helper('royalties')->__('STEP'),
          'align'     =>'left',
          'width'     => '50px',
          'index'     => 'STEP',
      ));
      
      $this->addColumn('AUTH', array(
          'header'    => Mage::helper('royalties')->__('AUTH'),
          'align'     =>'left',
          'width'     => '50px',
          'index'     => 'AUTH',
      ));
      
      $this->addColumn('AUTH1', array(
          'header'    => Mage::helper('royalties')->__('AUTH1'),
          'align'     =>'left',
          'width'     => '50px',
          'index'     => 'AUTH1',
      ));
      
      $this->addColumn('ITEM', array(
          'header'    => Mage::helper('royalties')->__('ITEM'),
          'align'     =>'left',
          'width'     => '50px',
          'index'     => 'ITEM',
      ));
      
      $this->addColumn('PRICE', array(
          'header'    => Mage::helper('royalties')->__('PRICE'),
          'align'     =>'right',
          'width'     => '50px',
          'index'     => 'PRICE',
      ));
      
      $this->addColumn('INTALP', array(
          'header'    => Mage::helper('royalties')->__('INTALP'),
          'align'     =>'right',
          'width'     => '50px',
          'index'     => 'INTALP',
      ));
      
      $this->addColumn('VENDNUM', array(
          'header'    => Mage::helper('royalties')->__('VENDNUM'),
          'align'     =>'right',
          'width'     => '50px',
          'index'     => 'VENDNUM',
      ));
      
        $this->addColumn('action',
            array(
                'header'    =>  Mage::helper('royalties')->__('Action'),
                'width'     => '100',
                'type'      => 'action',
                'getter'    => 'getId',
                'actions'   => array(
                    array(
                        'caption'   => Mage::helper('royalties')->__('Edit'),
                        'url'       => array('base'=> '*/*/edit'),
                        'field'     => 'id'
                    )
                ),
                'filter'    => false,
                'sortable'  => false,
                'index'     => 'stores',
                'is_system' => true,
        ));
		
		$this->addExportType('*/*/exportCsv', Mage::helper('royalties')->__('CSV'));
		$this->addExportType('*/*/exportXml', Mage::helper('royalties')->__('XML'));
	  
      return parent::_prepareColumns();
  }

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('royalty_rate_id');
        $this->getMassactionBlock()->setFormFieldName('royalties');

        $this->getMassactionBlock()->addItem('delete', array(
             'label'    => Mage::helper('royalties')->__('Delete'),
             'url'      => $this->getUrl('*/*/massDelete'),
             'confirm'  => Mage::helper('royalties')->__('Are you sure?')
        ));

        return $this;
    }

  public function getRowUrl($row)
  {
      return $this->getUrl('*/*/edit', array('id' => $row->getRoyaltyRateId()));
  }

}
