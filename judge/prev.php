<?php
include_once(dirname(__FILE__).'/../public/public.php');

$type = 0;  // 行为类型，0为默认（分歧+入库），1为检测分歧，2为入库
if(isset($_GET['type'])){
    $type = intval($_GET['type']);
}

array_pop($_SESSION["record$type"]);
if(count($_SESSION["record$type"])){
    $r = $_SESSION["record$type"][count($_SESSION["record$type"])-1];
    header("Location: check.php?phid=".$r['phId']."&type=$type");
}else{
    header("Location: ../index.php");
}
?>