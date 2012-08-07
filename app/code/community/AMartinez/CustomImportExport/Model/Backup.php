<?php
/**
 * Magento
 *
 * @category   AMartinez
 * @package    AMartinez_CustomImportExport
 * @author     Antonio Martinez
 * @copyright  Copyright (c) 2011 Antonio Martinez (toniyecla [at] gmail [dot] com)
 * @license    http://opensource.org/licenses/osl-3.0 Open Software License (OSL 3.0)
 */

/**
 * Backup file item model
 *
 * @category   Mage
 * @package    Mage_Backup
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class AMartinez_CustomImportExport_Model_Backup extends Mage_Backup_Model_Backup
{
    /**
     * Backup filename
     *
     * @var string
     */
    private $_file = '';

    /**
     * Sets filename
     *
     * @param string $value
     */
    public function setFile($value)
    {
        $this->_file = $value;

        return $this;
    }
    
    /**
     * Return file name of backup file
     *
     * @return string
     */
    public function getFileName()
    {
    	return $this->_file;
    }
}
