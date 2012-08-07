<?php
/**
* @author Amasty Team
* @copyright Amasty
* @package Amasty_Customerattr
*/
class Amasty_Customerattr_Block_Adminhtml_Customer_Attribute_Grid extends Mage_Adminhtml_Block_Widget_Grid
{

    public function __construct()
    {
        parent::__construct();
        $this->setId('attributeGrid');
        $this->setDefaultSort('attribute_code');
        $this->setDefaultDir('ASC');
    }

    protected function _prepareCollection()
    {
//        $collection = Mage::getResourceModel('eav/entity_attribute_collection')
//            ->setEntityTypeFilter( Mage::getModel('eav/entity')->setType('customer')->getTypeId() );
        $collection = Mage::getModel('customer/attribute')->getCollection();
        $collection->getSelect()
            ->where('main_table.is_user_defined = ?', 1)
            ->where('main_table.attribute_code != ?', 'customer_activated');
        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        /*
        $this->addColumn('attribute_id', array(
            'header'=>Mage::helper('catalog')->__('ID'),
            'align'=>'right',
            'sortable'=>true,
            'width' => '50px',
            'index'=>'attribute_id'
        ));
        */

        $this->addColumn('attribute_code', array(
            'header'=>Mage::helper('catalog')->__('Attribute Code'),
            'sortable'=>true,
            'index'=>'attribute_code'
        ));

        $this->addColumn('frontend_label', array(
            'header'=>Mage::helper('catalog')->__('Attribute Label'),
            'sortable'=>true,
            'index'=>'frontend_label'
        ));

        $this->addColumn('is_visible', array(
            'header'=>Mage::helper('catalog')->__('Visible'),
            'sortable'=>true,
            'index'=>'is_visible_on_front',
            'type' => 'options',
            'options' => array(
                '1' => Mage::helper('catalog')->__('Yes'),
                '0' => Mage::helper('catalog')->__('No'),
            ),
            'align' => 'center',
        ));

        /*$this->addColumn('is_global', array(
            'header'=>Mage::helper('catalog')->__('Scope'),
            'sortable'=>true,
            'index'=>'is_global',
            'type' => 'options',
            'options' => array(
                Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE =>Mage::helper('catalog')->__('Store View'),
                Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_WEBSITE =>Mage::helper('catalog')->__('Website'),
                Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL =>Mage::helper('catalog')->__('Global'),
            ),
            'align' => 'center',
        ));*/

        $this->addColumn('is_required', array(
            'header'=>Mage::helper('catalog')->__('Required'),
            'sortable'=>true,
            'index'=>'is_required',
            'type' => 'options',
            'options' => array(
                '1' => Mage::helper('catalog')->__('Yes'),
                '0' => Mage::helper('catalog')->__('No'),
            ),
            'align' => 'center',
        ));
        
        $this->addColumn('sorting_order', array(
            'header'=>Mage::helper('amcustomerattr')->__('Sorting Order'),
            'sortable'=>true,
            'index'=>'sorting_order',
            'width' => '90px',
        ));
        
        $this->addColumn('is_filterable_in_search', array(
            'header'=>Mage::helper('amcustomerattr')->__('Show on Manage Customers Grid'),
            'sortable'=>true,
            'index'=>'is_filterable_in_search',
            'type' => 'options',
            'width' => '90px',
            'options' => array(
                '1' => Mage::helper('catalog')->__('Yes'),
                '0' => Mage::helper('catalog')->__('No'),
            ),
            'align' => 'center',
        ));
        
        $this->addColumn('used_in_product_listing', array(
            'header'=>Mage::helper('amcustomerattr')->__('Show on Billing During Checkout'),
            'sortable'=>true,
            'index'=>'used_in_product_listing',
            'type' => 'options',
            'width' => '90px',
            'options' => array(
                '1' => Mage::helper('catalog')->__('Yes'),
                '0' => Mage::helper('catalog')->__('No'),
            ),
            'align' => 'center',
        ));

        return parent::_prepareColumns();
    }

    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit', array('attribute_id' => $row->getAttributeId()));
    }

}