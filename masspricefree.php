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
if (!defined('_PS_VERSION_')) {
    exit;
}


class masspricefree extends Module
{
    public function __construct(): void
    {
        $this->name = 'masspricefree';
        $this->module_key = '680cd01f97ebd84b44bb98a1e54d758f';
        $this->version = '1.2.2';
        $this->author = 'MyPresta.eu';
        $this->mypresta_link = 'https://mypresta.eu/modules/administration-tools/free-mass-products-prices-update.html';
        $this->bootstrap = true;
        parent::__construct();
        $this->checkforupdates();
        $this->displayName = $this->l('Mass alter prices by percentage value');
        $this->description = $this->l('With this module you can quickly alter prices of your products by % (decrease or increase)');
    }

    public function inconsistency($ret)
    {
        return true;
    }

    public function hookActionAdminControllerSetMedia($params)
    {
        // FOR UPDATES ONLY
    }

    public function displayAdvert()
    {
        return $this->display(__file__, 'views/advert.tpl');
    }

    public function checkforupdates($display_msg = 0, $form = 0)
    {
        // ---------- //
        // ---------- //
        // VERSION 16 //
        // ---------- //
        // ---------- //
        $this->mkey = "nlc";
        if (@file_exists('../modules/' . $this->name . '/key.php')) {
            @require_once('../modules/' . $this->name . '/key.php');
        } else {
            if (@file_exists(dirname(__FILE__) . $this->name . '/key.php')) {
                @require_once(dirname(__FILE__) . $this->name . '/key.php');
            } else {
                if (@file_exists('modules/' . $this->name . '/key.php')) {
                    @require_once('modules/' . $this->name . '/key.php');
                }
            }
        }
        if ($form == 1) {
            return '
            <div class="panel" id="fieldset_myprestaupdates" style="margin-top:20px;">
            ' . ($this->psversion() == 6 || $this->psversion() == 7 ? '<div class="panel-heading"><i class="icon-wrench"></i> ' . $this->l('MyPresta updates') . '</div>' : '') . '
			<div class="form-wrapper" style="padding:0px!important;">
            <div id="module_block_settings">
                    <fieldset id="fieldset_module_block_settings">
                         ' . ($this->psversion() == 5 ? '<legend style="">' . $this->l('MyPresta updates') . '</legend>' : '') . '
                        <form action="' . $_SERVER['REQUEST_URI'] . '" method="post">
                            <label>' . $this->l('Check updates') . '</label>
                            <div class="margin-form">' . (Tools::isSubmit('submit_settings_updates_now') ? ($this->inconsistency(0) ? '' : '') . $this->checkforupdates(1) : '') . '
                                <button style="margin: 0px; top: -3px; position: relative;" type="submit" name="submit_settings_updates_now" class="button btn btn-default" />
                                <i class="process-icon-update"></i>
                                ' . $this->l('Check now') . '
                                </button>
                            </div>
                            <label>' . $this->l('Updates notifications') . '</label>
                            <div class="margin-form">
                                <select name="mypresta_updates">
                                    <option value="-">' . $this->l('-- select --') . '</option>
                                    <option value="1" ' . ((int)(Configuration::get('mypresta_updates') == 1) ? 'selected="selected"' : '') . '>' . $this->l('Enable') . '</option>
                                    <option value="0" ' . ((int)(Configuration::get('mypresta_updates') == 0) ? 'selected="selected"' : '') . '>' . $this->l('Disable') . '</option>
                                </select>
                                <p class="clear">' . $this->l('Turn this option on if you want to check MyPresta.eu for module updates automatically. This option will display notification about new versions of this addon.') . '</p>
                            </div>
                            <label>' . $this->l('Module page') . '</label>
                            <div class="margin-form">
                                <a style="font-size:14px;" href="' . $this->mypresta_link . '" target="_blank">' . $this->displayName . '</a>
                                <p class="clear">' . $this->l('This is direct link to official addon page, where you can read about changes in the module (changelog)') . '</p>
                            </div>
                            <div class="panel-footer">
                                <button type="submit" name="submit_settings_updates"class="button btn btn-default pull-right" />
                                <i class="process-icon-save"></i>
                                ' . $this->l('Save') . '
                                </button>
                            </div>
                        </form>
                    </fieldset>
                    <style>
                    #fieldset_myprestaupdates {
                        display:block;clear:both;
                        float:inherit!important;
                    }
                    </style>
                </div>
            </div>
            </div>';
        } else {
            if (defined('_PS_ADMIN_DIR_')) {
                if (Tools::isSubmit('submit_settings_updates')) {
                    Configuration::updateValue('mypresta_updates', Tools::getValue('mypresta_updates'));
                }
                if (Configuration::get('mypresta_updates') != 0 || (bool)Configuration::get('mypresta_updates') != false) {
                    if (Configuration::get('update_' . $this->name) < (date("U") - 259200)) {
                        $actual_version = masspricefreeUpdate::verify($this->name, (isset($this->mkey) ? $this->mkey : 'nokey'), $this->version);
                    }
                    if (masspricefreeUpdate::version($this->version) < masspricefreeUpdate::version(Configuration::get('updatev_' . $this->name)) && Tools::getValue('ajax', 'false') == 'false') {
                        $this->context->controller->warnings[] = '<strong>' . $this->displayName . '</strong>: ' . $this->l('New version available, check http://MyPresta.eu for more informations') . ' <a href="' . $this->mypresta_link . '">' . $this->l('More details in changelog') . '</a>';
                        $this->warning = $this->context->controller->warnings[0];
                    }
                } else {
                    if (Configuration::get('update_' . $this->name) < (date("U") - 259200)) {
                        $actual_version = masspricefreeUpdate::verify($this->name, (isset($this->mkey) ? $this->mkey : 'nokey'), $this->version);
                    }
                }
                if ($display_msg == 1) {
                    if (masspricefreeUpdate::version($this->version) < masspricefreeUpdate::version(masspricefreeUpdate::verify($this->name, (isset($this->mkey) ? $this->mkey : 'nokey'), $this->version))) {
                        return "<span style='color:red; font-weight:bold; font-size:16px; margin-right:10px;'>" . $this->l('New version available!') . "</span>";
                    } else {
                        return "<span style='color:green; font-weight:bold; font-size:16px; margin-right:10px;'>" . $this->l('Module is up to date!') . "</span>";
                    }
                }
            }
        }
    }

    public function install(): bool
    {
        if (!parent::install() || !$this->registerHook('ActionAdminControllerSetMedia')) {
            return false;
        }
        return true;
    }

    public function uninstall(): bool
    {
        if (!parent::uninstall()) {
            return false;
        }
        return true;
    }

    public static function psversion($part = 1)
    {
        $version = _PS_VERSION_;
        $exp = $explode = explode(".", $version);
        if ($part == 1) {
            return $exp[1];
        }
        if ($part == 2) {
            return $exp[2];
        }
        if ($part == 3) {
            return $exp[3];
        }
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
        return $this->displayAdvert() . $helper->generateForm([$fields_form]) . $this->checkforupdates(0, 1);
    }

    public function getContent(): string
    {
        if (Tools::isSubmit('btnSubmit')) {
            $this->postProcess();
        }
        return $this->renderForm();
    }

    public function getConfigFieldsValues()
    {
        return array(
            'masspricefree_value' => '0.00',
            'masspricefree_wtd' => '1',
            'masspricefree_id_shop' => Tools::getValue('masspricefree_id_shop', $this->context->shop->id),
            'masspricefree_type' => Tools::getValue('masspricefree_type', 2),
            'masspricefree_cat' => Tools::getValue('masspricefree_cat', 0)
        );
    }

    private function postProcess(): void
    {
        $WHERE_PRODUCT_ATTRIBUTE = '';
        $WHERE_PRODUCT = '';
        $INNER_JOIN = '';

        if (Tools::getValue('masspricefree_cat') !== false) {
            $WHERE_PRODUCT = ' AND id_category_default IN (' . implode(',', Tools::getValue('masspricefree_cat')) . ')';
            $WHERE_PRODUCT_ATTRIBUTE = ' AND ps.id_category_default IN (' . implode(',', Tools::getValue('masspricefree_cat')) . ')';
            $INNER_JOIN = 'INNER JOIN `' . _DB_PREFIX_ . 'product_shop` AS ps ON ps.id_product = pas.id_product';
        }

        $shopId = Tools::getValue('masspricefree_id_shop');
        $value = (float) Tools::getValue('masspricefree_value', 0);
        $type = Tools::getValue('masspricefree_type');
        $action = (int) Tools::getValue('masspricefree_wtd', 1);

        $db = Db::getInstance(); // _PS_USE_SQL_SLAVE_ is deprecated in PS 8.1

        if ($action === 1) { // increase
            if ($type == 2 || $type == 3) {
                $db->execute('UPDATE `' . _DB_PREFIX_ . 'product_attribute_shop` AS pas ' . $INNER_JOIN . " SET pas.price = pas.price + pas.price * $value / 100 WHERE pas.id_shop = '$shopId'" . $WHERE_PRODUCT_ATTRIBUTE);
            }
            if ($type != 3) {
                $db->execute('UPDATE `' . _DB_PREFIX_ . 'product_shop` SET price = price + price * $value / 100 WHERE id_shop = "$shopId"' . $WHERE_PRODUCT);
            }
        } else { // decrease
            if ($type == 2 || $type == 3) {
                $db->execute('UPDATE `' . _DB_PREFIX_ . 'product_attribute_shop` AS pas ' . $INNER_JOIN . " SET pas.price = pas.price - pas.price * $value / 100 WHERE pas.id_shop = '$shopId'" . $WHERE_PRODUCT_ATTRIBUTE);
            }
            if ($type != 3) {
                $db->execute('UPDATE `' . _DB_PREFIX_ . 'product_shop` SET price = price - price * $value / 100 WHERE id_shop = "$shopId"' . $WHERE_PRODUCT);
            }
        }

        // Clear template cache (method name changed in PS 8.1)
        $this->clearCache();
        $this->context->controller->confirmations[] = $this->l('Settings updated');
    }
}

class masspricefreeUpdate extends masspricefree
{
    public static function version(string $version): int
    {
        $clean = (int) str_replace('.', '', $version);
        $len = strlen((string) $clean);
        return match ($len) {
            3 => (int) ($clean . '0'),
            2 => (int) ($clean . '00'),
            1 => (int) ($clean . '000'),
            0 => (int) ($clean . '0000'),
            default => (int) $clean,
        };
    }

    public static function encrypt(string $string): string
    {
        return base64_encode($string);
    }

    public static function verify(string $module, string $key, string $version): ?string
    {
        $actual_version = null;
        if (ini_get('allow_url_fopen') && function_exists('file_get_contents')) {
            $url = 'http://dev.mypresta.eu/update/get.php?module=' . $module . '&version=' . self::encrypt($version) . '&lic=' . $key . '&u=' . self::encrypt(_PS_BASE_URL_ . __PS_BASE_URI__);
            $actual_version = @file_get_contents($url);
        }
        Configuration::updateValue('update_' . $module, date('U'));
        Configuration::updateValue('updatev_' . $module, $actual_version);
        return $actual_version;
    }
}