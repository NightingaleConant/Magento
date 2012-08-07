<?php

// initialize magento environment for 'default' store
  require_once '../../app/Mage.php';
  Mage::app('default');

// add categories here
  $categories = array(

  "Clothing",
  "Electrical",
  "Homeware"

  );

  foreach ($categories as $cat) {

      // get a new category object
      $category = Mage::getModel('catalog/category');
      $category->setStoreId(0);

      $category->load($id);

      $toplevel['name'] = $cat;
      $toplevel['path'] = "1/2"; // this is the catgeory path - normally 1/2 for root (default category)
      $toplevel['description'] = "";
      $toplevel['meta_title'] = "";
      $toplevel['meta_keywords'] = "";
      $toplevel['meta_description'] = "";
      $toplevel['landing_page'] = "";
      $toplevel['display_mode'] = "PRODUCTS";
      $toplevel['is_active'] = 1;
      $toplevel['is_anchor'] = 0;
      $toplevel['url_key'] = "";

      $category->addData($toplevel);

      try {
          $category->save();
          echo "<p><strong>".$category->getName()." (".$category->getId().")</strong> - Category Added</p>";
      }
      catch (Exception $e){
          echo $e->getMessage();
      }

 }

?>
