<?php
include_once(dirname(__FILE__).'/../public/public.php');
include_once(dirname(__FILE__).'/../public/DataManager.class.php');

$db = new DataManager();
$data = $db->getProps();
$props = array();
$userProps = array();
foreach($data as $r){
    $props[$r['id']] = $r['text'];
    array_push($userProps, $r['id']);
}
$data;

$hasPost = isset($_POST['props']);
if($hasPost){
    $userProps = $_POST['props'];
    $data = $db->getDataSetTotalInfoViaData($userProps);
}
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

    <style>
        .props{
            max-width: 300px;
            text-align: left;
            margin: 0 auto;
            border: 1px solid #cccccc;
            padding:15px;
            font-size: 16px;
        }
    </style>
</head>

<body style="text-align: center">
    <h1>数据集准备进度（按样本）</h1>
    <hr>
    <h2>请设定样本需包含的属性</h2>
    <form action="total-info-data.php" method="post">
        <div class="props">
            <?php foreach($props as $pid => $p){ ?>
                <!-- name后加[]，这样php才能正确读取 -->
                <input type="checkbox" name="props[]" value="<?php echo $pid ?>"
                <?php if(in_array($pid, $userProps)){
                    echo 'checked';
                } ?>>
                <?php echo $p ?><br>
            <?php } ?>
            <p style="text-align:center;margin:15px 0px 0px 0px">
                <input type="submit" value="查询">
            </p>
        </div>
    </form>
    <br>
    <?php if($hasPost){ ?>
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
                    <td>
                        <form action="get-partial-dataset-via-data.php" method="post">
                            <input type="hidden" name="props" value="<?php echo implode(',', $userProps) ?>">
                            <input type="submit" value="导出数据集">
                        </form>
                    </td>
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
                <?php foreach($userProps as $pid){ ?>
                    <tr>
                        <td><?php echo $props[$pid] ?></td>
                        <td><?php echo $data['prop_'.$pid] ?></td>
                        <td><?php 
                            $a = floatval($data['prop_'.$pid]);
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
    <?php } ?>
    <hr>
    <p><a href="../index.php">返回</a></p>
</body>

</html>