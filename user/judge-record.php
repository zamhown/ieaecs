<?php
include_once(dirname(__FILE__).'/../public/public.php');
include_once(dirname(__FILE__).'/../public/DataManager.class.php');

$db = new DataManager();
$data = $db->getLabels();
$labels = array();
foreach($data as $l){
    $labels[$l['id']] = $l['text'];
}
$data = $db->getProps();
$props = array();
foreach($data as $p){
    $props[$p['id']] = $p['text'];
}

$userJudges = $db->getUserJudges($_SESSION['userId']);
$db->close();
?>
<!DOCTYPE html>
<html>

<head>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
  <meta content="" name="keywords">
  <meta content="" name="description">
  <title><?php echo $_SESSION['userName'] ?>的检测记录</title>
  <meta name="apple-touch-fullscreen" content="yes">
  <meta name="apple-mobile-web-app-capable" content="yes">
  <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
  <meta name="renderer" content="webkit">
  <link href="../css/public.css" type=text/css rel=stylesheet>
</head>

<body style="text-align: center">
    <h1>检测记录</h1>
    <hr>
    <table style="margin: 10px">
        <thead>
            <tr>
                <th>序号</th>
                <th>数据</th>
                <th>属性</th>
                <th>抽取结果</th>
                <th>我的评判</th>
                <th>时间</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $i = count($userJudges);
            foreach($userJudges as $r){ ?>
                <tr>
                    <td><?php echo $i-- ?></td>
                    <td style="width:40%;text-align:left"><?php echo $r['dtext'] ?></td>
                    <td><?php echo $props[$r['prop_id']] ?></td>
                    <td style="width:20%"><?php echo $r['rtext'] ?></td>
                    <td><a href="../judge/check.php?phid=<?php echo $r['ph_id'] ?>">
                        <?php echo $labels[$r['label_id']] ?>
                    </a></td>
                    <td><?php echo $r['time'] ?></td>
                </tr>
            <?php } ?>
        </tbody>
    </table>
    <br>
    <hr>
    <p><a href="../index.php">返回</a></p>
</body>

</html>