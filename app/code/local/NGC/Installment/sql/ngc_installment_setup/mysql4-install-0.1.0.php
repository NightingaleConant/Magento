<?php
/* @var $installer NGC_Installment_Model_Entity_Setup */
$installer = $this;
$installer->startSetup();


$installer->run("
CREATE TABLE {$this->getTable('installment_type')} (
  `installment_type_id` varchar(255) NOT NULL,
  `installment_type_description` text NOT NULL,
  `installment_type_plan_active` tinyint(1) unsigned NOT NULL DEFAULT 0,
  PRIMARY KEY (`installment_type_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE {$this->getTable('installment_definition')} (
  `installment_type_id` varchar(255) NOT NULL,
  `installment_definition_sequence_number` int(10) unsigned NOT NULL,
  `installment_definition_record_type` int(10) unsigned NOT NULL,
  `installment_definition_value_for_type` decimal(8,2) NOT NULL,
  `installment_definition_days_to_payment` int(10) unsigned NOT NULL,
  PRIMARY KEY  (`installment_type_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE {$this->getTable('installment_master')} (
  `order_id`  int(10) unsigned NOT NULL,
  `installment_master_payment_token` varchar(255) NOT NULL,
  `installment_master_sequence_number` int(10) unsigned NOT NULL,
  `installment_master_amount_due` decimal(8,2) NOT NULL,
  `installment_master_amount_due_date` datetime NOT NULL,
  `installment_master_merchant_number` varchar(255) NOT NULL,
  `installment_master_installment_paid` tinyint(1) unsigned DEFAULT 0,
  `installment_master_installment_authorized` tinyint(1) unsigned DEFAULT 0,
  `installment_master_attempt_to_authorize`tinyint(1) unsigned DEFAULT 0,
  `installment_master_suspend_installment` tinyint(1) unsigned NOT NULL DEFAULT 0,
  `installment_master_suspended_date` datetime NOT NULL,
  `installment_master_suspended_reason` varchar(255),
  `installment_master_currency_code` char(3) NOT NULL,
  PRIMARY KEY  (`order_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE {$this->getTable('installment_transaction')} (
  `order_id` int(10) unsigned NOT NULL,
  `installment_transaction_transaction_sequence_number` int(10) unsigned NOT NULL,
  `installment_transaction_payment_token` varchar(255) NOT NULL,
  `installment_transaction_sequence_number` int(10) unsigned NOT NULL,
  `installment_transaction_authorize_amount` decimal(8,2) NOT NULL,
  `installment_transaction_authorize_attempt_date` datetime NOT NULL,
  `installment_transaction_authorization_code` varchar(255),
  `installment_transaction_decline_code` varchar(255),
  `installment_transaction_decline_transaction_message` varchar(255),
  `installment_transaction_merchant_number` varchar(255) NOT NULL,
  `installment_transaction_currency_code` char(3) NOT NULL,
  PRIMARY KEY  (`order_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

");

$installer->endSetup();
