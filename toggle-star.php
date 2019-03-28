<?php
include_once('public.php');
include_once('DataManager.class.php');

if(isset($_POST['phId']) && $_POST['phId']){
    $db = new DataManager();
    $db->toggleStar($_POST['phId'], $_SESSION['userId']);
    if($_POST['from']=='judge'){
        header("Location: judge.php?phid=".$_POST['phId']);
    }else if($_POST['from']=='star-record'){
        header("Location: star-record.php");
    }
}else{
    header("Location: index.php");
}
?>