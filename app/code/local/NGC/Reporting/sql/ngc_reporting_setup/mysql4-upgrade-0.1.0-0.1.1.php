<?php
$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */

$setup = new Mage_Eav_Model_Entity_Setup('core_setup');

$installer->startSetup();

$installer->run("

CREATE TABLE IF NOT EXISTS {$this->getTable('custom_qb_map_adjustments')} (
  `adjustmentCode` varchar(3) NOT NULL,
  `adjustmentReason` varchar(50) NOT NULL,
  `qbAccount` varchar(7) NOT NULL,
  `qbClass` varchar(25) NOT NULL,
  PRIMARY KEY (`adjustmentCode`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Magento Adjustments to QB ';

INSERT INTO `{$this->getTable('custom_qb_map_adjustments')}` (`adjustmentCode`, `adjustmentReason`, `qbAccount`, `qbClass`)
VALUES
	('BAD', 'Bad Debt', '1008000', 'Unallocated'),
	('BER', 'Billing Error', '1009000', 'Unallocated'),
	('CB', 'Charge Back', '7901000', 'Unallocated'),
	('CLM', 'Claims Return', '1008000', 'Unallocated'),
	('DA', '001 - Deposit Application - US', '1001000', 'Unallocated'),
	('DC', 'Deposit Application - Canada', '1001000', 'Unallocated'),
	('DT', '050 - Deposit Application - TVI', '1001000', 'Unallocated'),
	('FOR', 'Forgiven', '1008000', 'Unallocated'),
	('FRT', 'Freight', '1009000', 'Unallocated'),
	('NR', 'Never Received', '1009000', 'Unallocated'),
	('NSF', 'NSF Check', '0107000', 'Unallocated'),
	('RET', 'Returned Merchandise', '1009000', 'Unallocated'),
	('ROY', 'Royalty Allowance', '2232000', 'Unallocated'),
	('RTY', 'Royalty Income', '3851001', 'Unallocated'),
	('SAD', 'Sales Distributors (NC)', '3090000', 'Distributor'),
	('TAX', 'Tax Exempt', '2250000', 'Unallocated'),
	('VDD', 'Voided Refund Check', '1006000', 'Unallocated');

");

$installer->endSetup();
