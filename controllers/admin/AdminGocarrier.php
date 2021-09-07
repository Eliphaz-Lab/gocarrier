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


class AdminGocarrier extends ModuleAdminController
{
    private $html;

    public function __construct()
    {
        $this->bootstrap = true;
        $this->lang = false;
        $this->table = 'gocarrier_shipping';
        $this->className = 'GocarrierShipping';
        $this->context = Context::getContext();
        $this->identifier = 'id_gocarrier_shipping';
        $this->module_name = 'gocarrier';
        $this->isSeven = Tools::version_compare(_PS_VERSION_, '1.7', '>=') ? true : false;
        $this->context->smarty->assign(array(
            'gocarrier_dir' => _MODULE_DIR_ . '/gocarrier/'
        ));
        $this->success = array();
        $this->fields_list = array(
            'id_gocarrier_shipping' => array(
                'title' => $this->l('ID'),
                'align' => 'left',
                'width' => 'auto'
            ),
            'name' => array(
                'title' => $this->l('Shipping City'),
                'align' => 'left',
                'width' => 'auto'
            ),
            'price' => array(
                'title' => $this->l('Shipping Cost'),
                'align' => 'left',
                'width' => 'auto'
            ),
            'active' => array(
                'title' => $this->l('Status'),
                'type' => 'bool',
                'active' => 'status',
                'orderby' => false,
                'class' => 'fixed-width-sm'
            )
        );
        $this->colorOnBackground = true;

        $this->_select = 'l.name, p.city';

        $this->_join =
            'LEFT JOIN `' . _DB_PREFIX_ . 'gocarrier_depot_lang` l
                ON (
                    l.`id_gocarrier_depot` = a.`id_gocarrier_depot`
                )
            LEFT JOIN `' . _DB_PREFIX_ . 'gocarrier` p
                ON (
                    p.`id_gocarrier` = a.`id_gocarrier`
                )
            LEFT JOIN `' . _DB_PREFIX_ . 'gocarrier_depot_shipping_lang` al
                ON (
                    al.`id_gocarrier_depot_shipping` = a.`id_gocarrier_depot_shipping`
                )';

        parent::__construct();
    }
}