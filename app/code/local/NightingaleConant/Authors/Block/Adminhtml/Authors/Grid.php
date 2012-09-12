<?php

class NightingaleConant_Authors_Block_Adminhtml_Authors_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
  public function __construct()
  {
      parent::__construct();
      $this->setId('authorsGrid');
      $this->setDefaultSort('author_id');
      $this->setDefaultDir('ASC');
      $this->setSaveParametersInSession(true);
  }

  protected function _prepareCollection()
  {
      $collection = Mage::getModel('authors/authors')->getCollection();
      $this->setCollection($collection);
      return parent::_prepareCollection();
  }

  protected function _prepareColumns()
  { 
      $this->addColumn('author_id', array(
          'header'    => Mage::helper('authors')->__('Author ID'),
          'align'     =>'right',
          'width'     => '50px',
          'index'     => 'author_id',
      ));

      $this->addColumn('first_name', array(
          'header'    => Mage::helper('authors')->__('First Name'),
          'align'     =>'left',
            'width'     => '250px',
          'index'     => 'first_name',
      ));
      
      $this->addColumn('last_name', array(
          'header'    => Mage::helper('authors')->__('Last Name'),
          'align'     =>'left',
            'width'     => '250px',
          'index'     => 'last_name',
      ));
      
      $this->addColumn('display_name', array(
          'header'    => Mage::helper('authors')->__('Display Name'),
          'align'     =>'left',
            'width'     => '250px',
          'index'     => 'display_name',
      ));
      
/*  //Uncomment to display these in Grid    
      $this->addColumn('bio', array(
          'header'    => Mage::helper('authors')->__('Bio'),
          'align'     =>'left',
            'width'     => '250px',
          'index'     => 'bio',
      ));*/
      
      $this->addColumn('image', array(
          'header'    => Mage::helper('authors')->__('Image'),
          'align'     =>'left',
            'width'     => '100px',
          'index'     => 'image',
          'renderer' => 'authors/renderer_image'
      ));
      
/*  //Uncomment to display these in Grid    
 
      $this->addColumn('meta_title', array(
          'header'    => Mage::helper('authors')->__('Meta Title'),
          'align'     =>'left',
            'width'     => '250px',
          'index'     => 'meta_title',
      ));
      
      $this->addColumn('meta_description', array(
          'header'    => Mage::helper('authors')->__('Meta Description'),
          'align'     =>'left',
            'width'     => '250px',
          'index'     => 'meta_description',
      ));
      
      $this->addColumn('meta_keywords', array(
          'header'    => Mage::helper('authors')->__('Meta Keywords'),
          'align'     =>'left',
            'width'     => '250px',
          'index'     => 'meta_keywords',
      ));
  
      $this->addColumn('author_link_text', array(
          'header'    => Mage::helper('authors')->__('Author Link Text'),
          'align'     =>'left',
            'width'     => '250px',
          'index'     => 'author_link_text',
      ));
      
      $this->addColumn('author_page_title', array(
          'header'    => Mage::helper('authors')->__('Author Page Title'),
          'align'     =>'left',
            'width'     => '250px',
          'index'     => 'author_page_title',
      ));
      
      $this->addColumn('bio_link_text', array(
          'header'    => Mage::helper('authors')->__('Bio Link Text'),
          'align'     =>'left',
            'width'     => '250px',
          'index'     => 'bio_link_text',
      ));
      
      $this->addColumn('bio_page_title', array(
          'header'    => Mage::helper('authors')->__('Bio Page Title'),
          'align'     =>'left',
            'width'     => '250px',
          'index'     => 'bio_page_title',
      ));*/


      $this->addColumn('active', array(
          'header'    => Mage::helper('authors')->__('Active'),
          'align'     => 'right',
          'width'     => '100px',
          'index'     => 'active',
          'type'      => 'options',
          'options'   => array(
              1 => 'Yes',
              0 => 'No',
          ),
      ));
      
      
        
	$this->addColumn('created_by', array(
          'header'    => Mage::helper('authors')->__('Created By'),
          'align'     =>'right',
          'width'     => '150px',
          'index'     => 'created_by',
            'renderer' => 'authors/renderer_created'
      ));

        

        
	$this->addColumn('updated_by', array(
          'header'    => Mage::helper('authors')->__('Last Modified By'),
          'align'     =>'right',
          'width'     => '150px',
          'index'     => 'updated_by',
            'renderer' => 'authors/renderer_updated'
      ));

        
	
        
	$this->addColumn('created_at', array(
          'header'    => Mage::helper('authors')->__('Created Date'),
          'align'     =>'right',
          'width'     => '150px',
          'index'     => 'created_at',
      ));
        
        $this->addColumn('updated_at', array(
          'header'    => Mage::helper('authors')->__('Last Modified Date'),
          'align'     =>'right',
          'width'     => '150px',
          'index'     => 'updated_at',
      ));
        

        $this->addColumn('action',
            array(
                'header'    =>  Mage::helper('authors')->__('Action'),
                'width'     => '100',
                'type'      => 'action',
                'getter'    => 'getId',
                'actions'   => array(
                    array(
                        'caption'   => Mage::helper('authors')->__('Edit'),
                        'url'       => array('base'=> '*/*/edit'),
                        'field'     => 'id'
                    )
                ),
                'filter'    => false,
                'sortable'  => false,
                'index'     => 'stores',
                'is_system' => true,
        ));
		
		$this->addExportType('*/*/exportCsv', Mage::helper('authors')->__('CSV'));
		$this->addExportType('*/*/exportXml', Mage::helper('authors')->__('XML'));
	  
      return parent::_prepareColumns();
  }

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('author_id');
        $this->getMassactionBlock()->setFormFieldName('authors');

        $this->getMassactionBlock()->addItem('delete', array(
             'label'    => Mage::helper('authors')->__('Delete'),
             'url'      => $this->getUrl('*/*/massDelete'),
             'confirm'  => Mage::helper('authors')->__('Are you sure?')
        ));

        return $this;
    }

  public function getRowUrl($row)
  {
      return $this->getUrl('*/*/edit', array('id' => $row->getId()));
  }

}
