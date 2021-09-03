<?php
/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * @author    PrestaShop SA and Contributors <contact@prestashop.com>
 * @copyright Since 2007 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0  Academic Free License (AFL 3.0)
 */


declare(strict_types=1);

if (!defined('_PS_VERSION_')) {
    exit;
}

class gocarrier extends CarrierModule
{
    public function __construct()
    {
        $this->name = 'gocarrier';
        $this->author = 'artinux';
        $this->version = '1.0.0';
        $this->ps_versions_compliancy = ['min' => '1.7.7', 'max' => _PS_VERSION_];

        parent::__construct();

        $this->displayName = $this->l('Go Carrier');
        $this->description = $this->l('Just a carrier module');
    }


    public function install()
    {
        $this->addCarrier();
        return (parent::install() 
            && $this->registerHook('updateCarrier')
            &&$this->registerHook('displayCarrierExtraContent')
        );
    }

    public function uninstall()
    {
        $carrier = new Carrier(
            (int)Configuration::get('Go_CARRIER_ID')
        );

        $carrier->delete();

        Configuration::deleteByName('Go_CARRIER_ID');
        return parent::uninstall();
    }

    public function getOrderShippingCost($params, $shippinh_cost)
    {

    }

    public function getOrderShippingCostExternal($params)
    {

    }

    protected function addCarrier()
    {
        $result = false;
        $carrier = new Carrier();
        $carrier->name = $this->l('Go Carrier');
        $carrier->is_module = true;
        $carrier->active = 1;
        $carrier->range_behavior = 1;
        $carrier->need_range = 1;
        $carrier->shipping_external = true;
        $carrier->range_behavior = 0;
        $carrier->external_module_name = $this->name;
        $carrier->shipping_method = 2;

        foreach (Language::getLanguages() as $lang) {
            $carrier->delay[$lang['id_lang']] = $this->l('Pick a shipping');
        }

        if($carrier->add() == true) {
            Configuration::updateValue('GO_CARRIER_ID', (int)$carrier->id);

            $result &= $this->addZones($carrier);
            $result &= $this->addGroups($carrier);
            $result &= $this->addRanges($carrier);
        }
        return $result;
    }


    protected function addZones($carrier)
    {
        $zones = Zone::getZones();

        foreach ($zones as $zone) {
            $carrier->addZone($zone);
        }
    }

    protected function addGroups($carrier)
    {
        $groups_ids = arrays();

        $groups = Group::getGroups(Context::getContext()->language->id);
        foreach ($groups as $group) {
            $groups_ids[] = $group['id_group'];
        }

        $carrier->addGroups($groups_ids);
    }

    protected function addRanges($carrier)
    {
        $range_price = new RangePrice();
        $range_price->id_carrier = $carrier->id;
        $range_price->delimiter1 = '0';
        $range_price->delimiter2 = '10000';
        $range_price->add();
    }

}