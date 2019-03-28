<?php
include_once('public.php');
include_once('DataManager.class.php');

$db = new DataManager();

// 系统分配下一批目标
function nextBatch($db){
    $data = $db->nextPlaceholder($_SESSION['userId'], $_SESSION['userProps'], 100);
    if($data===false){
        echo "没有需要检测的数据！";
        echo '<br><br><a href="index.php">返回</a>';
        exit();
    }
    $_SESSION['batch'] = $data;
    $_SESSION['batchPtr'] = 0;
}

// 系统分配下一个目标
function nextTarget($db){
    if(!isset($_SESSION['batch'])
    || !isset($_SESSION['batchPtr'])
    || $_SESSION['batchPtr']>=count($_SESSION['batch'])){
        nextBatch($db);
    }
    return $_SESSION['batch'][$_SESSION['batchPtr']];
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
            $db->addJudge($r, $_SESSION['userId'], $_POST['r'.$r]);
        }
    }
    if(isset($_POST['amendment']) && $_POST['amendment']){
        $db->addResult($_POST['phId'], $_SESSION['userId'], $_POST['amendment'], 1);
    }
    // 历史记录与任务分配重合，则继续任务
    if(isset($_SESSION['batchPtr'])
    && isset($_SESSION['record'])
    && count($_SESSION['record'])
    && $_SESSION['record'][count($_SESSION['record'])-1]['phId']
    ==$_SESSION['batch'][$_SESSION['batchPtr']]['id']){
        $_SESSION['batchPtr']++;
    }
}

if(isset($_GET['clear'])){
    // 清空已分配的任务
    unset($_SESSION['batch']);
    unset($_SESSION['batchPtr']);
    // 清空缓存
    unset($_SESSION['propDic']);
    unset($_SESSION['labels']);
    unset($_SESSION['userJudgeCount']);
}

// 分配下一个
$t = nextTarget($db);
header("Location: judge.php?phid=".$t['id']);
$db->close();
?>