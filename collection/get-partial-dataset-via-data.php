<?php
include_once(dirname(__FILE__).'/../public/public.php');
include_once(dirname(__FILE__).'/../public/DataManager.class.php');

$db = new DataManager();
$props = $db->getProps();

$data = $db->getCompleteResultDetailViaData();
$db->close();

// 确定列名
$cols = array('ID', '病理诊断');
foreach($props as $r){
    array_push($cols, $r['text']);
}
$dataDic = array();
foreach($data as $r){
    $row = array(
        $r['id'],
        csv_escape($r['text'])
    );
    foreach($props as $p){
        array_push($row, csv_escape($r['prop_'.$p['id']]['rtext']));
    }
    array_push($dataDic, $row);
}
$csvData = array(implode(',', $cols));
foreach($dataDic as $v){
    for($i=0;$i<count($cols);$i++){
        if(!isset($v[$i])){
            $v[$i] = '';
        }
    }
    array_push($csvData, implode(',', $v));
}

$filepath = "../tmp-files/".$_SESSION['userId'];
if(!create_folders($filepath)){
    echo "创建文件夹失败！";
    echo '<br><br><a href="../input.php">返回</a>';
    exit();
}
$filename = "$filepath/partial-dataset.csv";
$csv = fopen($filename, "w");
fwrite($csv, implode("\r\n", $csvData));
fclose($csv);
header("Location: $filename");
?>