<?php
/**
* @author Amasty Team
* @copyright Amasty
* @package Amasty_Orderattr
*/
class Amasty_Orderattr_Block_Adminhtml_Order_Attribute_Grid extends Mage_Adminhtml_Block_Widget_Grid
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
        $collection = Mage::getResourceModel('eav/entity_attribute_collection')
                        ->setEntityTypeFilter( Mage::getModel('eav/entity')->setType('order')->getTypeId() );
        $collection->getSelect()
            ->where('main_table.is_user_defined = ?', 1);
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
            'header'=>Mage::helper('amorderattr')->__('Attribute Code'),
            'sortable'=>true,
            'index'=>'attribute_code'
        ));

        $this->addColumn('frontend_label', array(
            'header'=>Mage::helper('amorderattr')->__('Attribute Label'),
            'sortable'=>true,
            'index'=>'frontend_label'
        ));

        $this->addColumn('is_visible', array(
            'header'=>Mage::helper('amorderattr')->__('Visible'),
            'sortable'=>true,
            'index'=>'is_visible_on_front',
            'type' => 'options',
            'options' => array(
                '1' => Mage::helper('catalog')->__('Yes'),
                '0' => Mage::helper('catalog')->__('No'),
            ),
            'align' => 'center',
            'width' => '120px',
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
            'header'=>Mage::helper('amorderattr')->__('Required'),
            'sortable'=>true,
            'index'=>'is_required',
            'type' => 'options',
            'options' => array(
                '1' => Mage::helper('amorderattr')->__('Yes'),
                '0' => Mage::helper('amorderattr')->__('No'),
            ),
            'align' => 'center',
            'width' => '120px',
        ));
        
        $this->addColumn('checkout_step', array(
            'header'=>Mage::helper('catalog')->__('Checkout Step'),
            'sortable'=>true,
            'index'=>'checkout_step',
            'type' => 'options',
            'options' => array(
                '2' => Mage::helper('amorderattr')->__('2. Billing Information'),
                '3' => Mage::helper('amorderattr')->__('3. Shipping Information'),
                '4' => Mage::helper('amorderattr')->__('4. Shipping Method'),
                '5' => Mage::helper('amorderattr')->__('5. Payment Information'),
            ),
            'align' => 'center',
        ));
        
        $this->addColumn('include_pdf', array(
            'header'=>Mage::helper('amorderattr')->__('Include Into PDFs'),
            'sortable'=>true,
            'index'=>'include_pdf',
            'type' => 'options',
            'options' => array(
                '1' => Mage::helper('amorderattr')->__('Yes'),
                '0' => Mage::helper('amorderattr')->__('No'),
            ),
            'width' => '120px',
        ));
        
        $this->addColumn('apply_default', array(
            'header'=>Mage::helper('amorderattr')->__('Apply Default'),
            'sortable'=>true,
            'index'=>'apply_default',
            'type' => 'options',
            'options' => array(
                '1' => Mage::helper('amorderattr')->__('Yes'),
                '0' => Mage::helper('amorderattr')->__('No'),
            ),
            'width' => '120px',
        ));
        
        $this->addColumn('show_on_grid', array(
            'header'=>Mage::helper('amorderattr')->__('Show On Grid'),
            'sortable'=>true,
            'index'=>'show_on_grid',
            'type' => 'options',
            'options' => array(
                '1' => Mage::helper('amorderattr')->__('Yes'),
                '0' => Mage::helper('amorderattr')->__('No'),
            ),
            'width' => '100px',
        ));
        
        $this->addColumn('sorting_order', array(
            'header'=>Mage::helper('amorderattr')->__('Position'),
            'sortable'=>true,
            'index'=>'sorting_order',
            'width' => '100px',
        ));
        
        /*$this->addColumn('is_filterable_in_search', array(
            'header'=>Mage::helper('amorderattr')->__('Show on Manage Order Grid'),
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
            'header'=>Mage::helper('amorderattr')->__('Show on Billing During Checkout'),
            'sortable'=>true,
            'index'=>'used_in_product_listing',
            'type' => 'options',
            'width' => '90px',
            'options' => array(
                '1' => Mage::helper('catalog')->__('Yes'),
                '0' => Mage::helper('catalog')->__('No'),
            ),
            'align' => 'center',
        ));*/

        return parent::_prepareColumns();
    }

    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit', array('attribute_id' => $row->getAttributeId()));
    }

}