<?php
@session_start();
include_once(dirname(__FILE__).'/../config/conf.php');

if(!isset($_SESSION['userName'])){
    header("Location: ../login.html");
    exit;
}
header("Content-type: text/html; charset=utf-8");
ini_set("upload_max_filesize", "10M");
ini_set("default_charset", "UTF-8");

$uploadErrors = array(
    'UPLOAD_ERR_OK',
    'UPLOAD_ERR_INI_SIZE',
    'UPLOAD_ERR_FORM_SIZE',
    'UPLOAD_ERR_PARTIAL',
    'UPLOAD_ERR_NO_FILE',
    '',
    'UPLOAD_ERR_NO_TMP_DIR',
    'UPLOAD_ERR_CANT_WRITE',
    'UPLOAD_ERR_EXTENSION'
);

// sql转义
function sql_escape($str){
    return str_replace("'", "\\'", str_replace("\\", "\\\\", $str));
}

// csv转义
function csv_escape($str){
    return '"'.str_replace('"', '""', $str).'"';
}

// 递归创建文件夹
function create_folders($dir){
    return is_dir($dir) || (create_folders(dirname($dir)) && mkdir($dir, 0777));
}
?>