<?php
/**
 * aheadWorks Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://ecommerce.aheadworks.com/LICENSE-M1.txt
 *
 * @category   AW
 * @package    AW_Core
 * @version    1.0
 * @copyright  Copyright (c) 2009-2010 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/LICENSE-M1.txt
 */
class AW_Core_Model_Mysql4_Logger_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{

    public function _construct()
    {
        parent::_construct();
        $this->_init('awcore/logger');
    }

    /**
     * Add filter to find records older than specified date
     * @param Zend_Date $Date
     * @return AW_Core_Model_Mysql4_Logger_Collection
     */
    public function addOlderThanFilter(Zend_Date $Date)
    {
        $this->getSelect()->where('date<?', $Date->toString(AW_Core_Model_Abstract::DB_DATETIME_FORMAT));
        return $this;
    }
}