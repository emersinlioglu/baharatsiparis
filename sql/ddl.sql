-- MySQL Script generated by MySQL Workbench
-- 11/23/15 11:03:57
-- Model: DOLD PIM    Version: 1.0
-- MySQL Workbench Forward Engineering

SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='TRADITIONAL,ALLOW_INVALID_DATES';

-- -----------------------------------------------------
-- Schema 40496_dold_pim
-- -----------------------------------------------------

-- -----------------------------------------------------
-- Schema 40496_dold_pim
-- -----------------------------------------------------
CREATE SCHEMA IF NOT EXISTS `40496_dold_pim` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci ;
USE `40496_dold_pim` ;

-- -----------------------------------------------------
-- Table `template`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `template` ;

CREATE TABLE IF NOT EXISTS `template` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(255) NULL,
  PRIMARY KEY (`id`))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `category`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `category` ;

CREATE TABLE IF NOT EXISTS `category` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `template_id` INT UNSIGNED NOT NULL,
  `category_id` INT UNSIGNED NOT NULL,
  `order` INT NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_category_category1`
    FOREIGN KEY (`category_id`)
    REFERENCES `category` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_category_template1`
    FOREIGN KEY (`template_id`)
    REFERENCES `template` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_general_ci;

CREATE INDEX `fk_category_category1_idx` ON `category` (`category_id` ASC);

CREATE INDEX `fk_category_template1_idx` ON `category` (`template_id` ASC);


-- -----------------------------------------------------
-- Table `lang`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `lang` ;

CREATE TABLE IF NOT EXISTS `lang` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(255) NOT NULL,
  `iso` VARCHAR(2) NOT NULL,
  `sort` INT NOT NULL DEFAULT 0,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `category_lang`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `category_lang` ;

CREATE TABLE IF NOT EXISTS `category_lang` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `category_id` INT UNSIGNED NOT NULL,
  `lang_id` INT UNSIGNED NOT NULL,
  `name` VARCHAR(255) NULL,
  `alias` VARCHAR(255) NULL,
  `description` VARCHAR(255) NULL,
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_category_lang_category`
    FOREIGN KEY (`category_id`)
    REFERENCES `category` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_category_lang_lang1`
    FOREIGN KEY (`lang_id`)
    REFERENCES `lang` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_general_ci;

CREATE INDEX `idx_category_lang_category` ON `category_lang` (`category_id` ASC);

CREATE INDEX `idx_category_lang_lang1` ON `category_lang` (`lang_id` ASC);


-- -----------------------------------------------------
-- Table `product`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `product` ;

CREATE TABLE IF NOT EXISTS `product` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `product_id` INT UNSIGNED NOT NULL,
  `number` VARCHAR(255) NULL,
  `price` FLOAT NULL,
  `online` TINYINT(1) NOT NULL DEFAULT 0,
  `order` INT NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_product_product1`
    FOREIGN KEY (`product_id`)
    REFERENCES `product` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_general_ci;

CREATE INDEX `fk_product_idx` ON `product` (`product_id` ASC);


-- -----------------------------------------------------
-- Table `product_lang`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `product_lang` ;

CREATE TABLE IF NOT EXISTS `product_lang` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `product_id` INT UNSIGNED NOT NULL,
  `lang_id` INT UNSIGNED NOT NULL,
  `name` VARCHAR(255) NULL,
  `alias` VARCHAR(255) NULL,
  `description` VARCHAR(255) NULL,
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_product_lang_product1`
    FOREIGN KEY (`product_id`)
    REFERENCES `product` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_product_lang_lang1`
    FOREIGN KEY (`lang_id`)
    REFERENCES `lang` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_general_ci;

CREATE INDEX `fk_product_lang_product1_idx` ON `product_lang` (`product_id` ASC);

CREATE INDEX `fk_product_lang_lang1_idx` ON `product_lang` (`lang_id` ASC);


-- -----------------------------------------------------
-- Table `product_category`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `product_category` ;

CREATE TABLE IF NOT EXISTS `product_category` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `product_id` INT UNSIGNED NOT NULL,
  `category_id` INT UNSIGNED NOT NULL,
  `order` INT NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_category_product_category1`
    FOREIGN KEY (`category_id`)
    REFERENCES `category` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_category_product_product1`
    FOREIGN KEY (`product_id`)
    REFERENCES `product` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_general_ci;

CREATE INDEX `fk_product_category_product1_idx` ON `product_category` (`product_id` ASC);

CREATE INDEX `fk_product_category_category1_idx` ON `product_category` (`category_id` ASC);


-- -----------------------------------------------------
-- Table `attribute`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `attribute` ;

CREATE TABLE IF NOT EXISTS `attribute` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `type` INT NOT NULL,
  `length` INT NULL,
  `is_uppercase` TINYINT(1) NULL,
  PRIMARY KEY (`id`))
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_general_ci;


-- -----------------------------------------------------
-- Table `attribute_lang`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `attribute_lang` ;

CREATE TABLE IF NOT EXISTS `attribute_lang` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `attribute_id` INT UNSIGNED NOT NULL,
  `lang_id` INT UNSIGNED NOT NULL,
  `name` VARCHAR(255) NULL,
  `alias` VARCHAR(255) NULL,
  `unit` VARCHAR(255) NULL,
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_attribute_lang_attribute1`
    FOREIGN KEY (`attribute_id`)
    REFERENCES `attribute` (`id`)
    ON DELETE CASCADE
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_attribute_lang_lang1`
    FOREIGN KEY (`lang_id`)
    REFERENCES `lang` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_general_ci;

CREATE INDEX `fk_attribute_lang_attribute1_idx` ON `attribute_lang` (`attribute_id` ASC);

CREATE INDEX `fk_attribute_lang_lang1_idx` ON `attribute_lang` (`lang_id` ASC);


-- -----------------------------------------------------
-- Table `attribute_group`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `attribute_group` ;

CREATE TABLE IF NOT EXISTS `attribute_group` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`))
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_general_ci;


-- -----------------------------------------------------
-- Table `attribute_group_lang`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `attribute_group_lang` ;

CREATE TABLE IF NOT EXISTS `attribute_group_lang` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `attribute_group_id` INT UNSIGNED NOT NULL,
  `lang_id` INT UNSIGNED NOT NULL,
  `name` VARCHAR(255) NULL,
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_attribute_grp_lang_attribute_grp1`
    FOREIGN KEY (`attribute_group_id`)
    REFERENCES `attribute_group` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_attibute_grp_lang_lang1`
    FOREIGN KEY (`lang_id`)
    REFERENCES `lang` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_general_ci;

CREATE INDEX `fk_attibute_col_lang_attribute_col1_idx` ON `attribute_group_lang` (`attribute_group_id` ASC);

CREATE INDEX `fk_attibute_col_lang_lang1_idx` ON `attribute_group_lang` (`lang_id` ASC);


-- -----------------------------------------------------
-- Table `attribute_group_attribute`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `attribute_group_attribute` ;

CREATE TABLE IF NOT EXISTS `attribute_group_attribute` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `attribute_id` INT UNSIGNED NOT NULL,
  `attribute_group_id` INT UNSIGNED NOT NULL,
  `order` INT NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_attribute_attribute_grp_attribute1`
    FOREIGN KEY (`attribute_id`)
    REFERENCES `attribute` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_attribute_attribute_grp_attribute_grp1`
    FOREIGN KEY (`attribute_group_id`)
    REFERENCES `attribute_group` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_general_ci;

CREATE INDEX `fk_attribute_grp_attribute_col1_idx` ON `attribute_group_attribute` (`attribute_group_id` ASC);

CREATE INDEX `fk_attribute_grpl_attribute1_idx` ON `attribute_group_attribute` (`attribute_id` ASC);


-- -----------------------------------------------------
-- Table `product_attribute_group`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `product_attribute_group` ;

CREATE TABLE IF NOT EXISTS `product_attribute_group` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `attribute_group_id` INT UNSIGNED NOT NULL,
  `product_id` INT UNSIGNED NOT NULL,
  `order` INT NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_attr_grp_product_attribute_grp1`
    FOREIGN KEY (`attribute_group_id`)
    REFERENCES `attribute_group` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_attri_grp_product_product1`
    FOREIGN KEY (`product_id`)
    REFERENCES `product` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_general_ci;

CREATE INDEX `fk_attr_grp_product_product1_idx` ON `product_attribute_group` (`product_id` ASC);

CREATE INDEX `fk_attr_grp_product_attribute_grp1_idx` ON `product_attribute_group` (`attribute_group_id` ASC);


-- -----------------------------------------------------
-- Table `attribute_value`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `attribute_value` ;

CREATE TABLE IF NOT EXISTS `attribute_value` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `product_lang_id` INT UNSIGNED NOT NULL,
  `attribute_lang_id` INT UNSIGNED NOT NULL,
  `value` VARCHAR(255) NULL DEFAULT NULL,
  `value_min` VARCHAR(255) NULL DEFAULT NULL,
  `value_max` VARCHAR(255) NULL DEFAULT NULL,
  `description` VARCHAR(255) NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_product_lang_attribute_lang_product_lang1`
    FOREIGN KEY (`product_lang_id`)
    REFERENCES `product_lang` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_product_lang_has_attribute_lang_attribute1`
    FOREIGN KEY (`attribute_lang_id`)
    REFERENCES `attribute_lang` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_general_ci;

CREATE INDEX `fk_product_lang_attribute_lang_attribute_idx` ON `attribute_value` (`attribute_lang_id` ASC);

CREATE INDEX `fk_product_lang_attribute_lang_product_idx` ON `attribute_value` (`product_lang_id` ASC);


-- -----------------------------------------------------
-- Table `product_group`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `product_group` ;

CREATE TABLE IF NOT EXISTS `product_group` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `product_group_id` INT UNSIGNED NOT NULL,
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_product_group_product_group1`
    FOREIGN KEY (`product_group_id`)
    REFERENCES `product_group` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;

CREATE INDEX `fk_product_group_product_group1_idx` ON `product_group` (`product_group_id` ASC);


-- -----------------------------------------------------
-- Table `product_group_lang`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `product_group_lang` ;

CREATE TABLE IF NOT EXISTS `product_group_lang` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `product_group_id` INT UNSIGNED NOT NULL,
  `lang_id` INT UNSIGNED NOT NULL,
  `name` VARCHAR(255) NULL,
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_product_group_lang_product_group1`
    FOREIGN KEY (`product_group_id`)
    REFERENCES `product_group` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_product_group_lang_lang1`
    FOREIGN KEY (`lang_id`)
    REFERENCES `lang` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;

CREATE INDEX `fk_product_group_lang_product_group1_idx` ON `product_group_lang` (`product_group_id` ASC);

CREATE INDEX `fk_product_group_lang_lang1_idx` ON `product_group_lang` (`lang_id` ASC);


-- -----------------------------------------------------
-- Table `product_group_product`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `product_group_product` ;

CREATE TABLE IF NOT EXISTS `product_group_product` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `product_group_id` INT UNSIGNED NOT NULL,
  `product_id` INT UNSIGNED NOT NULL,
  `order` INT NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_product_group_has_product_product_group1`
    FOREIGN KEY (`product_group_id`)
    REFERENCES `product_group` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_product_group__product_product1`
    FOREIGN KEY (`product_id`)
    REFERENCES `product` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;

CREATE INDEX `fk_product_group_product_product1_idx` ON `product_group_product` (`product_id` ASC);

CREATE INDEX `fk_product_group_product_product_group1_idx` ON `product_group_product` (`product_group_id` ASC);


-- -----------------------------------------------------
-- Table `template_attribute_group`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `template_attribute_group` ;

CREATE TABLE IF NOT EXISTS `template_attribute_group` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `attribute_group_id` INT UNSIGNED NOT NULL,
  `template_id` INT UNSIGNED NOT NULL,
  `order` INT UNSIGNED NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_attribute_col_template_attribute_col1`
    FOREIGN KEY (`attribute_group_id`)
    REFERENCES `attribute_group` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_attribute_col_has_template_template1`
    FOREIGN KEY (`template_id`)
    REFERENCES `template` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_general_ci;

CREATE INDEX `idx_attribute_grp_template_template1_idx` ON `template_attribute_group` (`template_id` ASC);

CREATE INDEX `idx_attribute_grp_template_attribute_grp1_idx` ON `template_attribute_group` (`attribute_group_id` ASC);


-- -----------------------------------------------------
-- Table `user`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `user` ;

CREATE TABLE IF NOT EXISTS `user` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(255) NULL,
  `password` VARCHAR(255) NOT NULL,
  `email` VARCHAR(255) NOT NULL,
  `last_login` DATETIME NULL,
  `failes_login_count` INT NULL DEFAULT 0,
  `is_locked` TINYINT(1) NOT NULL DEFAULT 0,
  `allow_products` TINYINT(1) NOT NULL DEFAULT 0,
  `allow_attributes` TINYINT(1) NOT NULL DEFAULT 0,
  `allow_templates` TINYINT(1) NOT NULL DEFAULT 0,
  `allow_admin` TINYINT(1) NOT NULL DEFAULT 0,
  `allow_delete` TINYINT(1) NOT NULL DEFAULT 0,
  `allow_edit` TINYINT(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`))
ENGINE = InnoDB;

CREATE UNIQUE INDEX `email_UNIQUE` ON `user` (`email` ASC);


SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;

-- -----------------------------------------------------
-- Data for table `lang`
-- -----------------------------------------------------
START TRANSACTION;
USE `40496_dold_pim`;
INSERT INTO `lang` (`id`, `name`, `iso`, `sort`, `is_active`) VALUES (1, 'de', 'de', 1, 1);
INSERT INTO `lang` (`id`, `name`, `iso`, `sort`, `is_active`) VALUES (2, 'en', 'en', 2, 1);
INSERT INTO `lang` (`id`, `name`, `iso`, `sort`, `is_active`) VALUES (3, 'fr', 'fr', 3, 1);
INSERT INTO `lang` (`id`, `name`, `iso`, `sort`, `is_active`) VALUES (4, 'sp', 'es', 4, 1);
INSERT INTO `lang` (`id`, `name`, `iso`, `sort`, `is_active`) VALUES (5, 'po', 'po', 5, 1);
INSERT INTO `lang` (`id`, `name`, `iso`, `sort`, `is_active`) VALUES (6, 'ru', 'ru', 6, 1);

COMMIT;


-- -----------------------------------------------------
-- Data for table `user`
-- -----------------------------------------------------
START TRANSACTION;
USE `40496_dold_pim`;
INSERT INTO `user` (`id`, `name`, `password`, `email`, `last_login`, `failes_login_count`, `is_locked`, `allow_products`, `allow_attributes`, `allow_templates`, `allow_admin`, `allow_delete`, `allow_edit`) VALUES (1, 'sysadmin', 'sysadmin', 'sysadmin@4fb.de', NULL, 0, 0, 1, 1, 1, 1, 1, 1);
INSERT INTO `user` (`id`, `name`, `password`, `email`, `last_login`, `failes_login_count`, `is_locked`, `allow_products`, `allow_attributes`, `allow_templates`, `allow_admin`, `allow_delete`, `allow_edit`) VALUES (2, 'Erdal Mersinlioglu', 'sysadmin', 'erdal.mersinlioglu@4fb.de', NULL, 0, 0, 1, 1, 1, 1, 1, 1);
INSERT INTO `user` (`id`, `name`, `password`, `email`, `last_login`, `failes_login_count`, `is_locked`, `allow_products`, `allow_attributes`, `allow_templates`, `allow_admin`, `allow_delete`, `allow_edit`) VALUES (3, 'Tester1', 'sysadmin', 'tester1@4fb.de', NULL, 0, 0, 1, 1, 1, 1, 1, 1);

COMMIT;

