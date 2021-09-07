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
    private $html;
    private $postErrors = array();
    private $postSuccess = array();

    public function __construct()
    {
        $this->name = 'gocarrier';
        $this->author = 'artinux';
        $this->version = '1.0.0';
        $this->ps_versions_compliancy = ['min' => '1.7.7', 'max' => _PS_VERSION_];

        parent::__construct();

        $this->displayName = $this->l('Go Carrier');
        $this->description = $this->l('Just a carrier module');
        $this->isSeven = Tools::version_compare(_PS_VERSION_, '1.7', '>=') ? true : false;
    }


    public function install()
    {
        if (extension_loaded('curl') == false) {
            $this->_errors[] = $this->l('You have to enable the cURL extension on your server to install this module');
            return false;
        }

        $carrier = $this->addCarrier();
        $this->addZones($carrier);
        $this->addGroups($carrier);
        $this->addRanges($carrier);

        $this->checkHooks();
        return parent::install()
            && $this->installModuleTab('AdminGocarrier', 'IMPROVE', $this->l('Go Carrier Shipping'));
    }

    private function installModuleTab($tabClass, $parent, $tabName)
    {
        $tab = new Tab();

        $tab->active = 1;
        $tab->class_name = $tabClass;
        $tab->id_parent = (int)Tab::getIdFromClassName($parent);
        $tab->position = Tab::getNewLastPosition($tab->id_parent);
        $tab->module = $this->name;
        if ($tabClass == 'AdminGocarrier' && $this->isSeven) {
            $tab->icon = 'icon-school';
        }

        foreach (Language::getLanguages(false) as $lang) {
            $tab->name[(int)$lang['id_lang']] = $tabName;
        }

        return $tab->add();
    }

    public function checkHooks()
    {
        $this->registerHook('updateCarrier');
        $this->registerHook('displayExpressCheckout');
        $this->registerHook('displayReassurance');
        $this->registerHook('displayCarrierExtraContent');
    }

    public function uninstall()
    {
        $carrier = new Carrier(
            (int)Configuration::get('GO_CARRIER_ID')
        );

        $carrier->delete();

        Configuration::deleteByName('GO_CARRIER_ID');
        Configuration::deleteByName('GOCARRIER_CI');
        return parent::uninstall()
            && $this->uninstallModuleTab('AdminGocarrier');
    }

    private function uninstallModuleTab($tabClass)
    {
        $tab = new Tab((int)Tab::getIdFromClassName($tabClass));

        return $tab->delete();
    }



    /**
     * Load the configuration form
     */
    public function getContent()
    {
        /**
         * If values have been submitted in the form, process.
         */
        if (((bool)Tools::isSubmit('submitGocarrierModule')) == true) {
            $this->postValidation();

            if (!count($this->postErrors)) {
                $this->postProcess();
            }
        }
        if (((bool)Tools::isSubmit('submitGocarrierPrices')) == true) {
            $this->exportDepotPricesToCsv();
        }

        // Display errors
        if (count($this->postErrors)) {
            foreach ($this->postErrors as $error) {
                $this->html .= $this->displayError($error);
            }
        }

        // Display confirmations
        if (count($this->postSuccess)) {
            foreach ($this->postSuccess as $success) {
                $this->html .= $this->displayConfirmation($success);
            }
        }
        $this->context->smarty->assign('gocarrier_dir', $this->_path);
        $this->html .= $this->context->smarty->fetch($this->local_path . 'views/templates/admin/header.tpl');
        $this->html .= $this->context->smarty->fetch($this->local_path . 'views/templates/admin/configure.tpl');
        $this->html .= $this->renderForm();
        $this->html .= $this->context->smarty->fetch($this->local_path . 'views/templates/admin/footer.tpl');

        return $this->html;
    }

    /**
     * Create the form that will be displayed in the configuration of your module.
     */
    protected function renderForm()
    {
        $helper = new HelperForm();

        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->module = $this;
        $helper->default_form_language = $this->context->language->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);

        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitGocarrierModule';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
            . '&configure=' . $this->name . '&tab_module=' . $this->tab . '&module_name=' . $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');

        $helper->tpl_vars = array(
            'fields_value' => $this->getConfigFormValues(), /* Add values for your inputs */
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        );

        return $helper->generateForm(array($this->getConfigForm()));
    }

    /**
     * Create the structure of your form.
     */
    protected function getConfigForm()
    {
        $countries = Country::getCountries(
            (int)$this->context->language->id,
            true
        );
        $taxes = Tax::getTaxes(
            (int)$this->context->language->id,
            false
        );

        return array(
            'form' => array(
                'legend' => $this->l('Settings'),
                'icon' => 'icon-cogs',
            ),
            'input' => array(
                array(
                    'type' => 'file',
                    'label' => $this->l('Import shipping from CSV file'),
                    'desc' => $this->l('Will Import shipping cost CSV file and import / update prices'),
                    'hint' => $this->l('For manually update only'),
                    'name' => 'shipping_file',
                    'display_image' => false,
                    'required' => false
                ),
                array(
                    'type' => 'select',
                    'label' => 'Please confirm default Ivory Coast country',
                    'hint' => 'Will be used for detecting shipping cost',
                    'desc' => 'This country will be used as Ivory Coast country',
                    'name' => 'GOCARRIER_CI',
                    'identifier' => 'name',
                    'required' => true,
                    'options' => array(
                        'query' => $countries,
                        'id' => 'id_country',
                        'name' => 'name',
                    ),
                ),
            ),
            'buttons' => array(
                'import' => array(
                    'name' => 'submitImport',
                    'type' => 'submit',
                    'class' => 'btn btn-success pull-right',
                    'icon' => 'process-icon-upload',
                    'title' => $this->l('Import uploaded file(s)')
                ),
            ),
            'submit' => array(
                'title' => $this->l('Save'),
            ),
        );
    }


    /**
     * Set values for the inputs.
     */
    protected function getConfigFormValues()
    {
        return array(
            'GOCARRIER_CI' => Configuration::get('GOCARRIER_CI'),
        );
    }

    public function postValidation()
    {
        if (Tools::isSubmit('submitGocarrierModule')) {
            if (
                !Tools::getValue('GOCARRIER_CI')
                || !Validate::isUnsignedInt(Tools::getValue('GOCARRIER_CI'))
            ) {
                $this->postErrors[] = $this->l('Error : The field GOCARRIER_CI is not valid');
            }
        }
    }

    /**
     * Save form data.
     */
    protected function postProcess()
    {
        $form_values = $this->getConfigFormValues();

        foreach (array_keys($form_values) as $key) {
            Configuration::updateValue($key, Tools::getValue($key));
        }
        $this->postSuccess[] = $this->l('All settings have been saved');
    }

    public function getOrderShippingCost($params, $shippinh_cost)
    {
        // $cart = Context::getContext()->cart;
        // dump($cart);
    }

    public function getOrderShippingCostExternal($params)
    {
    }

    protected function addCarrier()
    {
        $carrier = new Carrier();
        $carrier->name = $this->l('Delivery by') . ' ' . Configuration::get('PS_SHOP_NAME');
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

        if ($carrier->add() == true) {
            Configuration::updateValue('GO_CARRIER_ID', (int)$carrier->id);
            return $carrier;
        }
        return false;
    }


    protected function addZones($carrier)
    {
        $zones = Zone::getZones();

        foreach ($zones as $zone) {
            $carrier->addZone($zone['id_zone']);
        }
    }

    protected function addGroups($carrier)
    {
        $groups_ids = array();

        $groups = Group::getGroups(Context::getContext()->language->id);
        foreach ($groups as $group) {
            $groups_ids[] = $group['id_group'];
        }

        $carrier->setGroups($groups_ids);
    }

    protected function addRanges($carrier)
    {
        $range_price = new RangePrice();
        $range_price->id_carrier = $carrier->id;
        $range_price->delimiter1 = '0';
        $range_price->delimiter2 = '10000';
        $range_price->add();
    }

    public function hookUpdateCarrier($params)
    {
        $id_carrier_old = (int) $params['id_carrier'];
        $id_carrier_new = (int) $params['carrier']->id;
        if ($id_carrier_old === (int) Configuration::get('GO_CARRIER_ID')) {
            Configuration::updateValue('GO_CARRIER_ID', $id_carrier_new);
        }
    }

    public function hookDisplayCarrierExtraContent($params)
    {
        return $this->display(__FILE__, 'extra_carrier.tpl');
    }
}