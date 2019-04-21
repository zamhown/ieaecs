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

    $data = $db->getResultDiffDetail($propId);
    $db->close();

    // 确定列名
    $cols = array('ID', '病理诊断', '属性名');
    $users = array();
    $dataDic = array();
    foreach($data as $r){
        if(!isset($users[$r['uname']])){
            $users[$r['uname']] = count($users) + 2;
            array_push($cols, $r['uname'].'-抽取结果', $r['uname'].'-赞同率');
        }
        if(!isset($dataDic[$r['id']])){
            $dataDic[$r['id']] = array($r['id'], csv_escape($r['text']), csv_escape($props[$propId]));
        }
        $dataDic[$r['id']][$users[$r['uname']]*2-1] = csv_escape($r['rtext']);
        $dataDic[$r['id']][$users[$r['uname']]*2] = $r['agree_radio'] ? round($r['agree_radio']*100, 2).'%' : '0%';
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
    $filename = "$filepath/diff-detail-propid-$propId.csv";
    $csv = fopen($filename, "w");
    $cc = new CharsetConv('utf-8', 'utf-8bom');  // 加bom解决csv乱码问题
    fwrite($csv, $cc->convert(implode("\r\n", $csvData)));
    fclose($csv);
    header("Location: $filename");
}else{
    header("Location: ../index.php");
}
?>