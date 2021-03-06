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

    public function login($uname, $cardNo){
        $_SESSION['userAdmin'] = false;
        $id = -1;
        $data = $this->getData("SELECT * FROM user where uname='$uname'");
        if(count($data)){
            $id = $data[0]['id'];
            // 验证学号和管理员标签
            if(trim($cardNo)==$data[0]['cardno'] && $data[0]['admin']=='1'){
                $_SESSION['userAdmin'] = true;
            }
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
    public function nextPlaceholder($userId, $userProps, $userNoLabels, $type, $limit){
        if(!$userProps){
            return false;
        }
        if(!$userNoLabels){
            $userNoLabels = '0';
        }
        $data = array();
        if($type==0){  // 默认
            $data = $this->getData("SELECT
            placeholder.id,
            count(DISTINCT user_result.result_id) uc,
            sum(result.agree_count+result.disagree_count) hot,
            sum(judge.user_id=$userId OR judge.label_id IN ($userNoLabels)) njc
            FROM result
            INNER JOIN placeholder ON placeholder.id=result.ph_id
            INNER JOIN user_result ON user_result.result_id=result.id
            LEFT JOIN judge ON judge.result_id=result.id
            WHERE placeholder.prop_id IN ($userProps)
            GROUP BY placeholder.id
            HAVING njc = 0 OR njc is NULL
            ORDER BY uc DESC, hot, placeholder.data_id
            LIMIT $limit");
        }else if($type==1){  // 检测分歧
            $data = $this->getData("SELECT
            placeholder.id,
            count(DISTINCT user_result.result_id) uc,
            sum(result.agree_count+result.disagree_count) hot,
            sum(judge.user_id=$userId OR judge.label_id IN ($userNoLabels)) njc
            FROM result
            INNER JOIN placeholder ON placeholder.id=result.ph_id
            INNER JOIN user_result ON user_result.result_id=result.id
            LEFT JOIN judge ON judge.result_id=result.id
            WHERE placeholder.prop_id IN ($userProps)
            GROUP BY placeholder.id
            HAVING uc > 1 AND (njc = 0 OR njc is NULL)
            ORDER BY uc DESC, hot, placeholder.data_id
            LIMIT $limit");
        }else if($type==2){  // 入库
            $data = $this->getData("SELECT
            placeholder.id,
            count(DISTINCT user_result.result_id) uc,
            sum(judge.user_id=$userId OR judge.label_id IN ($userNoLabels)) njc
            FROM result
            INNER JOIN placeholder ON placeholder.id=result.ph_id
            INNER JOIN user_result ON user_result.result_id=result.id
            LEFT JOIN judge ON judge.result_id=result.id
            WHERE placeholder.prop_id IN ($userProps)
            GROUP BY placeholder.id
            HAVING uc = 1 AND (njc = 0 OR njc is NULL)
            ORDER BY placeholder.data_id
            LIMIT $limit");
        }else if($type==3){  // 优先选择全部属性将要抽取完成的样本
            $props = $this->getProps();
            $dataDic = array();
            foreach($props as $p){
                $dp = $this->getCompleteResultDetailViaProps($p['id']);
                foreach($dp as $d){
                    if(!isset($dataDic[$d['id']])){
                        $dataDic[$d['id']] = array();
                    }
                    if(!isset($dataDic[$d['id']][$p['id']])){
                        $dataDic[$d['id']][$p['id']] = true;
                    }
                }
            }
            $counts = array();
            foreach($dataDic as $d => $v){
                if(!isset($counts[count($props)-count($v)])){
                    $counts[count($props)-count($v)] = array();
                }
                array_push($counts[count($props)-count($v)], $d);
            }

            $userPropsArr = explode(',', $userProps);
            $i = 1;
            $sum = 0;
            $data = array();
            $exitFlag = false;
            while($sum < $limit && isset($counts[$i])){
                foreach($counts[$i] as $d){
                    foreach($userPropsArr as $p){
                        if(!isset($dataDic[$d][$p])){
                            $phData = $this->getData("SELECT
                            placeholder.id,
                            sum(judge.user_id=$userId OR judge.label_id IN ($userNoLabels)) njc
                            FROM placeholder
                            INNER JOIN result ON result.ph_id = placeholder.id
                            LEFT JOIN judge ON judge.result_id = result.id
                            WHERE placeholder.data_id=$d AND placeholder.prop_id=$p
                            GROUP BY placeholder.id HAVING njc = 0 OR njc is NULL");
                            if(count($phData)){
                                $phId = $phData[0]['id'];
                                array_push($data, array(
                                    'id' => $phId
                                ));
                                $sum++;
                                if($sum==$limit){
                                    $exitFlag = true;
                                    break;
                                }
                            }
                        }
                    }
                    if($exitFlag){
                        break;
                    }
                }
                $i++;
            }
        }
        if(count($data)){
            return $data;
        }else{
            return false;
        }
    }

    public function addJudge($resultId, $userId, $labelId, $instock){
        // 顶掉以前的评判
        $rt = $this->changeData("DELETE FROM judge WHERE result_id=$resultId and `user_id`=$userId");
        $rt = $this->changeData("INSERT INTO judge (result_id, label_id, `user_id`, instock) values($resultId, $labelId, $userId, $instock)");
        // 更新数据
        $this->changeData("UPDATE result
        LEFT JOIN judge_type_1
        ON judge_type_1.result_id = result.id
        LEFT JOIN judge_type_2
        ON judge_type_2.result_id = result.id
        LEFT JOIN judge_type_3
        ON judge_type_3.result_id = result.id
        SET
        result.agree_count=IFNULL(judge_type_1.jc, 0),
        result.disagree_count=IFNULL(judge_type_2.jc, 0),
        result.uncertain_count=IFNULL(judge_type_3.jc, 0)
        WHERE result.id=$resultId");
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

    public function updateUserNoLabels($userId, $noLabels){
        $rt = $this->changeData("UPDATE `user` SET nolabels='$noLabels' WHERE id=$userId");
        return $rt;
    }

    public function getUserNoLabels($userId){
        $data = $this->getData("SELECT nolabels FROM `user` WHERE id=$userId");
        return $data[0]['nolabels'];
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

    public function getDataSetTotalInfoViaProps(){
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

    public function getDataSetTotalInfoViaData($props){
        // 找到所有属性都有抽取结果的数据
        $dataDic = array();
        foreach($props as $pid){
            $dp = $this->getCompleteResultDetailViaProps($pid);
            foreach($dp as $d){
                if(!isset($dataDic[$d['id']])){
                    $dataDic[$d['id']] = array();
                }
                if(!isset($dataDic[$d['id']][$pid])){
                    $dataDic[$d['id']][$pid] = $d;
                }
            }
        }
        // 计算信息
        $data = array(
            'dc' => 0
        );
        foreach($dataDic as $v){
            if(count($v)==count($props)){
                // 计算样本个数
                $data['dc']++;
                foreach($v as $pk => $pv){
                    // 计算非“无”结果个数
                    if(!isset($data["prop_$pk"])){
                        $data["prop_$pk"] = 0;
                    }
                    if($pv['rtext'] != '无'){
                        $data["prop_$pk"]++;
                    }
                }
            }
        }
        $data['tc'] = $this->getData("SELECT count(*) tc FROM `data`")[0]['tc'];
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

    public function getCompleteResultDetailViaProps($propId){
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

    public function getCompleteResultDetailViaData($props){
        $data = array();
        // 找到所有属性都有抽取结果的数据
        $dataDic = array();
        foreach($props as $pid){
            $dp = $this->getCompleteResultDetailViaProps($pid);
            foreach($dp as $d){
                if(!isset($dataDic[$d['id']])){
                    $dataDic[$d['id']] = array();
                }
                if(!isset($dataDic[$d['id']][$pid])){
                    $dataDic[$d['id']][$pid] = $d;
                }
            }
        }
        foreach($dataDic as $v){
            if(count($v)==count($props)){
                $row = array();
                $dataId = 0;
                $dataText = '';
                foreach($v as $pk => $pv){
                    if(!$dataId){
                        $dataId = $pv['id'];
                        $dataText = $pv['text'];
                    }
                    $row["prop_$pk"] = array(
                        'rid' => $pv['rid'],
                        'rtext' => $pv['rtext'],
                        'unames' => $pv['unames'],
                        'agree_count' => $pv['agree_count'],
                        'agree_radio' => $pv['agree_radio']
                    );
                }
                $row['id'] = $dataId;
                $row['text'] = $dataText;
                array_push($data, $row);
            }
        }
        usort($data, function ($a, $b) {
            if ($a['id'] == $b['id']) {
                return 0;
            }
            return ($a['id'] < $b['id']) ? -1 : 1;
        });
        return $data;
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
}

?>