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
class AW_Sarp_Model_Mysql4_Sequence extends Mage_Core_Model_Mysql4_Abstract
{
    protected function _construct()
    {
        $this->_init('sarp/sequence', 'id');
    }

    /**
     * Delete complete sequence for selected subscription_id
     * @param int $id
     * @return AW_Sarp_Model_Mysql4_Sequence
     */
    public function deleteBySubscriptionId($id)
    {
        $this->_getWriteAdapter()->delete($this->getMainTable(), 'subscription_id=' . $id . ' AND status=\'' . AW_Sarp_Model_Sequence::STATUS_PENDING . "'");
        return $this;
    }


}
