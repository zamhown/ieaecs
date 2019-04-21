<?php
include_once(dirname(__FILE__).'/../public/public.php');
include_once(dirname(__FILE__).'/../public/DataManager.class.php');
include_once(dirname(__FILE__).'/../public/CharsetConv.class.php');

if(isset($_GET['propid']) && $_GET['propid']){
    $propId = $_GET['propid'];
    $db = new DataManager();
    $data = $db->getProps();
    $props = array();
    foreach($data as $r){
        $props[$r['id']] = $r['text'];
    }

    $data = $db->getCompleteResultDetailViaProps($propId);
    $db->close();

    // 确定列名
    $cols = array('ID', '病理诊断', '属性名', '抽取结果', '赞同率', '赞同数', '抽取人');
    $dataDic = array();
    foreach($data as $r){
        array_push($dataDic, array(
            $r['id'],
            csv_escape($r['text']),
            csv_escape($props[$propId]),
            csv_escape($r['rtext']),
            $r['agree_radio'] ? round($r['agree_radio']*100, 2).'%' : '0%',
            $r['agree_count'],
            csv_escape($r['unames'])
        ));
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
    $filename = "$filepath/partial-dataset-propid-$propId.csv";
    $csv = fopen($filename, "w");
    $cc = new CharsetConv('utf-8', 'utf-8bom');  // 加bom解决csv乱码问题
    fwrite($csv, $cc->convert(implode("\r\n", $csvData)));
    fclose($csv);
    header("Location: $filename");
}else{
    header("Location: ../index.php");
}
?>