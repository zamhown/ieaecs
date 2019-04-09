<?php
$DEV = true;
if($DEV){
    define('DB_HOST', '222.204.216.24');
    define('DB_USER_NAME', 'root');
    define('DB_PASSWORD', '816817');
    define('DB_NAME', 'zb');
}else{
    define('DB_HOST', '127.0.0.1');  // 不能用localhost，localhost要先解析成ip地址，所以会占用一定的时间
    define('DB_USER_NAME', 'root');
    define('DB_PASSWORD', '816817');
    define('DB_NAME', 'zb');
}

define('TITLE', '肠癌诊断信息抽取准确性检测众包系统');
?>