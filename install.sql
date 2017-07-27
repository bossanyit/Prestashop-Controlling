CREATE TABLE IF NOT EXISTS `PREFIX_controlling_supplier` (
	`id_controlling_supplier` INT(10) NOT NULL AUTO_INCREMENT,
	`id_expense_type` INT(10) UNSIGNED NOT NULL,
	`name` CHAR(50) NOT NULL,
	PRIMARY KEY (`id_controlling_supplier`),
	INDEX `id_expense_type` (`id_expense_type`)
) COLLATE='utf8_general_ci' ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `PREFIX_controlling_expense_type` (
	`id_expense_type` INT(10) NOT NULL AUTO_INCREMENT,
	`id_book_nr` INT(10) UNSIGNED NOT NULL DEFAULT 0,
	`id_inner_nr` INT(10) UNSIGNED NOT NULL DEFAULT 0,
	`name` CHAR(50),
	PRIMARY KEY (`id_expense_type`),
	INDEX `id_inner_nr` (`id_inner_nr`)
) COLLATE='utf8_general_ci' ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `PREFIX_controlling_expenses` (
	`id_expense` INT(10) NOT NULL AUTO_INCREMENT,
	`id_expense_type` INT(10) UNSIGNED NOT NULL,
	`id_controlling_supplier` INT(10) UNSIGNED NOT NULL,
	`sum_tax_inclusive` FLOAT(14) NOT NULL,
	`sum_tax_exclusive` FLOAT(14) NOT NULL,
	`tax` FLOAT(14) NOT NULL,
	`date_add` DATE NOT NULL,
	`date_invoice` DATE NOT NULL,
	`date_payment` DATE NOT NULL,
	`invoice_nr` CHAR(30),
	`payment_type` CHAR(30),
	`manual` INT(1) UNSIGNED NOT NULL DEFAULT 0,
	PRIMARY KEY (`id_expense`),
	INDEX `id_expense_type` (`id_expense_type`),
	INDEX `id_controlling_supplier` (`id_controlling_supplier`)
) COLLATE='utf8_general_ci' ENGINE=InnoDB;


CREATE TABLE IF NOT EXISTS `PREFIX_controlling_marketing_source` (
	`id_marketing_source` INT(10) NOT NULL AUTO_INCREMENT,
	`id_source` INT(10) UNSIGNED NOT NULL,
	`ad_expense` FLOAT(14) NOT NULL,
	`visits` INT(10) UNSIGNED NOT NULL,
	`ctr` FLOAT(10) NOT NULL,
	`subscription_count` INT(10) UNSIGNED NOT NULL,
	`order_count` INT(10) UNSIGNED NOT NULL,
	`avg_cart` FLOAT(10) NOT NULL,
	`income_sum` FLOAT(10) NOT NULL,
	`margin_sum` FLOAT(10) NOT NULL,
	`covert` FLOAT(10) NOT NULL,	
	`date_add` DATE NOT NULL,
	PRIMARY KEY (`id_marketing_source`),
	INDEX `id_campaign` (`id_campaign`)
) COLLATE='utf8_general_ci' ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `PREFIX_controlling_source` (
	`id_source` INT(10) NOT NULL AUTO_INCREMENT,
	`position` INT(10) UNSIGNED NOT NULL,
	`name` CHAR(30) NOT NULL,
	`date_add` DATE NOT NULL,
	`date_end` DATE NOT NULL,
	`callback` CHAR( 50 ) NULL DEFAULT NULL,
	PRIMARY KEY (`id_source`),
	INDEX `name` (`name`)
) COLLATE='utf8_general_ci' ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `PREFIX_controlling_source_entrypoint` (
	`id_entry` INT(10) NOT NULL AUTO_INCREMENT,
	`id_source` INT(10) UNSIGNED NOT NULL,
	`entry_name` CHAR(100) NOT NULL,
	`id_entry_type` INT(10) UNSIGNED NOT NULL,
	PRIMARY KEY (`id_entry`),
	INDEX `id_source` (`id_source`),
	INDEX `request_uri` (`request_uri`),
	INDEX `id_entry_type` (`id_entry_type`)
) COLLATE='utf8_general_ci' ENGINE=InnoDB;


CREATE TABLE IF NOT EXISTS `PREFIX_controlling_source_entry_type` (
	`id_entry_type` INT(10) NOT NULL AUTO_INCREMENT,
	`type` CHAR(30) NOT NULL,
	PRIMARY KEY (`id_entry_type`)
) COLLATE='utf8_general_ci' ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `PREFIX_controlling_margin` (
	`id_order` INT(10) UNSIGNED NOT NULL,
	`brutto` FLOAT(10) NOT NULL,
	`actual_margin` FLOAT(10) NOT NULL,
	PRIMARY KEY (`id_order`)
) COLLATE='utf8_general_ci' ENGINE=InnoDB;


CREATE TABLE IF NOT EXISTS `PREFIX_controlling_funnel_step_source` (
	`id_funnel_step_source` INT(10) NOT NULL AUTO_INCREMENT,
	`step_name` CHAR(30) NOT NULL,
	`funnel_source_type` INT(10) UNSIGNED NOT NULL,
	`funnel_source` CHAR(30),
	PRIMARY KEY (`id_funnel_step_source`)
) COLLATE='utf8_general_ci' ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `PREFIX_controlling_funnel` (
	`id_funnel` INT(10) NOT NULL AUTO_INCREMENT,	
	`id_source` INT(10) UNSIGNED NOT NULL,
	`funnel_name` CHAR(30) NOT NULL,
	`id_step1_source` CHAR(30) NOT NULL,
	`id_step2_source` CHAR(30) NOT NULL,
	`id_step3_source` CHAR(30) NOT NULL,
	`id_cart_source` CHAR(30) NOT NULL,
	`date_add` DATE NOT NULL,
	`position` INT(10) UNSIGNED NOT NULL,
	PRIMARY KEY (`id_funnel`),
	INDEX `id_step1_source` (`id_step1_source`),
	INDEX `id_step2_source` (`id_step2_source`),
	INDEX `id_step3_source` (`id_step3_source`),
	INDEX `id_cart_source` (`id_cart_source`)	
) COLLATE='utf8_general_ci' ENGINE=InnoDB;
	
CREATE TABLE IF NOT EXISTS `ps_controlling_funnel_data` (
	`id_funnel_data` INT(10) NOT NULL AUTO_INCREMENT,		
	`id_funnel` INT(10) UNSIGNED NOT NULL,
	`reach` INT(10) UNSIGNED DEFAULT 0,
	`reach_expense` FLOAT(14) DEFAULT 0,	
	`step1_visits` INT(10) UNSIGNED NOT NULL,
	`step1_clicks` INT(10) UNSIGNED DEFAULT 0,
	`step2_data` INT(10) UNSIGNED DEFAULT 0,
	`step3_data` INT(10) UNSIGNED DEFAULT 0,    
	`cart_data` INT(10) UNSIGNED NOT NULL,
	`order_data` INT(10) UNSIGNED NOT NULL,   
	`date_add` DATE NOT NULL,
	PRIMARY KEY (`id_funnel_data`),
	INDEX `id_funnel` (`id_funnel`)
) COLLATE='utf8_general_ci' ENGINE=InnoDB;