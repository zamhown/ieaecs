<?php
include_once(dirname(__FILE__).'/../public/public.php');
include_once(dirname(__FILE__).'/../public/DataManager.class.php');

$db = new DataManager();
$data = $db->getProps();
$props = array();
foreach($data as $r){
    $props[$r['id']] = $r['text'];
}
$data = $db-> getDataSetTotalInfo();
$db->close();
?>
<!DOCTYPE html>
<html>

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <meta content="" name="keywords">
    <meta content="" name="description">
    <title>数据集准备进度</title>
    <meta name="apple-touch-fullscreen" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="renderer" content="webkit">
    <link href="../css/public.css" type=text/css rel=stylesheet>
    <script src="//cdn.staticfile.org/jquery/1.12.4/jquery.min.js"></script>
</head>

<body style="text-align: center">
    <h1>数据集准备进度</h1>
    <hr>
    <table style="margin: 10px auto">
        <thead>
            <tr>
                <th>序号</th>
                <th>属性</th>
                <th>数据条数</th>
                <th>已处理条数</th>
                <th>处理率</th>
                <th>导出已处理部分</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $i = 1;
            $sum = array(0,0);
            foreach($data as $r){
                $sum[0]+=$r['tc'];
                $sum[1]+=$r['cc'];
            ?>
                <tr <?php
                    if($r['rate']>95){
                        echo 'style="color:green"';
                    }
                ?>>
                    <td><?php echo $i++ ?></td>
                    <td><?php echo $props[$r['prop_id']] ?></td>
                    <td><?php echo $r['tc'] ?></td>
                    <td><?php echo $r['cc'] ?></td>
                    <td><?php
                        if($r['rate']>=0){
                            echo $r['rate'].'%';
                        }else{
                            echo '-';
                        }
                    ?></td>
                    <td><a href="get-partial-dataset.php?propid=<?php echo $r['prop_id'] ?>">导出csv</a></td>
                </tr>
            <?php } ?>
            <tr>
                <td colspan="2">合计</td>
                <td><?php echo $sum[0] ?></td>
                <td><?php echo $sum[1] ?></td>
                <td><?php
                    $a = floatval($sum[1]);
                    $b = floatval($sum[0]);
                    if($b){
                        echo round($a/$b*100,2).'%';
                    }else{
                        echo '-';
                    }
                ?></td>
                <td>-</td>
            </tr>
        </tbody>
    </table>
    <br>
    <hr>
    <p><a href="../index.php">返回</a></p>
</body>

</html>