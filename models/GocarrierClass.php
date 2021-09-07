<?php

/**
 * Project : everpsshippingperpostcode
 * @author Team Ever
 * @copyright Team Ever
 * @license   Tous droits réservés / Le droit d'auteur s'applique (All rights reserved / French copyright law applies)
 * @link https://www.team-ever.com
 */

class GocarrierClass extends ObjectModel
{
    public $city;
    public $lat;
    public $lon;
    public $active;

    public static $definition = array(
        'table' => 'gocarrier',
        'primary' => 'id_gocarrier',
        'multilang' => false,
        'fields' => array(
            'city' => array(
                'type' => self::TYPE_STRING,
                'lang' => false,
                'validate' => 'isCityName',
                'required' => true
            ),
            'lat' => array(
                'type' => self::TYPE_STRING,
                'lang' => false,
                'validate' => 'isCoordinate',
                'required' => false
            ),
            'lon' => array(
                'type' => self::TYPE_STRING,
                'lang' => false,
                'validate' => 'isCoordinate',
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

    public static function getLocalisationArray()
    {
        $cache_id = 'Gocarrier::getLocalisationArray';
        if (!Cache::isStored($cache_id)) {
            $sql = new DbQuery;
            $sql->select('*');
            $sql->from('gocarrier');
            $sql->where('active = 1');
            $sql->orderBy('city DESC');
            $localisation = Db::getInstance()->executeS($sql);
            Cache::store($cache_id, $localisation);
            return $localisation;
        }
        return Cache::retrieve($cache_id);
    }


    public static function getLocalisationObj()
    {
        $sql = new DbQuery;
        $sql->select('*');
        $sql->from('gocarrier');
        $sql->where('active = 1');
        $sql->orderBy('city DESC');
        $localisation = Db::getInstance()->executeS($sql);
        $return = array();
        foreach ($localisation as $loc) {
            $return[] = new self(
                (int)$loc['id_gocarrier']
            );
        }
        return $return;
    }

    public static function checkLocalisationByCityName($city_name)
    {
        $sql = new DbQuery;
        $sql->select('*');
        $sql->from('gocarrier', 'g');
        $sql->where('g.city = "' . pSQL($city_name) . '"');
        $id_localisation = (int)Db::getInstance()->getValue($sql);
        if ($id_localisation > 0) {
            return $id_localisation;
        }
        return false;
    }

    public static function checkLocalisationDatas($id_gocarrier, $city_name)
    {
        $exist = false;
        // Exist using its ID
        $exist = new self(
            (int)$id_gocarrier
        );
        if (Validate::isLoadedObject($exist)) {
            return $exist;
        }
        // Exists using city name
        $exist = (int)self::checkLocalisationByCityName($city_name);
        if (Validate::isLoadedObject($exist)) {
            return $exist;
        }
        // Does not exist, create new localisation
        $localisation = new self();
        $localisation->city = $city_name;
        $localisation->save();
        return $localisation;
    }
}