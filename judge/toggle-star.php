<?php
include_once(dirname(__FILE__).'/../public/public.php');
include_once(dirname(__FILE__).'/../public/DataManager.class.php');

if(isset($_POST['phId']) && $_POST['phId']){
    $db = new DataManager();
    $db->toggleStar($_POST['phId'], $_SESSION['userId']);
    if($_POST['from']=='judge'){
        header("Location: check.php?phid=".$_POST['phId']);
    }else if($_POST['from']=='star-record'){
        header("Location: ../user/star-record.php");
    }
}else{
    header("Location: ../index.php");
}
?>