<?php
include_once(dirname(__FILE__).'/../public/public.php');
include_once(dirname(__FILE__).'/../public/DataManager.class.php');

if(isset($_POST['phId']) && $_POST['phId']){
    $type = 0;  // 行为类型，0为默认（分歧+入库），1为检测分歧，2为入库
    if(isset($_GET['type'])){
        $type = intval($_GET['type']);
    }

    $db = new DataManager();
    $db->toggleStar($_POST['phId'], $_SESSION['userId']);
    $db->close();
    if($_POST['from']=='judge'){
        header("Location: check.php?phid=".$_POST['phId']."&type=$type");
    }else if($_POST['from']=='star-record'){
        header("Location: ../user/star-record.php");
    }
}else{
    header("Location: ../index.php");
}
?>