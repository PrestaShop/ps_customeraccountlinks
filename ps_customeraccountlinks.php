<?php
/*
* 2007-2015 PrestaShop
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
*  @author PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2015 PrestaShop SA
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

if (!defined('_PS_VERSION_')) {
    exit;
}

use PrestaShop\PrestaShop\Core\Module\WidgetInterface;

class Ps_Customeraccountlinks extends Module implements WidgetInterface
{
    public function __construct()
    {
        $this->name = 'ps_customeraccountlinks';
        $this->tab = 'front_office_features';
        $this->version = '1.0.3';
        $this->author = 'PrestaShop';
        $this->need_instance = 0;

        parent::__construct();

        $this->displayName = $this->l('My Account block');
        $this->description = $this->l('Displays a block with links relative to a user\'s account.');
        $this->ps_versions_compliancy = array('min' => '1.7.0.0', 'max' => _PS_VERSION_);
    }

    public function install()
    {
        return parent::install()
            && $this->registerHook('actionModuleRegisterHookAfter')
            && $this->registerHook('actionModuleUnRegisterHookAfter')
            && $this->registerHook('displayFooter')
        ;
    }

    public function uninstall()
    {
        return (parent::uninstall() && $this->removeMyAccountBlockHook());
    }

    public function renderWidget($hookName = null, array $configuration = [])
    {
        $this->smarty->assign($this->getWidgetVariables($hookName, $configuration));

        return $this->fetch('module:'.$this->name.'/'.$this->name.'.tpl');
    }

    public function getWidgetVariables($hookName = null, array $configuration = [])
    {
        $link = $this->context->link;

        $my_account_urls = [
            0 => [
                'title' => $this->l('Orders'),
                'url' => $link->getPageLink('history', true),
            ],
            2 => [
                'title' => $this->l('Credit slips'),
                'url' => $link->getPageLink('order-slip', true),
            ],
            3 => [
                'title' => $this->l('Addresses'),
                'url' => $link->getPageLink('addresses', true),
            ],
            4 => [
                'title' => $this->l('Personal info'),
                'url' => $link->getPageLink('identity', true),
            ],
        ];

        if ((int)Configuration::get('PS_ORDER_RETURN')) {
            $my_account_urls[1] = [
                'title' => $this->l('Merchandise returns'),
                'url' => $link->getPageLink('order-follow', true),
            ];
        }

        if (CartRule::isFeatureActive()) {
            $my_account_urls[5] = [
                'title' => $this->l('Vouchers'),
                'url' => $link->getPageLink('discount', true),
            ];
        }

        // Sort Account links base in his title, keeping the keys
        asort($my_account_urls);

        return [
            'my_account_urls' => $my_account_urls,
            'logout_url' => $link->getPageLink('index', true, null, "mylogout"),
        ];
    }

    public function hookActionModuleUnRegisterHookAfter($params)
    {
        return $this->hookActionModuleRegisterHookAfter($params);
    }
    public function hookActionModuleRegisterHookAfter($params)
    {
        if ($params['hook_name'] == 'displayMyAccountBlock') {
            $this->_clearCache($this->name.'.tpl');
        }
    }

    private function addMyAccountBlockHook()
    {
        return Db::getInstance()->execute('INSERT IGNORE INTO `'._DB_PREFIX_.'hook` (`name`, `title`, `description`, `position`) VALUES (\'displayMyAccountBlock\', \'My account block\', \'Display extra informations inside the "my account" block\', 1)');
    }
    private function removeMyAccountBlockHook()
    {
        return Db::getInstance()->execute('DELETE FROM `'._DB_PREFIX_.'hook` WHERE `name` = \'displayMyAccountBlock\'');
    }
}
