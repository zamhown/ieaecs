<?php
include_once(dirname(__FILE__).'/../public/public.php');
include_once(dirname(__FILE__).'/../public/DataManager.class.php');

$db = new DataManager();
$data = $db->getProps();
$props = array();
foreach($data as $r){
    $props[$r['id']] = $r['text'];
}
$data = $db-> getResultInstockInfo();
$db->close();
?>
<!DOCTYPE html>
<html>

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <meta content="" name="keywords">
    <meta content="" name="description">
    <title>入库情况</title>
    <meta name="apple-touch-fullscreen" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="renderer" content="webkit">
    <link href="../css/public.css" type=text/css rel=stylesheet>
    <script src="//cdn.staticfile.org/jquery/1.12.4/jquery.min.js"></script>
</head>

<body style="text-align: center">
    <h1>入库情况</h1>
    <hr>
    <table style="margin: 10px auto">
        <thead>
            <tr>
                <th>序号</th>
                <th>属性</th>
                <th>待入库条数</th>
                <th>已入库条数</th>
                <th>处理率</th>
                <th>详细报告</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $i = 1;
            $sum = array(0,0);
            foreach($data as $r){
                if(!isset($r['labelPhCount']['1'])){
                    $r['labelPhCount']['1'] = 0;
                }
                $sum[0]+=$r['dc'];
                $sum[1]+=$r['labelPhCount']['1'];

                $a = $r['labelPhCount']['1'];
                $b = $r['dc'];
                $rate = -1;
                if($b){
                    $rate = round($a/$b*100,2);
                }
            ?>
                <tr <?php
                    if($rate>95){
                        echo 'style="color:green"';
                    }
                ?>>
                    <td><?php echo $i++ ?></td>
                    <td><?php echo $props[$r['prop_id']] ?></td>
                    <td><?php echo $r['dc'] ?></td>
                    <td><?php echo $r['labelPhCount']['1'] ?></td>
                    <td><?php
                        if($rate>=0){
                            echo $rate.'%';
                        }else{
                            echo '-';
                        }
                    ?></td>
                    <td><a href="get-instock-detail.php?propid=<?php echo $r['prop_id'] ?>">导出csv</a></td>
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