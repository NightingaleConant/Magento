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
 * CSV import adapter (previous version: all values in a single row, comma separated)
 * e.g.: ..."store1,store2","visibility1,visibility2","CAT1,CAT2,CAT3","ASSOCIATED1,ASSOCIATED2"...
 */
class AMartinez_CustomImportExport_Model_Import_Adapter_Csv1 extends Mage_ImportExport_Model_Import_Adapter_Abstract
{
    /**
     * Field delimiter.
     *
     * @var string
     */
    protected $_delimiter = ',';

    /**
     * Field enclosure character.
     *
     * @var string
     */
    protected $_enclosure = '"';

    /**
     * Source file handler.
     *
     * @var resource
     */
    protected $_fileHandler;

	protected $_multiValuedColNames = array('_store', 'visibility', '_category', '_associated_sku', '_media_image', '_media_attribute_id', '_media_label', '_media_position', '_media_is_disabled');
	protected $_values = array();
	protected $_currentOffsetKey = 0;
	protected $_maxOffsetKey = 0;
	
    /**
     * Object destructor.
     *
     * @return void
     */
    public function __destruct()
    {
        if (is_resource($this->_fileHandler)) {
            fclose($this->_fileHandler);
        }
    }

    /**
     * Method called as last step of object instance creation. Can be overrided in child classes.
     *
     * @return AMartinez_CustomImportExport_Model_Import_Adapter_Abstract
     */
    protected function _init()
    {
        $this->_fileHandler = fopen($this->_source, 'r');
        $this->rewind();
        // echo ":::: " . Mage::helper('importexport')->__("Multivalued column names: ") . implode(',', $this->_multiValuedColNames) . " ::::\n";
        
        return $this;
    }

    /**
     * Move forward to next element
     *
     * @return void Any returned value is ignored.
     */
    public function next()
    {	
    	if ($this->_maxOffsetKey > $this->_currentOffsetKey) {
    		$this->_currentOffsetKey ++;
    		$this->_setValues();
    	} else {
    		$this->_currentRow = fgetcsv($this->_fileHandler, null, $this->_delimiter, $this->_enclosure);
    		$this->_getValues();
    	}
    	
        $this->_currentKey = $this->_currentRow ? $this->_currentKey + 1 : null;
    }

    /**
     * Rewind the Iterator to the first element.
     *
     * @return void Any returned value is ignored.
     */
    public function rewind()
    {
        // rewind resource, reset column names, read first row as current
        rewind($this->_fileHandler);
        $this->_colNames = fgetcsv($this->_fileHandler, null, $this->_delimiter, $this->_enclosure);
        
        $multi = array();
        $i = 0;
        while ($i < count($this->_colNames))
        {
			if (substr($this->_colNames[$i], 0, 1) == "*")
        	{
        		$this->_colNames[$i] = substr($this->_colNames[$i], 1);
        		$multi[] = $this->_colNames[$i];
        	}
        	$i++;
        }
		if (count($multi) > 0)
		{
			$this->_multiValuedColNames = $multi;
		}
        
        $this->_currentRow = fgetcsv($this->_fileHandler, null, $this->_delimiter, $this->_enclosure);
    	$this->_getValues();
    	
        if ($this->_currentRow) {
            $this->_currentKey = 0;
        }
    }

    /**
     * Seeks to a position.
     *
     * @param int $position The position to seek to.
     * @throws OutOfBoundsException
     * @return void
     */
    public function seek($position)
    {
        if ($position != $this->_currentKey) {
            if (0 == $position) {
                return $this->rewind();
            } elseif ($position > 0) {
                if ($position < $this->_currentKey) {
                    $this->rewind();
                }
                while ($this->_currentRow = fgetcsv($this->_fileHandler, null, $this->_delimiter, $this->_enclosure)) {
                	$this->_getValues();
                	do {
                    	if (++ $this->_currentKey == $position) {
                    		$this->_setValues();
    						return;
                    	}
                    } while ($this->_maxOffsetKey > $this->_currentOffsetKey ++);
                }
            }
            throw new OutOfBoundsException(Mage::helper('importexport')->__('Invalid seek position'));
        }
    }

    protected function _getValues()
    {
    	$this->_values = array();
    	$this->_currentOffsetKey = 0;
    	$this->_maxOffsetKey = 0;

    	foreach ($this->_multiValuedColNames as $colName)
    	{
        	if ($keyId = array_search($colName, $this->_colNames))
        	{
        		$values = explode(",", $this->_currentRow[$keyId]);
        		$this->_values[$colName] = $values;
        		$this->_maxOffsetKey = max($this->_maxOffsetKey, count($values) - 1);
        	}
    	}
        
        $this->_setValues();
    }

    protected function _setValues()
    {
    	if ($this->_currentOffsetKey > 0) {
    		foreach ($this->_currentRow as $key => $value)
    		{
				$this->_currentRow[$key] = "";
			}
    	}
    	
    	foreach ($this->_multiValuedColNames as $colName) {
    		if (array_key_exists($colName, $this->_values) && $this->_currentOffsetKey < count($this->_values[$colName]) && $this->_values[$colName][$this->_currentOffsetKey]) {
    			$keyId = array_search($colName, $this->_colNames);
    			$this->_currentRow[$keyId] = $this->_values[$colName][$this->_currentOffsetKey];
        	}
    	}
    }
}
