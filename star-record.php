<?php
include_once('public.php');
include_once('DataManager.class.php');

$db = new DataManager();
$data = $db->getProps();
$props = array();
foreach($data as $p){
    $props[$p['id']] = $p['text'];
}

$starRecords = $db->getStarRecords($_SESSION['userId']);
$db->close();
?>
<!DOCTYPE html>
<html>

<head>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
  <meta content="" name="keywords">
  <meta content="" name="description">
  <title><?php echo $_SESSION['userName'] ?>的收藏记录</title>
  <meta name="apple-touch-fullscreen" content="yes">
  <meta name="apple-mobile-web-app-capable" content="yes">
  <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
  <meta name="renderer" content="webkit">
  <link href="css/public.css" type=text/css rel=stylesheet>
</head>

<body style="text-align: center">
    <h1>我的收藏</h1>
    <hr>
    <table style="margin: 10px">
        <thead>
            <tr>
                <th>序号</th>
                <th>数据ID</th>
                <th>数据</th>
                <th>属性</th>
                <th>操作</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $i = count($starRecords);
            foreach($starRecords as $r){ ?>
                <tr>
                    <td><?php echo $i-- ?></td>
                    <td><?php echo $r['data_id'] ?></td>
                    <td style="width:50%">
                        <a href="judge.php?phid=<?php echo $r['ph_id'] ?>">
                            <?php echo $r['text'] ?>
                        </a>
                    </td>
                    <td><?php echo $props[$r['prop_id']] ?></td>
                    <td>
                        <form action="toggle-star.php" method="post">
                            <input type="hidden" name="phId" value="<?php echo $r['ph_id'] ?>">
                            <input type="hidden" name="from" value="star-record">
                            <input type="submit" style="color:red;border:none;background:none;" value="取消收藏">
                        </form>
                    </td>
                </tr>
            <?php } ?>
        </tbody>
    </table>
    <br>
    <hr>
    <p><a href="index.php">返回</a></p>
</body>

</html>