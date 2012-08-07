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
 * Import adapter model
 */
class AMartinez_CustomImportExport_Model_Import_Adapter extends Mage_ImportExport_Model_Import_Adapter
{
    /**
     * Adapter factory. Checks for availability, loads and create instance of import adapter object.
     *
     * @param string $type Adapter type ('csv', 'xml' etc.)
     * @param mixed $options OPTIONAL Adapter constructor options
     * @throws Exception
     * @return Mage_ImportExport_Model_Import_Adapter_Abstract
     */
    public static function factory($type, $options = null)
    {
        if (!is_string($type) || !$type) {
            Mage::throwException(Mage::helper('importexport')->__('Adapter type must be a non empty string'));
        }
        $adapterClass = __CLASS__ . '_' . ucfirst(strtolower($type));
		
        if (!class_exists($adapterClass, false)) {
            $adapterFile = str_replace('_', '/', $adapterClass) . '.php';
            if (!@include_once($adapterFile)) {
            	$adapterClass = get_parent_class() . '_' . ucfirst(strtolower($type));
            	$adapterFile = str_replace('_', '/', $adapterClass) . '.php';
            	if (!@include_once($adapterFile)) {
                	Mage::throwException("'{$type}' file extension is not supported");
                }
            }
            if (!class_exists($adapterClass, false)) {
                Mage::throwException("Can not find adapter class {$adapterClass} in adapter file {$adapterFile}");
            }
        }
        $adapter = new $adapterClass($options);

        if (! $adapter instanceof Mage_ImportExport_Model_Import_Adapter_Abstract) {
            Mage::throwException(
                Mage::helper('importexport')->__('Adapter must be an instance of Mage_ImportExport_Model_Import_Adapter_Abstract')
            );
        }
        return $adapter;
    }
    
    /**
     * Create adapter instance for specified source file.
     *
     * @param string $source Source file path.
     * @return Mage_ImportExport_Model_Import_Adapter_Abstract
     */
    public static function findAdapterFor($source)
    {
        return self::factory(pathinfo($source, PATHINFO_EXTENSION), $source);
    }
}
