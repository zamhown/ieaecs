/*
 Navicat Premium Data Transfer

 Source Server         : 众包系统
 Source Server Type    : MySQL
 Source Server Version : 50553
 Source Host           : 222.204.216.24:3306
 Source Schema         : zb

 Target Server Type    : MySQL
 Target Server Version : 50553
 File Encoding         : 65001

 Date: 29/04/2019 20:17:45
*/

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for data
-- ----------------------------
DROP TABLE IF EXISTS `data`;
CREATE TABLE `data`  (
  `id` int(11) NOT NULL,
  `text` varchar(10000) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL COMMENT '原始数据字符串',
  `upload_id` int(11) NULL DEFAULT NULL COMMENT '用户上传记录号',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `upload_id`(`upload_id`) USING BTREE,
  CONSTRAINT `data_upload_id` FOREIGN KEY (`upload_id`) REFERENCES `upload` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = InnoDB CHARACTER SET = utf8 COLLATE = utf8_unicode_ci COMMENT = '原始数据' ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for judge
-- ----------------------------
DROP TABLE IF EXISTS `judge`;
CREATE TABLE `judge`  (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `result_id` int(11) NOT NULL COMMENT '检测对象id',
  `user_id` int(11) NOT NULL COMMENT '用户id',
  `label_id` int(11) NOT NULL COMMENT '检测结果id',
  `instock` tinyint(1) NOT NULL DEFAULT 0 COMMENT '是否是入库的决断',
  `time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '检测时间',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `user_id`(`user_id`) USING BTREE,
  INDEX `label_id`(`label_id`) USING BTREE,
  INDEX `result_id`(`result_id`) USING BTREE,
  INDEX `result_id_user_id`(`result_id`, `user_id`) USING BTREE,
  CONSTRAINT `judge_label_id` FOREIGN KEY (`label_id`) REFERENCES `label` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  CONSTRAINT `judge_result_id` FOREIGN KEY (`result_id`) REFERENCES `result` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `judge_user_id` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = InnoDB AUTO_INCREMENT = 121637 CHARACTER SET = utf8 COLLATE = utf8_unicode_ci COMMENT = '检测记录' ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for label
-- ----------------------------
DROP TABLE IF EXISTS `label`;
CREATE TABLE `label`  (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `text` varchar(20) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL COMMENT '特征名',
  `type` int(11) NOT NULL COMMENT '标签性质，1为赞同，2为反对，3为不确定',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `type`(`type`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 6 CHARACTER SET = utf8 COLLATE = utf8_unicode_ci COMMENT = '检测结果标签' ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for placeholder
-- ----------------------------
DROP TABLE IF EXISTS `placeholder`;
CREATE TABLE `placeholder`  (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `data_id` int(11) NOT NULL COMMENT '原始数据id',
  `prop_id` int(11) NOT NULL COMMENT '要抽取的属性id',
  `result_count` int(11) NOT NULL DEFAULT 0 COMMENT '已有的不同抽取结果数量',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `prop_id`(`prop_id`) USING BTREE,
  INDEX `data_id`(`data_id`) USING BTREE,
  INDEX `data_id_prop_id`(`data_id`, `prop_id`) USING BTREE,
  CONSTRAINT `placeholder_data_id` FOREIGN KEY (`data_id`) REFERENCES `data` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `placeholder_prop_id` FOREIGN KEY (`prop_id`) REFERENCES `prop` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = InnoDB AUTO_INCREMENT = 315145 CHARACTER SET = utf8 COLLATE = utf8_unicode_ci COMMENT = '所有可能的抽取位' ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for prop
-- ----------------------------
DROP TABLE IF EXISTS `prop`;
CREATE TABLE `prop`  (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `text` varchar(20) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL COMMENT '特征名',
  `kwreg` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL COMMENT '特征在数据里的关键词，用于高亮，形式是正则表达式',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 16 CHARACTER SET = utf8 COLLATE = utf8_unicode_ci COMMENT = '特征列表' ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for result
-- ----------------------------
DROP TABLE IF EXISTS `result`;
CREATE TABLE `result`  (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ph_id` int(11) NOT NULL COMMENT '抽取位id',
  `text` varchar(100) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL COMMENT '抽取出的结果',
  `text_crc` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '抽取结果的哈希',
  `agree_count` int(11) NOT NULL DEFAULT 0 COMMENT '赞同数量',
  `disagree_count` int(11) NOT NULL DEFAULT 0 COMMENT '反对数量',
  `uncertain_count` int(11) NOT NULL DEFAULT 0 COMMENT '不确定数量',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `ph_id`(`ph_id`) USING BTREE,
  INDEX `text_crc`(`text_crc`) USING BTREE,
  INDEX `ph_id_text_crc`(`ph_id`, `text_crc`) USING BTREE,
  CONSTRAINT `result_ph_id` FOREIGN KEY (`ph_id`) REFERENCES `placeholder` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = InnoDB AUTO_INCREMENT = 550885 CHARACTER SET = utf8 COLLATE = utf8_unicode_ci COMMENT = '全部已标注待检测的结果' ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for star
-- ----------------------------
DROP TABLE IF EXISTS `star`;
CREATE TABLE `star`  (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ph_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `user_id`(`user_id`) USING BTREE,
  INDEX `ph_id`(`ph_id`) USING BTREE,
  INDEX `ph_id_user_id`(`ph_id`, `user_id`) USING BTREE,
  CONSTRAINT `star_ph_id` FOREIGN KEY (`ph_id`) REFERENCES `placeholder` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `star_user_id` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = InnoDB AUTO_INCREMENT = 161 CHARACTER SET = utf8 COLLATE = utf8_unicode_ci COMMENT = '用户收藏的抽取位' ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for upload
-- ----------------------------
DROP TABLE IF EXISTS `upload`;
CREATE TABLE `upload`  (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL COMMENT '用户id',
  `filename` varchar(100) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL COMMENT '文件名',
  `type` int(11) NOT NULL COMMENT '数据类型，1为原始数据，2为已抽取好的数据',
  `time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '上传时间',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `user_id`(`user_id`) USING BTREE,
  CONSTRAINT `upload_user_id` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = InnoDB AUTO_INCREMENT = 145 CHARACTER SET = utf8 COLLATE = utf8_unicode_ci COMMENT = '用户数据上传记录' ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for user
-- ----------------------------
DROP TABLE IF EXISTS `user`;
CREATE TABLE `user`  (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uname` varchar(10) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL COMMENT '用户名',
  `password` varchar(32) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '用户密码',
  `props` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL COMMENT '用户擅长的属性id列表',
  `nolabels` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL COMMENT '用户检测时排除的已检测标签',
  `admin` tinyint(1) NOT NULL DEFAULT 0 COMMENT '是否是管理员',
  `cardno` varchar(10) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL COMMENT '学号',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 15 CHARACTER SET = utf8 COLLATE = utf8_unicode_ci COMMENT = '用户信息' ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for user_result
-- ----------------------------
DROP TABLE IF EXISTS `user_result`;
CREATE TABLE `user_result`  (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ph_id` int(11) NOT NULL COMMENT '抽取位id',
  `result_id` int(11) NOT NULL COMMENT '结果id',
  `user_id` int(11) NOT NULL COMMENT '用户id',
  `upload_id` int(11) NULL DEFAULT 0 COMMENT '用户上传记录号',
  `amendment` tinyint(1) NOT NULL DEFAULT 0,
  `time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `user_id`(`user_id`) USING BTREE,
  INDEX `upload_id`(`upload_id`) USING BTREE,
  INDEX `ph_id`(`ph_id`) USING BTREE,
  INDEX `result_id`(`result_id`) USING BTREE,
  INDEX `user_id_ph_id`(`ph_id`, `user_id`) USING BTREE,
  CONSTRAINT `user_result_ph_id` FOREIGN KEY (`ph_id`) REFERENCES `placeholder` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `user_result_result_id` FOREIGN KEY (`result_id`) REFERENCES `result` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `user_result_upload_id` FOREIGN KEY (`upload_id`) REFERENCES `upload` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `user_result_user_id` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = InnoDB AUTO_INCREMENT = 1952359 CHARACTER SET = utf8 COLLATE = utf8_unicode_ci COMMENT = '所有用户的标注结果' ROW_FORMAT = Compact;

-- ----------------------------
-- View structure for judge_type_1
-- ----------------------------
DROP VIEW IF EXISTS `judge_type_1`;
CREATE ALGORITHM = UNDEFINED DEFINER = `root`@`%` SQL SECURITY DEFINER VIEW `judge_type_1` AS select `judge`.`result_id` AS `result_id`,count(distinct `judge`.`id`) AS `jc` from (`judge` join `label` on((`label`.`id` = `judge`.`label_id`))) where (`label`.`type` = 1) group by `judge`.`result_id`;

-- ----------------------------
-- View structure for judge_type_2
-- ----------------------------
DROP VIEW IF EXISTS `judge_type_2`;
CREATE ALGORITHM = UNDEFINED DEFINER = `root`@`%` SQL SECURITY DEFINER VIEW `judge_type_2` AS select `judge`.`result_id` AS `result_id`,count(distinct `judge`.`id`) AS `jc` from (`judge` join `label` on((`label`.`id` = `judge`.`label_id`))) where (`label`.`type` = 2) group by `judge`.`result_id`;

-- ----------------------------
-- View structure for judge_type_3
-- ----------------------------
DROP VIEW IF EXISTS `judge_type_3`;
CREATE ALGORITHM = UNDEFINED DEFINER = `root`@`%` SQL SECURITY DEFINER VIEW `judge_type_3` AS select `judge`.`result_id` AS `result_id`,count(distinct `judge`.`id`) AS `jc` from (`judge` join `label` on((`label`.`id` = `judge`.`label_id`))) where (`label`.`type` = 3) group by `judge`.`result_id`;

SET FOREIGN_KEY_CHECKS = 1;
