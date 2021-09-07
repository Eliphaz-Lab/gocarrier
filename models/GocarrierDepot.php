<?php

/**
 * Project : gocarrier
 * @author Team Ever
 * @copyright Team Ever
 * @license   Tous droits réservés / Le droit d'auteur s'applique (All rights reserved / French copyright law applies)
 * @link https://www.team-ever.com
 */

if (!defined('_PS_VERSION_')) {
    exit;
}


class GocarrierDepot extends ObjectModel
{
    public $id_gocarrier_depot;
    public $id_gocarrier;
    public $id_shop;
    public $name;
    public $active;

    public static $definition = array(
        'table' => 'gocarrier_depot',
        'primary' => 'id_gocarrier_depot',
        'multilang' => true,
        'fields' => array(
            'id_gocarrier' => array(
                'type' => self::TYPE_INT,
                'lang' => false,
                'validate' => 'isInt',
                'required' => true
            ),
            'id_shop' => array(
                'type' => self::TYPE_INT,
                'lang' => false,
                'validate' => 'isInt',
                'required' => false
            ),
            'name' => array(
                'type' => self::TYPE_STRING,
                'lang' => true,
                'validate' => 'isString',
                'required' => false
            ),
            'active' => array(
                'type' => self::TYPE_BOOL,
                'lang' => false,
                'validate' => 'isBool',
                'required' => false
            ),
        )
    );


    public static function depotExists($id_gocarrier, $name, $id_lang)
    {
        $sql = new DbQuery;
        $sql->select('go.id_gocarrier_depot');
        $sql->from('gocarrier_depot', 'go');
        $sql->leftJoin(
            'gocarrier_depot_lang',
            'gol',
            'gol.id_gocarrier_depot = go.id_gocarrier_depot'
        );
        $sql->where('go.id_gocarrier = ' . (int)$id_gocarrier);
        $sql->where('gol.name = "' . pSQL($name) . '"');
        $sql->where('gol.id_lang = ' . (int)$id_lang);
        $return = (bool)Db::getInstance()->getValue($sql);
        return $return;
    }


    public static function getDepotArray($id_shop, $id_lang)
    {
        $sql = new DbQuery;
        $sql->select('*');
        $sql->from('gocarrier_depot', 'go');
        $sql->leftJoin(
            'gocarrier',
            'g',
            'g.id_gocarrier = go.id_gocarrier'
        );
        $sql->leftJoin(
            'gocarrier_depot_lang',
            'gol',
            'gol.id_gocarrier_depot = go.id_gocarrier_depot'
        );
        $sql->where('gol.id_lang = ' . (int)$id_lang);
        $depots = Db::getInstance()->executeS($sql);
        // die(var_dump($depots));
        return $depots;
    }


    public static function getDepotObjects($id_lang)
    {
        $sql = new DbQuery;
        $sql->select('*');
        $sql->from('gocarrier_depot', 'go');
        $sql->leftJoin(
            'gocarrier',
            'g',
            'g.id_gocarrier = go.id_gocarrier'
        );
        $sql->leftJoin(
            'gocarrier_depot_lang',
            'gol',
            'gol.id_gocarrier_depot = go.id_gocarrier_depot'
        );
        $sql->where('gol.id_lang = ' . (int)$id_lang);
        $depots = Db::getInstance()->executeS($sql);
        $return = array();

        foreach ($depots as $dep) {
            $depot = new stdClass();
            $depot->id_gocarrier_depot = $dep['id_gocarrier_depot'];
            $depot->id_gocarrier = $dep['id_gocarrier'];
            $depot->name = $dep['name'];
            $depot->city = $dep['city'];
            $depot->lat = $dep['lat'];
            $depot->lon = $dep['lon'];
            $depot->active = $dep['active'];
            $return[] = $depot;
        }
        return $return;
    }

    public static function checkDepot($id_gocarrier_depot, $name, $city, $obj = false)
    {
        $sql = new DbQuery;
        $sql->select('go.id_gocarrier_depot');
        $sql->from('gocarrier_depot', 'go');
        $sql->leftJoin(
            'gocarrier',
            'g',
            'g.id_gocarrier = go.id_gocarrier'
        );
        $sql->leftJoin(
            'gocarrier_depot_lang',
            'gol',
            'gol.id_gocarrier_depot = go.id_gocarrier_depot'
        );
        $sql->where('g.city = "' . pSQL($city) . '"');
        $sql->where('gol.name = "' . pSQL($name) . '"');
        $id_depot = (int)Db::getInstance()->getValue($sql);
        if ($id_depot > 0) {
            if ((bool)$obj === true) {
                return new self(
                    (int)$id_depot
                );
            }
            return $id_depot;
        }
        return false;
    }


    public static function checkDepotDatas($id_gocarrier_depot, $name, $localisation)
    {
        $exist = false;
        // Check depot by ID, name and city
        $exist = self::checkDepot(
            $id_gocarrier_depot,
            $name,
            $localisation->city,
            true
        );
        if (Validate::isLoadedObject($exist)) {
            return $exist;
        }
        $depot = new self();
        $depot->id_gocarrier = $localisation->id;
        $depot->id_shop = (int)Context::getContext()->shop->id;
        foreach (Language::getLanguages(false) as $lang) {
            $depot->name[$lang['id_lang']] = $name;
        }
        $depot->active = true;
        $depot->save();
        return $depot;
    }

    public static function getByNameAndCity($name, $city)
    {
        $sql = new DbQuery;
        $sql->select('go.id_gocarrier_depot');
        $sql->from('gocarrier_depot', 'go');
        $sql->leftJoin(
            'gocarrier',
            'g',
            'g.id_gocarrier = go.id_gocarrier'
        );
        $sql->leftJoin(
            'gocarrier_depot_lang',
            'gol',
            'gol.id_gocarrier_depot = go.id_gocarrier_depot'
        );
        $sql->where('e.city = "' . pSQL($city) . '"');
        $sql->where('gol.name = "' . pSQL($name) . '"');
        return (bool)Db::getInstance()->getValue($sql);
    }

    public static function copyStoresToDepots($id_shop, $id_lang)
    {
        $stores = Db::getInstance()->executeS(
            'SELECT s.id_store
            FROM ' . _DB_PREFIX_ . 'store s
            ' . Shop::addSqlAssociation('store', 's') . '
            WHERE s.active = 1'
        );
        foreach ($stores as $shop_store) {
            $store = new Store(
                (int)$shop_store['id_store']
            );
            $depot = new self();
            foreach (Language::getLanguages(false) as $lang) {
                $depot->name[$lang['id_lang']] = $store->name[$lang['id_lang']];
            }
            $id_localisation = GocarrierClass::checkLocalisationByCityName(
                $store->city
            );
            if (
                $id_localisation
                && Validate::isInt($id_localisation)
            ) {
                $depot->id_gocarrier = $id_localisation;
            } else {
                $localisation = new GocarrierClass();
                $localisation->city = $store->city;
                $localisation->latitude = $store->latitude;
                $localisation->longitude = $store->longitude;
                $localisation->active = true;
                $localisation->save();
                $depot->id_gocarrier = $localisation->id;
            }
            $exists = self::getByNameAndCity(
                $store->name[(int)Configuration::get('PS_LANG_DEFAULT')],
                $store->city
            );
            if ((bool)$exists === false) {
                $depot->id_shop = $id_shop;
                $depot->active = $store->active;
                $depot->save();
            }
        }
    }
}