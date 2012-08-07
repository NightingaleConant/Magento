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

    $subcategory['entity_id'] = 10000 + (int)$author->Author_ID;
    $subcategory['name'] = $author->Name;
    $subcategory['path'] = "1/4";
    $subcategory['description'] = $author->Bio;
    $subcategory['meta_title'] = "";
    $subcategory['meta_keywords'] = $author->Meta_Keywords;
    $subcategory['meta_description'] = $author->Meta_Description;
    $subcategory['image'] = $author->Image_Thumbnail;
    $subcategory['landing_page'] = "";
    $subcategory['display_mode'] = "PRODUCTS";
    $subcategory['is_active'] = 1;
    $subcategory['is_anchor'] = 1;
    $subcategory['url_key'] = "";

    $category->addData($subcategory);

    try {
        $category->save();
        echo "<p><strong>".$category->getName()." (".$category->getId().")</strong> - Category Added</p>";
    }
    catch (Exception $e){
        echo $e->getMessage() . 'Cat: ';
    }
}

?>
