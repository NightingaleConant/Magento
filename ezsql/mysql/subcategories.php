<?php

// initialize magento environment for 'default' store
require_once '../../app/Mage.php';
Mage::app('default');

$categories = array(

    // Categories - add your category name, and the ID of the category it belongs in
    array(
        'name' => "T Shirts",
        'parent_id' => 4
    )
);

function subval_sort($a,$subkey) {
    foreach($a as $k=>$v) {
        $b[$k] = strtolower($v[$subkey]);
    }
    asort($b);
    foreach($b as $key=>$val) {
        $c[] = $a[$key];
    }
    return $c;
}

$categories = subval_sort($categories,'name');

foreach ($categories as $cat) {

    //get a new category object
    $category = Mage::getModel('catalog/category');
    $category->setStoreId(0); //default/all

    //if update
    if ($id) {
        $category->load($id);
    }

    if($cat['parent_id']) { $parent = '/'.$cat['parent_id']; } else { $parent = ""; }

    $subcategory['entity_id'] = 21;
    $subcategory['name'] = $cat['name'];
    $subcategory['path'] = "1/4"; //.$parent; // Append other catgeory ids for subcatgeories (e.g 1/2/13 - where 13 is the subcategory ID)
    $subcategory['description'] = "Description";
    $subcategory['meta_title'] = "meta";
    $subcategory['meta_keywords'] = "key";
    $subcategory['meta_description'] = "desc";
    $subcategory['thumb'] = "1.1_1.jpg";
    $subcategory['image'] = "1.1.jpg";
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
        echo $e->getMessage();
    }

}

?>
