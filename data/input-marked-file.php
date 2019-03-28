<?php
include_once(dirname(__FILE__).'/../public/public.php');
include_once(dirname(__FILE__).'/../public/csv_to_array.php');
include_once(dirname(__FILE__).'/../public/DataManager.class.php');

if ($_FILES["file"]["error"] > 0) {
    echo "上传错误！错误代码：" . $uploadErrors[$_FILES["file"]["error"]];
    echo '<br><br><a href="input.php">返回</a>';
} else {
    $filepath = "marked-data-files/".$_SESSION['userId'];
    if(!create_folders($filepath)){
        echo "创建文件夹失败！";
        echo '<br><br><a href="input.php">返回</a>';
        exit();
    }
    $filename = iconv('utf-8', 'gb2312', $filepath."/".$_FILES["file"]["name"]);
    if (file_exists($filename)){
        echo $_FILES["file"]["name"] . " 已经存在！";
        echo '<br><br><a href="input.php">返回</a>';
    } else {
        move_uploaded_file($_FILES["file"]["tmp_name"], $filename);
        $cc = csv_to_array(file_get_contents($filename));
        $db = new DataManager();
        $uploadId = $db->addUpload($_SESSION['userId'], $_FILES["file"]["name"], 2);
        $data = $db->getProps();
        $props = array();
        foreach($data as $r){
            $props[$r['text']] = intval($r['id']);
        }
        $idCol = 0;
        $colIDs = array();
        $colTitles = array();
        for($i=0; $i<count($cc[0]); $i++){
            if(isset($props[$cc[0][$i]])){
                array_push($colTitles, $cc[0][$i]);
                array_push($colIDs, $i);
                $cc[0][$i] = $props[$cc[0][$i]];  // 列名转换为内部id号
            }else if($cc[0][$i]=='ID'){
                $idCol = $i;
            }
        }
        for($i=1; $i<count($cc); $i++){
            foreach($colIDs as $col){
                $db->addResult($cc[$i][$idCol], $cc[0][$col], $_SESSION['userId'], sql_escape($cc[$i][$col]), 0, $uploadId);
            }
        }
        $db->close();
        echo "导入成功！已导入的属性有：" . implode('，', $colTitles);
        echo '<br><br><a href="../index.php">返回</a>';
    }
}
?>