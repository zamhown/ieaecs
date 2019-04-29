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
    $db->login($_POST['userName'], $_POST['cardNo']);
}
$userJudgeCount = $db->getUserJudgeCount($_SESSION['userId']);

$props = $db->getProps();
$data = $db->getUserProps($_SESSION['userId']);
$userProps = explode(',', $data);

$labels = $db->getLabels();
$data = $db->getUserNoLabels($_SESSION['userId']);
$userNoLabels = explode(',', $data);

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
    <script src="//cdn.staticfile.org/jquery/1.12.4/jquery.min.js"></script>

    <style>
        #propForm{
            max-width: 400px;
            text-align: left;
            margin: 0 auto;
        }
    </style>
</head>

<body style="text-align: center">
    <h1><?php echo TITLE ?></h1>
    <hr>
    <p>欢迎<?php echo $_SESSION['userName'] ?>！你已检测<?php echo $userJudgeCount ?>条数据。</p>
    <form id="propForm" action="judge/check.php?clear=1&type=" method="post">
        <table>
            <tr>
                <td style="padding:15px">
                    <p>请选择你擅长的属性类型：</p>
                    <?php foreach($props as $p){ ?>
                        <!-- name后加[]，这样php才能正确读取 -->
                        <input type="checkbox" name="props[]" value="<?php echo $p['id'] ?>"
                        <?php if(in_array($p['id'], $userProps)){
                            echo 'checked';
                        } ?>>
                        <?php echo $p['text'] ?><br>
                    <?php } ?>
                </td>
            </tr>
            <tr>
                <td style="padding:15px">
                    <p>请选择检测时需要排除的已被检测过的抽取位：</p>
                    <?php foreach($labels as $l){ ?>
                        <!-- name后加[]，这样php才能正确读取 -->
                        <input type="checkbox" name="nolabels[]" value="<?php echo $l['id'] ?>"
                        <?php if(in_array($l['id'], $userNoLabels)){
                            echo 'checked';
                        } ?>>
                        <?php echo $l['text'] ?><br>
                    <?php } ?>
                </td>
            </tr>
            <tr>
                <td style="text-align:center">
                    检测有分歧的抽取结果：<input type="submit" value="开始消歧" onclick="clickJudge(event)">
                </td>
            </tr>
            <tr>
                <td style="text-align:center">
                    检测无分歧的抽取结果：<input type="submit" value="开始入库" onclick="clickInstock(event)">
                </td>
            </tr>
            <tr>
                <td style="text-align:center">
                    检测完成度最高的样本：<input type="submit" value="开始检测" onclick="clickNearlyComplete(event)">
                </td>
            </tr>
        </table>
    </form>
    <br>
    <p>
        <a href="user/judge-record.php">检测记录</a>&nbsp;&nbsp;&nbsp;
        <a href="user/star-record.php">我的收藏</a>&nbsp;&nbsp;&nbsp;
        <a href="user/results.php">我的抽取</a>&nbsp;&nbsp;&nbsp;
        <?php if($_SESSION['userAdmin']) { ?><a href="data/input.php">数据录入</a><?php } ?>
    </p>
    <p>
        <a href="collection/total-info-data.php">查看数据集</a>&nbsp;&nbsp;&nbsp;
        <a href="collection/diff-info.php">消歧情况</a>&nbsp;&nbsp;&nbsp;
        <a href="collection/instock-info.php">入库情况</a>&nbsp;&nbsp;&nbsp;
    </p>
    <p>
        <a href="collection/rank.php">高分榜</a>&nbsp;&nbsp;&nbsp;
        <a href="login.html">重新登录</a>
    </p>
    <script>
        function clickJudge(e){
            e.preventDefault();
            var $form = $('#propForm');
            $form.attr('action', 'judge/check.php?clear=1&type=1');
            $form.submit();
        }

        function clickInstock(e){
            e.preventDefault();
            var $form = $('#propForm');
            $form.attr('action', 'judge/check.php?clear=1&type=2');
            $form.submit();
        }

        function clickNearlyComplete(e){
            e.preventDefault();
            var $form = $('#propForm');
            $form.attr('action', 'judge/check.php?clear=1&type=3');
            $form.submit();
        }
    </script>
</body>

</html>