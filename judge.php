<?php
include_once('public.php');
include_once('DataManager.class.php');

$hasGet = isset($_GET['phid']) && $_GET['phid'];
$phId;$data;$prop;$res;$labels;$amendment;$userJudgeCount;

if(!$hasGet){
    header("Location: judge-action.php");
}else{
    $db = new DataManager();
    $phId = intval($_GET['phid']);
    $ph = $db->getPlaceholderById($phId);
    if(!$ph){
        header("Location: judge-action.php");
    }

    // 做记录以备返回上一条
    if(!isset($_SESSION['record'])){
        $_SESSION['record'] = array();
    }
    if(!(count($_SESSION['record']) 
    && $_SESSION['record'][count($_SESSION['record'])-1]['phId'] == $phId)){
        array_push($_SESSION['record'], array(
            'phId' => $phId
        ));
    }

    // 缓存数据
    if(!isset($_SESSION['propDic'])){
        $props = $db->getProps();
        $_SESSION['propDic'] = array();
        foreach($props as $p){
            $_SESSION['propDic'][intval($p['id'])] = $p;
        }
    }
    $prop = $_SESSION['propDic'][intval($ph['prop_id'])];
    if(!isset($_SESSION['labels'])){
        $_SESSION['labels'] = $db->getLabels();
    }
    $labels = $_SESSION['labels'];

    $data = $db->getDataRecord($ph['data_id']);

    $dres = $db->getResultsForJudge($phId, $_SESSION['userId']);
    $res = array();
    for($i = 0; $i<count($dres); $i++){
        $flag = true;
        foreach($res as &$r){
            if($dres[$i]['id']==$r['id']){
                $r['uname'].='，'.$dres[$i]['uname'];
                $flag = false;
                break;
            }
        }
        unset($r);
        if($flag){
            array_push($res, $dres[$i]);
        }
    }

    $isStar = $db->isStar($phId, $_SESSION['userId']);
    /*
    $aa = $db->getAmendment($phId, $_SESSION['userId']);
    if(count($aa)){
        $amendment = $aa[0]['text'];
    }else{
        $amendment = '';
    }
    */
    $userJudgeCount = $db->getUserJudgeCount($_SESSION['userId']);
    $db->close();
}
?>
<!DOCTYPE html>
<html>

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <meta content="" name="keywords">
    <meta content="" name="description">
    <title>检测病理诊断<?php echo $data['id'] ?>的“<?php echo $prop['text'] ?>”</title>
    <meta name="apple-touch-fullscreen" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="renderer" content="webkit">
    <link href="css/public.css" type=text/css rel=stylesheet>
    <link rel="stylesheet" href="remodal/remodal.css">
    <link rel="stylesheet" href="remodal/remodal-default-theme.css">
    <script src="//cdn.staticfile.org/jquery/1.12.4/jquery.min.js"></script>
    <script src="remodal/remodal.min.js"></script>
    <style>
        p.data{
            text-align: left;
            max-width: 500px;
            margin: 0 auto;
            font-size: 16px;
            line-height: 25px;
            letter-spacing: 2px;
            display: inline-block;
            padding: 10px;
            border: 1px solid #cccccc;
        }

        span.result{
            color: red;
            background-color: #efefef;
            font-weight: bold;
        }

        #mainTable {
            margin: 0;
        }

        #mainTable th {
            border-color: #666666;
            background-color: #FFE4E1;
        }

        #mainTable tr.active td{
            background-color: #FFF0F5;
        }

        #mainTable tr.trCon td{
            background-color: #eee;
        }

        #mainTable td.tdName, #mainTable td.tdProp, #mainTable th{
            text-align: center;
        }

        a.label{
            -webkit-appearance: button;
            padding: 5px;
            background-color: #ca5b4238;
            margin: 5px;
            display: block;
            color: black;
            border-radius: 5px;
            text-align: center;
            font-size: 15px;
        }

        a.label.hover{
            background-color: #ca5b426a;
        }

        a.label.checked{
            color: white !important;
            background-color: #ca5b42 !important;
        }

        .remodal {
            max-width: 400px;
        }

        section {
            float: left;
            width: 50%;
            padding: 0px 20px;
        }

        .section-data {
            text-align: right;
        }

        .section-judge {
            text-align: left;
        }

        .stared {
            color: #ca5b42;
        }

        @media screen and (max-width: 1000px) {
            section {
                float: left;
                width: 100%;
            }
            .section-data {
                text-align: center;
            }
            .section-judge {
                text-align: center;
            }
            #mainTable {
                margin: 0 auto;
            }
        }
    </style>
</head>

<body style="text-align: center">
    <h1>正在检测病理诊断<?php echo $data['id'] ?>的“<?php echo $prop['text'] ?>”</h1>
    <hr>
    <article class="article-main">
        <section class="section-data">
            <h2>原始数据（ID：<?php echo $data['id'] ?>）</h2>
            <p class="data">
                <?php echo $data['text'] ?>
            </p>
            <form action="toggle-star.php" method="post">
                <input type="hidden" name="phId" value="<?php echo $phId ?>">
                <input type="hidden" name="from" value="judge">
                <input type="submit" value="<?php
                    if($isStar){
                        echo "★ 已收藏";
                    }else{
                        echo "☆ 收藏该记录";
                    }
                ?>" <?php
                    if($isStar){
                        echo 'class="stared"';
                    }
                ?>>
            </form>
        </section>
        <section class="section-judge">
            <form id="judgeForm" action="judge-action.php" method="post">
                <input type="hidden" name="phId" value="<?php echo $phId ?>">
                <input type="hidden" name="rlist" value="<?php echo json_encode(array_map(function($e){return intval($e['id']);}, $res)) ?>">
                <h2>属性“<?php echo $prop['text'] ?>”已有<?php echo count($res) ?>种抽取结果</h2>
                <table id="mainTable">
                    <thead>
                        <tr>
                            <th>数据源</th>
                            <th>属性</th>
                            <th>抽取结果</th>
                            <th>我的评判</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($res as $r){ 
                            $text = $r['text'];
                            // 自动判断格式问题
                            $gsError = false;
                            $lastIndex = mb_strlen($text, 'utf-8')-1;
                            if(mb_strpos($text, '（', 0, 'utf-8')===0  // strpos匹配中文有问题
                            || mb_strpos($text, '）', 0, 'utf-8')===$lastIndex
                            || mb_strpos($text, '(', 0, 'utf-8')===0
                            || mb_strpos($text, ')', 0, 'utf-8')===$lastIndex
                            || mb_strpos($text, ' ', 0, 'utf-8')===0
                            || mb_strpos($text, ' ', 0, 'utf-8')===$lastIndex
                            || mb_strpos($text, '"', 0, 'utf-8')===0
                            || mb_strpos($text, '"', 0, 'utf-8')===$lastIndex
                            || mb_strpos($text, '“', 0, 'utf-8')===0
                            || mb_strpos($text, '”', 0, 'utf-8')===$lastIndex
                            || mb_strpos($text, ',', 0, 'utf-8')===0
                            || mb_strpos($text, ',', 0, 'utf-8')===$lastIndex
                            || mb_strpos($text, '，', 0, 'utf-8')===0
                            || mb_strpos($text, '，', 0, 'utf-8')===$lastIndex
                            || mb_strpos($text, '。', 0, 'utf-8')===0
                            || mb_strpos($text, '。', 0, 'utf-8')===$lastIndex
                            ){
                                $gsError = true;
                            }
                            // 自动判断抽取内容错误问题
                            $nrError = (count($res)>0 && $text==='无') || !trim($text);
                            ?>
                            <tr id="tr<?php echo $r['id'] ?>" class="trData" onmouseenter="ome(event)">
                                <td class="tdName"><?php echo $r['uname'] ?></td>
                                <td class="tdProp"><?php echo $prop['text'] ?></td>
                                <td class="tdData">
                                    <span class="data"><?php echo $text ?></span>
                                    <?php
                                        if(!$r['label_id']){
                                            if($gsError){
                                                echo '<br><br><span style="color:red">【系统检测到格式可能出错，已自动标记】</span>';
                                            }else if($nrError){
                                                echo '<br><br><span style="color:red">【系统检测到抽取可能出错，已自动标记】</span>';
                                            }
                                        }
                                    ?>
                                </td>
                                <td style="text-align: left; min-width: 110px">
                                    <a class="label agree" href="#" rid="<?php echo $r['id'] ?>" type="1" onclick="clickLabel(event)">
                                        ▲ 赞同(<?php echo $r['agree_count'] ?>)
                                    </a>
                                    <a class="label disagree" href="#" rid="<?php echo $r['id'] ?>" type="2" onclick="clickLabel(event)">
                                        ▼ 反对(<?php echo $r['disagree_count'] ?>)
                                    </a>
                                    <a class="label uncertain" href="#" rid="<?php echo $r['id'] ?>" type="3" onclick="clickLabel(event)">
                                        不确定(<?php echo $r['uncertain_count'] ?>)
                                    </a>
                                    <input type="hidden" class="label-v" name="r<?php echo $r['id'] ?>" value="<?php
                                        if($r['label_id']){
                                            echo $r['label_id'];
                                        }else if($gsError){
                                            echo '3';
                                        }else if($nrError){
                                            echo '2';
                                        }
                                    ?>">
                                </td>
                            </tr>
                        <?php } ?>
                        <tr class="trCon">
                            <td style="text-align:center; min-width:90px">
                                <a href="#" onclick="clickAllDisagree(event)">
                                    全部反对
                                </a>
                            </td>
                            <td colspan="2">
                                <label for="amendment">你认为正确的结果是（可选）</label>
                                <input style="display:block; width:100%" type="text" name="amendment" value="">
                            </td>
                            <td style="text-align:center">
                                <?php if(count($_SESSION['record'])>1){ 
                                    $r = $_SESSION['record'][count($_SESSION['record'])-2]; ?>
                                    <a href="judge-prev.php">上一个</a><br>
                                <?php } ?>
                                <input type="submit" value="提交，下一个">
                            </td>
                        </tr>
                    </tbody>
                </table>
            </form>
        </section>
    </article>
    <p style="clear:both"></p>
    <hr>
    <p>
        你已检测<?php echo $userJudgeCount ?>条数据&nbsp;&nbsp;
        <a href="judge-record.php">检测记录</a>&nbsp;&nbsp;
        <a href="star-record.php">我的收藏</a>
    </p>
    <p>快捷键：←→：切换数据源，↑↓：选择检测结果，Enter：确定/提交</p>
    <p><a href="index.php">返回</a></p>

    <div class="remodal" data-remodal-id="modalAgree">
        <button data-remodal-action="close" class="remodal-close"></button>
        <h1>请选择赞同的理由</h1>
        <p>
            <?php foreach($labels as $l){ 
                if($l['type']=='1'){?>
                <a class="label" href="#" rid="" value="<?php echo $l['id'] ?>" onclick="clickSubLabel(event)">
                    <?php echo $l['text'] ?>
                </a>
            <?php } 
            } ?>
        </p>
    </div>

    <div class="remodal" data-remodal-id="modalDisagree">
        <button data-remodal-action="close" class="remodal-close"></button>
        <h1>请选择反对的理由</h1>
        <p>
            <?php foreach($labels as $l){ 
                if($l['type']=='2'){?>
                <a class="label" href="#" rid="" value="<?php echo $l['id'] ?>" onclick="clickSubLabel(event)">
                    <?php echo $l['text'] ?>
                </a>
            <?php } 
            } ?>
        </p>
    </div>

    <div class="remodal" data-remodal-id="modalUncertain">
        <button data-remodal-action="close" class="remodal-close"></button>
        <h1>请选择不确定的理由</h1>
        <p>
            <?php foreach($labels as $l){ 
                if($l['type']=='3'){?>
                <a class="label" href="#" rid="" value="<?php echo $l['id'] ?>" onclick="clickSubLabel(event)">
                    <?php echo $l['text'] ?>
                </a>
            <?php } 
            } ?>
        </p>
    </div>

    <script>
        var oldData = '';
        var trCount = 0;
        var $activeTr;
        var $activeLabelContainer;
        var modalOpened = false;
        var modalAgree, modalDisagree, modalUncertain;
        var $modalAgree, $modalDisagree, $modalUncertain;

        var labelDic = {
            <?php foreach($labels as $l){ 
                echo $l['id'].":".$l['type'].",";
            } ?>
        };
        var agreeLabelIds = [<?php foreach($labels as $l){ 
                if($l['type']=='1'){
                    echo $l['id'].',';
                }
            } ?>];
        var disagreeLabelIds = [<?php foreach($labels as $l){ 
                if($l['type']=='2'){
                    echo $l['id'].',';
                }
            } ?>];
        var uncertainLabelIds = [<?php foreach($labels as $l){ 
                if($l['type']=='3'){
                    echo $l['id'].',';
                }
            } ?>];

        $(function(){
            oldData = $('p.data').html();
            trCount = $('tr.trData').length;

            refreshLabelState();

            $('a.label').mouseenter(function(e){
                $('a.label').removeClass('hover');
                $(e.target).addClass('hover');
            })
            $('a.label').mouseleave(function(e){
                $('a.label').removeClass('hover');
            })

            $modalAgree = $('[data-remodal-id=modalAgree]');
            $modalDisagree = $('[data-remodal-id=modalDisagree]');
            $modalUncertain = $('[data-remodal-id=modalUncertain]');

            modalAgree = $modalAgree.remodal();
            modalDisagree = $modalDisagree.remodal();
            modalUncertain = $modalUncertain.remodal();

            $(document).on('opened', '[data-remodal-id=modalAgree]', function () {
                $activeLabelContainer = $modalAgree;
            });
            $(document).on('opened', '[data-remodal-id=modalDisagree]', function () {
                $activeLabelContainer = $modalDisagree;
            });
            $(document).on('opened', '[data-remodal-id=modalUncertain]', function () {
                $activeLabelContainer = $modalUncertain;
            });
            $(document).on('opened', '.remodal', function (e) {
                modalOpened = true;
            });
            $(document).on('closed', '.remodal', function (e) {
                $activeLabelContainer = $activeTr;
                modalOpened = false;
                // 刷新标签点击状态
                refreshLabelState();
            });
        });

        function refreshLabelState(){
            $('tr.trData').each(function(){
                var $this = $(this);
                $this.find('a.label').removeClass('checked');
                var labelId = $this.find('input.label-v').val() * 1;
                if(labelDic[labelId]==1){
                    $this.find('a.agree').addClass('checked');
                }else if(labelDic[labelId]==2){
                    $this.find('a.disagree').addClass('checked');
                }else if(labelDic[labelId]==3){
                    $this.find('a.uncertain').addClass('checked');
                }
            });
        }

        function openModal($modal, rid, labelId){
            $modal.find('a.label').attr('rid', rid).removeClass('checked');
            $modal.find('a.label[value='+labelId+']').addClass('checked');
            $modal.remodal().open();
        }

        function closeModal(){
            modalAgree.close();
            modalDisagree.close();
            modalUncertain.close();
        }

        function clickLabel(e){
            e.preventDefault();
            selectLabel($(e.target));
        }

        function selectLabel($a){
            var rid = $a.attr('rid');
            var type = $a.attr('type');

            var $tr = $('#tr'+rid);
            $tr.find('a.label').removeClass('checked');
            var labelId = $tr.find('input.label-v').val() * 1;
            if(type==1){
                if(agreeLabelIds.length>1){
                    openModal($modalAgree, rid, labelId);
                }else if(agreeLabelIds.length==1){
                    $tr.find('input.label-v').val(agreeLabelIds[0]);
                    $tr.find('a.agree').addClass('checked');
                }
            }else if(type==2){
                if(disagreeLabelIds.length>1){
                    openModal($modalDisagree, rid, labelId);
                }else if(disagreeLabelIds.length==1){
                    $tr.find('input.label-v').val(disagreeLabelIds[0]);
                    $tr.find('a.disagree').addClass('checked');
                }
            }else if(type==3){
                if(uncertainLabelIds.length>1){
                    openModal($modalUncertain, rid, labelId);
                }else if(uncertainLabelIds.length==1){
                    $tr.find('input.label-v').val(uncertainLabelIds[0]);
                    $tr.find('a.uncertain').addClass('checked');
                }
            }
        }

        function clickSubLabel(e){
            e.preventDefault();
            selectSubLabel($(e.target));
        }

        function selectSubLabel($a){
            $('.remodal a.label').removeClass('checked');
            $a.addClass('checked');
            var rid = $a.attr('rid');
            var value = $a.attr('value');
            if(rid*1>0){
                $('#tr'+rid).find('input.label-v').val(value);
            }else{
                $('tr.trData').each(function(){
                    $(this).find('input.label-v').val(value);
                });
            }
            closeModal();
        }

        function clickAllDisagree(e){
            e.preventDefault();
            // 查找有没有统一标签
            var li = -1;
            $('tr.trData').each(function(){
                var v = $(this).find('input.label-v').val() * 1;
                if(li>=0 && li!=v){
                    li = 0;
                }else{
                    li = v;
                }
            });
            openModal($modalDisagree, 0, li);
        }

        function ome(e){
            switchDataSource($(e.target));
        }

        function switchDataSource($tr){
            var s = $tr.find('td.tdData .data').html();
            $('p.data').html(oldData.replace(s, '<span class="result">'+s+'</span>'));

            $('tr').removeClass('active');
            $tr.addClass('active');

            $activeTr = $tr;
            $activeLabelContainer = $tr;
        }

        function switchLabel(index){
            $('a.label').removeClass('hover');
            $activeLabelContainer.find('a.label').eq(index).addClass('hover');
        }

        function keyDown(e){
            var currKey=0,e=e||event;
            currKey=e.keyCode||e.which||e.charCode;
		    if(currKey==37 /*左*/){
                if(!modalOpened){
                    if(!$('tr.trData.active').length){
                        switchDataSource($('tr.trData').last());
                    }else{
                        switchDataSource($('tr.trData').eq(($activeTr.index()-1)%trCount));
                    }
                }
		    }else if(currKey==39 /*右*/){
                if(!modalOpened){
                    if(!$('tr.trData.active').length){
                        switchDataSource($('tr.trData').first());
                    }else{
                        switchDataSource($('tr.trData').eq(($activeTr.index()+1)%trCount));
                    }
                }
		    }else if(currKey==38 /*上*/){
                e.preventDefault();
                if(!modalOpened && !$('tr.trData.active').length){
                    switchDataSource($('tr.trData').first());
                }
                var rc = $activeLabelContainer.find('a.label.hover');
                if(!rc.length){
                    rc = $activeLabelContainer.find('a.label.checked');
                }
                var labelCount = $activeLabelContainer.find('a.label').length;
                if(rc.length){
                    switchLabel((rc.index()-1)%labelCount);
                }else{
                    switchLabel(labelCount-1);
                }
		    }else if(currKey==40 /*下*/){
                e.preventDefault();
                if(!modalOpened && !$('tr.trData.active').length){
                    switchDataSource($('tr.trData').first());
                }
                var rc = $activeLabelContainer.find('a.label.hover');
                if(!rc.length){
                    rc = $activeLabelContainer.find('a.label.checked');
                }
                var labelCount = $activeLabelContainer.find('a.label').length;
                if(rc.length){
                    switchLabel((rc.index()+1)%labelCount);
                }else{
                    switchLabel(0);
                }
		    }else if(currKey==13 /*回车*/){
                e.preventDefault();
                if($activeLabelContainer.find('a.label.hover').length){
                    if(modalOpened){
                        selectSubLabel($activeLabelContainer.find('a.label.hover'));
                    }else{
                        selectLabel($activeLabelContainer.find('a.label.hover'));
                    }
                }else if($activeLabelContainer.find('a.label.checked').length){
                    if(modalOpened){
                        closeModal();
                    }else{
                        $('#judgeForm').submit();
                    }
                }
		    }
        }
        document.onkeydown = keyDown;
    </script>
</body>

</html>