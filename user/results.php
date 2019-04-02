<?php
include_once(dirname(__FILE__).'/../public/public.php');
include_once(dirname(__FILE__).'/../public/DataManager.class.php');

$db = new DataManager();
$data = $db->getProps();
$props = array();
foreach($data as $r){
    $props[$r['id']] = $r['text'];
}
$data = $db->getResultInfo($_SESSION['userId']);
$db->close();
?>
<!DOCTYPE html>
<html>

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <meta content="" name="keywords">
    <meta content="" name="description">
    <title>我的抽取</title>
    <meta name="apple-touch-fullscreen" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="renderer" content="webkit">
    <link href="../css/public.css" type=text/css rel=stylesheet>
</head>

<body style="text-align: center">
    <h1>我的抽取</h1>
    <hr>
    <table style="margin: 10px auto">
        <thead>
            <tr>
                <th>序号</th>
                <th>抽取属性</th>
                <th>抽取条数</th>
                <th>收到赞同</th>
                <th>收到反对</th>
                <th>赞同率</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $i = 1;
            $sum = array(0,0,0);
            foreach($data as $r){ 
                $sum[0]+=$r['rcount'];
                $sum[1]+=$r['agree'];
                $sum[2]+=$r['disagree'];
            ?>
                <tr>
                    <td><?php echo $i++ ?></td>
                    <td><?php echo $props[$r['prop_id']] ?></td>
                    <td><?php echo $r['rcount'] ?></td>
                    <td><a href="judged-detail.php?propid=<?php echo $r['prop_id'] ?>&type=1">
                        <?php echo $r['agree'] ?>
                    </a></td>
                    <td><a href="judged-detail.php?propid=<?php echo $r['prop_id'] ?>&type=2">
                        <?php echo $r['disagree'] ?>
                    </a></td>
                    <td><?php
                        $a = floatval($r['agree']);
                        $b = floatval($r['disagree']);
                        if($a+$b){
                            echo round($a/($a+$b)*100,2).'%';
                        }else{
                            echo '-';
                        }
                    ?></td>
                </tr>
            <?php } ?>
            <tr>
                <td colspan="2">合计</td>
                <td><?php echo $sum[0] ?></td>
                <td><?php echo $sum[1] ?></td>
                <td><?php echo $sum[2] ?></td>
                <td><?php
                    $a = floatval($sum[1]);
                    $b = floatval($sum[2]);
                    if($a+$b){
                        echo round($a/($a+$b)*100,2).'%';
                    }else{
                        echo '-';
                    }
                ?></td>
            </tr>
        </tbody>
    </table>
    <br>
    <hr>
    <p><a href="../index.php">返回</a></p>
</body>

</html>