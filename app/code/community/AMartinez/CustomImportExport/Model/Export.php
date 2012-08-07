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
 * Export model
 *
 */
class AMartinez_CustomImportExport_Model_Export extends Mage_ImportExport_Model_Export
{
    /**
     * Export data.
     *
     * @return string
     */
    public function export()
    {
        return $this->_getEntityAdapter()
                ->setWriter($this->_getWriter())
                ->export();
    }
}
