<?php
include_once(dirname(__FILE__).'/../public/public.php');
include_once(dirname(__FILE__).'/../public/DataManager.class.php');

$hasGet = isset($_GET['propid']) && $_GET['propid']
    && isset($_GET['type']) && $_GET['type'];
$props;$propId;

if(!$hasGet){
    header("Location: index.php");
}else{
    $propId = $_GET['propid'];
    $type = $_GET['type'];
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

    $data = $db->getJudgedDetail($_SESSION['userId'], $propId, $type);
    $db->close();
}
?>
<!DOCTYPE html>
<html>

<head>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
  <meta content="" name="keywords">
  <meta content="" name="description">
  <title><?php echo $_SESSION['userName'] ?>抽取的“<?php echo $props[$propId] ?>”属性的评判详情</title>
  <meta name="apple-touch-fullscreen" content="yes">
  <meta name="apple-mobile-web-app-capable" content="yes">
  <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
  <meta name="renderer" content="webkit">
  <link href="../css/public.css" type=text/css rel=stylesheet>
</head>

<body style="text-align: center">
    <h1>“<?php echo $props[$propId] ?>”属性的评判详情</h1>
    <hr>
    <table style="margin: 10px">
        <thead>
            <tr>
                <th>序号</th>
                <th>数据ID</th>
                <th>数据</th>
                <th>抽取结果</th>
                <th>评判结果</th>
                <th>评判人</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $i = 1;
            foreach($data as $r){ ?>
                <tr>
                    <td rowspan="<?php echo count($r) ?>"><?php echo $i++ ?></td>
                    <td rowspan="<?php echo count($r) ?>"><?php echo $r[0]['id'] ?></td>
                    <td rowspan="<?php echo count($r) ?>" style="width:40%;text-align:left">
                        <a href="../judge/check.php?phid=<?php echo $r[0]['ph_id'] ?>">
                            <?php echo $r[0]['dtext'] ?>
                        </a>
                    </td>
                    <td rowspan="<?php echo count($r) ?>" style="width:20%"><?php echo $r[0]['text'] ?></td>
                    <td><?php echo $r[0]['ltext'] ?></td>
                    <td><?php echo $r[0]['unames'] ?></td>
                </tr>
                <?php for($j=1;$j<count($r);$j++){ ?>
                    <tr>
                        <td><?php echo $r[$j]['ltext'] ?></td>
                        <td><?php echo $r[$j]['unames'] ?></td>
                    </tr>
            <?php }} ?>
        </tbody>
    </table>
    <br>
    <hr>
    <p><a href="results.php">返回</a></p>
</body>

</html>