<?php

/**
 * Project : everpsshippingperpostcode
 * @author Team Ever
 * @copyright Team Ever
 * @license   Tous droits réservés / Le droit d'auteur s'applique (All rights reserved / French copyright law applies)
 * @link https://www.team-ever.com
 */

if (!defined('_PS_VERSION_')) {
    exit;
}


$sql = array();

// FR postcode list, will be set using insert.php file
$sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'gocarrier` (
    `id_gocarrier` int(11) NOT NULL AUTO_INCREMENT,
    `city` varchar(255) NOT NULL,
    `lat` varchar(255) DEFAULT NULL,
    `lon` varchar(255) DEFAULT NULL,
    `active` tinyint(1) NOT NULL DEFAULT 1,
    PRIMARY KEY  (`id_gocarrier`)
) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;';

// Carriers
$sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'gocarrier_depot` (
    `id_gocarrier_depot` int(11) NOT NULL AUTO_INCREMENT,
    `id_gocarrier` int(11) NOT NULL,
    `id_shop` int(11) DEFAULT 1,
    `active` tinyint(1) NOT NULL DEFAULT 1,
    PRIMARY KEY  (`id_gocarrier_depot`)
) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;';

// Carriers name
$sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'gocarrier_depot_lang` (
    `id_gocarrier_depot` int(11) NOT NULL AUTO_INCREMENT,
    `id_lang` int(10) unsigned NOT NULL,
    `name` varchar(255) NOT NULL,
    PRIMARY KEY  (`id_gocarrier_depot`, `id_lang`)
) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;';

// Carriers depot shipping
$sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'gocarrier_depot_shipping` (
    `id_gocarrier_depot_shipping` int(11) NOT NULL AUTO_INCREMENT,
    `id_gocarrier_depot` int(11) NOT NULL,
    `id_gocarrier` int(11) NOT NULL,
    `overcost` decimal(20,6) NOT NULL DEFAULT "0.000000",
    `weight` decimal(20,6) NOT NULL DEFAULT "0.000000",
    `price` decimal(20,6) NOT NULL DEFAULT "0.000000",
    `active` tinyint(1) NOT NULL DEFAULT 1,
    PRIMARY KEY  (`id_gocarrier_depot_shipping`, `id_gocarrier_depot`, `id_gocarrier`)
) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;';

$sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'gocarrier_depot_shipping_lang` (
    `id_gocarrier_depot_shipping` int(11) NOT NULL AUTO_INCREMENT,
    `id_lang` int(10) unsigned NOT NULL,
    `delivery_time` varchar(255) NOT NULL,
    PRIMARY KEY  (`id_gocarrier_depot_shipping`, `id_lang`)
) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;';

// FR postcode list, will be set using insert.php file
$sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'gocarrier_cart` (
    `id_cart` int(11) NOT NULL,
    `id_gocarrier_depot_shipping` int(11) NOT NULL,
    `cost` decimal(20,6) NOT NULL DEFAULT "0.000000",
    PRIMARY KEY  (`id_cart`)
) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;';

foreach ($sql as $query) {
    if (Db::getInstance()->execute($query) == false) {
        return false;
    }
}