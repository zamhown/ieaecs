<?php
include_once(dirname(__FILE__).'/../public/public.php');
include_once(dirname(__FILE__).'/../public/DataManager.class.php');

$db = new DataManager();
$type = 0;  // 行为类型，0为默认（分歧+入库），1为检测分歧，2为入库
if(isset($_GET['type'])){
    $type = intval($_GET['type']);
}

// 系统分配下一批目标
function nextBatch($db, $type){
    $data = $db->nextPlaceholder($_SESSION['userId'], $_SESSION['userProps'], $type, 100);
    if($data===false){
        echo "没有需要检测的数据！";
        echo '<br><br><a href="../index.php">返回</a>';
        exit();
    }
    $_SESSION["batch$type"] = $data;
    $_SESSION["batchPtr$type"] = 0;
}

// 系统分配下一个目标
function nextTarget($db, $type){
    if(!isset($_SESSION["batch$type"])
    || !isset($_SESSION["batchPtr$type"])
    || $_SESSION["batchPtr$type"]>=count($_SESSION["batch$type"])){
        nextBatch($db, $type);
    }
    return $_SESSION["batch$type"][$_SESSION["batchPtr$type"]];
}

// 改变用户擅长的属性类型
if(isset($_POST['props'])){
    $userProps = implode(",", $_POST['props']);
    $db->updateUserProps($_SESSION['userId'], $userProps);
    $_SESSION['userProps'] = $userProps;
}
if(!isset($_SESSION['userProps'])){
    $_SESSION['userProps'] = '';
}

// 储存/更改检测结果
if(isset($_POST['rlist'])){
    $rlist = json_decode($_POST['rlist'], true);
    foreach($rlist as $r){
        if(isset($_POST['r'.$r]) && $_POST['r'.$r]){
            $db->addJudge($r, $_SESSION['userId'], $_POST['r'.$r], $type==2?1:0);
        }
    }
    if(isset($_POST['amendment']) && $_POST['amendment']){
        $db->addResult($_POST['phId'], $_SESSION['userId'], $_POST['amendment'], 1);
    }
    // 历史记录与任务分配重合，则继续任务
    if(isset($_SESSION["batchPtr$type"])
    && isset($_SESSION["record$type"])
    && count($_SESSION["record$type"])
    && $_SESSION["record$type"][count($_SESSION["record$type"])-1]['phId']
    ==$_SESSION["batch$type"][$_SESSION["batchPtr$type"]]['id']){
        $_SESSION["batchPtr$type"]++;
    }
}

if(isset($_GET['clear'])){
    // 清空已分配的任务
    unset($_SESSION["batch$type"]);
    unset($_SESSION["batchPtr$type"]);
    // 清空缓存
    unset($_SESSION['propDic']);
    unset($_SESSION['labels']);
    unset($_SESSION['userJudgeCount']);
}

// 分配下一个
$t = nextTarget($db, $type);
header("Location: check.php?phid=".$t['id']."&type=$type");
$db->close();
?>