<?php
include_once(dirname(__FILE__).'/../public/public.php');
include_once(dirname(__FILE__).'/../public/DataManager.class.php');

$db = new DataManager();
if(isset($_POST['ids'])){
    // 保存设置
    $ids = json_decode($_POST['ids'], true);
    foreach($ids as $id){
        $db->setPropKwreg($id, sql_escape($_POST["kwreg$id"]));
    }
}
$data = $db->getProps();
$db->close();
?>
<!DOCTYPE html>
<html>

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <meta content="" name="keywords">
    <meta content="" name="description">
    <title>自定义关键字高亮规则</title>
    <meta name="apple-touch-fullscreen" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="renderer" content="webkit">
    <link href="../css/public.css" type=text/css rel=stylesheet>
    <script src="//cdn.staticfile.org/jquery/1.12.4/jquery.min.js"></script>
</head>

<body style="text-align: center">
    <h1>自定义关键字高亮规则</h1>
    <hr>
    <form action="edit-prop-kwreg.php" method="post">
        <input type="hidden" name="ids" value="<?php echo json_encode(array_map(function($e){
            return intval($e['id']);
        }, $data)) ?>">
        <table style="margin: 10px auto">
            <thead>
                <tr>
                    <th>序号</th>
                    <th>属性</th>
                    <th>关键字高亮正则表达式</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $i = 1;
                foreach($data as $r){
                ?>
                    <tr>
                        <td><?php echo $i++ ?></td>
                        <td><?php echo $r['text'] ?></td>
                        <td><input type="text" name="kwreg<?php echo $r['id'] ?>" value="<?php echo $r['kwreg'] ?>"></td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
        <p>
            <input type="submit" value="保存">
        </p>
        <br>
    </form>
    <hr>
    <p><a href="../index.php">返回</a></p>
</body>

</html>