<?php
include_once('public.php');
include_once('DataManager.class.php');

if(isset($_POST['id']) && $_POST['id']){
    $db = new DataManager();
    $r = $db->getUpload($_POST['id']);
    $filename;
    if($r['type']==1){
        $filename = "data/".$r['user_id']."/".$r['filename'];
    }else if($r['type']==2){
        $filename = "marked-data/".$r['user_id']."/".$r['filename'];
    }
    @unlink(iconv('utf-8', 'gb2312', $filename));
    $db->delUpload($_POST['id']);
}
header("Location: data-input.php");
?>