<?php
include_once(dirname(__FILE__).'/../public/public.php');
include_once(dirname(__FILE__).'/../public/DataManager.class.php');

$db = new DataManager();
$data = $db->getProps();
$props = array();
foreach($data as $r){
    $props[$r['id']] = $r['text'];
}
$data = $db->getDataSetTotalInfoViaData();
$db->close();
?>
<!DOCTYPE html>
<html>

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <meta content="" name="keywords">
    <meta content="" name="description">
    <title>数据集准备进度（按样本）</title>
    <meta name="apple-touch-fullscreen" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="renderer" content="webkit">
    <link href="../css/public.css?1" type=text/css rel=stylesheet>
    <script src="//cdn.staticfile.org/jquery/1.12.4/jquery.min.js"></script>
</head>

<body style="text-align: center">
    <h1>数据集准备进度（按样本）</h1>
    <hr>
    <h2>总体情况</h2>
    <table style="margin: 10px auto">
        <tbody>
            <tr>
                <td class="th">数据条数</td>
                <td><?php echo $data['tc'] ?></td>
            </tr>
            <tr>
                <td class="th">已处理条数</td>
                <td><?php echo $data['dc'] ?></td>
            </tr>
            <tr>
                <td class="th">处理率</td>
                <td><?php 
                    $a = floatval($data['dc']);
                    $b = floatval($data['tc']);
                    if($b){
                        echo round($a/$b*100,2).'%';
                    }else{
                        echo '-';
                    }
                ?></td>
            </tr>
            <tr>
                <td class="th">导出已处理部分</td>
                <td><a href="get-partial-dataset-via-data.php">导出csv</a></td>
            </tr>
        </tbody>
    </table>
    <br>
    <h2>各属性有效抽取结果分析</h2>
    <table style="margin: 10px auto">
        <thead>
            <tr>
                <th>属性</th>
                <th>非“无”结果条数</th>
                <th>非“无”结果占比</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($props as $k=>$v){ ?>
                <tr>
                    <td><?php echo $v ?></td>
                    <td><?php echo $data['prop_'.$k] ?></td>
                    <td><?php 
                        $a = floatval($data['prop_'.$k]);
                        $b = floatval($data['dc']);
                        if($b){
                            echo round($a/$b*100,2).'%';
                        }else{
                            echo '-';
                        }
                    ?></td>
                </tr>
            <?php } ?>
        </tbody>
    </table>
    <br>
    <hr>
    <p><a href="../index.php">返回</a></p>
</body>

</html>