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
class AW_Sarp_Block_Adminhtml_Customer_Group_Edit_Form extends Mage_Adminhtml_Block_Customer_Group_Edit_Form
{

    const BLOCK_ID = 'aw_sarp_customer_group_edit';
    const BLOCK_LEGEND = 'Subscriptions settings';


    public function _prepareLayout()
    {
        return parent::_prepareLayout();
        $fs = $this
                ->getForm()
                ->addFieldset(self::BLOCK_ID, array('legend' => Mage::helper('sarp')->__(self::BLOCK_LEGEND)));

        $fs->addField('aw_sarp_enabled', 'select', array(
                                                        'label' => Mage::helper('sarp')->__('This group is subscription'),
                                                        'name' => 'status',
                                                        'values' => array(
                                                            array(
                                                                'value' => 0,
                                                                'label' => Mage::helper('sarp')->__('No'),
                                                            ),
                                                            array(
                                                                'value' => 1,
                                                                'label' => Mage::helper('sarp')->__('Yes'),
                                                            )
                                                        )
                                                   ));

        $fs->addField('aw_sarp_period', 'select', array(
                                                       'label' => Mage::helper('sarp')->__('Period'),
                                                       'name' => 'status',
                                                       'values' => array(
                                                           array(
                                                               'value' => 0,
                                                               'label' => Mage::helper('sarp')->__('Monthly delivery'),
                                                           ),
                                                           array(
                                                               'value' => 1,
                                                               'label' => Mage::helper('sarp')->__('AAAA'),
                                                           )
                                                       )
                                                  ));

        return $this;
    }
}
