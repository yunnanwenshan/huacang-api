-- MySQL dump 10.13  Distrib 5.7.13, for osx10.11 (x86_64)
--
-- Host: 115.231.92.59    Database: huacang
-- ------------------------------------------------------
-- Server version	5.6.22-72.0-log

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `brands`
--

DROP TABLE IF EXISTS `brands`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `brands` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT COMMENT '品牌编号',
  `brands` varchar(30) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL COMMENT '产品品牌',
  `user_id` int(11) DEFAULT NULL COMMENT '用户ID',
  `update_time` datetime DEFAULT NULL COMMENT '更新时间',
  `create_time` datetime DEFAULT NULL COMMENT '创建时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COMMENT='品牌';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `brands`
--

LOCK TABLES `brands` WRITE;
/*!40000 ALTER TABLE `brands` DISABLE KEYS */;
INSERT INTO `brands` VALUES (8,'华为',2,'2017-11-21 07:20:49','2017-11-21 07:20:49'),(9,'华为',2,'2017-11-21 07:26:05','2017-11-21 07:26:05'),(10,'华为',2,'2017-11-21 12:11:49','2017-11-21 12:11:49'),(11,'5',2,'2017-11-23 13:32:09','2017-11-23 13:32:09'),(12,'4',2,'2017-11-23 13:36:50','2017-11-23 13:36:50'),(13,'1',2,'2017-11-23 13:45:41','2017-11-23 13:45:41'),(15,'华为ddd',2,'2017-11-26 06:59:54','2017-11-26 06:59:54'),(16,'华为dddtest--------sdsd',2,'2017-11-26 07:02:44','2017-11-26 07:02:44');
/*!40000 ALTER TABLE `brands` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cart_items`
--

DROP TABLE IF EXISTS `cart_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cart_items` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT COMMENT '购物车项目编号',
  `cart_id` bigint(20) NOT NULL DEFAULT '0' COMMENT '购物车编号',
  `product_id` int(11) NOT NULL DEFAULT '0' COMMENT '商品编号',
  `count` int(11) NOT NULL DEFAULT '0' COMMENT '同一件商品数量',
  `price` int(11) NOT NULL DEFAULT '0' COMMENT '商品单价',
  `total_price` int(11) NOT NULL DEFAULT '0' COMMENT '同一件商品总价',
  `update_time` datetime DEFAULT NULL COMMENT '更新时间',
  `create_time` datetime DEFAULT NULL COMMENT '创建时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='购物车项目';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cart_items`
--

LOCK TABLES `cart_items` WRITE;
/*!40000 ALTER TABLE `cart_items` DISABLE KEYS */;
/*!40000 ALTER TABLE `cart_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `carts`
--

DROP TABLE IF EXISTS `carts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `carts` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT COMMENT '购物车编号',
  `user_id` int(11) NOT NULL DEFAULT '0' COMMENT '用户编号',
  `total_value` int(11) DEFAULT '0' COMMENT '购物车总金额',
  `total_number` int(11) DEFAULT '0' COMMENT '购物车商品总数量',
  `update_time` datetime DEFAULT NULL COMMENT '更新时间',
  `create_time` datetime DEFAULT NULL COMMENT '创建时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COMMENT='购物车';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `carts`
--

LOCK TABLES `carts` WRITE;
/*!40000 ALTER TABLE `carts` DISABLE KEYS */;
INSERT INTO `carts` VALUES (4,2,0,0,'2017-11-25 10:33:13','2017-11-25 09:18:40');
/*!40000 ALTER TABLE `carts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `class`
--

DROP TABLE IF EXISTS `class`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `class` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(20) COLLATE utf8_bin DEFAULT NULL COMMENT '类型名称',
  `parent_id` int(11) DEFAULT '0' COMMENT '一级分类id',
  `type` int(11) DEFAULT '0' COMMENT '0:产品分类 1 模板分类',
  `user_id` int(11) DEFAULT '0' COMMENT '用户ID,分类要跟用户相关',
  `create_time` datetime DEFAULT NULL,
  `update_time` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=52 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `class`
--

LOCK TABLES `class` WRITE;
/*!40000 ALTER TABLE `class` DISABLE KEYS */;
INSERT INTO `class` VALUES (15,'主分类名称',0,0,2,'2017-11-21 07:20:49','2017-11-21 07:20:49'),(16,'二级分类名称',15,0,2,'2017-11-21 07:20:49','2017-11-21 07:20:49'),(17,'主分类名称',0,0,2,'2017-11-21 07:26:05','2017-11-21 07:26:05'),(18,'二级分类名称',17,0,2,'2017-11-21 07:26:05','2017-11-21 07:26:05'),(19,'主分类名称',0,0,2,'2017-11-21 12:11:49','2017-11-21 12:11:49'),(20,'二级分类名称',19,0,2,'2017-11-21 12:11:49','2017-11-21 12:11:49'),(21,'耳机规格',0,1,2,'2017-11-22 02:50:27','2017-11-22 02:50:27'),(22,'耳机规格',0,1,2,'2017-11-22 02:51:24','2017-11-22 02:51:24'),(23,'手机',0,0,2,'2017-11-23 13:32:09','2017-11-23 13:32:09'),(24,'华为',23,0,2,'2017-11-23 13:32:09','2017-11-23 13:32:09'),(25,'手机',0,0,2,'2017-11-23 13:36:50','2017-11-23 13:36:50'),(26,'华为',25,0,2,'2017-11-23 13:36:50','2017-11-23 13:36:50'),(27,'手机',0,0,2,'2017-11-23 13:45:41','2017-11-23 13:45:41'),(28,'华为',27,0,2,'2017-11-23 13:45:41','2017-11-23 13:45:41'),(29,'we',0,1,2,'2017-11-26 05:34:01','2017-11-26 05:34:01'),(36,'主分类名称test',0,0,2,'2017-11-26 06:59:54','2017-11-26 06:59:54'),(37,'二级分类名称test',36,0,2,'2017-11-26 06:59:54','2017-11-26 06:59:54'),(39,'fweae',0,1,2,'2017-11-26 08:07:02','2017-11-26 08:07:02'),(40,'27',0,0,2,'2017-11-26 08:27:29','2017-11-26 08:27:29'),(41,'27',40,0,2,'2017-11-26 08:27:29','2017-11-26 08:27:29'),(42,'haha',0,0,2,'2017-11-26 08:31:08','2017-11-26 08:31:08'),(43,'haha',42,0,2,'2017-11-26 08:31:08','2017-11-26 08:31:08'),(44,'42',0,0,2,'2017-11-26 08:35:57','2017-11-26 08:35:57'),(45,'42',44,0,2,'2017-11-26 08:35:57','2017-11-26 08:35:57'),(46,'44',0,0,2,'2017-11-26 08:37:27','2017-11-26 08:37:27'),(47,'44',46,0,2,'2017-11-26 08:37:27','2017-11-26 08:37:27'),(48,'46',0,0,2,'2017-11-26 19:15:11','2017-11-26 19:15:11'),(49,'46',48,0,2,'2017-11-26 19:15:11','2017-11-26 19:15:11'),(50,'36',0,0,2,'2017-11-26 19:22:19','2017-11-26 19:22:19'),(51,'37',50,0,2,'2017-11-26 19:22:19','2017-11-26 19:22:19');
/*!40000 ALTER TABLE `class` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `django_migrations`
--

DROP TABLE IF EXISTS `django_migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `django_migrations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `app` varchar(255) COLLATE utf8_bin NOT NULL,
  `name` varchar(255) COLLATE utf8_bin NOT NULL,
  `applied` datetime(6) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `django_migrations`
--

LOCK TABLES `django_migrations` WRITE;
/*!40000 ALTER TABLE `django_migrations` DISABLE KEYS */;
/*!40000 ALTER TABLE `django_migrations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `image`
--

DROP TABLE IF EXISTS `image`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `image` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL COMMENT '用户ID',
  `url` varchar(256) COLLATE utf8_bin DEFAULT NULL COMMENT '图片地址（七牛服务器地址）',
  `type` int(11) DEFAULT NULL COMMENT '图片类型：1, 图片类型: 1 头像，2 主图片(product-main_img) 3 产品展示图片(product-sub_img)',
  `create_time` datetime DEFAULT NULL,
  `update_time` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `image`
--

LOCK TABLES `image` WRITE;
/*!40000 ALTER TABLE `image` DISABLE KEYS */;
/*!40000 ALTER TABLE `image` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mobile_code`
--

DROP TABLE IF EXISTS `mobile_code`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mobile_code` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mobile` char(11) NOT NULL COMMENT '手机号',
  `code` varchar(10) NOT NULL COMMENT '验证码',
  `type` tinyint(4) NOT NULL COMMENT '验证码类型：1:登录注册  2:换绑手机号 3:邀请好友 4: 分享红包',
  `verify_times` tinyint(4) NOT NULL DEFAULT '0' COMMENT '验证失败次数',
  `code_expired` datetime NOT NULL COMMENT '过期时间',
  `create_time` datetime NOT NULL COMMENT '创建时间',
  `update_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `mobile_type` (`mobile`,`type`),
  KEY `idx_mobile` (`mobile`)
) ENGINE=InnoDB AUTO_INCREMENT=34 DEFAULT CHARSET=utf8mb4 COMMENT='手机验证码';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mobile_code`
--

LOCK TABLES `mobile_code` WRITE;
/*!40000 ALTER TABLE `mobile_code` DISABLE KEYS */;
INSERT INTO `mobile_code` VALUES (2,'13311588124','0',0,0,'0000-00-00 00:00:00','2017-11-15 15:23:14','2017-11-15 15:23:35'),(13,'158 0159 54','4451',0,0,'2017-12-06 16:19:26','2017-12-06 16:09:26','2017-12-06 16:09:26'),(19,'15210583226','0',0,0,'0000-00-00 00:00:00','2017-12-07 12:29:09','2017-12-07 12:56:51'),(20,'15210353226','0',0,0,'0000-00-00 00:00:00','2017-12-07 12:30:53','2017-12-07 12:31:04'),(33,'15801595467','0',0,0,'0000-00-00 00:00:00','2017-12-14 23:39:26','2017-12-14 23:39:38');
/*!40000 ALTER TABLE `mobile_code` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `order_product_detail`
--

DROP TABLE IF EXISTS `order_product_detail`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `order_product_detail` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL DEFAULT '0' COMMENT '用户ID',
  `order_sn` varchar(50) NOT NULL DEFAULT '' COMMENT '订单编号',
  `product_id` int(11) NOT NULL DEFAULT '0' COMMENT '产品编号',
  `product_num` int(11) NOT NULL DEFAULT '0' COMMENT '产品数量',
  `detail` varchar(4096) NOT NULL DEFAULT '' COMMENT '产品详情',
  `create_time` datetime NOT NULL COMMENT '创建时间',
  `update_time` datetime NOT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='订单商品关系表';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `order_product_detail`
--

LOCK TABLES `order_product_detail` WRITE;
/*!40000 ALTER TABLE `order_product_detail` DISABLE KEYS */;
/*!40000 ALTER TABLE `order_product_detail` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `orders`
--

DROP TABLE IF EXISTS `orders`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `orders` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `sn` varchar(50) COLLATE utf8_bin NOT NULL DEFAULT '' COMMENT '订单编号',
  `user_id` int(11) DEFAULT '0' COMMENT '下单用户',
  `supplier_id` int(11) DEFAULT '0' COMMENT '供应商',
  `share_id` int(11) DEFAULT '0' COMMENT '分享id',
  `total_fee` int(11) DEFAULT '0' COMMENT '订单金额',
  `status` int(11) DEFAULT '0' COMMENT '订单状态：0:已下单 1 待支付， 2 待发货， 3 已发货， 4 待审核， 5 待退款， 6 已完成， 7 已取消， 8 客户申请取消',
  `order_detail` varchar(4098) COLLATE utf8_bin DEFAULT '' COMMENT '订单详情：[{"product_id": 12, "num": 23, "price": 23432}]',
  `remark` varchar(100) COLLATE utf8_bin DEFAULT '' COMMENT '备注',
  `start_time` datetime DEFAULT '0000-00-00 00:00:00' COMMENT '下单时间',
  `end_time` datetime DEFAULT '0000-00-00 00:00:00' COMMENT '订单结束时间',
  `update_time` datetime DEFAULT NULL COMMENT '更新时间',
  `create_time` datetime DEFAULT NULL COMMENT '创建时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `orders`
--

LOCK TABLES `orders` WRITE;
/*!40000 ALTER TABLE `orders` DISABLE KEYS */;
INSERT INTO `orders` VALUES (1,'T17112537993062300002',2,0,12,1200,6,'[{\"user_product_id\":2,\"count\":20,\"price\":60,\"fee\":1200}]','','2017-11-25 10:33:13','0000-00-00 00:00:00','2017-11-26 11:25:12','2017-11-25 10:33:13'),(2,'T17120762255049500002',2,0,15,120,6,'[{\"user_product_id\":2,\"count\":2,\"price\":60,\"fee\":120}]','','2017-12-07 17:17:35','0000-00-00 00:00:00','2017-12-07 17:17:35','2017-12-07 17:17:35'),(3,'T17120764148056700002',2,0,12,120,0,'[{\"user_product_id\":2,\"count\":2,\"price\":60,\"fee\":120}]','','2017-12-07 17:49:08','0000-00-00 00:00:00','2017-12-07 17:49:08','2017-12-07 17:49:08'),(4,'T1712078039301270000E',14,0,12,7380,0,'[{\"user_product_id\":2,\"count\":123,\"price\":60,\"fee\":7380}]','','2017-12-07 22:19:53','0000-00-00 00:00:00','2017-12-07 22:19:53','2017-12-07 22:19:53'),(5,'T1712130826001860000E',14,0,12,2760,0,'[{\"user_product_id\":2,\"count\":23,\"price\":60,\"fee\":1380},{\"user_product_id\":3,\"count\":23,\"price\":60,\"fee\":1380}]','','2017-12-13 02:17:40','0000-00-00 00:00:00','2017-12-13 02:17:40','2017-12-13 02:17:40'),(6,'T1712131006009010000E',14,0,12,720,0,'[{\"user_product_id\":2,\"count\":12,\"price\":60,\"fee\":720}]','','2017-12-13 02:47:40','0000-00-00 00:00:00','2017-12-13 02:47:40','2017-12-13 02:47:40');
/*!40000 ALTER TABLE `orders` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `product`
--

DROP TABLE IF EXISTS `product`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `product` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) COLLATE utf8_bin DEFAULT NULL,
  `type` int(11) NOT NULL DEFAULT '1' COMMENT '1 实物 2 虚拟',
  `class_id` int(11) DEFAULT '0' COMMENT '分类ID',
  `brand_id` int(11) DEFAULT '0' COMMENT '分类ID',
  `code` varchar(20) COLLATE utf8_bin DEFAULT '0' COMMENT '产品编号',
  `brands` varchar(30) COLLATE utf8_bin DEFAULT NULL COMMENT '产品品牌',
  `valid_time` datetime DEFAULT NULL COMMENT '生效日期',
  `main_img` varchar(501) COLLATE utf8_bin DEFAULT NULL COMMENT '主图片,产品默认图片',
  `sub_img` varchar(2048) COLLATE utf8_bin DEFAULT NULL COMMENT '产品展示图片',
  `detail` varchar(4096) COLLATE utf8_bin DEFAULT NULL COMMENT '产品详情',
  `is_test` int(11) NOT NULL DEFAULT '0' COMMENT '0 正式产品 1测试产品',
  `template_id` int(11) DEFAULT NULL COMMENT '模板编号',
  `user_id` int(11) DEFAULT NULL COMMENT '创建产品的用户id',
  `create_time` datetime DEFAULT NULL,
  `update_time` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `product`
--

LOCK TABLES `product` WRITE;
/*!40000 ALTER TABLE `product` DISABLE KEYS */;
INSERT INTO `product` VALUES (4,'华为手机',1,16,8,'1231','华为','2017-12-12 00:00:00','/uploads/image_06.jpeg','[\"\\/uploads\\/image_07.jpeg\",\"\\/uploads\\/image_01.jpeg\",\"\\/uploads\\/image_10.jpg\"]','2342',0,2,2,'2017-11-21 07:20:50','2017-12-03 05:53:19'),(5,'华为手机34',1,18,8,'1231','华为','2017-12-12 00:00:00','/uploads/image_07.jpg','[\"\\/uploads\\/image_04.jpg\",\"\\/uploads\\/image_03.jpg\"]','2342',0,3,2,'2017-11-21 07:26:05','2017-12-02 10:04:14'),(6,'华为手机',1,20,8,'1231','华为','2017-12-12 00:00:00','/uploads/image_08.jpg','[\"\\/uploads\\/image_04.jpg\"]','2342',0,4,2,'2017-11-21 12:11:49','2017-12-02 10:02:12'),(7,'liu test',1,24,11,'0','5','2017-12-12 00:00:00','/uploads/image_01.jpg','[\"\\/uploads\\/image_06.jpg\"]','aa产品详情 不能为空!',0,1,2,'2017-11-23 13:32:09','2017-12-02 09:41:39'),(8,'liu test',1,16,8,'0','华为','2017-12-12 00:00:00','/uploads/image_04.jpg','[\"\\/uploads\\/image_08.jpg\"]','产品详情 不能为空!',0,3,2,'2017-11-23 13:36:50','2017-12-02 09:46:29'),(9,'liu test',1,37,15,'abcde','华为ddd','2017-12-12 00:00:00','/uploads/image_11.jpg','[\"\\/uploads\\/image_07.jpg\",\"\\/uploads\\/image_09.jpg\"]','产品详情 不能为空!',0,3,2,'2017-11-23 13:45:41','2017-12-02 09:53:31'),(11,'华为手机test',1,37,15,'1231eee','华为ddd','2017-12-12 00:00:00','/uploads/image_04.jpg','[\"\\/uploads\\/image_08.jpg\",\"\\/uploads\\/image_07.jpg\"]','2342',0,3,2,'2017-11-26 06:59:54','2017-12-02 09:53:11'),(12,'华为手机test',1,38,15,'1231eee','华为ddd','2017-12-05 00:00:00','http://d.hiphotos.baidu.com/image/pic/item/5bafa40f4bfbfbedc0c97cbf71f0f736aec31f59.jpg','[\"http:\\/\\/www.baidu.com\",\"http:\\/\\/www.baidu.com\"]','2342',0,3,2,'2017-11-26 07:00:11','2017-11-26 07:00:11'),(13,'华为手机test',1,37,15,'1231eee','华为ddd','2017-12-05 00:00:00','http://d.hiphotos.baidu.com/image/pic/item/5bafa40f4bfbfbedc0c97cbf71f0f736aec31f59.jpg','[\"http:\\/\\/www.baidu.com\",\"http:\\/\\/www.baidu.com\"]','2342',0,3,2,'2017-11-26 07:02:09','2017-11-26 07:02:09'),(14,'华为手机test',1,37,16,'1231eee','华为dddtest--------sdsd','2017-12-05 00:00:00','http://d.hiphotos.baidu.com/image/pic/item/5bafa40f4bfbfbedc0c97cbf71f0f736aec31f59.jpg','[\"http:\\/\\/www.baidu.com\",\"http:\\/\\/www.baidu.com\"]','2342',0,3,2,'2017-11-26 07:02:44','2017-11-26 07:02:44'),(15,'华为手机test',1,37,16,'1231eee','华为dddtest--------sdsd','2017-12-05 00:00:00','http://d.hiphotos.baidu.com/image/pic/item/5bafa40f4bfbfbedc0c97cbf71f0f736aec31f59.jpg','[\"http:\\/\\/www.baidu.com\",\"http:\\/\\/www.baidu.com\"]','2342',0,3,2,'2017-11-26 07:03:11','2017-11-26 07:03:11'),(16,'wqeqwe',1,16,8,'12121','华为','2017-12-12 00:00:00','http://api.huacang.com/uploads/WechatIMG1814.jpeg','[\"http:\\/\\/api.huacang.com\\/uploads\\/\\u5c4f\\u5e55\\u5feb\\u7167 2017-11-16 \\u4e0b\\u53487.52.54.png\",\"http:\\/\\/api.huacang.com\\/uploads\\/\\u5c4f\\u5e55\\u5feb\\u7167 2017-11-16 \\u4e0b\\u53487.52.28.png\"]','wewew',0,1,2,'2017-11-27 06:55:42','2017-11-27 06:55:42'),(17,'werae',1,16,8,'23242','华为','2017-12-12 00:00:00','http://api.huacang.com/uploads/WechatIMG297.jpeg','[\"http:\\/\\/api.huacang.com\\/uploads\\/\\u5c4f\\u5e55\\u5feb\\u7167 2017-11-16 \\u4e0b\\u53487.53.02.png\",\"http:\\/\\/api.huacang.com\\/uploads\\/\\u5c4f\\u5e55\\u5feb\\u7167 2017-11-16 \\u4e0b\\u53487.50.50.png\"]','aewea',0,3,2,'2017-11-27 07:07:36','2017-11-27 07:07:36'),(18,'ltp1234',1,16,15,'1234','华为ddd','2017-12-12 00:00:00','/uploads/test1234123.png','[\"\\/uploads\\/test1234123.png\"]','sdfasdf',0,1,2,'2017-12-03 21:04:54','2017-12-03 21:04:54');
/*!40000 ALTER TABLE `product` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `product_detail`
--

DROP TABLE IF EXISTS `product_detail`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `product_detail` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `user_token` int(11) DEFAULT NULL COMMENT '用户标识',
  `user_id` int(11) DEFAULT NULL COMMENT '用户ID (已登陆时拿到)',
  `order_sn` int(11) DEFAULT NULL COMMENT '订单编号',
  `product_id` int(11) DEFAULT NULL COMMENT '产品编号',
  `product_num` int(11) DEFAULT NULL COMMENT '产品数量',
  `status` int(11) DEFAULT NULL COMMENT '产品状态：1 购物车，2 订单中, 3 已完成， 4 已退货',
  `create_time` datetime DEFAULT NULL COMMENT '创建时间',
  `update_time` datetime DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `product_detail`
--

LOCK TABLES `product_detail` WRITE;
/*!40000 ALTER TABLE `product_detail` DISABLE KEYS */;
/*!40000 ALTER TABLE `product_detail` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `share`
--

DROP TABLE IF EXISTS `share`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `share` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL COMMENT '用户id',
  `name` varchar(50) COLLATE utf8_bin DEFAULT '',
  `url` int(11) DEFAULT NULL COMMENT '分享链接',
  `create_time` datetime DEFAULT NULL,
  `update_time` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `share`
--

LOCK TABLES `share` WRITE;
/*!40000 ALTER TABLE `share` DISABLE KEYS */;
INSERT INTO `share` VALUES (12,2,'第一商城',0,'2017-11-26 04:53:58','2017-11-26 04:53:58'),(13,2,'第二商城',0,'2017-11-26 09:12:02','2017-11-26 09:12:02'),(14,2,'一级商城',0,'2017-11-30 18:27:45','2017-11-30 18:27:45'),(15,2,'哇哈哈商城',0,'2017-12-02 17:36:01','2017-12-02 17:36:01');
/*!40000 ALTER TABLE `share` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `share_detail`
--

DROP TABLE IF EXISTS `share_detail`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `share_detail` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `share_id` int(11) DEFAULT NULL COMMENT '分享ID',
  `product_id` int(11) DEFAULT NULL COMMENT '产品id',
  `cost_price` int(11) DEFAULT NULL COMMENT '进货价',
  `supply_price` int(11) DEFAULT NULL COMMENT '供应价',
  `create_time` datetime DEFAULT NULL,
  `update_time` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=35 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `share_detail`
--

LOCK TABLES `share_detail` WRITE;
/*!40000 ALTER TABLE `share_detail` DISABLE KEYS */;
INSERT INTO `share_detail` VALUES (17,12,4,43,56,'2017-11-26 04:53:58','2017-11-26 10:12:44'),(18,12,5,43,56,'2017-11-26 04:53:58','2017-11-26 10:12:44'),(31,12,8,100,130,'2017-11-26 05:00:43','2017-11-26 10:12:44'),(32,13,8,100,130,'2017-11-26 09:12:02','2017-11-26 09:12:02'),(33,14,4,43,56,'2017-11-30 18:27:45','2017-11-30 18:27:45'),(34,15,4,43,56,'2017-12-02 17:36:01','2017-12-02 17:36:01');
/*!40000 ALTER TABLE `share_detail` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `template`
--

DROP TABLE IF EXISTS `template`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `template` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `template_name` varchar(100) COLLATE utf8_bin DEFAULT NULL COMMENT '模板名称',
  `user_id` int(11) DEFAULT NULL COMMENT '用户id',
  `class_id` int(11) DEFAULT NULL COMMENT '主分类',
  `status` int(11) DEFAULT NULL COMMENT '0: 有效， 1 无效（已删除）',
  `create_time` datetime DEFAULT NULL,
  `update_time` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `template`
--

LOCK TABLES `template` WRITE;
/*!40000 ALTER TABLE `template` DISABLE KEYS */;
INSERT INTO `template` VALUES (1,'铁三角规格模板',2,21,0,'2017-11-22 02:50:27','2017-11-22 02:50:27'),(2,'铁三角规格模板',2,22,0,'2017-11-22 02:51:24','2017-11-22 02:51:24'),(3,'12312',2,29,0,'2017-11-26 05:34:01','2017-11-26 05:34:01'),(4,'liutest',2,39,0,'2017-11-26 08:07:02','2017-11-26 08:07:02');
/*!40000 ALTER TABLE `template` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `template_form_item`
--

DROP TABLE IF EXISTS `template_form_item`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `template_form_item` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `template_id` int(11) DEFAULT NULL COMMENT '模板id',
  `form_name` varchar(100) COLLATE utf8_bin DEFAULT NULL COMMENT '规格名称',
  `form_content` varchar(500) COLLATE utf8_bin DEFAULT NULL COMMENT '规格内容',
  `create_time` datetime DEFAULT NULL COMMENT '创建时间',
  `update_time` datetime DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `template_form_item`
--

LOCK TABLES `template_form_item` WRITE;
/*!40000 ALTER TABLE `template_form_item` DISABLE KEYS */;
INSERT INTO `template_form_item` VALUES (1,1,'铁三角规格模板','[{\"name\":\"\\u9891\\u54cd\\u8303\\u56f4\",\"content\":\"5-45000Hz\"},{\"name\":\"\\u7ebf\\u957f\",\"content\":\".2 \\u7c73\\u957f\\u5bfc\\u7ebf\"}]','2017-11-22 02:50:27','2017-11-22 02:50:27'),(2,2,'铁三角规格模板','[{\"name\":\"\\u9891\\u54cd\\u8303\\u56f4\",\"content\":\"5-45000Hz\"},{\"name\":\"\\u7ebf\\u957f\",\"content\":\".2 \\u7c73\\u957f\\u5bfc\\u7ebf\"}]','2017-11-22 02:51:24','2017-11-22 02:51:24'),(3,3,'12312','[{\"name\":\"afw\",\"content\":\"afweaw\",\"key\":0}]','2017-11-26 05:34:01','2017-11-26 05:34:01'),(4,4,'liutest','[{\"name\":\"aew\",\"content\":\"afwa\",\"key\":0}]','2017-11-26 08:07:02','2017-11-26 08:07:02');
/*!40000 ALTER TABLE `template_form_item` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user`
--

DROP TABLE IF EXISTS `user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `mobile` char(11) COLLATE utf8_bin DEFAULT NULL COMMENT '用户手机号',
  `user_name` varchar(127) COLLATE utf8_bin DEFAULT NULL COMMENT '登录用户名, type=2时使用',
  `password` varchar(127) COLLATE utf8_bin DEFAULT NULL COMMENT '登录用户名, type=2时使用',
  `token` varchar(127) COLLATE utf8_bin DEFAULT NULL COMMENT '登录token',
  `device_id` varchar(127) COLLATE utf8_bin DEFAULT NULL,
  `status` int(11) DEFAULT NULL COMMENT '账户状态：0 正常,1黑名单,2已注销',
  `type` tinyint(4) DEFAULT NULL COMMENT '用户类型, 1: 前台用户, 2: 后台用户',
  `market_name` varchar(127) COLLATE utf8_bin DEFAULT '' COMMENT '商家名称',
  `create_time` datetime DEFAULT NULL COMMENT '创建时间',
  `update_time` datetime DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user`
--

LOCK TABLES `user` WRITE;
/*!40000 ALTER TABLE `user` DISABLE KEYS */;
INSERT INTO `user` VALUES (1,'13311588124',NULL,NULL,'1b467d5416d1dce27ffaf6dbc695e998',NULL,NULL,NULL,'','2017-11-15 15:17:29','2017-11-15 15:23:35'),(2,'10000000000','ltptest123456','f447b20a7fcbf53a5d5be013ea0b15af','01772d94e1ff8505a7fa9b535de2052d',NULL,NULL,NULL,'','2017-11-15 15:24:19','2017-12-16 10:40:05'),(3,'15210353226','testest','7c4a8d09ca3762af61e59520943dc26494f8941b','7ecb2f20d0d7627378f1b6f5a7b3c612',NULL,NULL,NULL,'中国移动','2017-12-03 22:58:14','2017-12-07 12:31:04'),(4,'15210353227','testest123','f0f8e902ca7a41c634c5c8247d4b94f2c9b351fb',NULL,NULL,NULL,NULL,'中国移动9','2017-12-04 22:50:24','2017-12-04 22:50:24'),(13,'15210583226',NULL,NULL,'498a843f48dbd7570beea6515f39db66',NULL,NULL,NULL,'','2017-12-07 12:56:52','2017-12-07 12:56:52'),(14,'15801595467',NULL,NULL,'c08f09207f453051896e3e4f219155f1',NULL,NULL,NULL,'','2017-12-07 14:03:35','2017-12-14 23:39:38'),(15,'13811031900','yangzhanyong','f447b20a7fcbf53a5d5be013ea0b15af','ff3c9f73130f8b620649f6f61f1fe39a',NULL,NULL,NULL,'','2017-12-07 14:03:35','2017-12-16 10:40:48'),(16,'13901064594','zhuangzening','f447b20a7fcbf53a5d5be013ea0b15af','abf2a0235c8a8b69b69ae4fd7a3f8ce3',NULL,NULL,NULL,'','2017-12-07 14:03:35','2017-12-16 10:41:04');
/*!40000 ALTER TABLE `user` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_action`
--

DROP TABLE IF EXISTS `user_action`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_action` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `user_token` varchar(100) COLLATE utf8_bin DEFAULT NULL,
  `product_id` int(11) DEFAULT NULL,
  `type` int(11) DEFAULT NULL COMMENT '类型： 1 进入系统， 2浏览产品 3 加入购物车， 4 购买 5 结算',
  `operation` int(11) DEFAULT NULL COMMENT '操作类型',
  `create_time` datetime DEFAULT NULL,
  `update_time` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_action`
--

LOCK TABLES `user_action` WRITE;
/*!40000 ALTER TABLE `user_action` DISABLE KEYS */;
/*!40000 ALTER TABLE `user_action` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_info`
--

DROP TABLE IF EXISTS `user_info`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_info` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) NOT NULL,
  `mobile` char(11) NOT NULL COMMENT '手机号',
  `real_name` varchar(20) NOT NULL DEFAULT '' COMMENT '真实姓名',
  `idcard` char(18) NOT NULL DEFAULT '' COMMENT '身份证号',
  `bank_card` varchar(63) NOT NULL DEFAULT '' COMMENT '银行卡卡号',
  `certified` tinyint(4) NOT NULL DEFAULT '0' COMMENT '是否实名认证：0 未认证，1 已认证',
  `avatar` varchar(511) NOT NULL DEFAULT '' COMMENT '头像地址',
  `gender` tinyint(4) NOT NULL DEFAULT '0' COMMENT '性别 0未知 1:男 2:女',
  `client_type` varchar(32) NOT NULL DEFAULT '' COMMENT '用户登录类型 Android,ios,Web,Web-1:邀请好友活动,Web-2运营活动',
  `has_order` tinyint(4) NOT NULL DEFAULT '0' COMMENT '是否有完成的订单：0 没，1 有',
  `address` varchar(511) NOT NULL DEFAULT '' COMMENT '地址',
  `create_time` datetime NOT NULL COMMENT '创建时间\n',
  `update_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_mobile` (`mobile`),
  UNIQUE KEY `uk_user_id` (`user_id`),
  KEY `idx_name` (`real_name`)
) ENGINE=InnoDB AUTO_INCREMENT=988426 DEFAULT CHARSET=utf8mb4 COMMENT='用户信息';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_info`
--

LOCK TABLES `user_info` WRITE;
/*!40000 ALTER TABLE `user_info` DISABLE KEYS */;
INSERT INTO `user_info` VALUES (988418,1,'13311588124','测试员','','',0,'',0,'',0,'','2017-11-15 15:17:29','2017-11-15 15:17:29'),(988419,2,'10000000000','测试号','','',0,'',0,'',0,'','2017-11-15 15:24:19','2017-11-15 15:24:19'),(988420,3,'15210353226','','','',0,'',0,'',0,'','2017-12-03 22:58:14','2017-12-03 22:58:14'),(988421,4,'15210353227','','','',0,'',0,'',0,'','2017-12-04 22:50:24','2017-12-04 22:50:24'),(988422,13,'15210583226','','','',0,'',0,'1',0,'','2017-12-07 12:56:52','2017-12-07 12:56:52'),(988423,14,'15801595467','','','',0,'',0,'1',0,'','2017-12-07 14:03:35','2017-12-07 14:03:35'),(988424,15,'13811031900','杨占勇','','',0,'',0,'',0,'','2017-12-07 14:03:35','2017-12-07 14:03:35'),(988425,16,'13901064594','庄泽宇','','',0,'',0,'',0,'','2017-12-07 14:03:35','2017-12-07 14:03:35');
/*!40000 ALTER TABLE `user_info` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_product`
--

DROP TABLE IF EXISTS `user_product`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_product` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `product_id` int(11) DEFAULT '0' COMMENT '产品ID',
  `status` int(11) DEFAULT '0' COMMENT '状态 1 已上架 2 未上架, 3  已下架 4 已删除',
  `cost_price` int(11) DEFAULT '0' COMMENT '成本价',
  `supply_price` int(11) DEFAULT '0' COMMENT '供应价',
  `selling_price` int(11) DEFAULT '0' COMMENT '销售价',
  `stock_num` int(11) DEFAULT '0' COMMENT '库存量',
  `selled_num` int(11) DEFAULT '0' COMMENT ' 已买出量',
  `min_sell_num` int(11) DEFAULT '0' COMMENT '至少批发多少件',
  `recommend` varchar(200) COLLATE utf8_bin DEFAULT '' COMMENT '推荐理由',
  `create_time` datetime DEFAULT NULL,
  `update_time` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_product`
--

LOCK TABLES `user_product` WRITE;
/*!40000 ALTER TABLE `user_product` DISABLE KEYS */;
INSERT INTO `user_product` VALUES (2,4,1,43,56,60,802,162,10,'好','2017-11-21 07:20:50','2017-12-13 02:47:40'),(3,5,1,43,56,60,77,23,1,'好','2017-11-21 07:26:05','2017-12-13 02:17:40'),(4,6,1,43,56,60,100,0,1,'好','2017-11-21 12:11:49','2017-12-03 10:45:31'),(5,7,1,100,120,150,10000,0,10,'测试','2017-11-23 13:32:09','2017-12-03 18:56:50'),(6,8,4,100,130,150,9990,10,1200,'测试','2017-11-23 13:36:50','2017-12-02 09:46:29'),(7,9,4,100,120,150,10000,0,110,'测试','2017-11-23 13:45:41','2017-12-02 09:53:31'),(9,11,2,43,56,60,100,0,10,'好ddd','2017-11-26 06:59:54','2017-12-02 09:53:11'),(10,12,2,43,56,60,100,0,10,'好ddd','2017-11-26 07:00:11','2017-11-26 07:00:11'),(11,13,2,43,56,60,100,0,10,'好ddd','2017-11-26 07:02:09','2017-11-26 07:02:09'),(12,14,2,43,56,60,100,0,10,'好ddd','2017-11-26 07:02:44','2017-11-26 07:02:44'),(13,15,2,43,56,60,100,0,10,'好ddd','2017-11-26 07:03:11','2017-11-26 07:03:11'),(14,16,2,1222,1211,22333,22222,0,21,'212e','2017-11-27 06:55:42','2017-11-27 06:55:42'),(15,17,2,122,135,2123,9000,0,90,'afwea','2017-11-27 07:07:36','2017-11-27 07:07:36'),(16,18,3,0,0,0,0,0,0,'hellowd','2017-12-03 21:04:54','2017-12-03 21:07:59');
/*!40000 ALTER TABLE `user_product` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2017-12-16 10:54:32
