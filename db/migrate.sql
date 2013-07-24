

-- all below applied --

ALTER TABLE  `sales_goods` ADD  `loss_margin` INT NOT NULL DEFAULT  '0';

CREATE TABLE IF NOT EXISTS `orderslog` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `created` timestamp NOT NULL default CURRENT_TIMESTAMP,
  `creator_id` int(10) unsigned NOT NULL,
  `date` date NOT NULL,
  `staff_id` int(10) unsigned NOT NULL,
  `summ_start` DOUBLE( 10, 2 ) UNSIGNED NOT NULL DEFAULT '0',
  `summ_income` DOUBLE( 10, 2 ) UNSIGNED NOT NULL DEFAULT '0',
  `summ_inkasso` DOUBLE( 10, 2 ) UNSIGNED NOT NULL DEFAULT '0',
  `summ_rest` DOUBLE( 10, 2 ) UNSIGNED NOT NULL DEFAULT '0',
  `inkasso_dst` TEXT NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `staff_id` (`staff_id`),
  KEY `creator_id` (`creator_id`),
  KEY `date` (`date`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_bin ;

ALTER TABLE `orderslog`
  ADD CONSTRAINT `staff_id` FOREIGN KEY (`staff_id`) REFERENCES `staff` (`id`),
  ADD CONSTRAINT `creator_id` FOREIGN KEY (`creator_id`) REFERENCES `accounts` (`id`);

CREATE TABLE IF NOT EXISTS `sales_expendables` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `name` varchar(250) NOT NULL,
  `price` double(10,2) NOT NULL,
  `measure` varchar(50) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_bin ;

CREATE TABLE IF NOT EXISTS `sales_goods` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `code` varchar(250) NOT NULL,
  `name` varchar(250) NOT NULL,
  `price` double(10,2) NOT NULL,
  `measure` varchar(50) NOT NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `code` (`code`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_bin ;

CREATE TABLE `sales_goods_expendables` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY ,
  `goods_id` INT UNSIGNED NOT NULL ,
  `expendables_id` INT UNSIGNED NOT NULL ,
  `qty` INT UNSIGNED NOT NULL ,
  INDEX `goods_id` (`goods_id`) ,
  INDEX `expendables_id` (`expendables_id`)
) ENGINE = InnoDB;

ALTER TABLE `sales_goods_expendables`
  ADD CONSTRAINT `goods_id` FOREIGN KEY (`goods_id`) REFERENCES `sales_goods` (`id`) ON DELETE CASCADE ,
  ADD CONSTRAINT `expendables_id` FOREIGN KEY (`expendables_id`) REFERENCES `sales_expendables` (`id`);