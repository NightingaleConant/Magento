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
 * Import model
 */
class AMartinez_CustomImportExport_Model_Import extends Mage_ImportExport_Model_Import
{
    /**
     * Returns source adapter object.
     *
     * @param string $sourceFile Full path to source file
     * @return Mage_ImportExport_Model_Import_Adapter_Abstract
     */
    protected function _getSourceAdapter($sourceFile)
    {
        return AMartinez_CustomImportExport_Model_Import_Adapter::findAdapterFor($sourceFile);
    }
    
    /**
     * Validates source file and returns validation result (no unlink file)
     *
     * @param string $sourceFile Full path to source file
     * @return bool
     */
    public function validateSource($sourceFile)
    {
        $result = $this->_getEntityAdapter()
            ->setSource($this->_getSourceAdapter($sourceFile))
            ->isDataValid();

        return $result;
    }
}
