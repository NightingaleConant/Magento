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
 * Require the Magento abstract class for cli scripts
 */
require_once dirname(__FILE__) . '/../../../../../../shell/abstract.php';

class AMartinez_CustomImportExport extends Mage_Shell_Abstract
{

	/**
	 * Trigger the import
	 */
	public function run()
	{
		Mage::setIsDeveloperMode(true);
        ini_set('display_errors', 1);
        echo "\n";

		// help
		if ($this->getArg('h') || $this->getArg('help') || count($this->_args) == 0)
		{
			echo $this->usageHelp();
			return 1;
		}

		// database export
		if ($backup = $this->initDatabaseBackupModel()) {
			echo "Start database output to file " . $backup->getPath() . DS . $backup->getFilename() . "\n";
			Mage::log("Start database output to file " . $backup->getPath() . DS . $backup->getFilename(), Zend_Log::DEBUG);
	
			$backupDb = Mage::getModel('backup/db');
			Mage::register('backup_model', $backup);
			if ($backupDb->createBackup($backup))
			{
				echo "Done.\n";
			}
		}

		// delete products
		if ($this->getArg('deleteallproducts'))
		{
			echo "Start all products delete\n";
			Mage::log("Start all products delete", Zend_Log::DEBUG);
			if ($count = Mage::helper('customimportexport')->deleteAllProducts())
			{
				echo "Done (deleted products: " . $count . ")\n";
			}
		}

		// delete customers
		if ($this->getArg('deleteallcustomers'))
		{
			echo "Start all customers delete\n";
			Mage::log("Start all customers delete", Zend_Log::DEBUG);
			if ($count = Mage::helper('customimportexport')->deleteAllCustomers())
			{
				echo "Done (deleted customers: " . $count . ")\n";
			}
		}
		
		// behavior
		$this->behavior = ($this->getArg('b') ? $this->getArg('b') : ($this->getArg('behavior') ? $this->getArg('behavior') : 'replace'));
		$this->behavior === true ? $this->behavior = 'replace': '';

		// products export
		if ($file = $this->getOutputFile()) {
			echo "Start products output to file $file\n";
			Mage::log("Start products output to file $file", Zend_Log::DEBUG);
	
			$export = $this->initExportModel();
			if ($f = fopen($file, 'w'))
			{	
				$result = $export->export();
				fwrite($f, $result);
				fclose($f);
				echo "Done (processed rows count: " . (substr_count($result, "\n") - 1). ")\n";
			}
			else
			{
				echo "Cannot output products to file $file\n";
			};
		}
		
        // products import
		if ($filename = $this->getSourceFile()) {
			$files = Mage::helper('customimportexport')->splitFile($filename, ($this->getArg('l') ? $this->getArg('l') : ($this->getArg('linecount') ? $this->getArg('linecount') : true)));	
			
			echo "Start products '$this->behavior' action from $filename\n";
			Mage::log("Start products '$this->behavior' action from $filename", Zend_Log::DEBUG);
			
			$count = 0;
			foreach ($files as $file)
			{
				$import = $this->initImportModel();
				$validationResult = $import->validateSource($file);
				$processedRowsCount = $import->getProcessedRowsCount();
		
				if ($processedRowsCount > 0)
				{
					while (!$validationResult)
					{
						$message = '';
						// if 'select' attr options added, revalidate source (not needed for categories)
						foreach ($import->getErrors() as $type => $lines)
						{
							if (!strpos($type, "added"))
							{
								$message .= "\n:::: " . $type . " ::::\nIn Line(s) " . implode(", ", $lines) . "\n";
							}
						}
	
						if ($message) {
							Mage::helper('customimportexport')->unlinkFiles($files);
							Mage::throwException(sprintf("File %s contains %s corrupt records (from a total of %s)", $file, $import->getInvalidRowsCount(), $processedRowsCount) . $message);
						}
						
						$import = $this->initImportModel();
						$validationResult = $import->validateSource($file);
						$processedRowsCount = $import->getProcessedRowsCount();
					}
		
					$count += $processedRowsCount;
					$import->importSource();
				}
			}
			Mage::helper('customimportexport')->unlinkFiles($files);
			echo "\nDone (processed rows count: " . $count . ")\n";
		}

		// customers export
		if ($file = $this->getCustomersOutputFile()) {
			echo "Start customers output to file $file\n";
			Mage::log("Start customers output to file $file", Zend_Log::DEBUG);
	
			$customersexport = $this->initCustomersExportModel();
			if ($f = fopen($file, 'w'))
			{	
				$result = $customersexport->export();
				fwrite($f, $result);
				fclose($f);
				echo "Done (processed rows count: " . (substr_count($result, "\n") - 1) . ")\n";
			}
			else
			{
				echo "Cannot output customers to file $file\n";
			};
		}
		
        // customers import
		if ($filename = $this->getCustomersSourceFile()) {
			$files = Mage::helper('customimportexport')->splitFile($filename, ($this->getArg('l') ? $this->getArg('l') : ($this->getArg('linecount') ? $this->getArg('linecount') : true)));	
		
			echo "Start customers '$this->behavior' action from $filename\n";
			Mage::log("Start customers '$this->behavior' action from $filename", Zend_Log::DEBUG);

			$count = 0;
			$files_ = array();
			foreach ($files as $file)
			{
				$customersimport = $this->initCustomersImportModel();
				$validationResult = $customersimport->validateSource($file);
				$processedRowsCount = $customersimport->getProcessedRowsCount();
		
				if ($processedRowsCount > 0)
				{
					// get only one field each time to prevent disruption because some line errors
					while (!$validationResult)
					{
						echo "\n";
						$errorLines = array();
						foreach ($customersimport->getErrors() as $type => $lines)
						{
							echo ":::: " . $type . " in line(s) " . implode(", ", $lines) . " ::::\n";
							$errorLines = array_merge($errorLines, $lines);
						}
						echo "\n";
						$file_ = str_replace('tmp.', 'tmp._', $file);
						$files_[] = $file_;
						$h = fopen($file, 'r');
						$t = fopen($file_, 'w');
						$i = 0;
						$error = false;
						while($line = fgets($h))
						{
							if ((substr($line, 0 ,1) != ',' && substr($line, 0 ,2) != '""' && substr($line, 0 ,2) != "''"))
							{
								$error = false;
							}
							if (!$error)
							{
								if (!in_array($i, $errorLines))
								{
									fwrite($t, $line);
								}
								else
								{
									echo ":: Line " . $i . " :: " . $line;
									$error = true;
								}
							}
							$i++;
						}
						fclose($h);
						fclose($t);
						
						$customersimport = $this->initCustomersImportModel();
						$validationResult = $customersimport->validateSource($file_);
						$processedRowsCount = $customersimport->getProcessedRowsCount();
						$file = $file_;
					}
		
					$count += $processedRowsCount;
					$customersimport->importSource();
				}
			}
			
			Mage::helper('customimportexport')->unlinkFiles($files);
			Mage::helper('customimportexport')->unlinkFiles($files_);
			echo "\nDone (processed rows count: " . $count . ")\n";
		}

		// price rules
		if ($this->getArg('p') || $this->getArg('applyrules') || $this->getArg('a') || $this->getArg('all'))
		{
			Mage::helper('customimportexport')->applyRules();
		}
		
		// image files
		if ($this->getArg('f') || $this->getArg('flushimages') || $this->getArg('a') || $this->getArg('all'))
		{
			Mage::helper('customimportexport')->flushImages();
		}
		
		// index
		if ($this->getArg('r') || $this->getArg('reindex') || $this->getArg('a') || $this->getArg('all'))
		{
			Mage::helper('customimportexport')->reindexAll();
		}
		
		// cache
		if ($this->getArg('c') || $this->getArg('cleancache') || $this->getArg('a') || $this->getArg('all'))
		{
			Mage::helper('customimportexport')->cleanCache();
		}
		
		return 0;
	}

	/**
	 * Initialize database export model
	 *
	 * @return AMartinez_CustomImportExport_Model_Backup
	 */
	public function initDatabaseBackupModel()
	{
		$option1 = $this->getArg('db');
		$option2 = $this->getArg('databasebackup');
		if ($option1 || $option2)
		{
			$backup = Mage::getModel('customimportexport/backup')
            	->setType('db');
            if ($option1 && $option1 != 1)
			{
				$fileinfo = pathinfo($option1);
				if (substr($fileinfo['dirname'], 0) != DS) {
					$fileinfo['dirname'] = Mage::getBaseDir() . DS . $fileinfo['dirname'];
				}
				$backup->setPath($fileinfo['dirname'])
					->setFile($fileinfo['basename']);
				return $backup;
			}
            if ($option2 && $option2 != 1)
			{
				$fileinfo = pathinfo($option2);
				if (substr($fileinfo['dirname'], 0) != DS) {
					$fileinfo['dirname'] = Mage::getBaseDir() . DS . $fileinfo['dirname'];
				}
				$backup->setPath($fileinfo['dirname'])
					->setFile($fileinfo['basename']);
				return $backup;
			}
            $backup->setPath(Mage::getBaseDir("var") . DS . "backups")
            	->setFile(time() . "_" . $backup->getType() . ".gz");
			return $backup;
		}
		else
		{
			return false;
		}
	}
	
	/**
	 * Initialize products export model
	 *
	 * @return AMartinez_CustomImportExport_Model_Export
	 */
	public function initExportModel()
	{
		$export = Mage::getModel('customimportexport/export');
		$export->setEntity('catalog_product');
		$export->setData('file_format', 'csv');
		
		return $export;
	}

	/**
	 * Return the specified products output file
	 *
	 * @return string
	 */
	public function getOutputFile()
	{
		$option1 = $this->getArg('e');
		$option2 = $this->getArg('export');
		if ($option1 || $option2)
		{
			if ($option1 && $option1 != 1)
			{
				return $option1;
			}
			if ($option2 && $option2 != 1)
			{
				return $option2;
			}
			if ($default = Mage::getStoreConfig('customimportexport/products/outputfile'))
			{
				return $default;
			}
			else
			{
				echo "No default products output file found in config.xml\n";
			}
		}
	}
	
	/**
	 * Initialize products import model
	 *
	 * @return AMartinez_CustomImportExport_Model_Import
	 */
	public function initImportModel()
	{
		$import = Mage::getModel('customimportexport/import');
		$import->setEntity('catalog_product');
		$import->setBehavior($this->behavior);
		
		return $import;
	}
	
	/**
	 * Return the specified products source file
	 *
	 * @return string
	 */
	public function getSourceFile()
	{
		$option1 = $this->getArg('i');
		$option2 = $this->getArg('import');
		if ($option1 || $option2)
		{
			if ($option1 && $option1 != 1)
			{
				if (file_exists($option1))
				{
					return $option1;
				}
				else
				{
					echo "Skipping products source file $option1\n";
				}
			}
			if ($option2 && $option2 != 1)
			{
				if (file_exists($option2))
				{
					return $option2;
				}
				else
				{
					echo "Skipping products source file $option2\n";
				}
			}
			if ($default = Mage::getStoreConfig('customimportexport/products/sourcefile'))
			{
				if(file_exists($default)) 
				{
					return $default;
				}
				else
				{
					echo "Skipping default products source file $default\n";
				}
			}
			else
			{
				echo "No default products source file found in config.xml\n";
			}
		}
	}

	/**
	 * Initialize customers export model
	 *
	 * @return AMartinez_CustomImportExport_Model_Export
	 */
	public function initCustomersExportModel()
	{
		$customersexport = Mage::getModel('customimportexport/export');
		$customersexport->setEntity('customer');
		$customersexport->setData('file_format', 'csv');
		
		return $customersexport;
	}

	/**
	 * Return the specified customers output file
	 *
	 * @return string
	 */
	public function getCustomersOutputFile()
	{
		$option1 = $this->getArg('ce');
		$option2 = $this->getArg('customersexport');
		if ($option1 || $option2)
		{
			if ($option1 && $option1 != 1)
			{
				return $option1;
			}
			if ($option2 && $option2 != 1)
			{
				return $option2;
			}
			if ($default = Mage::getStoreConfig('customimportexport/customers/outputfile'))
			{
				return $default;
			}
			else
			{
				echo "No default customers output file found in config.xml\n";
			}
		}
	}
	
	/**
	 * Initialize customers import model
	 *
	 * @return AMartinez_CustomImportExport_Model_Import
	 */
	public function initCustomersImportModel()
	{
		$customersimport = Mage::getModel('customimportexport/import');
		$customersimport->setEntity('customer');
		$customersimport->setBehavior($this->behavior);
		
		return $customersimport;
	}
	
	/**
	 * Return the specified customers source file
	 *
	 * @return string
	 */
	public function getCustomersSourceFile()
	{
		$option1 = $this->getArg('ci');
		$option2 = $this->getArg('customersimport');
		if ($option1 || $option2)
		{
			if ($option1 && $option1 != 1)
			{
				if (file_exists($option1))
				{
					return $option1;
				}
				else
				{
					echo "Skipping customers source file $option1\n";
				}
			}
			if ($option2 && $option2 != 1)
			{
				if (file_exists($option2))
				{
					return $option2;
				}
				else
				{
					echo "Skipping customers source file $option2\n";
				}
			}
			if ($default = Mage::getStoreConfig('customimportexport/customers/sourcefile'))
			{
				if(file_exists($default)) 
				{
					return $default;
				}
				else
				{
					echo "Skipping default customers source file $default\n";
				}
			}
			else
			{
				echo "No default customers source file found in config.xml\n";
			}
		}
	}

	/**
	 * Retrieve usage help message
	 */
	public function usageHelp()
	{
		$tmp_folder = substr(Mage::getConfig()->getOptions()->getTmpDir(), strlen(Mage::getConfig()->getOptions()->getBaseDir()) + 1);
		
		return <<<HELP
AMartinez_CustomImportExport script (v. 1.5.014)

NAME
    run.php

SYNOPSIS
    php -f amartinez_customimportexport.php
    php -f amartinez_customimportexport.php [-- [OPTIONS...]]

DESCRIPTION
	This extension can Import/Export products and customers from/to CSV file. Create categories, add attribute options, import images and media galleries, reindex, refresh cache, media and price rules automatically. In addition you can backup the entire database in SQL format
	
	Imports:
		(products, customers)
			file.csv: multiple-row csv files, supports multiselect fields
		(products)
			file.csv1: single-row csv files, supports multiselect and multivalued fields
		
		Multivalued fields:
			Only with CSV1 files, these fields are automatically processed as multivalued:
				_store
				visibility
				_category
				_associated_sku
				_media_image
				_media_attribute_id
				_media_label
				_media_position
				_media_is_disabled
			To define your own ones, use * (this overrides default values)
				sku ,*_category      ,*_associated_sku
				TEST,"CAT1/CAT2,CAT3","ASSOCIATED1,ASSOCIATED2"
			
		Multiselect fields:
			Use commas to separate values, but make sure not to put asterisks in column header, ie:
				sku ,*_category      ,color
				TEST,"CAT1/CAT2,CAT3","red,yellow"
		
	Exports:
		(products, customers)
		file.csv: multiple-row new csv files (no support for multiselect)
		(database)
		backup all tables to SQL gz-compressed file

OPTIONS
    -h
    -help
                            print this usage and exit

    -a
    -all
                            same as -c -f -p -r options
                            
    -b
    -behavior
                            set import behavior for csv file: append|delete|replace (default)

    -c
    -cleancache
                            clean cache storage (html output, etc)

    -ce [file]
    -customersexport [file]
                            output customers to csv file, if output file not specified uses defined in config.xml

    -ci [file]
    -customersimport [file]
                            import customers from csv file, if source file not specified or not found uses defined in config.xml

    -db [file]
    -databasebackup [file]
                            output whole magento database to sql file and try to gzcompress it, if output file not specified uses time generated name

    -deleteallcustomers
                            delete all customers (NOT from csv, entire magento database!)
    
    -deleteallproducts
                            delete all products and non-root categories (NOT from csv, entire magento database!)
 
    -e [file]
    -export [file]
                            output products to csv file, if output file not specified uses defined in config.xml

    -i [file]
    -import [file]
                            import products from csv or csv1 file, if source file not specified or not found uses defined in config.xml. 

    -f
    -flushimages
                            flush catalog images cache

    -l <n>
    -linecount <n>
                            split import file into pieces of n lines length ($tmp_folder must be writable)
                            
    -p
    -applyrules
                            recalculate catalog price rules

    -r
    -reindex
                            reindex data by all indexers (attributes, prices, etc)

EXAMPLES
    import
                            php -f amartinez_customimportexport.php -- -all -import var/customimportexport/test_single_row_style.csv1
    						
    export
                            php -f amartinez_customimportexport.php -- -e   
    						
    reindex
                            php -f amartinez_customimportexport.php -- -a
 
    backup, import, reindex
                            php -f amartinez_customimportexport.php -- -databasebackup -import -all
   							
CRONTAB EXAMPLES
    # do tasks and send results by email
    00 03 * * * root php -f /magento_base_dir/amartinez_customimportexport.php -- -db -a -i /magento_base_dir/var/customimportexport/products.csv1 2>&1 | xargs -0 -I msg echo -e "Subject: MY STORE products daily update \\n\\n " msg | /usr/sbin/sendmail -f some@address.com some@address.com
   							
    # do tasks, append output to log file and send last results by email
    00 05 * * * root php -f /magento_base_dir/amartinez_customimportexport.php -- -db -a -i /magento_base_dir/var/customimportexport/products.csv1 2>&1 | tee -a /var/log/amartinez_customimportexport.log | echo -e "Subject: MY STORE products daily update \\n\\n `cat`" | /usr/sbin/sendmail -f some@address.com foo@address.com,bar@address.com

CREDITS
    I would be delighted to hear from you if you like this script. I also appreciate any donation you wish to contribute to the growth of this project (paypal: toniyecla [at] gmail [dot] com).


HELP;
	}
	
}

$main = new AMartinez_CustomImportExport();
$main->run();
