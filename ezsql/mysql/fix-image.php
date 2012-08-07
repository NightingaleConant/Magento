<?php

require_once '../../app/Mage.php';
Mage::app('default');

include_once "../shared/ez_sql_core.php";
include_once "ez_sql_mysql.php";
$db = new ezSQL_mysql('nightingaleuser','elagnithgin','nightingale','localhost');

$authors = $db->get_results("SELECT * FROM Authors");

foreach ( $authors as $author ) {

    $category = Mage::getModel('catalog/category');
    $category->setStoreId(0); //default/all

    $category->load(10000 + (int)$author->Author_ID);

    $category['image'] = $author->Image_Thumbnail;
    $category->save();
    print $author->Name;
    exit();
}

?>
