<?php
include_once(dirname(__FILE__).'/../public/public.php');
include_once(dirname(__FILE__).'/../public/DataManager.class.php');

if(isset($_POST['id']) && $_POST['id']){
    $db = new DataManager();
    $r = $db->getUpload($_POST['id']);
    $filename;
    if($r['type']==1){
        $filename = "data-files/".$r['user_id']."/".$r['filename'];
    }else if($r['type']==2){
        $filename = "marked-data-files/".$r['user_id']."/".$r['filename'];
    }
    @unlink(iconv('utf-8', 'gb2312', $filename));
    $db->delUpload($_POST['id']);
    $db->close();
}
header("Location: input.php");
?>