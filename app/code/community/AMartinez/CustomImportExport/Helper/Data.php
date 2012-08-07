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
 * Data Helper
 */
class AMartinez_CustomImportExport_Helper_Data extends Mage_Core_Helper_Abstract
{
    /**
     * Recalculate catalog price rules
     */
    public function applyRules()
    {
		echo "Recalculating catalog price rules...\n";
		
		try
		{
			Mage::getModel('catalogrule/rule')->applyAll();
        	Mage::app()->removeCache('catalog_rules_dirty');
		}
		catch (Exception $e)
		{
			echo $e;
		}
    }
    
    /**
     * Flush catalog images cache
     */
    public function flushImages()
    {
		echo "Erasing catalog images cache...\n";
		
		try
		{
			Mage::getModel('catalog/product_image')->clearCache();
		}
		catch (Exception $e)
		{
			echo $e;
		}
    }
    
    /**
     * Reindex data by all indexers
     *
     * @return array
     */
    public function reindexAll()
    {
    	echo "Reindexing data...\n";
    	
    	$processes = array();
    	
		$collection = Mage::getSingleton('index/indexer')->getProcessesCollection();
		foreach ($collection as $process)
		{
			$processes[] = $process;
		}

		foreach ($processes as $process) {
			/* @var $process Mage_Index_Model_Process */
			try
			{
				echo "\t" . $process->getIndexer()->getName() . "... ";
				$process->reindexEverything();
				echo "index was rebuilt successfully\n";
			}
			catch (Mage_Core_Exception $e)
			{
				echo $e->getMessage() . "\n";
			}
			catch (Exception $e)
			{
				echo "index process unknown error:\n";
				echo "\t" . $e . "\n";
			}
		}
    
    	return $processes;
    }

    /**
     * Clean cache
     */
    public function cleanCache()
    {
		echo "Cleaning cache...\n";
		
		try
		{
			Mage::app()->cleanCache();
		}
		catch (Exception $e)
		{
			echo $e;
		}
    }

	/**
	 * Delete all products and non-root categories
	 *
	 * Alternative method: run.php -e -i -b delete
	 */
	public function deleteAllProducts()
	{
		// products
		$connection = Mage::getSingleton('core/resource')->getConnection('read');
		$select = $connection->select()
            ->from(Mage::getResourceModel('catalog/product')->getTable('catalog/product'), array('sku'));
        $result = $connection->query($select);

		$filename = Mage::getConfig()->getOptions()->getTmpDir() . DS . 'deleteallproducts.csv';
		$f = fopen($filename, 'w');
		fwrite($f, "sku\n");
        while ($row = $result->fetch())
        {
            fwrite($f, $row['sku'] . "\n");
        }
        fclose($f);

		$import = Mage::getModel('customimportexport/import');
		$import->setEntity('catalog_product');
		$import->setBehavior('delete');
		$validationResult = $import->validateSource($filename);
		$processedRowsCount = $import->getProcessedRowsCount();
		if ($processedRowsCount > 0)
		{
			$import->importSource();
		}
				
		unlink($filename);
       
		// categories
		$tree = Mage::getResourceSingleton('catalog/category_tree')->load(); 
		$ids = $tree->getCollection()->getAllIds(); 
		if ($ids)
		{ 
			foreach ($ids as $id)
			{ 
				$cat = Mage::getModel('catalog/category'); 
				$cat->load($id);
				// skip root categories
				if ($cat->getParentId() > 1)
				{
					$cat->delete();
				}
			} 
		}

		return $processedRowsCount;
	}

	/**
	 * Delete all customers
	 *
	 * Alternative method: run.php -ce -ci -b delete
	 */
	public function deleteAllCustomers()
	{
    	$websiteIdToCode = array();
		foreach (Mage::app()->getWebsites(true) as $website) {
			$websiteIdToCode[$website->getId()] = $website->getCode();
		}
    
		$connection = Mage::getSingleton('core/resource')->getConnection('read');
		$select = $connection->select()
            ->from(Mage::getResourceModel('customer/customer_collection')->getTable('customer_entity'), array('email','website_id'));
        $result = $connection->query($select);

		$filename = Mage::getConfig()->getOptions()->getTmpDir() . DS . 'deleteallcustomers.csv';
		$f = fopen($filename, 'w');
		fwrite($f, "email,_website\n");
        while ($row = $result->fetch())
        {
            fwrite($f, $row['email'] . "," . $websiteIdToCode[$row['website_id']] . "\n");
        }
        fclose($f);

		$import = Mage::getModel('customimportexport/import');
		$import->setEntity('customer');
		$import->setBehavior('delete');
		$validationResult = $import->validateSource($filename);
		$processedRowsCount = $import->getProcessedRowsCount();
		if ($processedRowsCount > 0)
		{
			$import->importSource();
		}
				
		unlink($filename);
		
		return $processedRowsCount;
	}
	
	/**
	 * Split file into pieces
	 */
	public function splitFile($filename, $linecount)
	{
		$extension = pathinfo($filename, PATHINFO_EXTENSION);
		$count = 0;
		$i = 0;
		$j = 0;
		$files = array();
		$h = fopen($filename, 'r');		
		$header = fgets($h);
		while($line = fgets($h))
		{
			// first iteration / no "attr-only" line / line count has been reached
			if ($i == 0 || (substr($line, 0 ,1) != ',' && substr($line, 0 ,2) != '""' && substr($line, 0 ,2) != "''" && $i >= $linecount && !($linecount === true)))
			{
				if (defined('t'))
				{
					fclose($t);
				}
				$files[] = Mage::getConfig()->getOptions()->getTmpDir() . DS . pathinfo($filename, PATHINFO_FILENAME) . '.tmp.' . $j . '.' . $extension;
				$t = fopen($files[$j++], 'w');
				fwrite($t, $header);
				$i = 0;
			}
			fwrite($t, $line);
			$count++;
			$i++;
		}
		fclose($h);
		if (defined('t'))
		{
			fclose($t);
		}
		
		return $files;
	}

	/**
	 * Unlink temp files
	 */
	public function unlinkFiles($files)
	{
		foreach ($files as $file)
		{
			unlink($file);
		}
	}
}
