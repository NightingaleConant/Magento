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

class AMartinez_CustomImportExport_Model_Observer
{
	public function dispatch($schedule)
	{
		// print_r($schedule['executed_at']);
		
		// $_SERVER['argv'] = array('-c', '-p', '-r', '-l', '5000', '-i', '/var/www/html/newrockworld/store/var/customimportexport/productos.csv1');
		// $_SERVER['argv'] = array('-f');
		// require_once 'app/code/community/AMartinez/CustomImportExport/shell/run.php';

        return $this;
	}
}
