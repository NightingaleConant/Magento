<?php
/**
 * aheadWorks Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://ecommerce.aheadworks.com/AW-LICENSE-COMMUNITY.txt
 * 
 * =================================================================
 *                 MAGENTO EDITION USAGE NOTICE
 * =================================================================
 * This package designed for Magento COMMUNITY edition
 * aheadWorks does not guarantee correct work of this extension
 * on any other Magento edition except Magento COMMUNITY edition.
 * aheadWorks does not provide extension support in case of
 * incorrect edition usage.
 * =================================================================
 *
 * @category   AW
 * @package    AW_Sarp
 * @copyright  Copyright (c) 2010-2011 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE-COMMUNITY.txt
 */
class AW_Sarp_Block_Adminhtml_Periods_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('subscriptionPeriodsGrid');
        $this->setDefaultSort('id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);

    }

    protected function _prepareCollection()
    {
        $collection = Mage::getModel('sarp/period')->getCollection();
        $collection->getSelect()->reset(Zend_Db_Select::ORDER);

        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('id', array(
                                    'header' => Mage::helper('sarp')->__('ID'),
                                    'align' => 'right',
                                    'width' => '5',
                                    'index' => 'id',
                               ));

        $this->addColumn('name', array(
                                      'header' => Mage::helper('sarp')->__('Title'),
                                      'align' => 'left',
                                      'index' => 'name',
                                 ));

        $this->addColumn('sort_order', array(
                                            'header' => Mage::helper('sarp')->__('Sort Order'),
                                            'align' => 'right',
                                            'width' => '5',
                                            'index' => 'sort_order',
                                       ));


        $this->addColumn('action',
                         array(
                              'header' => $this->__('Action'),
                              'width' => '100',
                              'type' => 'action',
                              'getter' => 'getId',
                              'actions' => array(

                                  array(
                                      'caption' => $this->__('Edit'),
                                      'url' => array('base' => '*/*/edit'),
                                      'field' => 'id'
                                  )
                              ),
                              'filter' => false,
                              'sortable' => false,
                              'index' => 'stores',
                              'is_system' => true,
                         ));


        //$this->addExportType('*/*/exportCsv', Mage::helper('helpdesk')->__('CSV'));
        //$this->addExportType('*/*/exportXml', Mage::helper('helpdesk')->__('XML'));

        $ret = parent::_prepareColumns();


        return $ret;
    }

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('subscriptions');
        $this->getMassactionBlock()->setFormFieldName('subscriptions');

        //$this->getMassactionBlock()->addItem('delete', array(
        //	 'label'    => Mage::helper('helpdeskultimate')->__('Delete'),
        //	 'url'      => $this->getUrl('*/*/massDelete'),
        //	 'confirm'  => Mage::helper('helpdeskultimate')->__('Are you sure?')
        //));

        return $this;
    }

    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit/', array('id' => $row->getId()));
    }

}
