<?php
include_once(dirname(__FILE__).'/../public/public.php');
include_once(dirname(__FILE__).'/../public/DataManager.class.php');

// 系统分配下一批目标
function nextBatch($db, $type){
    $data = $db->nextPlaceholder($_SESSION['userId'], $_SESSION['userProps'], $type, 100);
    if($data===false){
        echo "没有需要检测的数据！";
        echo '<br><br><a href="../index.php">返回</a>';
        exit();
    }
    $_SESSION["batch$type"] = $data;
    $_SESSION["batchPtr$type"] = 0;
}

// 系统分配下一个目标
function nextTarget($db, $type){
    if(!isset($_SESSION["batch$type"])
    || !isset($_SESSION["batchPtr$type"])
    || $_SESSION["batchPtr$type"]>=count($_SESSION["batch$type"])){
        nextBatch($db, $type);
    }
    return $_SESSION["batch$type"][$_SESSION["batchPtr$type"]];
}

$hasGet = isset($_GET['phid']) && $_GET['phid'];
$phId = 0;
$type = 0;  // 行为类型，0为默认（分歧+入库），1为检测分歧，2为入库
if(isset($_GET['type'])){
    $type = intval($_GET['type']);
}

$db = new DataManager();
if($hasGet){
    $phId = intval($_GET['phid']);
}else{
    // 改变用户擅长的属性类型
    if(isset($_POST['props'])){
        $userProps = implode(',', $_POST['props']);
        $db->updateUserProps($_SESSION['userId'], $userProps);
        $_SESSION['userProps'] = $userProps;
    }
    if(!isset($_SESSION['userProps'])){
        $_SESSION['userProps'] = '';
    }

    // 储存/更改检测结果
    if(isset($_POST['rlist'])){
        $rlist = json_decode($_POST['rlist'], true);
        foreach($rlist as $r){
            if(isset($_POST['r'.$r]) && $_POST['r'.$r]){
                $db->addJudge($r, $_SESSION['userId'], $_POST['r'.$r], $type==2?1:0);
            }
        }
        if(isset($_POST['amendment']) && $_POST['amendment']){
            $rid = $db->addResultByPhId($_POST['phId'], $_SESSION['userId'], $_POST['amendment'], 1);
            // 给自己写的结果自动点个赞（这里默认赞同的标签id为1）
            $db->addJudge($rid, $_SESSION['userId'], 1, $type==2?1:0);
        }
        // 历史记录与任务分配重合，则继续任务
        if(isset($_SESSION["batchPtr$type"])
        && isset($_SESSION["record$type"])
        && count($_SESSION["record$type"])
        && $_SESSION["record$type"][count($_SESSION["record$type"])-1]['phId']
        ==$_SESSION["batch$type"][$_SESSION["batchPtr$type"]]['id']){
            $_SESSION["batchPtr$type"]++;
        }
    }

    if(isset($_GET['clear'])){
        // 清空已分配的任务
        unset($_SESSION["batch$type"]);
        unset($_SESSION["batchPtr$type"]);
        // 清空缓存
        unset($_SESSION['propDic']);
        unset($_SESSION['labels']);
        unset($_SESSION['userJudgeCount']);
    }

    // 分配下一个
    $t = nextTarget($db, $type);
    $phId = $t['id'];
}

$ph = $db->getPlaceholderById($phId);
if(!$ph){
    echo "没有需要检测的数据！";
    echo '<br><br><a href="../index.php">返回</a>';
    exit();
}

// 做记录以备返回上一条
if(!isset($_SESSION["record$type"])){
    $_SESSION["record$type"] = array();
}
if(!(count($_SESSION["record$type"]) 
&& $_SESSION["record$type"][count($_SESSION["record$type"])-1]['phId'] == $phId)){
    array_push($_SESSION["record$type"], array(
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

// 读取数据
$data = $db->getDataRecord($ph['data_id']);
$res = $db->getResultsForJudge($phId, $_SESSION['userId']);
$isStar = $db->isStar($phId, $_SESSION['userId']);
$userJudgeCount = $db->getUserJudgeCount($_SESSION['userId']);
$db->close();
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
    <link href="../css/public.css" type=text/css rel=stylesheet>
    <link rel="stylesheet" href="../lib/remodal/remodal.css">
    <link rel="stylesheet" href="../lib/remodal/remodal-default-theme.css">
    <link rel="stylesheet" href="css/check.css?3">
    <script src="//cdn.staticfile.org/jquery/1.12.4/jquery.min.js"></script>
    <script src="../lib/remodal/remodal.min.js"></script>
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
            <form action="toggle-star.php?type=<?php echo $type ?>" method="post">
                <input type="hidden" name="phId" value="<?php echo $phId ?>">
                <input type="hidden" name="from" value="judge">
                <?php if(strlen($prop['kwreg'])){ ?>
                    <a style="font-size:12px" href="../setting/edit-prop-kwreg.php" title="点击自定义关键字高亮规则">若出现“<?php echo $prop['kwreg'] ?>”，将自动标为绿色&nbsp;&nbsp;</a>
                <?php } ?>
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
            <form id="judgeForm" action="check.php?type=<?php echo $type ?>" method="post">
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
                                <td class="tdName"><?php echo $r['unames'] ?></td>
                                <td class="tdProp"><?php echo $prop['text'] ?></td>
                                <td class="tdData">
                                    <span class="data"><?php echo $text ?></span>
                                    <?php
                                        if(!$r['label_id']){
                                            if($type==2){
                                                echo '<br><br><span style="color:red">【为方便操作，已默认标记为赞同】</span>';
                                            }else{
                                                if($gsError){
                                                    echo '<br><br><span style="color:red">【系统检测到格式可能出错，已自动标记】</span>';
                                                }else if($nrError){
                                                    echo '<br><br><span style="color:red">【系统检测到抽取可能出错，已自动标记】</span>';
                                                }
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
                                        }else if($type==2){
                                            echo '1';
                                        }else{
                                            if($gsError){
                                                echo '3';
                                            }else if($nrError){
                                                echo '2';
                                            }
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
                                <?php if(count($_SESSION["record$type"])>1){ ?>
                                    <a href="prev.php?type=<?php echo $type ?>">上一个</a><br>
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
        <a href="../user/judge-record.php">检测记录</a>&nbsp;&nbsp;
        <a href="../user/star-record.php">我的收藏</a>
    </p>
    <p>快捷键：←→：切换数据源，↑↓：选择检测结果，Enter：确定/提交</p>
    <p style="color:#888888;font-size:12px">本数据页地址：<span id="url"></span></p>
    <p><a href="../index.php">返回</a></p>

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

        var kwreg = '<?php echo sql_escape($prop['kwreg']) ?>';

        var url = location.protocol + '//' + location.host + location.pathname + '?phid=' + '<?php echo $phId ?>';
    </script>
    <script src="js/check.js?5"></script>
</body>

</html>