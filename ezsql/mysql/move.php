<?php

require_once '../../app/Mage.php';
Mage::app('default');

include_once "../shared/ez_sql_core.php";
include_once "ez_sql_mysql.php";
$db = new ezSQL_mysql('nightingaleuser','elagnithgin','nightingale','localhost');

$authors = $db->get_results("SELECT * FROM Authors");

foreach ( $authors as $author ) {
    $parentId = 4;
    $categoryId = 10000 + (int)$author->Author_ID;

    $category = Mage::getModel('catalog/category')->load($categoryId);
    $category['path'] = '';
    $category['path'] = '1/4/'.$catgoryId;
    $category->move($parentId, null);
//
//    $category = Mage::getModel('catalog/category')->load($categoryId);
//    $category->move($parentId, null);
//
}
/*
    $categoryId = 10332;
    $parentId = 4;

    $category = Mage::getModel('catalog/category')->load($categoryId);
    $category->move($parentId, null);
    $category['path'] = '1/4/'.$catgoryId;
 */

?>
