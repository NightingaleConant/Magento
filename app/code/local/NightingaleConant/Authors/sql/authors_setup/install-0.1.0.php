<?php
$installer = $this;
$installer->startSetup();
$installer->run("
    CREATE TABLE `custom_authors` (
        `author_id` int(11) PRIMARY KEY AUTO_INCREMENT NOT NULL,
        `first_name` varchar(255) NULL,
        `last_name` varchar(255) NULL,
        `display_name` varchar(1000) NULL,
        `bio` varchar(1000) NULL,
        `image` varchar(255) NULL,
        `meta_title` varchar(255) NULL,
        `meta_description` varchar(255) NULL,
        `meta_keywords` varchar(255) NULL,
        `author_link_text` varchar(255) NULL,
        `author_page_title` varchar(1000) NULL,
        `bio_link_text` varchar(255) NULL,
        `bio_page_title` varchar(1000) NULL,
        `active` boolean NOT NULL DEFAULT 0,
        `created_at` datetime NULL,
        `created_by` int(11) NOT NULL DEFAULT 0,
        `updated_at` datetime NULL,
        `updated_by` int(11) NOT NULL DEFAULT 0
    ) ENGINE=InnoDB;
    
    UPDATE `eav_attribute` SET `source_model` = 'authors/authors' WHERE `attribute_code` = 'author_id';

");
$installer->endSetup();
?>
