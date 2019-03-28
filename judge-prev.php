<?php
include_once('public.php');

array_pop($_SESSION['record']);
if(count($_SESSION['record'])){
    $r = $_SESSION['record'][count($_SESSION['record'])-1];
    header("Location: judge.php?phid=".$r['phId']);
}else{
    header("Location: index.php");
}
?>