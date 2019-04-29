<?php
include_once(dirname(__FILE__).'/../public/public.php');
include_once(dirname(__FILE__).'/../public/DataManager.class.php');

if(!isset($_SESSION['userAdmin']) || !$_SESSION['userAdmin']) {
    echo "权限不足！";
    echo '<br><br><a href="../index.php">返回</a>';
    exit();
}

$db = new DataManager();
$uploads = $db->getUserUploads($_SESSION['userId']);
$db->close();
?>
<!DOCTYPE html>
<html>

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <meta content="" name="keywords">
    <meta content="" name="description">
    <title>数据录入</title>
    <meta name="apple-touch-fullscreen" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="renderer" content="webkit">
    <link href="../css/public.css" type=text/css rel=stylesheet>
    <link rel="stylesheet" href="../lib/remodal/remodal.css">
    <link rel="stylesheet" href="../lib/remodal/remodal-default-theme.css">
    <script src="//cdn.staticfile.org/jquery/1.12.4/jquery.min.js"></script>
    <script src="../lib/remodal/remodal.min.js"></script>
</head>

<body style="text-align: center">
    <h1>数据录入</h1>
    <hr>
    <h2>录入已抽取属性的数据文件（CSV格式）</h2>
    <form id="markedFileForm" action="input-marked-file.php" method="post"
    enctype="multipart/form-data">
        <input type="file" name="file" id="file" accept=".csv" /> 
        <input type="submit" onclick="clickMarkedFileSubmit(event)" value="确认上传">
    </form>
    <!--
    <p></p><p></p>
    <h2>原始数据文件（CSV格式）</h2>
    <form id="dataFileForm" action="input-data-file.php" method="post"
    enctype="multipart/form-data">
        <input type="file" name="file" id="file" accept=".csv" /> 
        <input type="submit" onclick="clickDataFileSubmit(event)" value="确认上传">
    </form>
    -->
    <br>
    <hr>
    <h2>已上传的文件</h2>
    <table style="margin: 10px auto">
        <thead>
            <tr>
                <th>序号</th>
                <th>文件名</th>
                <th>分类</th>
                <th>上传时间</th>
                <th>操作</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $i = 1;
            $types = array('原始数据', '已抽取属性');
            foreach($uploads as $r){ 
                $filename;
                if($r['type']==1){
                    $filename = "data-files/".$_SESSION['userId']."/".$r['filename'];
                }else if($r['type']==2){
                    $filename = "marked-data-files/".$_SESSION['userId']."/".$r['filename'];
                }
            ?>
                <tr>
                    <td><?php echo $i++ ?></td>
                    <td><?php echo $r['filename'] ?></td>
                    <td><?php echo $types[$r['type']-1] ?></td>
                    <td><?php echo $r['time'] ?></td>
                    <td>
                    <a href="<?php echo $filename ?>">下载</a>&nbsp;&nbsp;
                    <a href="#" style="color:red" onclick="clickDel(event, <?php echo $r['id'] ?>)">删除</a>
                    </td>
                </tr>
            <?php } ?>
        </tbody>
    </table>
    <form id="formDel" action="del-data.php" method="post">
        <input type="hidden" name="id" id="inputId">
    </form>
    <br>
    <hr>
    <p><a href="../index.php">返回</a></p>

    <div class="remodal" data-remodal-id="modalWaiting" style="max-width: 400px">
        正在分析中，时间可能较长，请稍等……<br>
        请勿关闭或刷新页面
    </div>

    <script>
        var modalWaiting;
        var uploadType = 0;
        $(function(){
            modalWaiting = $('[data-remodal-id=modalWaiting]').remodal({
                closeOnConfirm: false,
                closeOnCancel: false,
                closeOnEscape: false,
                closeOnOutsideClick: false
            });

            $(document).on('opened', '[data-remodal-id=modalWaiting]', function () {
                if(uploadType==0){
                    $('#markedFileForm').submit();
                }else if(uploadType==1){
                    $('#dataFileForm').submit();
                }
            });
        });

        function clickMarkedFileSubmit(e){
            e.preventDefault();
            uploadType = 0;
            modalWaiting.open();
        }

        function clickDataFileSubmit(e){
            e.preventDefault();
            uploadType = 1;
            modalWaiting.open();
        }

        function clickDel(e, id){
            e.preventDefault();
            if(confirm('删除该文件后，该文件的所有检测结果都会消失。确定删除吗？')){
                $('#inputId').val(id);
                $('#formDel').submit();
            }
        }
    </script>
</body>

</html>