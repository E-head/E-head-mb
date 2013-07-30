-- phpMyAdmin SQL Dump
-- version 3.5.1
-- http://www.phpmyadmin.net
--
-- Хост: localhost
-- Время создания: Июл 30 2013 г., 03:55
-- Версия сервера: 5.0.51a-community-nt
-- Версия PHP: 5.2.5

SET FOREIGN_KEY_CHECKS=0;

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

--
-- База данных: `mb`
--

-- --------------------------------------------------------

--
-- Структура таблицы `accounts`
--

DROP TABLE IF EXISTS `accounts`;
CREATE TABLE IF NOT EXISTS `accounts` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `login` varchar(100) NOT NULL,
  `password` varchar(32) character set utf8 collate utf8_bin NOT NULL,
  `role_id` int(11) unsigned NOT NULL,
  `name` varchar(255) default NULL,
  `email` varchar(255) default NULL,
  `phone` varchar(20) default NULL,
  `state` text COMMENT 'store user interface state',
  `active` tinyint(1) NOT NULL default '1',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `login` (`login`),
  KEY `fk_role_id` (`role_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=3 ;

--
-- Дамп данных таблицы `accounts`
--

INSERT INTO `accounts` (`id`, `login`, `password`, `role_id`, `name`, `email`, `phone`, `state`, `active`) VALUES
(1, 'admin', 'bd4d2ba0a0cc1d15992715406f754121', 1, 'Администратор', 'admin@e-head.ru', '', NULL, 1),
(2, 'user', '5f4dcc3b5aa765d61d8327deb882cf99', 3, 'Пользователь', 'bvh.box@gmail.com', '', NULL, 1);

-- --------------------------------------------------------

--
-- Структура таблицы `acl_permissions`
--

DROP TABLE IF EXISTS `acl_permissions`;
CREATE TABLE IF NOT EXISTS `acl_permissions` (
  `id` int(11) NOT NULL auto_increment,
  `role_id` int(11) unsigned NOT NULL,
  `resource_id` int(11) unsigned NOT NULL,
  `privilege_id` tinyint(3) unsigned NOT NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `role_id_2` (`role_id`,`resource_id`,`privilege_id`),
  KEY `fk_role_id` (`role_id`),
  KEY `fk_resource_id` (`resource_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=10 ;

--
-- Дамп данных таблицы `acl_permissions`
--

INSERT INTO `acl_permissions` (`id`, `role_id`, `resource_id`, `privilege_id`) VALUES
(1, 1, 1, 1),
(2, 1, 1, 3),
(3, 1, 2, 1),
(4, 1, 2, 3),
(5, 1, 3, 1),
(6, 1, 3, 3),
(7, 3, 2, 1),
(8, 3, 2, 3),
(9, 3, 3, 1);

-- --------------------------------------------------------

--
-- Структура таблицы `acl_resources`
--

DROP TABLE IF EXISTS `acl_resources`;
CREATE TABLE IF NOT EXISTS `acl_resources` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `name` varchar(100) NOT NULL,
  `title` varchar(100) default NULL,
  `parent_id` int(11) unsigned default NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `name` (`name`,`parent_id`),
  KEY `fk_parent_id` (`parent_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=4 ;

--
-- Дамп данных таблицы `acl_resources`
--

INSERT INTO `acl_resources` (`id`, `name`, `title`, `parent_id`) VALUES
(1, 'admin', 'Администрирование', NULL),
(2, 'orders', 'Заказы', NULL),
(3, 'goods', 'Товары', NULL);

-- --------------------------------------------------------

--
-- Структура таблицы `acl_roles`
--

DROP TABLE IF EXISTS `acl_roles`;
CREATE TABLE IF NOT EXISTS `acl_roles` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `name` varchar(50) NOT NULL,
  `alias` varchar(40) default NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `name` (`name`),
  UNIQUE KEY `alias` (`alias`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=4 ;

--
-- Дамп данных таблицы `acl_roles`
--

INSERT INTO `acl_roles` (`id`, `name`, `alias`) VALUES
(1, 'Админ', 'admin'),
(3, 'Пользователь', 'user');

-- --------------------------------------------------------

--
-- Структура таблицы `goods`
--

DROP TABLE IF EXISTS `goods`;
CREATE TABLE IF NOT EXISTS `goods` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `name` varchar(255) NOT NULL,
  `price` double(10,2) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Структура таблицы `orders`
--

DROP TABLE IF EXISTS `orders`;
CREATE TABLE IF NOT EXISTS `orders` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `account_id` int(10) unsigned NOT NULL,
  `created` timestamp NOT NULL default CURRENT_TIMESTAMP,
  `ondate` timestamp NULL default NULL,
  `closed` timestamp NULL default NULL,
  PRIMARY KEY  (`id`),
  KEY `account_id` (`account_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Структура таблицы `orders_goods`
--

DROP TABLE IF EXISTS `orders_goods`;
CREATE TABLE IF NOT EXISTS `orders_goods` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `order_id` int(10) unsigned NOT NULL,
  `good_id` int(10) unsigned NOT NULL,
  `number` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `good_id` (`good_id`),
  KEY `order_id` (`order_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Ограничения внешнего ключа сохраненных таблиц
--

--
-- Ограничения внешнего ключа таблицы `accounts`
--
ALTER TABLE `accounts`
  ADD CONSTRAINT `accounts_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `acl_roles` (`id`),
  ADD CONSTRAINT `accounts_ibfk_2` FOREIGN KEY (`role_id`) REFERENCES `acl_roles` (`id`);

--
-- Ограничения внешнего ключа таблицы `acl_permissions`
--
ALTER TABLE `acl_permissions`
  ADD CONSTRAINT `acl_permissions_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `acl_roles` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `acl_permissions_ibfk_2` FOREIGN KEY (`resource_id`) REFERENCES `acl_resources` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `acl_permissions_ibfk_3` FOREIGN KEY (`role_id`) REFERENCES `acl_roles` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `acl_permissions_ibfk_4` FOREIGN KEY (`resource_id`) REFERENCES `acl_resources` (`id`) ON DELETE CASCADE;

--
-- Ограничения внешнего ключа таблицы `acl_resources`
--
ALTER TABLE `acl_resources`
  ADD CONSTRAINT `acl_resources_ibfk_1` FOREIGN KEY (`parent_id`) REFERENCES `acl_resources` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `acl_resources_ibfk_2` FOREIGN KEY (`parent_id`) REFERENCES `acl_resources` (`id`) ON DELETE CASCADE;

--
-- Ограничения внешнего ключа таблицы `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`account_id`) REFERENCES `accounts` (`id`);

--
-- Ограничения внешнего ключа таблицы `orders_goods`
--
ALTER TABLE `orders_goods`
  ADD CONSTRAINT `orders_goods_ibfk_2` FOREIGN KEY (`good_id`) REFERENCES `goods` (`id`),
  ADD CONSTRAINT `orders_goods_ibfk_3` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

SET FOREIGN_KEY_CHECKS=1;
