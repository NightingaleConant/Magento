<?php
    /* @var $installer NGC_Installment_Model_Entity_Setup */
    $installer = $this;
    $installer->startSetup();


$installer->run("

CREATE TABLE IF NOT EXISTS {$this->getTable('custom_qb_map')} (
  `orderTypeId` int(11) NOT NULL,
  `OrderTypeCode` varchar(5) NOT NULL,
  `qbSalesAccount` varchar(7) NOT NULL,
  `qbSalesClass` varchar(25) NOT NULL,
  `qbCostAccount` varchar(7) NOT NULL,
  `qbCostClass` varchar(25) NOT NULL,
  `qbReturnAccount` varchar(7) NOT NULL,
  `qbReturnClass` varchar(25) NOT NULL,
  `qbAllowancesAccount` varchar(7) NOT NULL,
  `qbAllowancesClass` varchar(25) NOT NULL,
   PRIMARY KEY (`orderTypeId`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Nightingale-Conant Quick Books Order Type Mappings';

");


$installer->endSetup();
