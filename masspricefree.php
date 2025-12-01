<?php
/**
 * PrestaShop module created by VEKIA, a guy from official PrestaShop community ;-)
 *
 * @author    VEKIA https://www.prestashop.com/forums/user/132608-vekia/
 * @copyright 2010-2020 VEKIA
 * @license   This program is not free software and you can't resell and redistribute it
 *
 * CONTACT WITH DEVELOPER http://mypresta.eu
 * support@mypresta.eu
 */

declare(strict_types=1);

if (!defined('_PS_VERSION_')) {
    exit;
}

class MassPriceFree extends Module
{
    public function __construct()
    {
        $this->name = 'masspricefree';
        $this->tab = 'administration';
        $this->version = '1.2.2';
        $this->author = 'MyPresta.eu';
        $this->need_instance = 0;
        $this->bootstrap = true;
        $this->module_key = '680cd01f97ebd84b44bb98a1e54d758f';

        parent::__construct();

        $this->displayName = $this->l('Mass alter prices by percentage value');
        $this->description = $this->l('With this module you can quickly alter prices of your products by % (decrease or increase)');
        
        $this->ps_versions_compliancy = ['min' => '1.7', 'max' => _PS_VERSION_];
    }

    public function install(): bool
    {
        if (!parent::install() || !$this->registerHook('actionAdminControllerSetMedia')) {
            return false;
        }
        return true;
    }

    public function uninstall(): bool
    {
        return parent::uninstall();
    }

    public function hookActionAdminControllerSetMedia(array $params): void
    {
        // Add media if needed
    }

    public function getContent(): string
    {
        if (Tools::isSubmit('btnSubmit')) {
            $this->postProcess();
        }
        return $this->renderForm();
    }

    public function renderForm(): string
    {
        $fields_form = [
            'form' => [
                'legend' => [
                    'title' => $this->l('Settings'),
                    'icon' => 'icon-cubes',
                ],
                'input' => [
                    [
                        'type' => 'select',
                        'label' => $this->l('Shop'),
                        'name' => 'masspricefree_id_shop',
                        'desc' => $this->l('Module will change prices in selected shop only'),
                        'options' => [
                            'query' => Shop::getShops(false),
                            'id' => 'id_shop',
                            'name' => 'name',
                        ],
                    ],
                    [
                        'type' => 'text',
                        'label' => $this->l('Increase / decrease by'),
                        'name' => 'masspricefree_value',
                        'suffix' => '%',
                        'desc' => $this->l('Type here percentage value, separate decimal values by dot (not comma)') . $this->context->smarty->fetch(_PS_MODULE_DIR_ . 'masspricefree/views/script.tpl'),
                    ],
                    [
                        'type' => 'categories',
                        'label' => $this->l('By category'),
                        'name' => 'masspricefree_cat',
                        'class' => 'masspricefree_cat',
                        'desc' => $this->l('You can increase or decrease price of products from selected categories only. Select categories here and module will change price only if product\'s main category will be one from selected categories. If you will not select categories here - module will change price of all products.'),
                        'tree' => [
                            'root_category' => 1,
                            'use_checkbox' => 1,
                            'id' => 'id_category',
                            'name' => 'name_category',
                            'selected_categories' => [],
                        ],
                    ],
                    [
                        'type' => 'select',
                        'label' => $this->l('What to do?'),
                        'name' => 'masspricefree_wtd',
                        'required' => true,
                        'lang' => false,
                        'options' => [
                            'query' => [
                                ['value' => '1', 'name' => $this->l('Increase prices by defined percentage value')],
                                ['value' => '2', 'name' => $this->l('Decrease price by defined percentage value')],
                            ],
                            'id' => 'value',
                            'name' => 'name',
                        ],
                    ],
                    [
                        'type' => 'select',
                        'label' => $this->l('Type of products'),
                        'name' => 'masspricefree_type',
                        'required' => true,
                        'lang' => false,
                        'options' => [
                            'query' => [
                                ['value' => '1', 'name' => $this->l('Change price of products only')],
                                ['value' => '3', 'name' => $this->l('Change price of products\'s combinations')],
                                ['value' => '2', 'name' => $this->l('Change price of products and its combinations')],
                            ],
                            'id' => 'value',
                            'name' => 'name',
                        ],
                    ],
                ],
                'submit' => [
                    'title' => $this->l('Alter prices!'),
                ],
            ],
        ];

        $helper = new HelperForm();
        $helper->show_toolbar = false;
        $helper->table = $this->name;
        $helper->module = $this;
        $helper->default_form_language = $this->context->language->id;
        $helper->allow_employee_form_lang = (int) Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG');
        $helper->identifier = 'masspricefree';
        $helper->submit_action = 'btnSubmit';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false) . '&configure=' . $this->name . '&tab_module=' . $this->tab . '&module_name=' . $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->tpl_vars = [
            'fields_value' => $this->getConfigFieldsValues(),
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        ];

        return $helper->generateForm([$fields_form]);
    }

    public function getConfigFieldsValues(): array
    {
        return [
            'masspricefree_value' => '0.00',
            'masspricefree_wtd' => '1',
            'masspricefree_id_shop' => Tools::getValue('masspricefree_id_shop', (string) $this->context->shop->id),
            'masspricefree_type' => Tools::getValue('masspricefree_type', '2'),
            'masspricefree_cat' => Tools::getValue('masspricefree_cat', '0'),
        ];
    }

    private function postProcess(): void
    {
        $whereProduct = '';
        $whereProductAttribute = '';
        $innerJoin = '';

        $categories = Tools::getValue('masspricefree_cat');
        if ($categories !== false && is_array($categories)) {
            $categoriesList = implode(',', array_map('intval', $categories));
            $whereProduct = ' AND id_category_default IN (' . $categoriesList . ')';
            $whereProductAttribute = ' AND ps.id_category_default IN (' . $categoriesList . ')';
            $innerJoin = 'INNER JOIN `' . _DB_PREFIX_ . 'product_shop` AS ps ON ps.id_product = pas.id_product';
        }

        $shopId = (int) Tools::getValue('masspricefree_id_shop');
        $value = (float) Tools::getValue('masspricefree_value', 0);
        $type = (int) Tools::getValue('masspricefree_type');
        $action = (int) Tools::getValue('masspricefree_wtd', 1);

        $db = Db::getInstance();

        // Calculate multiplier based on action (Increase or Decrease)
        // If Increase: price = price + (price * value / 100)  => price * (1 + value/100)
        // If Decrease: price = price - (price * value / 100)  => price * (1 - value/100)
        
        $factor = $value / 100;
        $multiplier = ($action === 1) ? (1 + $factor) : (1 - $factor);

        // Sanitize inputs for SQL
        // $shopId is int, $multiplier is float.
        
        if ($type == 2 || $type == 3) {
            // Update Combinations
            $sql = 'UPDATE `' . _DB_PREFIX_ . 'product_attribute_shop` AS pas ' . $innerJoin . ' 
                    SET pas.price = pas.price * ' . (float)$multiplier . ' 
                    WHERE pas.id_shop = ' . (int)$shopId . $whereProductAttribute;
            $db->execute($sql);
        }

        if ($type != 3) {
            // Update Products
            $sql = 'UPDATE `' . _DB_PREFIX_ . 'product_shop` 
                    SET price = price * ' . (float)$multiplier . ' 
                    WHERE id_shop = ' . (int)$shopId . $whereProduct;
            $db->execute($sql);
        }

        // Clear template cache
        $this->_clearCache('*');
        
        $this->context->controller->confirmations[] = $this->l('Settings updated');
    }
}