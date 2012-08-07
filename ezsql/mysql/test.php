<?php

    include_once "../shared/ez_sql_core.php";
    include_once "ez_sql_mysql.php";
    $db = new ezSQL_mysql('nightingaleuser','elagnithgin','nightingale','localhost');

    $authors = $db->get_results("SELECT * FROM Authors");

    foreach ( $authors as $author ) {
        echo $author->Image. "\n";
        //echo $author->First_Name . ' ' . $author->Last_Name. "\n";
    }

?>
