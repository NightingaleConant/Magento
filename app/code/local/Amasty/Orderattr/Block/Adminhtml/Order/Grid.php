<?php
/**
* @author Amasty Team
* @copyright Amasty
* @package Amasty_Orderattr
*/
class Amasty_Orderattr_Block_Adminhtml_Order_Grid extends Amasty_Orderattr_Block_Adminhtml_Order_Grid_Pure
{
    protected function _prepareCollection()
    {
        $isVersion14 = ! Mage::helper('ambase')->isVersionLessThan(1,4);
         
        $collection = array();
        /* @var $collection Mage_Sales_Model_Mysql4_Order_Grid_Collection */
        if ($isVersion14){
            $collection = Mage::getResourceModel($this->_getCollectionClass());
        }
        else {
            $collection = Mage::getResourceModel('sales/order_collection')
                ->addAttributeToSelect('*')
                ->joinAttribute('billing_firstname', 'order_address/firstname', 'billing_address_id', null, 'left')
                ->joinAttribute('billing_lastname', 'order_address/lastname', 'billing_address_id', null, 'left')
                ->joinAttribute('shipping_firstname', 'order_address/firstname', 'shipping_address_id', null, 'left')
                ->joinAttribute('shipping_lastname', 'order_address/lastname', 'shipping_address_id', null, 'left')
                ->addExpressionAttributeToSelect('billing_name',
                    'CONCAT({{billing_firstname}}, " ", {{billing_lastname}})',
                    array('billing_firstname', 'billing_lastname'))
                ->addExpressionAttributeToSelect('shipping_name',
                    'CONCAT({{shipping_firstname}}, " ", {{shipping_lastname}})',
                    array('shipping_firstname', 'shipping_lastname'));            
        }
        
        
        
        /**
        * Building the list of attribute field names for select query
        */
        $attributes = Mage::getModel('eav/entity_attribute')->getCollection();
        $attributes->addFieldToFilter('entity_type_id', Mage::getModel('eav/entity')->setType('order')->getTypeId());
        $attributes->addFieldToFilter('show_on_grid', 1);
        $attributes->load();
        if ($attributes->getSize()){
            $fields = array();
            foreach ($attributes as $attribute) {
                $fields[] = $attribute->getAttributeCode();
            }
            
            $alias = $isVersion14 ? 'main_table' : 'e';
            $collection->getSelect()
                       ->joinLeft(
                            array('custom_attributes' => Mage::getModel('amorderattr/attribute')->getResource()->getTable('amorderattr/order_attribute')),
                            "$alias.entity_id = custom_attributes.order_id",
                            $fields
                       );
        }
        
        $this->setCollection($collection);
        return Mage_Adminhtml_Block_Widget_Grid::_prepareCollection();
    }
    
    protected function _prepareColumns()
    {
        parent::_prepareColumns();
        
        $actionsColumn = null;
        if (isset($this->_columns['action']))
        {
            $actionsColumn = $this->_columns['action'];
            unset($this->_columns['action']);
        }
        
        /**
        * Loading attributes collection
        */
        $collection = Mage::getModel('eav/entity_attribute')->getCollection();
        $collection->addFieldToFilter('entity_type_id', Mage::getModel('eav/entity')->setType('order')->getTypeId());
        $collection->addFieldToFilter('show_on_grid', 1);
        $collection->getSelect()->order('sorting_order');
        $attributes = $collection->load();
        if ($attributes->getSize())
        {
            foreach ($attributes as $attribute)
            {
                if ($inputType = $attribute->getFrontend()->getInputType())
                {
                    switch ($inputType)
                    {
                        case 'date':
                        	if ('time' == $attribute->getNote())
                        	{
                        		$this->addColumn($attribute->getAttributeCode(), array(
	                                'header'    => __($attribute->getFrontend()->getLabel()),
	                                'type'      => 'datetime',
	                                'align'     => 'center',
	                                'index'     => $attribute->getAttributeCode(),
	                                'gmtoffset' => false,
                        			'renderer'	=> 'amorderattr/adminhtml_order_grid_renderer_datetime',
	                            ));
                        	} else 
                        	{
                        		$this->addColumn($attribute->getAttributeCode(), array(
	                                'header'    => __($attribute->getFrontend()->getLabel()),
	                                'type'      => 'date',
	                                'align'     => 'center',
	                                'index'     => $attribute->getAttributeCode(),
	                                'gmtoffset' => false,
	                            ));
                        	}
                            
                            break;
                        case 'text':
                        case 'textarea':
                            $this->addColumn($attribute->getAttributeCode(), array(
                                'header'    => __($attribute->getFrontend()->getLabel()),
                                'index'     => $attribute->getAttributeCode(),
                                'filter'    => 'adminhtml/widget_grid_column_filter_text',
                                'sortable'  => true,
                            ));
                            break;
                        case 'select':
                            $options = array();
                            foreach ($attribute->getSource()->getAllOptions(false, true) as $option)
                            {
                                $options[$option['value']] = $option['label'];
                            }
                            $this->addColumn($attribute->getAttributeCode(), array(
                                'header'    =>  __($attribute->getFrontend()->getLabel()),
                                'index'     =>  $attribute->getAttributeCode(),
                                'type'      =>  'options',
                                'options'   =>  $options,
                            ));
                            break;
                        case 'multiselect':
                            $options = array();
                            foreach ($attribute->getSource()->getAllOptions(false, true) as $option)
                            {
                                $options[$option['value']] = $option['label'];
                            }
                            $this->addColumn($attribute->getAttributeCode(), array(
                                'header'    =>  __($attribute->getFrontend()->getLabel()),
                                'index'     =>  $attribute->getAttributeCode(),
                                'type'      =>  'options',
                                'options'   =>  $options,
                            ));
                            break;
                    }
                }
            }
        }
        
        if ($actionsColumn)
        {
            $this->_columns['action'] = $actionsColumn;
        }
        
        return $this;
    }
}