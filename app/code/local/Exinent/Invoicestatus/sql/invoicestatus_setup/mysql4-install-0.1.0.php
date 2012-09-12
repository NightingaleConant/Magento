<?php

$installer = $this;

$installer->startSetup();

$installer->run(" 
    INSERT INTO  `{$this->getTable('sales/order_status')}` ( 
        `status`, 
        `label` 
    ) VALUES ( 
        'ready_to_print',  'Ready To Print' 
    ); 
    INSERT INTO  `{$this->getTable('sales/order_status_state')}` ( 
        `status`, 
        `state`, 
        `is_default` 
    ) VALUES ( 
        'ready_to_print',  'ready_to_print',  '0' 
    ); 
    INSERT INTO  `{$this->getTable('sales/order_status')}` ( 
        `status`, 
        `label` 
    ) VALUES ( 
        'printed',  'printed' 
    ); 
    INSERT INTO  `{$this->getTable('sales/order_status_state')}` ( 
        `status`, 
        `state`, 
        `is_default` 
    ) VALUES ( 
        'printed',  'printed',  '0' 
    );
");


$installer->endSetup();