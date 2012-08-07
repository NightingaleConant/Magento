<?php

class NGC_Installment_Block_Adminhtml_Type_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();

        $this->_controller = 'adminhtml_type';

        $this->setId('typeGrid');
        $this->setDefaultSort('installment_type_id');
        $this->setDefaultDir('asc');
        $this->setSaveParametersInSession(true);
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getModel('installment/type')->getCollection();
        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('id', array(
            'header'        => Mage::helper('installment')->__('ID'),
            'index'         => 'id',
        ));

        $this->addColumn('installment_type_id', array(
            'header'        => Mage::helper('installment')->__('Name'),
            'index'         => 'installment_type_id',
        ));

        $this->addColumn('installment_type_description', array(
            'header'        => Mage::helper('installment')->__('Description'),
            'index'         => 'installment_type_description',
        ));

        $this->addColumn('installment_type_plan_active', array(
            'header'        => Mage::helper('installment')->__('Active'),
            'index'         => 'installment_type_plan_active',
            'renderer'      => 'NGC_Installment_Block_Adminhtml_Type_Renderer_Active',
        ));

        $this->addColumn('installment_type_auto_adjust', array(
            'header'        => Mage::helper('installment')->__('Auto Adjust'),
            'index'         => 'installment_type_auto_adjust',
            'renderer'      => 'NGC_Installment_Block_Adminhtml_Type_Renderer_Yesno',
        ));

        $this->addColumn('installment_type_plan_default', array(
            'header'        => Mage::helper('installment')->__('Default'),
            'index'         => 'installment_type_plan_default',
            'renderer'      => 'NGC_Installment_Block_Adminhtml_Type_Renderer_Default',
        ));

        return parent::_prepareColumns();
    }

    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit', array('id'=>$row->getId()));
    }
}
