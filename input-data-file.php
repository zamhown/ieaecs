<?php
include_once('public.php');
include_once('util/csv_to_array.php');
include_once('DataManager.class.php');

if ($_FILES["file"]["error"] > 0) {
    echo "上传错误！错误代码：" . $uploadErrors[$_FILES["file"]["error"]];
    echo '<br><br><a href="data-input.php">返回</a>';
} else {
    $filepath = "data/".$_SESSION['userId'];
    if(!create_folders($filepath)){
        echo "创建文件夹失败！";
        echo '<br><br><a href="data-input.php">返回</a>';
        exit();
    }
    $filename = iconv('utf-8', 'gb2312', $filepath."/".$_FILES["file"]["name"]);
    if (file_exists($filename)){
        echo $_FILES["file"]["name"] . " 已经存在！";
        echo '<br><br><a href="data-input.php">返回</a>';
    } else {
        move_uploaded_file($_FILES["file"]["tmp_name"], $filename);
        $cc = csv_to_array(file_get_contents($filename));
        $colID = array_search('ID', $cc[0]);
        $colText = array_search('病理诊断', $cc[0]);
        if($colID!==false && $colText!==false){
            $db = new DataManager();
            $uploadId = $db->addUpload($_SESSION['userId'], $_FILES["file"]["name"], 1);
            $props = $db->getProps();
            for($i=1; $i<count($cc); $i++){
                $db->addData($cc[$i][$colID], sql_escape($cc[$i][$colText]), $uploadId);
                foreach($props as $p){
                    $db->addPlaceholder($cc[$i][$colID], $p['id']);
                }
            }
            $db->close();
            echo "导入成功！";
            echo '<br><br><a href="index.php">返回</a>';
        }else{
            echo "数据格式不对！";
            echo '<br><br><a href="data-input.php">返回</a>';
        }
    }
}
?>