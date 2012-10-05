<?php
$this->startSetup();
$this->run("
    ALTER TABLE custom_royalty_rates ADD COLUMN royalty_rate_id INT(11) PRIMARY KEY AUTO_INCREMENT FIRST;
");
$this->endSetup();
?>