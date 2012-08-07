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
 */class AW_Sarp_Model_Mysql4_Alert_Event_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{

    public function _construct()
    {
        parent::_construct();
        $this->_init('sarp/alert_event');
    }

    /**
     * Adds filter by Alert
     * @param AW_Sarp_Model_Alert $Alert
     * @return AW_Sarp_Model_Mysql4_Alert_Event_Collection
     */
    public function addAlertFilter(AW_Sarp_Model_Alert $Alert)
    {
        return $this->addAlertIdFilter($Alert->getId());
    }

    /**
     * Adds filter by Alert ID
     * @param int $id
     * @return AW_Sarp_Model_Mysql4_Alert_Event_Collection
     */
    public function addAlertIdFilter($id)
    {
        $this->getSelect()->where('alert_id=?', $id);
        return $this;
    }

    /**
     * Factory only pending events
     * @return AW_Sarp_Model_Mysql4_Alert_Event_Collection
     */
    public function addPendingFilter()
    {
        $this->getSelect()->where('status=?', AW_Sarp_Model_Alert_Event::STATUS_PENDING);
        return $this;
    }

    /**
     * Adds filter for past events
     * @return AW_Sarp_Model_Mysql4_Alert_Event_Collection
     */
    public function addNowFilter()
    {
        $this->getSelect()->where('date<=?', now());
        return $this;
    }

}