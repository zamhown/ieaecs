<?php
include_once(dirname(__FILE__).'/../public/public.php');

array_pop($_SESSION['record']);
if(count($_SESSION['record'])){
    $r = $_SESSION['record'][count($_SESSION['record'])-1];
    header("Location: check.php?phid=".$r['phId']);
}else{
    header("Location: ../index.php");
}
?>