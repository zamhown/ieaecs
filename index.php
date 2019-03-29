<?php
include_once(dirname(__FILE__).'/public/DataManager.class.php');
@session_start();

$hasPost = isset($_POST['userName']) && $_POST['userName'];

if(!$hasPost && !isset($_SESSION['userName'])){
    header("Location: login.html");
    exit;
}

$db = new DataManager();
if($hasPost){
    $db->login($_POST['userName']);
}
$userJudgeCount = $db->getUserJudgeCount($_SESSION['userId']);
$props = $db->getProps();
$data = $db->getUserProps($_SESSION['userId']);
$userProps = explode(',', $data);
$db->close();
?>
<!DOCTYPE html>
<html>

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <meta content="" name="keywords">
    <meta content="" name="description">
    <title><?php echo TITLE ?></title>
    <meta name="apple-touch-fullscreen" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="renderer" content="webkit">
    <link href="css/public.css" type=text/css rel=stylesheet>

    <style>
        #propForm{
            max-width: 250px;
            text-align: left;
            margin: 0 auto;
            border: 1px solid #cccccc;
            padding: 10px;
        }
    </style>
</head>

<body style="text-align: center">
    <h1><?php echo TITLE ?></h1>
    <hr>
    <p>欢迎<?php echo $_SESSION['userName'] ?>！你已检测<?php echo $userJudgeCount ?>条数据。</p>
    <p>请选择你擅长的属性类型：</p>
    <form id="propForm" action="judge/action.php?clear=1" method="post">
        <?php foreach($props as $p){ ?>
            <!-- name后加[]，这样php才能正确读取 -->
            <input type="checkbox" name="props[]" value="<?php echo $p['id'] ?>"
            <?php if(in_array($p['id'], $userProps)){
                echo 'checked';
            } ?>>
            <?php echo $p['text'] ?><br>
        <?php } ?>
        <br>
        <p style="text-align: center"><input type="submit" value="开始检测！"></p>
    </form>
    <br>
    <p>
        <a href="user/judge-record.php">检测记录</a>&nbsp;&nbsp;&nbsp;
        <a href="user/star-record.php">我的收藏</a>&nbsp;&nbsp;&nbsp;
        <a href="user/results.php">我的抽取</a>
    </p>
    <p>
        <a href="rank.php">高分榜</a>&nbsp;&nbsp;&nbsp;
        <a href="diff-info.php">分歧处理情况</a>&nbsp;&nbsp;&nbsp;
        <a href="data/input.php">数据录入</a>&nbsp;&nbsp;&nbsp;
        <a href="login.html">重新登录</a>
    </p>
</body>

</html>