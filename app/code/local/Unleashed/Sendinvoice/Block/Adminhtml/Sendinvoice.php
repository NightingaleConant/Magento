<?php

class Unleashed_Sendinvoice_Block_Adminhtml_Sendinvoice extends Mage_Adminhtml_Block_Widget_Tabs
{

  
 public function __construct() {
        parent::__construct();
        $this->setTemplate('sendinvoice/export.phtml');
        $this->setFormAction(Mage::getUrl('*/*/update'));
    }

  protected function _beforeToHtml()
  {
      

     
      return parent::_beforeToHtml();
  }
  
  public function exportProducts(){
      //$dir = Mage::getBaseDir('media') . DS . "unleashed";
      $dir = Mage::getBaseDir('media') . DS . "unleashed" . DS ;
      
      set_time_limit(72000);
      
      $linelimit = 24998;
      $csvRow = "";
      
        $fileno = 0;
        $lineno = 0;
        $startofnewfile = true;
        $lastlineno = 0;
        
        $BOMfileno = 0;
        $BOMlineno = 0;
        $BOMstartofnewfile = true;
        $BOMlastlineno = 0;
      
      $csvRow = "*Product Code,*Product Description,Notes,Barcode,Units,Min Stock Alert Level,Max Stock Alert Level,Supplier Code,Supplier Name,Supplier Product Code,Default Purchase Price,Default Sell Price,Sell Price Tier 1,Sell Price Tier 2,Sell Price Tier 3,Sell Price Tier 4,Sell Price Tier 5,Sell Price Tier 6,Sell Price Tier 7,Sell Price Tier 8,Sell Price Tier 9,Sell Price Tier 10,Pack Size,Weight (kg),Width (metre),Height (metre),Depth (metre),*AverageLandPrice,Last Cost,Never Diminishing,Product Group,Xero Sales Account,Purchase Tax Rate,Sale Tax Rate,IsAssembledProduct,IsComponent,Is Api Product,Api Product Type"."\n";
      $SOHcsvRow = "*Product Code,*Warehouse Code,SOH"."\n";
      
    $resource = Mage::getSingleton('core/resource');
    $writeConnection = $resource->getConnection('core_write');
    $readConnection = $resource->getConnection('core_read');
    
    $skuarray = array();  
    
    
    $collection = Mage::getModel('catalog/product')->getCollection()
    ->addAttributeToSelect('sku')
    ->addAttributeToSelect('name')
    ->addAttributeToSelect('cost')
    ->addAttributeToSelect('price')
    ->addAttributeToSelect('weight');
    
    
    //$collection->addAttributeToSelect('url_key'); 
    foreach ($collection as $product) {
        
                
        if ($startofnewfile) {
        $startofnewfile = false;
        $lastlineno = 0;
//Create a file with unique name
        $file = $dir . "Product_" . $fileno . ".csv";
        $SOHfile = $dir . "SOH_" . $fileno . ".csv";
                
        
        echo $this->getFileButton($fileno);
        $fw = fopen($file, 'w');
        $SOHfw = fopen($SOHfile, 'w');
        
        $csvRow = "*Product Code,*Product Description,Notes,Barcode,Units,Min Stock Alert Level,Max Stock Alert Level,Supplier Code,Supplier Name,Supplier Product Code,Default Purchase Price,Default Sell Price,Sell Price Tier 1,Sell Price Tier 2,Sell Price Tier 3,Sell Price Tier 4,Sell Price Tier 5,Sell Price Tier 6,Sell Price Tier 7,Sell Price Tier 8,Sell Price Tier 9,Sell Price Tier 10,Pack Size,Weight (kg),Width (metre),Height (metre),Depth (metre),*AverageLandPrice,Last Cost,Never Diminishing,Product Group,Xero Sales Account,Purchase Tax Rate,Sale Tax Rate,IsAssembledProduct,IsComponent,Is Api Product,Api Product Type"."\n";
        $SOHcsvRow = "*Product Code,*Warehouse Code,SOH"."\n";
        
        
        $fwrite = fwrite($fw, $csvRow);
        $SOHfwrite = fwrite($SOHfw, $SOHcsvRow);
        
        }
        
        if ($BOMstartofnewfile) {
        $BOMstartofnewfile = false;
        $BOMlastlineno = 0;
//Create a file with unique name
        $BOMfile = $dir . "BOM_" . $BOMfileno . ".csv";
        //echo $this->getBOMFileButton($BOMfileno);
        $BOMfw = fopen($BOMfile, 'w');
        $BOMcsvRow = "*Assembled Product Code,*Component Product Code,*Quantity,Wastage Quantity"."\n";
        $BOMfwrite = fwrite($BOMfw, $BOMcsvRow);
        }
        
        
        
    if ($product->getTypeId() == "configurable") {
        
      $sku = str_replace(",","", $product->getSku());
      if(!$this->isDup($skuarray, $sku)){
      $name = str_replace(",","", $product->getName());
      $cost = str_replace(",","", $product->getCost());
      $price = str_replace(",","", $product->getPrice());
      $weight = str_replace(",","", $product->getWeight());
      
      $Qty = Mage::getModel('cataloginventory/stock_item')->loadByProduct($product)->getQty();
      if($Qty == "") $Qty = "0.0000";
        
      $prod_id = $product->getId();

      $query = "SELECT * FROM " . $resource->getTableName('cataloginventory_stock_item') . " WHERE product_id='{$prod_id}' LIMIT 1";
      $stock_data = $readConnection->fetchAll($query);
      
      if($stock_data[0]["use_config_notify_stock_qty"]==0)
          $notify_stock_below = $stock_data[0]["notify_stock_qty"];
      else $notify_stock_below = Mage::getStoreConfig('cataloginventory/item_options/notify_stock_qty');
      
      $api_type = "Configurable";
      $prod_cat = $this->getRootcat($product);
      $skuarray[]= $sku; //save the sku for checking duplicates
      if(isset($cost) && $cost!= NULL) $avgLandPrice = $cost;
      else $avgLandPrice = 0;
      
      $csvRow = $sku.",".$name.","."".","."".","."".",".$notify_stock_below.","."".","."".","."".","."".","."".",".$price.","."".","."".","."".","."".","."".","."".","."".","."".","."".","."".","."".",".$weight.","."".","."".","."".",".$avgLandPrice.",".$cost.","."".",".$prod_cat.","."".","."".","."".","."".","."".","."Yes".",".$api_type."\n";
      $SOHcsvRow = $sku.","."".",".$Qty."\n";
      
      $fwrite = fwrite($fw, $csvRow);
      $SOHfwrite = fwrite($SOHfw, $SOHcsvRow);
      $lineno++;
      if ($lineno == $linelimit) {
                    $lastlineno = $lineno;
                    fclose($fw);
                    fclose($SOHfw);
                    $lineno = 0;
                    $startofnewfile = true;
                    $fileno++;
                    
                    if ($startofnewfile) {
                    $startofnewfile = false;
                    $lastlineno = 0;
                    //Create a file with unique name
                    $file = $dir. "Product_" . $fileno . ".csv";
                    $SOHfile = $dir . "SOH_" . $fileno . ".csv";
                    
                    echo $this->getFileButton($fileno);
                    $fw = fopen($file, 'w');
                    $SOHfw = fopen($SOHfile, 'w');
                    $csvRow = "*Product Code,*Product Description,Notes,Barcode,Units,Min Stock Alert Level,Max Stock Alert Level,Supplier Code,Supplier Name,Supplier Product Code,Default Purchase Price,Default Sell Price,Sell Price Tier 1,Sell Price Tier 2,Sell Price Tier 3,Sell Price Tier 4,Sell Price Tier 5,Sell Price Tier 6,Sell Price Tier 7,Sell Price Tier 8,Sell Price Tier 9,Sell Price Tier 10,Pack Size,Weight (kg),Width (metre),Height (metre),Depth (metre),*AverageLandPrice,Last Cost,Never Diminishing,Product Group,Xero Sales Account,Purchase Tax Rate,Sale Tax Rate,IsAssembledProduct,IsComponent,Is Api Product,Api Product Type"."\n";
                    $SOHcsvRow = "*Product Code,*Warehouse Code,SOH"."\n";
                    $fwrite = fwrite($fw, $csvRow);
                    $SOHfwrite = fwrite($SOHfw, $SOHcsvRow);
                    }
                    
                    
                }
                
      
        
    $associated_products = $product->loadByAttribute('sku', $product->getSku())->getTypeInstance()->getUsedProducts();
    
    
    foreach ($associated_products as $assocProduct)
      {
      //$assocProduct =Mage::getModel('catalog/product')->load($assoc->getId());  
      //echo "<pre>";print_r($assocProduct);
      $sku = str_replace(",","", $assocProduct->getSku());  
      if(!$this->isDup($skuarray, $sku)){
      $name = str_replace(",","", $assocProduct->getName());
      $cost = str_replace(",","", $assocProduct->getCost());
      $price = str_replace(",","", $assocProduct->getPrice());
      $weight = str_replace(",","", $assocProduct->getWeight());
      $prod_id = str_replace(",","", $assocProduct->getId());
      
      $Qty = Mage::getModel('cataloginventory/stock_item')->loadByProduct($assocProduct)->getQty();
        if($Qty == "") $Qty = "0.0000";
      
      $query = "SELECT * FROM " . $resource->getTableName('cataloginventory_stock_item') . " WHERE product_id='{$prod_id}' LIMIT 1";
      $stock_data = $readConnection->fetchAll($query);
      
      if($stock_data[0]["use_config_notify_stock_qty"]==0)
          $notify_stock_below = $stock_data[0]["notify_stock_qty"];
      else $notify_stock_below = Mage::getStoreConfig('cataloginventory/item_options/notify_stock_qty');
      
      $api_type = "Simple";
      $prod_cat = $this->getRootcat($assocProduct);
      $skuarray[]= $sku; //save the sku for checking duplicates
      if(isset($cost) && $cost!= NULL) $avgLandPrice = $cost;
      else $avgLandPrice = 0;
      
      $csvRow = $sku.",".$name.","."".","."".","."".",".$notify_stock_below.","."".","."".","."".","."".","."".",".$price.","."".","."".","."".","."".","."".","."".","."".","."".","."".","."".","."".",".$weight.","."".","."".","."".",".$avgLandPrice.",".$cost.","."".",".$prod_cat.","."".","."".","."".","."".","."".","."Yes".",".$api_type."\n";
      $SOHcsvRow = $sku.","."".",".$Qty."\n";
      $fwrite = fwrite($fw, $csvRow);
      $SOHfwrite = fwrite($SOHfw, $SOHcsvRow);
      
      $lineno++;
      if ($lineno == $linelimit) {
                    $lastlineno = $lineno;
                    fclose($fw);
                    fclose($SOHfw);
                    $lineno = 0;
                    $startofnewfile = true;
                    $fileno++;
                    
                    if ($startofnewfile) {
                    $startofnewfile = false;
                    $lastlineno = 0;
                    //Create a file with unique name
                    $file = $dir . "Product_" . $fileno . ".csv";
                    $SOHfile = $dir . "SOH_" . $fileno . ".csv";
                    
                    echo $this->getFileButton($fileno);
                    $fw = fopen($file, 'w');
                    $SOHfw = fopen($SOHfile, 'w');
                    $csvRow = "*Product Code,*Product Description,Notes,Barcode,Units,Min Stock Alert Level,Max Stock Alert Level,Supplier Code,Supplier Name,Supplier Product Code,Default Purchase Price,Default Sell Price,Sell Price Tier 1,Sell Price Tier 2,Sell Price Tier 3,Sell Price Tier 4,Sell Price Tier 5,Sell Price Tier 6,Sell Price Tier 7,Sell Price Tier 8,Sell Price Tier 9,Sell Price Tier 10,Pack Size,Weight (kg),Width (metre),Height (metre),Depth (metre),*AverageLandPrice,Last Cost,Never Diminishing,Product Group,Xero Sales Account,Purchase Tax Rate,Sale Tax Rate,IsAssembledProduct,IsComponent,Is Api Product,Api Product Type"."\n";
                    $SOHcsvRow = "*Product Code,*Warehouse Code,SOH"."\n";
                    $fwrite = fwrite($fw, $csvRow);
                    $SOHfwrite = fwrite($SOHfw, $SOHcsvRow);
                    }
                    
                }
        }//if not dup
      }
    }//if not dup
    }// if configurable
        
   //++++++++++++++++++++++++++++++___ BUNDLE ___ ++++++++++++++++++++++++++     
        
    else  if ($product->getTypeId() == "bundle") {
        
      $sku = str_replace(",","", $product->getSku());
      if(!$this->isDup($skuarray, $sku)){
      $name = str_replace(",","", $product->getName());
      $cost = str_replace(",","", $product->getCost());
      $price = str_replace(",","", $product->getPrice());
      $weight = str_replace(",","", $product->getWeight());
      
      
      $prod_id = $product->getId();
      
      $query = "SELECT * FROM " . $resource->getTableName('cataloginventory_stock_item') . " WHERE product_id='{$prod_id}' LIMIT 1";
      $stock_data = $readConnection->fetchAll($query);
      
      if($stock_data[0]["use_config_notify_stock_qty"]==0)
          $notify_stock_below = $stock_data[0]["notify_stock_qty"];
      else $notify_stock_below = Mage::getStoreConfig('cataloginventory/item_options/notify_stock_qty');
      
      $api_type = "Bundle";
      $prod_cat = $this->getRootcat($product);
      $skuarray[]= $sku; //save the sku for checking duplicates
      if(isset($cost) && $cost!= NULL) $avgLandPrice = $cost;
      else $avgLandPrice = 0;
      
      $Qty = Mage::getModel('cataloginventory/stock_item')->loadByProduct($product)->getQty();
        if($Qty == "") $Qty = "0.0000";
      
      $csvRow = $sku.",".$name.","."".","."".","."".",".$notify_stock_below.","."".","."".","."".","."".","."".",".$price.","."".","."".","."".","."".","."".","."".","."".","."".","."".","."".","."".",".$weight.","."".","."".","."".",".$avgLandPrice.",".$cost.","."".",".$prod_cat.","."".","."".","."".","."".","."".","."Yes".",".$api_type."\n";
      $fwrite = fwrite($fw, $csvRow);
      $SOHcsvRow = $sku.","."".",".$Qty."\n";
      $SOHfwrite = fwrite($SOHfw, $SOHcsvRow);
      $lineno++;
      
      
      $BOMcsvRow = $sku.",".$sku.",".$Qty.",".""."\n";
      $BOMfwrite = fwrite($BOMfw, $BOMcsvRow);
      $BOMlineno++;
      
      if ($lineno == $linelimit) {
                    $lastlineno = $lineno;
                    fclose($fw);
                    fclose($SOHfw);
                    $lineno = 0;
                    $startofnewfile = true;
                    $fileno++;
                    
                    if ($startofnewfile) {
                    $startofnewfile = false;
                    $lastlineno = 0;
                    //Create a file with unique name
                    $file = $dir . "Product_" . $fileno . ".csv";
                    $SOHfile = $dir . "SOH_" . $fileno . ".csv";
                    
                    echo $this->getFileButton($fileno);
                    $fw = fopen($file, 'w');
                    $csvRow = "*Product Code,*Product Description,Notes,Barcode,Units,Min Stock Alert Level,Max Stock Alert Level,Supplier Code,Supplier Name,Supplier Product Code,Default Purchase Price,Default Sell Price,Sell Price Tier 1,Sell Price Tier 2,Sell Price Tier 3,Sell Price Tier 4,Sell Price Tier 5,Sell Price Tier 6,Sell Price Tier 7,Sell Price Tier 8,Sell Price Tier 9,Sell Price Tier 10,Pack Size,Weight (kg),Width (metre),Height (metre),Depth (metre),*AverageLandPrice,Last Cost,Never Diminishing,Product Group,Xero Sales Account,Purchase Tax Rate,Sale Tax Rate,IsAssembledProduct,IsComponent,Is Api Product,Api Product Type"."\n";
                    $fwrite = fwrite($fw, $csvRow);
                    
                    $SOHfw = fopen($SOHfile, 'w');
                    $SOHcsvRow = "*Product Code,*Warehouse Code,SOH"."\n";
                    $SOHfwrite = fwrite($SOHfw, $SOHcsvRow);
                    }
                    
                    
                }
                
        if ($BOMlineno == $linelimit) {
                    $BOMlastlineno = $BOMlineno;
                    fclose($BOMfw);
                    $BOMlineno = 0;
                    $BOMstartofnewfile = true;
                    $BOMfileno++;
                    
                    if ($BOMstartofnewfile) {
                    $BOMstartofnewfile = false;
                    $BOMlastlineno = 0;
                    //Create a file with unique name
                    $BOMfile = $dir . "BOM_" . $BOMfileno . ".csv";
                    
                    echo $this->getBOMFileButton($BOMfileno);
                    
                    $BOMfw = fopen($BOMfile, 'w');
                    $BOMcsvRow = "*Assembled Product Code,*Component Product Code,*Quantity,Wastage Quantity"."\n";
                    $BOMfwrite = fwrite($BOMfw, $BOMcsvRow);
                    }

        }
                
      
    
    $bundled_product = new Mage_Catalog_Model_Product();
    $bundled_product->load($product->getId());
    $selectionCollection = $bundled_product->getTypeInstance(true)->getSelectionsCollection(
        $bundled_product->getTypeInstance(true)->getOptionsIds($bundled_product), $bundled_product
    )->addAttributeToSelect('cost')
     ->addAttributeToSelect('weight');
    
    foreach ($selectionCollection as $assocProduct)
      {
        
      //$assocProduct =Mage::getModel('catalog/product')->load($assoc->getId());   
      $sku = str_replace(",","", $assocProduct->getSku());  
      if(!$this->isDup($skuarray, $sku)){
      $name = str_replace(",","", $assocProduct->getName());
      $cost = str_replace(",","", $assocProduct->getCost());
      $price = str_replace(",","", $assocProduct->getPrice());
      $weight = str_replace(",","", $assocProduct->getWeight());
      $prod_id = str_replace(",","", $assocProduct->getId());
      
      $Qty = Mage::getModel('cataloginventory/stock_item')->loadByProduct($assocProduct)->getQty();
        if($Qty == "") $Qty = "0.0000";
      
      $query = "SELECT * FROM " . $resource->getTableName('cataloginventory_stock_item') . " WHERE product_id='{$prod_id}' LIMIT 1";
      $stock_data = $readConnection->fetchAll($query);
      
      if($stock_data[0]["use_config_notify_stock_qty"]==0)
          $notify_stock_below = $stock_data[0]["notify_stock_qty"];
      else $notify_stock_below = Mage::getStoreConfig('cataloginventory/item_options/notify_stock_qty');
      
      $api_type = "Simple";
      $prod_cat = $this->getRootcat($assocProduct);
      $skuarray[]= $sku; //save the sku for checking duplicates
      if(isset($cost) && $cost!= NULL) $avgLandPrice = $cost;
      else $avgLandPrice = 0;
      
      $csvRow = $sku.",".$name.","."".","."".","."".",".$notify_stock_below.","."".","."".","."".","."".","."".",".$price.","."".","."".","."".","."".","."".","."".","."".","."".","."".","."".","."".",".$weight.","."".","."".","."".",".$avgLandPrice.",".$cost.","."".",".$prod_cat.","."".","."".","."".","."".","."".","."Yes".",".$api_type."\n";
      $fwrite = fwrite($fw, $csvRow);
      
      $SOHcsvRow = $sku.","."".",".$Qty."\n";
      $SOHfwrite = fwrite($SOHfw, $SOHcsvRow);
      $lineno++;
      
      //$BOMcsvRow = "".",".$sku.",".$Qty.",".""."\n";
      //$BOMfwrite = fwrite($BOMfw, $BOMcsvRow);
      //$BOMlineno++;
      
      if ($lineno == $linelimit) {
                    $lastlineno = $lineno;
                    fclose($fw);
                    fclose($SOHfw);
                    
                    $lineno = 0;
                    $startofnewfile = true;
                    $fileno++;
                    
                    if ($startofnewfile) {
                    $startofnewfile = false;
                    $lastlineno = 0;
                    //Create a file with unique name
                    $file = $dir. "Product_" . $fileno . ".csv";
                    $SOHfile = $dir . "SOH_" . $fileno . ".csv";
                    
                    //echo $this->getFileButton($fileno);
                    $fw = fopen($file, 'w');
                    $csvRow = "*Product Code,*Product Description,Notes,Barcode,Units,Min Stock Alert Level,Max Stock Alert Level,Supplier Code,Supplier Name,Supplier Product Code,Default Purchase Price,Default Sell Price,Sell Price Tier 1,Sell Price Tier 2,Sell Price Tier 3,Sell Price Tier 4,Sell Price Tier 5,Sell Price Tier 6,Sell Price Tier 7,Sell Price Tier 8,Sell Price Tier 9,Sell Price Tier 10,Pack Size,Weight (kg),Width (metre),Height (metre),Depth (metre),*AverageLandPrice,Last Cost,Never Diminishing,Product Group,Xero Sales Account,Purchase Tax Rate,Sale Tax Rate,IsAssembledProduct,IsComponent,Is Api Product,Api Product Type"."\n";
                    $fwrite = fwrite($fw, $csvRow);
                    
                    $SOHfw = fopen($SOHfile, 'w');
                    $SOHcsvRow = "*Product Code,*Warehouse Code,SOH"."\n";
                    $SOHfwrite = fwrite($SOHfw, $SOHcsvRow);
                    }
                }
                
                
        }//if not dup
        
        $BOMcsvRow = "".",".$sku.",".$Qty.",".""."\n";
        $BOMfwrite = fwrite($BOMfw, $BOMcsvRow);
        $BOMlineno++;
        
        if ($BOMlineno == $linelimit) {
                    $BOMlastlineno = $BOMlineno;
                    fclose($BOMfw);
                    
                    $BOMlineno = 0;
                    $BOMstartofnewfile = true;
                    $BOMfileno++;
                    
                    if ($BOMstartofnewfile) {
                    $BOMstartofnewfile = false;
                    $BOMlastlineno = 0;
                    //Create a file with unique name
                    $BOMfile = $dir . "BOM_" . $BOMfileno . ".csv";
                    
                    //echo $this->getBOMFileButton($BOMfileno);
                   
                    $BOMfw = fopen($BOMfile, 'w');
                    $BOMcsvRow = "*Assembled Product Code,*Component Product Code,*Quantity,Wastage Quantity"."\n";
                    $BOMfwrite = fwrite($BOMfw, $BOMcsvRow);
                    }
                }
        
        
      }
    }//if not dup
    }// if bundle    
        
        
   //++++++++++++++++++++++++++++++___ SIMPLE ___ ++++++++++++++++++++++++++          
    else if ($product->getTypeId() == "simple") {
        
      $sku = str_replace(",","", $product->getSku());
      if(!$this->isDup($skuarray, $sku)){
      $name = str_replace(",","", $product->getName());
      $cost = str_replace(",","", $product->getCost());
      $price = str_replace(",","", $product->getPrice());
      $weight = str_replace(",","", $product->getWeight());
      
      $Qty = Mage::getModel('cataloginventory/stock_item')->loadByProduct($product)->getQty();
      if($Qty == "") $Qty = "0.0000";
      
      $prod_id = $product->getId();
      
      $query = "SELECT * FROM " . $resource->getTableName('cataloginventory_stock_item') . " WHERE product_id='{$prod_id}' LIMIT 1";
      $stock_data = $readConnection->fetchAll($query);
      
      if($stock_data[0]["use_config_notify_stock_qty"]==0)
          $notify_stock_below = $stock_data[0]["notify_stock_qty"];
      else $notify_stock_below = Mage::getStoreConfig('cataloginventory/item_options/notify_stock_qty');
      
      $api_type = "Simple";
      $prod_cat = $this->getRootcat($product);
          
      $skuarray[]= $sku; //save the sku for checking duplicates
      if(isset($cost) && $cost!= NULL) $avgLandPrice = $cost;
      else $avgLandPrice = 0;
      
      $csvRow = $sku.",".$name.","."".","."".","."".",".$notify_stock_below.","."".","."".","."".","."".","."".",".$price.","."".","."".","."".","."".","."".","."".","."".","."".","."".","."".","."".",".$weight.","."".","."".","."".",".$avgLandPrice.",".$cost.","."".",".$prod_cat.","."".","."".","."".","."".","."".","."Yes".",".$api_type."\n";
      $fwrite = fwrite($fw, $csvRow);
      
      $SOHcsvRow = $sku.","."".",".$Qty."\n";
      $SOHfwrite = fwrite($SOHfw, $SOHcsvRow);
      $lineno++;
      if ($lineno == $linelimit) {
                    $lastlineno = $lineno;
                    fclose($fw);
                    fclose($SOHfw);
                    $lineno = 0;
                    $startofnewfile = true;
                    $fileno++;
                    
                    if ($startofnewfile) {
                    $startofnewfile = false;
                    $lastlineno = 0;
                    //Create a file with unique name
                    $file = $dir . "Product_" . $fileno . ".csv";
                    $SOHfile = $dir . "SOH_" . $fileno . ".csv";
                    
                    echo $this->getFileButton($fileno);
                    
                    $fw = fopen($file, 'w');
                    $csvRow = "*Product Code,*Product Description,Notes,Barcode,Units,Min Stock Alert Level,Max Stock Alert Level,Supplier Code,Supplier Name,Supplier Product Code,Default Purchase Price,Default Sell Price,Sell Price Tier 1,Sell Price Tier 2,Sell Price Tier 3,Sell Price Tier 4,Sell Price Tier 5,Sell Price Tier 6,Sell Price Tier 7,Sell Price Tier 8,Sell Price Tier 9,Sell Price Tier 10,Pack Size,Weight (kg),Width (metre),Height (metre),Depth (metre),*AverageLandPrice,Last Cost,Never Diminishing,Product Group,Xero Sales Account,Purchase Tax Rate,Sale Tax Rate,IsAssembledProduct,IsComponent,Is Api Product,Api Product Type"."\n";
                    $fwrite = fwrite($fw, $csvRow);
                    
                    $SOHfw = fopen($SOHfile, 'w');
                    $SOHcsvRow = "*Product Code,*Warehouse Code,SOH"."\n";
                    $SOHfwrite = fwrite($SOHfw, $SOHcsvRow);
                    }
                    
                    
                }
      }
      
     }
        

    }
    
     for($i = 0; $i <= $BOMfileno; $i++){
         echo $this->getBOMFileButton($i);
     } 
     
     
    if ($lastlineno == 0) {
                fclose($fw);
            }
            
    if ($BOMlastlineno == 0) {
                fclose($BOMfw);
            }

    
    
  }
  
  public function isDup($t_sku, $s_item){
      
      $found = false;
      if(count($t_sku)>0){
          foreach($t_sku as $sku){
              if($sku == $s_item)
                  $found = true;
          }
          if($found)
          return true;
      else return false;
          
      }else return false;
     
      
  }
  
  public function getFileButton($fileno){
      $buttonhtml = '<a class="expbutton" href="'.Mage::getBaseUrl('media') . DS . "unleashed" . DS . "Product_" . $fileno . ".csv".'"><button class="scalable " style="" type="button"><span>Export Product_'.$fileno.'.csv</span></button></a>';
      $buttonhtml .= '<a class="sohbutton" href="'.Mage::getBaseUrl('media') . DS . "unleashed" . DS . "SOH_" . $fileno . ".csv".'"><button class="scalable " style="" type="button"><span>Export SOH_'.$fileno.'.csv</span></button></a><br/><br/>';
      return $buttonhtml;
  }
  
    public function getBOMFileButton($fileno){
      $buttonhtml = '<a class="bombutton" href="'.Mage::getBaseUrl('media') . DS . "unleashed" . DS . "BOM_" . $fileno . ".csv".'"><button class="scalable " style="" type="button"><span>Export BOM_'.$fileno.'.csv</span></button></a><br/><br/>';
      return $buttonhtml;
  }
  
  public function getRootcat($product){
      
      $categoryIds = $product->getCategoryIds();
      
      $catName = "";
      $cid = $categoryIds[0];
      if($cid!= NULL){
      while(Mage::getModel('catalog/category')->load($cid)->parent_id != 1){
          $pcid = Mage::getModel('catalog/category')->load($cid)->parent_id;
          
          $cid = $pcid;
          $catName = Mage::getModel('catalog/category')->load($pcid)->getName();
      }
      }
      return $catName;
 
  }
  
  
  
  
  
  
}
