<?php
include_once(dirname(__FILE__).'/../config/conf.php');
set_time_limit(0);

class DataManager{
    public $pdo;
    private $sttmt;

    function __construct(){
        $host = DB_HOST;
        $dbname = DB_NAME;
        $username = DB_USER_NAME;
        $password = DB_PASSWORD;
        date_default_timezone_set('Asia/Chongqing');
        $this->pdo = new PDO("mysql:dbname=$dbname;host=$host", $username, $password);
        $this->execute("SET NAMES 'UTF8'");
    }

    public function execute($sql, $args = null){
        try{
            $this->sttmt = $this->pdo->prepare($sql);

            if($args) $result = $this->sttmt->execute($args);
            else $result = $this->sttmt->execute();

            if($result===false) {
                $info=$this->sttmt->errorInfo();
                throw new Exception("数据读写失败，详细信息：" . $info[2]);
            }
            return $result;
        }catch(Exception $e){
            print_r($e);
        }
    }

    // 同execute，但返回受影响行数
    public function changeData($sql, $args = null){
        try{
            $this->sttmt = $this->pdo->prepare($sql);

            if($args) $result = $this->sttmt->execute($args);
            else $result = $this->sttmt->execute();

            if($result===false) {
                $info=$this->sttmt->errorInfo();
                throw new Exception("数据读写失败，详细信息：" . $info[2]);
            }
            $res = $this->sttmt->rowCount();
            return $res;
        }catch(Exception $e){
            print_r($e);
        }
    }

    public function getData($sql){
        $this->execute($sql);
        return $this->sttmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // 获取秒数时间戳
    public function getTimeSec(){
        return intval(microtime(true));
    }

    public function close(){
        $this->pdo = null;
    }

    public function login($uname){
        $id = -1;
        $data = $this->getData("SELECT * FROM user where uname='$uname'");
        if(count($data)){
            $id = $data[0]['id'];
        }else{
            $rt = $this->changeData("INSERT INTO user (uname) values('$uname')");
            if($rt){
                // 获取插入后的id（不用担心并发）
                $id = intval($this->getData("SELECT LAST_INSERT_ID() id")[0]['id']);
            }
        }
        $_SESSION['userName'] = $uname;
        $_SESSION['userId'] = $id;
        return $data;
    }

    public function getUserInfo($userId){
        $data = $this->getData("SELECT * FROM user where id=$userId");
        if(count($data)){
            return $data[0];
        }else{
            return false;
        }
    }

    public function addUpload($userId, $filename, $type){
        $this->changeData("INSERT INTO upload (`user_id`, `filename`, `type`) values($userId, '$filename', $type)");
        // 获取插入后的id（不用担心并发）
        $id = intval($this->getData("SELECT LAST_INSERT_ID() id")[0]['id']);
        return $id;
    }

    public function getUpload($id){
        $data = $this->getData("SELECT * FROM upload WHERE id=$id");
        return $data[0];
    }

    public function getUserUploads($userId){
        $data = $this->getData("SELECT * FROM upload WHERE `user_id`=$userId ORDER BY `time`");
        return $data;
    }

    public function delUpload($id){
        $rt = $this->changeData("DELETE FROM upload WHERE id=$id");
        return $rt;
    }

    public function addData($id, $text, $uploadId){
        $rt = $this->changeData("INSERT INTO `data` (id, `text`, upload_id) values($id, '$text', $uploadId)");
        return $rt;
    }

    public function getDataRecord($id){
        $data = $this->getData("SELECT * FROM `data` WHERE id=$id");
        return $data[0];
    }

    public function getProps(){
        $data = $this->getData("SELECT * FROM prop");
        return $data;
    }

    public function getProp($id){
        $data = $this->getData("SELECT * FROM prop WHERE id=$id");
        return $data[0];
    }

    public function setPropKwreg($id, $kwreg){
        $rt = $this->changeData("UPDATE prop SET kwreg='$kwreg' WHERE id=$id");
        return $rt;
    }

    public function getPlaceholder($dataId, $propId){
        $data = $this->getData("SELECT * FROM placeholder WHERE data_id=$dataId and prop_id=$propId");
        return $data;
    }

    public function getPlaceholderById($phId){
        $data = $this->getData("SELECT * FROM placeholder WHERE id=$phId");
        if(count($data)){
            return $data[0];
        }else{
            return false;
        }
    }

    public function addPlaceholder($dataId, $propId){
        $rt = 0;
        $data = $this->getPlaceholder($dataId, $propId);
        if(!count($data)){
            $rt = $this->changeData("INSERT INTO placeholder (data_id, prop_id) values($dataId, $propId)");
        }
        return $rt;
    }

    public function addResult($dataId, $propId, $userId, $text, $amendment, $uploadId='null'){
        $data = $this->getData("SELECT id FROM placeholder WHERE data_id=$dataId and prop_id=$propId");
        if(count($data)){
            return $this->addResultByPhId($data[0]['id'], $userId, $text, $amendment, $uploadId);
        }else{
            return 0;
        }
    }

    public function addResultByPhId($phId, $userId, $text, $amendment, $uploadId='null'){
        $id = 0;
        $data = $this->getData("SELECT id FROM result WHERE ph_id=$phId and text_crc=CRC32('$text') and `text`='$text'");
        if(count($data)){
            $id = intval($data[0]['id']);
        }else{
            $this->changeData("INSERT INTO result (ph_id, `text`, text_crc) values($phId, '$text', CRC32('$text'))");
            // 获取插入后的id（不用担心并发）
            $id = intval($this->getData("SELECT LAST_INSERT_ID() id")[0]['id']);
            $this->changeData("UPDATE placeholder SET result_count=result_count+1 WHERE id=$phId");
        }
        // 覆盖过去的抽取结果
        $this->changeData("DELETE FROM user_result WHERE `user_id`=$userId and ph_id=$phId");
        // 写入新的结果
        $this->changeData("INSERT INTO user_result (ph_id, result_id, `user_id`, upload_id, amendment) VALUES ($phId, $id, $userId, $uploadId, $amendment)");
        return $id;
    }

    public function getResult($id){
        $data = $this->getData("SELECT * FROM result WHERE id=$id");
        return $data[0];
    }

    public function getPlaceholderByResultId($id){
        $data = $this->getData("SELECT * FROM placeholder
        INNER JOIN result ON result.ph_id=placeholder.id
        WHERE result.id=$id");
        return $data[0];
    }

    public function getResultsForJudge($phId, $userId){
        $data = $this->getData("SELECT
        result.id, 
        result.text, 
        result.agree_count,
        result.disagree_count,
        result.uncertain_count,
        GROUP_CONCAT(`user`.uname SEPARATOR ',') unames,
        judge.label_id
        FROM result
        INNER JOIN user_result ON result.id=user_result.result_id
        INNER JOIN user ON user_result.user_id=user.id
        LEFT JOIN judge ON result.id=judge.result_id AND judge.user_id=$userId
        WHERE result.ph_id=$phId
        GROUP BY result.id
        ORDER BY result.agree_count+result.uncertain_count-result.disagree_count DESC");
        return $data;
    }

    public function getAmendment($phId, $userId){
        $data = $this->getData("SELECT result.text
        FROM result
        INNER JOIN user_result ON result.id=user_result.result_id
        WHERE result.ph_id=$phId
        and user_result.user_id=$userId and user_result.amendment=1");
        return $data;
    }

    public function getLabels(){
        $data = $this->getData("SELECT * FROM label");
        return $data;
    }

    // 取用户未检测过的、有分歧的、已检测（不含不确定）人数最少的抽取结果
    public function nextPlaceholder($userId, $userProps, $type, $limit){
        if(!$userProps){
            return false;
        }
        $data = array();
        if($type==0){  // 默认
            $data = $this->getData("SELECT
            placeholder.id,
            count(DISTINCT user_result.result_id) uc,
            sum(result.agree_count+result.disagree_count) hot
            FROM result
            INNER JOIN placeholder ON placeholder.id=result.ph_id
            INNER JOIN user_result ON user_result.result_id=result.id
            WHERE placeholder.prop_id IN ($userProps)
            AND (SELECT count(*) FROM judge WHERE result_id=result.id AND `user_id`=$userId)=0
            GROUP BY placeholder.id
            ORDER BY uc DESC, hot LIMIT $limit");
        }else if($type==1){  // 检测分歧
            $data = $this->getData("SELECT
            placeholder.id,
            count(DISTINCT user_result.result_id) uc,
            sum(result.agree_count+result.disagree_count) hot
            FROM result
            INNER JOIN placeholder ON placeholder.id=result.ph_id
            INNER JOIN user_result ON user_result.result_id=result.id
            WHERE placeholder.prop_id IN ($userProps)
            AND (SELECT count(*) FROM judge WHERE result_id=result.id AND `user_id`=$userId)=0
            GROUP BY placeholder.id HAVING uc > 1
            ORDER BY uc DESC, hot LIMIT $limit");
        }else if($type==2){  // 入库
            $data = $this->getData("SELECT
            placeholder.id,
            count(DISTINCT user_result.result_id) uc
            FROM result
            INNER JOIN placeholder ON placeholder.id=result.ph_id
            INNER JOIN user_result ON user_result.result_id=result.id
            WHERE placeholder.prop_id IN ($userProps)
            AND (SELECT count(*) FROM judge WHERE result_id=result.id)=0
            GROUP BY placeholder.id HAVING uc = 1
            LIMIT $limit");
        }
        if(count($data)){
            return $data;
        }else{
            return false;
        }
    }

    public function addJudge($resultId, $userId, $labelId, $instock){
        $labels = $this->getLabels();
        $labelTypes = array();
        foreach($labels as $l){
            $labelTypes[intval($l['id'])] = intval($l['type']);
        }
        $li = intval($labelId);

        $rt = 0;
        $needUpdateResult = false;
        $data = $this->getData("SELECT * FROM judge WHERE result_id=$resultId and `user_id`=$userId");
        if(count($data)){
            $oldLabelId = intval($data[0]['label_id']);
            $oldInstock = intval($data[0]['instock']);
            if($oldLabelId != $li || $oldInstock != $instock){
                $rt = $this->changeData("UPDATE judge SET label_id=$labelId AND instock=$instock WHERE id=".$data[0]['id']);
                if($labelTypes[$oldLabelId] != $labelTypes[$li]){
                    if($labelTypes[$oldLabelId]==1){
                        $this->changeData("UPDATE result SET agree_count=agree_count-1 WHERE id=$resultId");
                    }else if($labelTypes[$oldLabelId]==2){
                        $this->changeData("UPDATE result SET disagree_count=disagree_count-1 WHERE id=$resultId");
                    }else if($labelTypes[$oldLabelId]==3){
                        $this->changeData("UPDATE result SET uncertain_count=uncertain_count-1 WHERE id=$resultId");
                    }
                    $needUpdateResult = true;
                }
            }
        }else{
            $rt = $this->changeData("INSERT INTO judge (result_id, label_id, `user_id`, instock) values($resultId, $labelId, $userId, $instock)");
            $needUpdateResult = true;
        }
        if($needUpdateResult){
            if($labelTypes[$li]==1){
                $this->changeData("UPDATE result SET agree_count=agree_count+1 WHERE id=$resultId");
            }else if($labelTypes[$li]==2){
                $this->changeData("UPDATE result SET disagree_count=disagree_count+1 WHERE id=$resultId");
            }else if($labelTypes[$li]==3){
                $this->changeData("UPDATE result SET uncertain_count=uncertain_count+1 WHERE id=$resultId");
            }
        }
        return $rt;
    }

    public function getUserJudgeCount($userId){
        $data = $this->getData("SELECT count(*) as c FROM judge WHERE `user_id`=$userId");
        return $data[0]['c'];
    }

    public function getUserJudges($userId){
        $data = $this->getData("SELECT 
        judge.label_id, 
        judge.time, 
        result.id as rid,
        result.text as rtext,
        result.ph_id,
        placeholder.data_id, 
        placeholder.prop_id,
        `data`.text as dtext FROM judge
        INNER JOIN result ON result.id=judge.result_id
        INNER JOIN placeholder ON placeholder.id=result.ph_id
        INNER JOIN `data` ON `data`.id=placeholder.data_id
        WHERE judge.user_id=$userId ORDER BY judge.time DESC");
        return $data;
    }

    public function updateUserProps($userId, $props){
        $rt = $this->changeData("UPDATE `user` SET props='$props' WHERE id=$userId");
        return $rt;
    }

    public function getUserProps($userId){
        $data = $this->getData("SELECT props FROM `user` WHERE id=$userId");
        return $data[0]['props'];
    }

    public function getUserRank(){
        $data = $this->getData("SELECT
        `user`.id,
        `user`.uname,
        count(judge.id) jc
        FROM `user`
        INNER JOIN judge ON judge.user_id=`user`.id
        GROUP BY `user`.id
        ORDER BY jc DESC");

        foreach($data as &$u){
            $userId = $u['id'];
            // 贡献条数与收到赞同、反对
            $info = $this->getData("SELECT
            sum(result.agree_count) agree,
            sum(result.disagree_count) disagree,
            count(user_result.id) rcount
            FROM user_result
            INNER JOIN result ON result.id=user_result.result_id
            WHERE user_result.user_id=$userId");
            if($info[0]['agree']){
                $u['agree']=$info[0]['agree'];
            }else{
                $u['agree']=0;
            }
            if($info[0]['disagree']){
                $u['disagree']=$info[0]['disagree'];
            }else{
                $u['disagree']=0;
            }
            if($info[0]['rcount']){
                $u['rcount']=$info[0]['rcount'];
            }else{
                $u['rcount']=0;
            }

            // 消歧条数与入库条数
            $info = $this->getData("SELECT
            count(DISTINCT judge.id) jcount
            FROM judge
            INNER JOIN result ON result.id=judge.result_id
            INNER JOIN placeholder ph ON ph.id=result.ph_id
            WHERE judge.user_id=$userId
            AND judge.label_id=1
            AND (SELECT
            count(DISTINCT result.id)
            FROM user_result
            INNER JOIN result ON result.id=user_result.result_id
            WHERE result.ph_id=ph.id)>1");
            $u['diff_count']=$info[0]['jcount'];

            $info = $this->getData("SELECT
            count(DISTINCT judge.id) jcount
            FROM judge
            INNER JOIN result ON result.id=judge.result_id
            INNER JOIN placeholder ph ON ph.id=result.ph_id
            WHERE judge.user_id=$userId
            AND judge.label_id=1
            AND (SELECT
            count(DISTINCT result.id)
            FROM user_result
            INNER JOIN result ON result.id=user_result.result_id
            WHERE result.ph_id=ph.id)=1");
            $u['instock_count']=$info[0]['jcount'];
        }
        unset($u);
        return $data;
    }

    public function getResultInfo($userId){
        $data = $this->getData("SELECT
        placeholder.prop_id,
        sum(result.agree_count) agree,
        sum(result.disagree_count) disagree,
        count(user_result.id) rcount
        FROM user_result
        INNER JOIN result ON result.id=user_result.result_id
        INNER JOIN placeholder ON placeholder.id=result.ph_id
        WHERE user_result.user_id=$userId
        GROUP BY placeholder.prop_id
        ORDER BY rcount DESC");
        return $data;
    }

    public function getResultDiffInfo(){
        // 寻找每个属性中有分歧的记录条数
        $data = $this->getData("SELECT
        ph.prop_id,
        count(DISTINCT ph.id) dc
        FROM placeholder ph
        WHERE (SELECT
            count(DISTINCT result.id)
            FROM user_result
            INNER JOIN result ON result.id=user_result.result_id
            WHERE result.ph_id=ph.id)>1
        GROUP BY ph.prop_id
        ORDER BY dc DESC");

        // 寻找每个属性有分歧的记录中，被打上某个标签的记录条数
        foreach($data as &$p){
            $propId = $p['prop_id'];
            $info = $this->getData("SELECT
            judge.label_id,
            count(DISTINCT ph.id) uc
            FROM placeholder ph
            INNER JOIN result ON ph.id=result.ph_id
            INNER JOIN user_result ON user_result.result_id=result.id
            INNER JOIN judge ON judge.result_id=result.id
            WHERE ph.prop_id=$propId
            AND (SELECT
                count(DISTINCT result.id)
                FROM user_result
                INNER JOIN result ON result.id=user_result.result_id
                WHERE result.ph_id=ph.id)>1
            GROUP BY judge.label_id");

            $labelPhCount = array();
            foreach($info as $r){
                $labelPhCount[$r['label_id']] = $r['uc'];
            }
            $p['labelPhCount'] = $labelPhCount;
        }
        unset($p);
        return $data;
    }

    public function getResultInstockInfo(){
        // 寻找每个属性中没有分歧的记录条数
        $data = $this->getData("SELECT
        ph.prop_id,
        count(DISTINCT ph.id) dc
        FROM placeholder ph
        WHERE (SELECT
            count(DISTINCT result.id)
            FROM user_result
            INNER JOIN result ON result.id=user_result.result_id
            WHERE result.ph_id=ph.id)=1
        GROUP BY ph.prop_id
        ORDER BY dc DESC");

        // 寻找每个属性没有分歧的记录中，被打上某个标签的记录条数
        foreach($data as &$p){
            $propId = $p['prop_id'];
            $info = $this->getData("SELECT
            judge.label_id,
            count(DISTINCT ph.id) uc
            FROM placeholder ph
            INNER JOIN result ON ph.id=result.ph_id
            INNER JOIN user_result ON user_result.result_id=result.id
            INNER JOIN judge ON judge.result_id=result.id
            WHERE ph.prop_id=$propId
            AND (SELECT
                count(DISTINCT result.id)
                FROM user_result
                INNER JOIN result ON result.id=user_result.result_id
                WHERE result.ph_id=ph.id)=1
            GROUP BY judge.label_id");

            $labelPhCount = array();
            foreach($info as $r){
                $labelPhCount[$r['label_id']] = $r['uc'];
            }
            $p['labelPhCount'] = $labelPhCount;
        }
        unset($p);
        return $data;
    }

    public function getDataSetTotalInfo(){
        // 寻找每个属性中有分歧的记录条数和被打上某个标签的记录条数
        $dataDiff = $this->getResultDiffInfo();
        // 寻找每个属性中没有分歧的记录条数和被打上某个标签的记录条数
        $dataInstock = $this->getResultInstockInfo();

        $dataDiffDic = array();
        $data = array();
        foreach($dataDiff as $r){
            $dataDiffDic[$r['prop_id']]=$r;
        }
        foreach($dataInstock as $r2){
            $r1 = $dataDiffDic[$r2['prop_id']];
            $tc = $r1['dc'] + $r2['dc'];
            $cc = (isset($r1['labelPhCount']['1']) ? $r1['labelPhCount']['1'] : 0)
                + (isset($r2['labelPhCount']['1']) ? $r2['labelPhCount']['1'] : 0);
            array_push($data, array(
                'prop_id' => $r2['prop_id'],
                'tc' => $tc,
                'cc' => $cc,
                'rate' => $tc==0 ? -1 : round($cc/$tc*100,2)
            ));
        }
        usort($data, function ($a, $b) {
            if ($a['rate'] == $b['rate']) {
                return 0;
            }
            return ($a['rate'] > $b['rate']) ? -1 : 1;
        });
        return $data;
    }

    public function getJudgedDetail($userId, $propId, $type){
        $data = $this->getData("SELECT 
        `data`.id,
        `data`.text dtext,
        result.ph_id,
        user_result.id urid,
        result.text text,
        label.text ltext,
        GROUP_CONCAT(`user`.uname SEPARATOR ',') unames
        FROM
        `data`
        INNER JOIN placeholder ON placeholder.data_id = `data`.id
        INNER JOIN result ON placeholder.id = result.ph_id
        INNER JOIN user_result ON result.id = user_result.result_id
        INNER JOIN judge ON result.id = judge.result_id
        INNER JOIN label ON label.id = judge.label_id
        INNER JOIN `user` ON `user`.id = judge.user_id
        WHERE
        placeholder.prop_id = $propId AND user_result.user_id = $userId
        AND label.type = $type
        GROUP BY user_result.id, judge.label_id
        ORDER BY user_result.id");

        if(count($data)){
            $a = array(array($data[0]));
            for($i=1;$i<count($data);$i++){
                if($data[$i]['urid']!=$a[count($a)-1][0]['urid']){
                    array_push($a, array());
                }
                array_push($a[count($a)-1], $data[$i]);
            }
            return $a;
        }else{
            return array();
        }
    }

    public function getResultDiffDetail($propId){
        // 寻找某个属性中有分歧的所有标注结果和赞同率
        $data = $this->getData("SELECT
        `data`.id,
        `data`.text,
        `user`.uname,
        result.text rtext,
        result.agree_count*1.0/(result.agree_count+result.disagree_count) agree_radio
        FROM placeholder ph
        INNER JOIN `data` ON `data`.id=ph.data_id
        INNER JOIN result ON result.ph_id=ph.id
        INNER JOIN user_result ON user_result.result_id=result.id
        INNER JOIN `user` ON `user`.id=user_result.user_id
        WHERE (SELECT
            count(DISTINCT result.id)
            FROM user_result
            INNER JOIN result ON result.id=user_result.result_id
            WHERE result.ph_id=ph.id)>1
        AND ph.prop_id=$propId");
        return $data;
    }

    public function getResultInstockDetail($propId){
        // 寻找某个属性中没有分歧的标注结果和赞同率
        $data = $this->getData("SELECT
        `data`.id,
        `data`.text,
        GROUP_CONCAT(`user`.uname SEPARATOR ',') unames,
        result.text rtext,
        result.agree_count*1.0/(result.agree_count+result.disagree_count) agree_radio
        FROM placeholder ph
        INNER JOIN `data` ON `data`.id=ph.data_id
        INNER JOIN result ON result.ph_id=ph.id
        INNER JOIN user_result ON user_result.result_id=result.id
        INNER JOIN `user` ON `user`.id=user_result.user_id
        WHERE (SELECT
            count(DISTINCT result.id)
            FROM user_result
            INNER JOIN result ON result.id=user_result.result_id
            WHERE result.ph_id=ph.id)=1
        AND ph.prop_id=$propId
        GROUP BY result.id");
        return $data;
    }

    public function getCompleteResultDetail($propId){
        // 寻找某个属性中已处理过的标注结果和赞同率
        $data = $this->getData("SELECT
        `data`.id,
        `data`.text,
        result.id rid,
        GROUP_CONCAT(`user`.uname SEPARATOR ',') unames,
        result.text rtext,
        result.agree_count,
        result.agree_count*1.0/(result.agree_count+result.disagree_count) agree_radio
        FROM placeholder ph
        INNER JOIN `data` ON `data`.id=ph.data_id
        INNER JOIN result ON result.ph_id=ph.id
        INNER JOIN user_result ON user_result.result_id=result.id
        INNER JOIN `user` ON `user`.id=user_result.user_id
        INNER JOIN judge ON judge.result_id=result.id
        WHERE judge.label_id=1 AND ph.prop_id=$propId
        GROUP BY result.id
        ORDER BY `data`.id, agree_radio DESC, agree_count DESC");
        // 每个data取第一个抽取结果
        $dataDic = array();
        foreach($data as $r){
            if(!isset($dataDic[$r['id']])){
                $dataDic[$r['id']] = $r;
            }
        }
        return $dataDic;
    }

    public function isStar($phId, $userId){
        $data = $this->getData("SELECT count(*) c FROM star WHERE ph_id=$phId AND `user_id`=$userId");
        return $data[0]['c']>0;
    }

    public function toggleStar($phId, $userId){
        if($this->isStar($phId, $userId)){
            $this->changeData("DELETE FROM star WHERE ph_id=$phId AND `user_id`=$userId");
            return false;
        }else{
            $this->changeData("INSERT INTO star(ph_id, `user_id`) VALUES($phId, $userId)");
            return true;
        }
    }

    public function getStarRecords($userId){
        $data = $this->getData("SELECT
        star.ph_id,
        placeholder.prop_id,
        placeholder.data_id,
        `data`.text
        FROM star
        INNER JOIN placeholder ON placeholder.id=star.ph_id
        INNER JOIN `data` ON `data`.id=placeholder.data_id
        WHERE star.user_id=$userId
        ORDER BY star.id DESC");
        return $data;
    }

    /*
    private function get1000R_act($ids, $con, $limit){
        $idst = implode(',', $ids);
        $data = $this->getData("SELECT `data`.id, `data`.text FROM `data`
        RIGHT JOIN result ON `data`.id = result.data_id AND result.user_id = 2
        $con
        WHERE `data`.id not in ($idst)
        ORDER BY length(`data`.text) DESC LIMIT $limit");
        return $data;
    }

    public function get1000Records($r200){
        $texts = array();
        $ids = array();
        foreach($r200 as $r){
            $data = $this->getData("SELECT id FROM `data` WHERE `text`='$r'");
            if(count($data)){
                array_push($ids, $data[0]['id']);
            }
        }
        print_r($ids);
        // 标本50有10无
        $data = $this->get1000R_act($ids, "AND result.prop_id = 2 AND result.text <> '无'", 50);
        foreach($data as $d){ array_push($ids, $d['id']); array_push($texts, $d['text']); }
        $data = $this->get1000R_act($ids, "AND result.prop_id = 2 AND result.text = '无'", 10);
        foreach($data as $d){ array_push($ids, $d['id']); array_push($texts, $d['text']); }
        // 浸润深度60不同
        $data = $this->get1000R_act($ids, "AND result.prop_id = 3 AND result.text <> '无'", 60);
        foreach($data as $d){ array_push($ids, $d['id']); array_push($texts, $d['text']); }
        // 淋巴结转移比例50有10无
        $data = $this->get1000R_act($ids, "AND result.prop_id = 4 AND result.text <> '无'", 50);
        foreach($data as $d){ array_push($ids, $d['id']); array_push($texts, $d['text']); }
        $data = $this->get1000R_act($ids, "AND result.prop_id = 4 AND result.text = '无'", 10);
        foreach($data as $d){ array_push($ids, $d['id']); array_push($texts, $d['text']); }
        // 肿瘤大小50有10无
        $data = $this->get1000R_act($ids, "AND result.prop_id = 5 AND result.text <> '无'", 50);
        foreach($data as $d){ array_push($ids, $d['id']); array_push($texts, $d['text']); }
        $data = $this->get1000R_act($ids, "AND result.prop_id = 5 AND result.text = '无'", 10);
        foreach($data as $d){ array_push($ids, $d['id']); array_push($texts, $d['text']); }
        // 病理分类50有10无
        $data = $this->get1000R_act($ids, "AND result.prop_id = 6 AND result.text <> '无'", 50);
        foreach($data as $d){ array_push($ids, $d['id']); array_push($texts, $d['text']); }
        $data = $this->get1000R_act($ids, "AND result.prop_id = 6 AND result.text = '无'", 10);
        foreach($data as $d){ array_push($ids, $d['id']); array_push($texts, $d['text']); }
        // 淋巴结转移情况20是20否20无
        $data = $this->get1000R_act($ids, "AND result.prop_id = 7 AND result.text = '是'", 20);
        foreach($data as $d){ array_push($ids, $d['id']); array_push($texts, $d['text']); }
        $data = $this->get1000R_act($ids, "AND result.prop_id = 7 AND result.text = '否'", 20);
        foreach($data as $d){ array_push($ids, $d['id']); array_push($texts, $d['text']); }
        $data = $this->get1000R_act($ids, "AND result.prop_id = 7 AND result.text = '无'", 20);
        foreach($data as $d){ array_push($ids, $d['id']); array_push($texts, $d['text']); }
        // 病理等级50有10无
        $data = $this->get1000R_act($ids, "AND result.prop_id = 8 AND result.text <> '无'", 50);
        foreach($data as $d){ array_push($ids, $d['id']); array_push($texts, $d['text']); }
        $data = $this->get1000R_act($ids, "AND result.prop_id = 8 AND result.text = '无'", 10);
        foreach($data as $d){ array_push($ids, $d['id']); array_push($texts, $d['text']); }
        // 分化程度50有10无
        $data = $this->get1000R_act($ids, "AND result.prop_id = 9 AND result.text <> '无'", 50);
        foreach($data as $d){ array_push($ids, $d['id']); array_push($texts, $d['text']); }
        $data = $this->get1000R_act($ids, "AND result.prop_id = 9 AND result.text = '无'", 10);
        foreach($data as $d){ array_push($ids, $d['id']); array_push($texts, $d['text']); }
        // 后5个20是20否20无
        $data = $this->get1000R_act($ids, "AND result.prop_id = 10 AND result.text = '是'", 20);
        foreach($data as $d){ array_push($ids, $d['id']); array_push($texts, $d['text']); }
        $data = $this->get1000R_act($ids, "AND result.prop_id = 10 AND result.text = '否'", 20);
        foreach($data as $d){ array_push($ids, $d['id']); array_push($texts, $d['text']); }
        $data = $this->get1000R_act($ids, "AND result.prop_id = 10 AND result.text = '无'", 20);
        foreach($data as $d){ array_push($ids, $d['id']); array_push($texts, $d['text']); }

        $data = $this->get1000R_act($ids, "AND result.prop_id = 11 AND result.text = '是'", 20);
        foreach($data as $d){ array_push($ids, $d['id']); array_push($texts, $d['text']); }
        $data = $this->get1000R_act($ids, "AND result.prop_id = 11 AND result.text = '否'", 20);
        foreach($data as $d){ array_push($ids, $d['id']); array_push($texts, $d['text']); }
        $data = $this->get1000R_act($ids, "AND result.prop_id = 11 AND result.text = '无'", 20);
        foreach($data as $d){ array_push($ids, $d['id']); array_push($texts, $d['text']); }

        $data = $this->get1000R_act($ids, "AND result.prop_id = 12 AND result.text = '是'", 20);
        foreach($data as $d){ array_push($ids, $d['id']); array_push($texts, $d['text']); }
        $data = $this->get1000R_act($ids, "AND result.prop_id = 12 AND result.text = '否'", 20);
        foreach($data as $d){ array_push($ids, $d['id']); array_push($texts, $d['text']); }
        $data = $this->get1000R_act($ids, "AND result.prop_id = 12 AND result.text = '无'", 20);
        foreach($data as $d){ array_push($ids, $d['id']); array_push($texts, $d['text']); }

        $data = $this->get1000R_act($ids, "AND result.prop_id = 13 AND result.text = '是'", 20);
        foreach($data as $d){ array_push($ids, $d['id']); array_push($texts, $d['text']); }
        $data = $this->get1000R_act($ids, "AND result.prop_id = 13 AND result.text = '否'", 20);
        foreach($data as $d){ array_push($ids, $d['id']); array_push($texts, $d['text']); }
        $data = $this->get1000R_act($ids, "AND result.prop_id = 13 AND result.text = '无'", 20);
        foreach($data as $d){ array_push($ids, $d['id']); array_push($texts, $d['text']); }

        $data = $this->get1000R_act($ids, "AND result.prop_id = 14 AND result.text = '是'", 20);
        foreach($data as $d){ array_push($ids, $d['id']); array_push($texts, $d['text']); }
        $data = $this->get1000R_act($ids, "AND result.prop_id = 14 AND result.text = '否'", 20);
        foreach($data as $d){ array_push($ids, $d['id']); array_push($texts, $d['text']); }
        $data = $this->get1000R_act($ids, "AND result.prop_id = 14 AND result.text = '无'", 20);
        foreach($data as $d){ array_push($ids, $d['id']); array_push($texts, $d['text']); }

        // 腺癌形状50有10无
        $data = $this->get1000R_act($ids, "AND result.prop_id = 15 AND result.text <> '无'", 50);
        foreach($data as $d){ array_push($ids, $d['id']); array_push($texts, $d['text']); }
        $data = $this->get1000R_act($ids, "AND result.prop_id = 15 AND result.text = '无'", 10);
        foreach($data as $d){ array_push($ids, $d['id']); array_push($texts, $d['text']); }

        return $texts;
    }
    */
}

?>