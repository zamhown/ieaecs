<?php
include_once(dirname(__FILE__).'/../public/public.php');
include_once(dirname(__FILE__).'/../public/DataManager.class.php');
include_once(dirname(__FILE__).'/../public/CharsetConv.class.php');

if(!isset($_SESSION['userAdmin']) || !$_SESSION['userAdmin']) {
    echo "权限不足！";
    echo '<br><br><a href="../index.php">返回</a>';
    exit();
}

$db = new DataManager();
$data = $db->getProps();
$props = array();
$userProps = array();
foreach($data as $r){
    $props[$r['id']] = $r['text'];
    array_push($userProps, $r['id']);
}
if(isset($_POST['props'])){
    $userProps = explode(',', $_POST['props']);
}

$data = $db->getCompleteResultDetailViaData($userProps);
$db->close();

// 确定列名
$cols = array('ID', '病理诊断');
foreach($userProps as $pid){
    array_push($cols, $props[$pid]);
}
$dataDic = array();
foreach($data as $r){
    $row = array(
        $r['id'],
        csv_escape($r['text'])
    );
    foreach($userProps as $pid){
        array_push($row, csv_escape($r["prop_$pid"]['rtext']));
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
$filename = "$filepath/partial-dataset-".implode('-', $userProps).".csv";
$csv = fopen($filename, "w");
$cc = new CharsetConv('utf-8', 'utf-8bom');  // 加bom解决csv乱码问题
fwrite($csv, $cc->convert(implode("\r\n", $csvData)));
fclose($csv);
header("Location: $filename");
?>