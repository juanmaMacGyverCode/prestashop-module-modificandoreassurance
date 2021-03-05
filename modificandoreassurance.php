<?php
/**
* 2007-2021 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author    PrestaShop SA <contact@prestashop.com>
*  @copyright 2007-2021 PrestaShop SA
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

if (!defined('_PS_VERSION_')) {
    exit;
}

use PrestaShop\PrestaShop\Core\Module\WidgetInterface;

class Modificandoreassurance extends Module implements WidgetInterface
{
    const ALLOWED_CONTROLLERS_CHECKOUT = [
        'cart',
        'order',
    ];
    const ALLOWED_CONTROLLERS_PRODUCT = [
        'product',
    ];
    const POSITION_NONE = 0;
    const POSITION_BELOW_HEADER = 1;
    const POSITION_ABOVE_HEADER = 2;

    protected $config_form = false;

    public function __construct()
    {
        $this->name = 'modificandoreassurance';
        $this->tab = 'front_office_features';
        $this->version = '1.0.0';
        $this->author = 'Juan Manuel L.D.';
        $this->need_instance = 0;

        /**
         * Set $this->bootstrap to true if your module is compliant with bootstrap (PrestaShop 1.6)
         */
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Modificando reassurance');
        $this->description = $this->l('Modificar el contenido de reassurance.');

        $this->confirmUninstall = $this->l('Desinstalado correctamente por Juan');

        $this->ps_versions_compliancy = array('min' => '1.7', 'max' => _PS_VERSION_);
    }

    /**
     * Don't forget to create update methods if needed:
     * http://doc.prestashop.com/display/PS16/Enabling+the+Auto-Update
     */
    public function install()
    {
        Configuration::updateValue('MODIFICANDOREASSURANCE_LIVE_MODE', false);

        return parent::install() &&
            $this->registerHook('header') &&
            $this->registerHook('backOfficeHeader') &&
            $this->registerHook('displayReassurance') &&
            $this->registerHook('displayProductPriceBlock');
    }

    public function uninstall()
    {
        Configuration::deleteByName('MODIFICANDOREASSURANCE_LIVE_MODE');

        return parent::uninstall();
    }

    /**
     * Load the configuration form
     */
    public function getContent()
    {
        /**
         * If values have been submitted in the form, process.
         */
        if (((bool)Tools::isSubmit('submitModificandoreassuranceModule')) == true) {
            $this->postProcess();
        }

        $this->context->smarty->assign('module_dir', $this->_path);

        $output = $this->context->smarty->fetch($this->local_path.'views/templates/admin/configure.tpl');

        return $output.$this->renderForm();
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
        $helper->submit_action = 'submitModificandoreassuranceModule';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
            .'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
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
        return array(
            'form' => array(
                'legend' => array(
                'title' => $this->l('Settings'),
                'icon' => 'icon-cogs',
                ),
                'input' => array(
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Live mode'),
                        'name' => 'MODIFICANDOREASSURANCE_LIVE_MODE',
                        'is_bool' => true,
                        'desc' => $this->l('Use this module in live mode'),
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => true,
                                'label' => $this->l('Enabled')
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => false,
                                'label' => $this->l('Disabled')
                            )
                        ),
                    ),
                    array(
                        'col' => 3,
                        'type' => 'text',
                        'prefix' => '<i class="icon icon-envelope"></i>',
                        'desc' => $this->l('Enter a valid email address'),
                        'name' => 'MODIFICANDOREASSURANCE_ACCOUNT_EMAIL',
                        'label' => $this->l('Email'),
                    ),
                    array(
                        'type' => 'password',
                        'name' => 'MODIFICANDOREASSURANCE_ACCOUNT_PASSWORD',
                        'label' => $this->l('Password'),
                    ),
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                ),
            ),
        );
    }

    /**
     * Set values for the inputs.
     */
    protected function getConfigFormValues()
    {
        return array(
            'MODIFICANDOREASSURANCE_LIVE_MODE' => Configuration::get('MODIFICANDOREASSURANCE_LIVE_MODE', true),
            'MODIFICANDOREASSURANCE_ACCOUNT_EMAIL' => Configuration::get('MODIFICANDOREASSURANCE_ACCOUNT_EMAIL', 'contact@prestashop.com'),
            'MODIFICANDOREASSURANCE_ACCOUNT_PASSWORD' => Configuration::get('MODIFICANDOREASSURANCE_ACCOUNT_PASSWORD', null),
        );
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
    }

    /**
    * Add the CSS & JavaScript files you want to be loaded in the BO.
    */
    public function hookBackOfficeHeader()
    {
        if (Tools::getValue('module_name') == $this->name) {
            $this->context->controller->addJS($this->_path.'views/js/back.js');
            $this->context->controller->addCSS($this->_path.'views/css/back.css');
        }
    }

    /**
     * Add the CSS & JavaScript files you want to be added on the FO.
     */
    public function hookHeader()
    {
        $this->context->controller->addJS($this->_path.'/views/js/front.js');
        $this->context->controller->addCSS($this->_path.'/views/css/front.css');
    }

    /**
     * @param array $params
     *
     * @return string
     *
     * @throws PrestaShopDatabaseException
     */
    public function hookdisplayReassurance($params)
    {
        $enableCheckout = (int) Configuration::get('PSR_HOOK_CHECKOUT');
        $enableProduct = (int) Configuration::get('PSR_HOOK_PRODUCT');
        $controller = Tools::getValue('controller');

        if (!$this->shouldWeDisplayOnBlockProduct($enableCheckout, $enableProduct, $controller)) {
            return '';
        }

        return $this->renderTemplateInHook('unFicheroConNombreQueYoQuiera.tpl');
    }

    /**
     * @param string $hookName
     *
     * @return string
     *
     * @throws PrestaShopDatabaseException
     */
    public function renderWidget($hookName = null, array $configuration = [])
    {
        /*if ($hookName === 'displayFooter') {
            return '';
        }
        if (!$this->isCached($this->templateFile, $this->getCacheId('blockreassurance'))) {
            $this->smarty->assign($this->getWidgetVariables($hookName, $configuration));
        }

        return $this->fetch($this->templateFile, $this->getCacheId('blockreassurance'));*/
    }

    /**
     * @param string $hookName
     *
     * @return array
     *
     * @throws PrestaShopDatabaseException
     */
    public function getWidgetVariables($hookName = null, array $configuration = [])
    {
        /*$blocks = ReassuranceActivity::getAllBlockByStatus(
            $this->context->language->id,
            $this->context->shop->id
        );

        $elements = [];
        foreach ($blocks as $key => $value) {
            if (!empty($value['icon'])) {
                $elements[$key]['image'] = $value['icon'];
            } elseif (!empty($value['custom_icon'])) {
                $elements[$key]['image'] = $value['custom_icon'];
            } else {
                $elements[$key]['image'] = '';
            }

            $elements[$key]['text'] = $value['title'] . ' ' . $value['description'];
            $elements[$key]['title'] = $value['title'];
            $elements[$key]['description'] = $value['description'];
        }

        return [
            'elements' => $elements,
        ];*/
    }

    /**
     * Assign smarty variables and display the hook
     *
     * @param string $template
     *
     * @return string
     *
     * @throws PrestaShopDatabaseException
     */
    private function renderTemplateInHook($template)
    {
        $id_lang = $this->context->language->id;

        $id_product = (int)Tools::getValue('id_product');
        //$product = new Product($id_product, false, $this->context->language->id);
        //$id_product = Tools::getValue('price');
        $price = Product::getPriceStatic(Tools::getValue('id_product'), true, null, 2);
        $priceWithouthDiscount = Product::getPriceStatic(Tools::getValue('id_product'), true, null, 2);
        $product = new Product($id_product, false, $this->context->language->id);
        /* Como obtener los taxes */
        $taxes = Tax::getTaxes();
        /* Como obtener el grupo de taxes*/
        $groupTaxes = TaxRulesGroup::getTaxRulesGroupsForOptions();
        /*Así extraigo el grupo de taxes */
        $product->id_tax_rules_group;
        /*Lo que paso a retrieveById está mal...
        Sin embargo esta función me devuelve toda la información alrededor de una regla de impuesto
        Array ( 
            [id_tax_rule] => 1 
            [id_tax_rules_group] => 1 
            [id_country] => 3 
            [id_state] => 0 
            [zipcode_from] => 0 
            [zipcode_to] => 0 
            [id_tax] => 1 
            [behavior] => 0 
            [description] => )
        
        */
        $retrieve = TaxRule::retrieveById($product->id_tax_rules_group);
        /*Si le paso el id de un producto, me devuelte el tipo impositivo que aplica sobre el*/
        $getTaxes = Tax::getProductTaxRate(Tools::getValue('id_product'));
        
        

        $elements = [];
        $elements[1]['type_link'] = "";
        $elements[1]['link'] = "";
        $elements[1]['icon'] = "";
        //$elements[1]['title'] = "HOLA MUNDO, el precio es: " . $product->price;
        //$elements[1]['title'] = "HOLA MUNDO, el precio es: " . print_r($product);
        //$elements[1]['title'] = "HOLA MUNDO, el precio es: <pre>" . print_r($taxes) . "</pre>";
        //$elements[1]['title'] = "HOLA MUNDO, el precio es: <pre>" . print_r($groupTaxes) . "</pre>";
        $elements[1]['title'] = "HOLA MUNDO, el precio es: " . $price;
        //$elements[1]['title'] = "HOLA MUNDO, el precio es: " . $product->id_tax_rules_group;
        //$elements[1]['title'] = "HOLA MUNDO, el precio es: " . print_r($retrieve);
        //$elements[1]['title'] = "HOLA MUNDO, el precio es: " . print_r($getTaxes);
        $elements[1]['description'] = "HOLA A TODO EL MUNDO";

        

        $precio = $product->price;
        $precioConTax = $this->calculatePriceWithTax($precio, $getTaxes);
        $obtainDiscountAbsolute = $this->obtainDiscount($price, $precioConTax);

        $unArray["has_discount"] = true;
        $unArray["regular_price"] = $priceWithouthDiscount;
        $this->context->smarty->assign([
            'precioConTax' => $precioConTax,
            'product' => $product,
            'taxRule' => $getTaxes,
            'precio' => $priceWithouthDiscount,
            'productHasDiscount' => $unArray["has_discount"], 
            'productShowPrice' => true, 
            //'productRegularPrice' => $unArray["regular_price"],
            'productRegularPrice' => $precioConTax,
            'productPrice' => $price,
            'productDiscountType' => 'percentage',
            'productDiscountPercentageAbsolute' => $obtainDiscountAbsolute,
            'blocks' => $elements,
            'iconColor' => "",
            'textColor' => '#00ff00',
            // constants
            'LINK_TYPE_NONE' => ReassuranceActivity::TYPE_LINK_NONE,
            'LINK_TYPE_CMS' => ReassuranceActivity::TYPE_LINK_CMS_PAGE,
            'LINK_TYPE_URL' => ReassuranceActivity::TYPE_LINK_URL,
        ]);

        return $this->context->smarty->display(dirname(__FILE__). '/views/templates/hook/' . $template);
    }

    private function calculatePriceWithTax($precio, $getTaxes)
    {
        return round($precio*(1+($getTaxes/100)), 2);
    }

    private function obtainDiscount($price, $priceWithTax){
        return round(100*(1-($price/$priceWithTax)), 2);
    }

    /**
     * Check if we can display the hook on product page or cart page.
     * The HOOK must be active
     *
     * @param int $enableCheckout
     * @param int $enableProduct
     * @param string $controller
     *
     * @return bool
     */
    private function shouldWeDisplayOnBlockProduct($enableCheckout, $enableProduct, $controller)
    {
        if ($enableCheckout === self::POSITION_BELOW_HEADER && in_array($controller, self::ALLOWED_CONTROLLERS_CHECKOUT)) {
            return true;
        }
        if ($enableProduct === self::POSITION_BELOW_HEADER && in_array($controller, self::ALLOWED_CONTROLLERS_PRODUCT)) {
            return true;
        }

        return false;
    }

    public function hookDisplayProductPriceBlock($params)
    {
        if ($params['type'] === 'weight' && 'product' === Tools::getValue('controller')) {
            if (false === Validate::isLoadedObject($this->context->cart)) {
                return;
            }

            /** @var \PrestaShop\Module\PrestashopCheckout\ShopContext $shopContext */
            $shopContext = $this->getService('ps_checkout.context.shop');

            /** @var \PrestaShop\Module\PrestashopCheckout\PayPal\PayPalPayIn4XConfiguration $payIn4XService */
            $payIn4XService = $this->getService('ps_checkout.pay_in_4x.configuration');

            $totalCartPrice = $this->context->cart->getSummaryDetails();
            $this->context->smarty->assign([
                'totalCartPrice' => $totalCartPrice['total_price'],
                'payIn4XisProductPageEnabled' => $payIn4XService->isProductPageEnabled(),
            ]);

            return $this->context->smarty->display(__FILE__, '/views/templates/hook/displayProductPriceBlockModificado.tpl');
        }
    }
}
