<?php
include_once(dirname(__FILE__).'/public/public.php');
include_once(dirname(__FILE__).'/public/DataManager.class.php');

$db = new DataManager();
$rank = $db-> getUserRank();
$db->close();
?>
<!DOCTYPE html>
<html>

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <meta content="" name="keywords">
    <meta content="" name="description">
    <title>高分榜</title>
    <meta name="apple-touch-fullscreen" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="renderer" content="webkit">
    <link href="css/public.css" type=text/css rel=stylesheet>
    <script src="//cdn.staticfile.org/jquery/1.12.4/jquery.min.js"></script>
</head>

<body style="text-align: center">
    <h1>高分榜</h1>
    <hr>
    <table style="margin: 10px auto">
        <thead>
            <tr>
                <th>排名</th>
                <th>用户名</th>
                <th>检测条数</th>
                <th>消歧条数</th>
                <th>入库条数</th>
                <th>贡献条数</th>
                <th>收到赞同</th>
                <th>赞同率</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $i = 0;
            $sum = array(0,0,0,0,0,0);
            foreach($rank as $r){
                $i++;
                $sum[0]+=$r['jc'];
                $sum[1]+=$r['rcount'];
                $sum[2]+=$r['agree'];
                $sum[3]+=$r['disagree'];
                $sum[4]+=$r['diff_count'];
                $sum[5]+=$r['instock_count'];
            ?>
                <tr>
                    <td <?php
                        if($i<=3){
                            echo 'style="color:red;font-weight:bold"';
                        }
                    ?>>
                        <?php echo $i ?>
                    </td>
                    <td <?php
                        if($i<=3){
                            echo 'style="font-weight:bold"';
                        }
                    ?>><?php echo $r['uname'] ?></td>
                    <td <?php
                        if($i<=3){
                            echo 'style="font-weight:bold"';
                        }
                    ?>><?php echo $r['jc'] ?></td>
                    <td><?php echo $r['diff_count'] ?></td>
                    <td><?php echo $r['instock_count'] ?></td>
                    <td>
                        <a href="user-result-info.php?userid=<?php echo $r['id'] ?>">
                            <?php echo $r['rcount'] ?>
                        </a>
                    </td>
                    <td><?php echo $r['agree'] ?></td>
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
                <td><?php echo $sum[4] ?></td>
                <td><?php echo $sum[5] ?></td>
                <td><?php echo $sum[1] ?></td>
                <td><?php echo $sum[2] ?></td>
                <td><?php
                    $a = floatval($sum[2]);
                    $b = floatval($sum[3]);
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
    <p><a href="index.php">返回</a></p>
</body>

</html>