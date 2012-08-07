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
class AW_Sarp_Block_Adminhtml_Subscribers_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('subscriptionSubscribersGrid');
        $this->setDefaultSort('entity_id');
        $this->setDefaultDir('desc');
        $this->setSaveParametersInSession(true);

    }

    protected function _prepareCollection()
    {
        $collection = Mage::getResourceModel('customer/customer_collection')
                ->addNameToSelect()
                ->addAttributeToSelect('email')
                ->addAttributeToSelect('created_at')
                ->addAttributeToSelect('group_id')
                ->joinAttribute('billing_postcode', 'customer_address/postcode', 'default_billing', null, 'left')
                ->joinAttribute('billing_city', 'customer_address/city', 'default_billing', null, 'left')
                ->joinAttribute('billing_telephone', 'customer_address/telephone', 'default_billing', null, 'left')
                ->joinAttribute('billing_region', 'customer_address/region', 'default_billing', null, 'left')
                ->joinAttribute('billing_country_id', 'customer_address/country_id', 'default_billing', null, 'left');

        //
        $coll = Mage::getResourceModel('sarp/subscription_collection');
        $coll->getSelect()->columns('customer_id')->group('customer_id');
        $filter = array();
        foreach ($coll as $item) {
            $filter[] = $item->getCustomerId();
        }

        if (sizeof($filter)) {
            $collection->addAttributeToFilter('entity_id', $filter);
        } else {
            $collection->addAttributeToFilter('entity_id', array(-1));
        }

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('entity_id', array(
                                           'header' => Mage::helper('customer')->__('ID'),
                                           'width' => '50px',
                                           'index' => 'entity_id',
                                           'type' => 'number',
                                      ));
        /*$this->addColumn('firstname', array(
            'header'    => Mage::helper('customer')->__('First Name'),
            'index'     => 'firstname'
        ));
        $this->addColumn('lastname', array(
            'header'    => Mage::helper('customer')->__('Last Name'),
            'index'     => 'lastname'
        ));*/
        $this->addColumn('name', array(
                                      'header' => Mage::helper('customer')->__('Name'),
                                      'index' => 'name'
                                 ));
        $this->addColumn('email', array(
                                       'header' => Mage::helper('customer')->__('Email'),
                                       'width' => '150',
                                       'index' => 'email'
                                  ));

        $groups = Mage::getResourceModel('customer/group_collection')
                ->addFieldToFilter('customer_group_id', array('gt' => 0))
                ->load()
                ->toOptionHash();

        $this->addColumn('group', array(
                                       'header' => Mage::helper('customer')->__('Group'),
                                       'width' => '100',
                                       'index' => 'group_id',
                                       'type' => 'options',
                                       'options' => $groups,
                                  ));

        $this->addColumn('Telephone', array(
                                           'header' => Mage::helper('customer')->__('Telephone'),
                                           'width' => '100',
                                           'index' => 'billing_telephone'
                                      ));

        $this->addColumn('billing_postcode', array(
                                                  'header' => Mage::helper('customer')->__('ZIP'),
                                                  'width' => '90',
                                                  'index' => 'billing_postcode',
                                             ));

        $this->addColumn('billing_country_id', array(
                                                    'header' => Mage::helper('customer')->__('Country'),
                                                    'width' => '100',
                                                    'type' => 'country',
                                                    'index' => 'billing_country_id',
                                               ));

        $this->addColumn('billing_region', array(
                                                'header' => Mage::helper('customer')->__('State/Province'),
                                                'width' => '100',
                                                'index' => 'billing_region',
                                           ));

        $this->addColumn('customer_since', array(
                                                'header' => Mage::helper('customer')->__('Customer Since'),
                                                'type' => 'datetime',
                                                'align' => 'center',
                                                'index' => 'created_at',
                                                'gmtoffset' => true
                                           ));

        if (!Mage::app()->isSingleStoreMode()) {
            $this->addColumn('website_id', array(
                                                'header' => Mage::helper('customer')->__('Website'),
                                                'align' => 'center',
                                                'width' => '80px',
                                                'type' => 'options',
                                                'options' => Mage::getSingleton('adminhtml/system_store')->getWebsiteOptionHash(true),
                                                'index' => 'website_id',
                                           ));
        }

        $this->addColumn('action',
                         array(
                              'header' => Mage::helper('customer')->__('Action'),
                              'width' => '100',
                              'type' => 'action',
                              'getter' => 'getId',
                              'actions' => array(
                                  array(
                                      'caption' => Mage::helper('customer')->__('Edit'),
                                      'url' => array('base' => 'adminhtml/customer/edit'),
                                      'field' => 'id'
                                  )
                              ),
                              'filter' => false,
                              'sortable' => false,
                              'index' => 'stores',
                              'is_system' => true,
                         ));

        $this->addExportType('*/*/exportCsv', Mage::helper('customer')->__('CSV'));
        $this->addExportType('*/*/exportXml', Mage::helper('customer')->__('XML'));
        return parent::_prepareColumns();
    }
}