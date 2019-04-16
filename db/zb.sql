# Host: 222.204.216.24  (Version: 5.5.53)
# Date: 2019-04-16 15:41:12
# Generator: MySQL-Front 5.3  (Build 4.234)

/*!40101 SET NAMES utf8 */;

#
# Structure for table "label"
#

DROP TABLE IF EXISTS `label`;
CREATE TABLE `label` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `text` varchar(20) COLLATE utf8_unicode_ci NOT NULL COMMENT '特征名',
  `type` int(11) NOT NULL COMMENT '标签性质，1为赞同，2为反对，3为不确定',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='检测结果标签';

#
# Structure for table "prop"
#

DROP TABLE IF EXISTS `prop`;
CREATE TABLE `prop` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `text` varchar(20) COLLATE utf8_unicode_ci NOT NULL COMMENT '特征名',
  `kwreg` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '特征在数据里的关键词，用于高亮，形式是正则表达式',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='特征列表';

#
# Structure for table "user"
#

DROP TABLE IF EXISTS `user`;
CREATE TABLE `user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uname` varchar(10) COLLATE utf8_unicode_ci NOT NULL COMMENT '用户名',
  `password` varchar(32) CHARACTER SET utf8 NOT NULL DEFAULT '' COMMENT '用户密码',
  `props` varchar(255) COLLATE utf8_unicode_ci NOT NULL COMMENT '用户擅长的属性id列表',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='用户信息';

#
# Structure for table "upload"
#

DROP TABLE IF EXISTS `upload`;
CREATE TABLE `upload` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL COMMENT '用户id',
  `filename` varchar(100) COLLATE utf8_unicode_ci NOT NULL COMMENT '文件名',
  `type` int(11) NOT NULL COMMENT '数据类型，1为原始数据，2为已抽取好的数据',
  `time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '上传时间',
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `upload_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=145 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='用户数据上传记录';

#
# Structure for table "data"
#

DROP TABLE IF EXISTS `data`;
CREATE TABLE `data` (
  `id` int(11) NOT NULL,
  `text` varchar(10000) COLLATE utf8_unicode_ci NOT NULL COMMENT '原始数据字符串',
  `upload_id` int(11) NOT NULL COMMENT '用户上传记录号',
  PRIMARY KEY (`id`),
  KEY `upload_id` (`upload_id`),
  CONSTRAINT `data_ibfk_1` FOREIGN KEY (`upload_id`) REFERENCES `upload` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='原始数据';

#
# Structure for table "placeholder"
#

DROP TABLE IF EXISTS `placeholder`;
CREATE TABLE `placeholder` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `data_id` int(11) NOT NULL COMMENT '原始数据id',
  `prop_id` int(11) NOT NULL COMMENT '要抽取的属性id',
  `result_count` int(11) NOT NULL DEFAULT '0' COMMENT '已有的不同抽取结果数量',
  PRIMARY KEY (`id`),
  KEY `data_id` (`data_id`,`prop_id`),
  KEY `prop_id` (`prop_id`),
  CONSTRAINT `placeholder_ibfk_1` FOREIGN KEY (`data_id`) REFERENCES `data` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `placeholder_ibfk_2` FOREIGN KEY (`prop_id`) REFERENCES `prop` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=315145 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='所有可能的抽取位';

#
# Structure for table "result"
#

DROP TABLE IF EXISTS `result`;
CREATE TABLE `result` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ph_id` int(11) NOT NULL COMMENT '抽取位id',
  `text` varchar(100) COLLATE utf8_unicode_ci NOT NULL COMMENT '抽取出的结果',
  `text_crc` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '抽取结果的哈希',
  `agree_count` int(11) NOT NULL DEFAULT '0' COMMENT '赞同数量',
  `disagree_count` int(11) NOT NULL DEFAULT '0' COMMENT '反对数量',
  `uncertain_count` int(11) NOT NULL DEFAULT '0' COMMENT '不确定数量',
  PRIMARY KEY (`id`),
  KEY `ph_id` (`ph_id`),
  KEY `text_crc` (`text_crc`) USING HASH,
  CONSTRAINT `result_ibfk_1` FOREIGN KEY (`ph_id`) REFERENCES `placeholder` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=550360 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='全部已标注待检测的结果';

#
# Structure for table "star"
#

DROP TABLE IF EXISTS `star`;
CREATE TABLE `star` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ph_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `ph_id` (`ph_id`,`user_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `star_ibfk_1` FOREIGN KEY (`ph_id`) REFERENCES `placeholder` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `star_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=156 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='用户收藏的抽取位';

#
# Structure for table "judge"
#

DROP TABLE IF EXISTS `judge`;
CREATE TABLE `judge` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `result_id` int(11) NOT NULL COMMENT '检测对象id',
  `user_id` int(11) NOT NULL COMMENT '用户id',
  `label_id` int(11) NOT NULL COMMENT '检测结果id',
  `instock` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否是入库的决断',
  `time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '检测时间',
  PRIMARY KEY (`id`),
  KEY `result_id` (`result_id`,`user_id`,`label_id`),
  KEY `user_id` (`user_id`),
  KEY `result_id_2` (`result_id`,`user_id`,`label_id`),
  KEY `label_id` (`label_id`),
  CONSTRAINT `judge_ibfk_1` FOREIGN KEY (`result_id`) REFERENCES `result` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `judge_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `judge_ibfk_3` FOREIGN KEY (`label_id`) REFERENCES `label` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=28152 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='检测记录';

#
# Structure for table "user_result"
#

DROP TABLE IF EXISTS `user_result`;
CREATE TABLE `user_result` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ph_id` int(11) NOT NULL COMMENT '抽取位id',
  `result_id` int(11) NOT NULL COMMENT '结果id',
  `user_id` int(11) NOT NULL COMMENT '用户id',
  `upload_id` int(11) NOT NULL DEFAULT '0' COMMENT '用户上传记录号',
  `amendment` tinyint(1) NOT NULL DEFAULT '0',
  `time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `result_id` (`result_id`,`user_id`),
  KEY `result_id_2` (`result_id`),
  KEY `user_id` (`user_id`),
  KEY `upload_id` (`upload_id`),
  KEY `ph_id` (`ph_id`),
  CONSTRAINT `user_result_ibfk_10` FOREIGN KEY (`ph_id`) REFERENCES `placeholder` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `user_result_ibfk_7` FOREIGN KEY (`result_id`) REFERENCES `result` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `user_result_ibfk_8` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `user_result_ibfk_9` FOREIGN KEY (`upload_id`) REFERENCES `upload` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1951750 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='所有用户的标注结果';
