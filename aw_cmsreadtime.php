<?php
/**
* 2025 AltumWeb
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@opensource.org so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author    AltumWeb <contact@mathieulp.fr>
*  @copyright 2025 AltumWeb
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*/

if (!defined('_PS_VERSION_')) {
    exit;
}

class Aw_cmsreadtime extends Module
{
    const CFG_ENABLED = 'AW_CMSREADTIME_ENABLED';
    const CFG_SPEED   = 'AW_CMSREADTIME_WPM';
    const CFG_POS     = 'AW_CMSREADTIME_POSITION';

    public function __construct()
    {
        $this->name = 'aw_cmsreadtime';
        $this->tab = 'front_office_features';
        $this->version = '1.0.0';
        $this->author = 'AltumWeb';
        $this->need_instance = 0;

        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('CMS Page Reading Time');
        $this->description = $this->l('Add the CMS page reading times in the page.');

        $this->confirmUninstall = $this->l('Are you sure you want to uninstall this module?');

        $this->ps_versions_compliancy = array('min' => '8.0', 'max' => '9.99.99.99');
    }

    public function install()
    {
        return parent::install()
            && Configuration::updateValue(self::CFG_ENABLED, 1)
            && Configuration::updateValue(self::CFG_SPEED, 200)
            && Configuration::updateValue(self::CFG_POS, 'top')
            && $this->registerHook('header')
            && $this->registerHook('displayContentWrapperTop')
            && $this->registerHook('displayContentWrapperBottom');
    }

    public function uninstall()
    {
        Configuration::deleteByName(self::CFG_ENABLED);
        Configuration::deleteByName(self::CFG_SPEED);
        Configuration::deleteByName(self::CFG_POS);
        return parent::uninstall();
    }

    /**
     * Load the configuration form
     */
    public function getContent()
    {
        if (Tools::isSubmit('submitAw_cmsreadtimeModule')) {
            $this->postProcess();
        }

        $this->context->smarty->assign([
            'module_dir' => $this->_path,
        ]);

        return $this->renderForm();
    }

    /**
     * Create the form that will be displayed in the configuration of your module.
     */
    protected function renderForm()
    {
        $helper = new HelperForm();
        $helper->show_toolbar = false;
        $helper->module = $this;
        $helper->default_form_language = $this->context->language->id;
        $helper->allow_employee_form_lang = 0;

        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitAw_cmsreadtimeModule';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
            .'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');

        $helper->tpl_vars = [
            'fields_value' => $this->getConfigFormValues(),
            'languages'    => $this->context->controller->getLanguages(),
            'id_language'  => $this->context->language->id,
        ];

        return $helper->generateForm([$this->getConfigForm()]);
    }

    /**
     * Create the structure of your form.
     */
    protected function getConfigForm()
    {
        return [
            'form' => [
                'legend' => [
                    'title' => $this->l('Settings'),
                    'icon'  => 'icon-cogs',
                ],
                'input' => [
                    [
                        'type'   => 'switch',
                        'label'  => $this->l('Enabled'),
                        'name'   => self::CFG_ENABLED,
                        'is_bool'=> true,
                        'values' => [
                            ['id' => 'active_on',  'value' => 1, 'label' => $this->l('Enabled')],
                            ['id' => 'active_off', 'value' => 0, 'label' => $this->l('Disabled')],
                        ],
                    ],
                    [
                        'type'  => 'text',
                        'label' => $this->l('Reading speed (words per minute)'),
                        'name'  => self::CFG_SPEED,
                        'desc'  => $this->l('Average is around 200 WPM.'),
                        'col'   => 2,
                    ],
                    [
                        'type'    => 'select',
                        'label'   => $this->l('Display position on CMS pages'),
                        'name'    => self::CFG_POS,
                        'options' => [
                            'query' => [
                                ['id' => 'top',    'name' => $this->l('Top of content')],
                                ['id' => 'bottom', 'name' => $this->l('Bottom of content')],
                            ],
                            'id'   => 'id',
                            'name' => 'name',
                        ],
                    ],
                ],
                'submit' => [
                    'title' => $this->l('Save'),
                ],
            ],
        ];
    }

    /**
     * Set values for the inputs.
     */
    protected function getConfigFormValues()
    {
        return [
            self::CFG_ENABLED => (bool)Configuration::get(self::CFG_ENABLED, 1),
            self::CFG_SPEED   => (int)Configuration::get(self::CFG_SPEED, 200),
            self::CFG_POS     => (string)Configuration::get(self::CFG_POS, 'top'),
        ];
    }

    /**
     * Save form data.
     */
    protected function postProcess()
    {
        Configuration::updateValue(self::CFG_ENABLED, (int)Tools::getValue(self::CFG_ENABLED, 0));
        Configuration::updateValue(self::CFG_SPEED, max(60, (int)Tools::getValue(self::CFG_SPEED, 200)));
        $pos = Tools::getValue(self::CFG_POS, 'top');
        Configuration::updateValue(self::CFG_POS, in_array($pos, ['top','bottom']) ? $pos : 'top');
    }

    /**
     * Add the CSS & JavaScript files you want to be added on the FO.
     */
    public function hookHeader()
    {
        if ($this->isCmsPage() && Configuration::get(self::CFG_ENABLED)) {
            $this->context->controller->registerStylesheet(
                'aw_cmsreadtime_front',
                'modules/'.$this->name.'/views/css/front.css',
                ['media' => 'all', 'priority' => 150]
            );
            $this->context->controller->registerJavascript(
                'aw_cmsreadtime_front',
                'modules/'.$this->name.'/views/js/front.js',
                ['position' => 'bottom', 'priority' => 150]
            );
        }
    }

    public function hookDisplayContentWrapperTop($params)
    {
        if (Configuration::get(self::CFG_POS) !== 'top') {
            return '';
        }
        return $this->renderReadingTime();
    }

    public function hookDisplayContentWrapperBottom($params)
    {
        if (Configuration::get(self::CFG_POS) !== 'bottom') {
            return '';
        }
        return $this->renderReadingTime();
    }

    protected function renderReadingTime()
    {
        if (!Configuration::get(self::CFG_ENABLED) || !$this->isCmsPage()) {
            return '';
        }

        $id_cms = (int)Tools::getValue('id_cms');
        $cms = new CMS($id_cms, (int)$this->context->language->id);

        if (!Validate::isLoadedObject($cms) || empty($cms->content)) {
            return '';
        }

        $text = $this->cleanHtmlToText($cms->content);
        $words = $this->countWordsUtf8($text);
        $wpm = max(60, (int)Configuration::get(self::CFG_SPEED, 200));

        $totalSeconds = (int)ceil($words * 60 / $wpm);
        $minutes = (int)floor($totalSeconds / 60);
        $seconds = $totalSeconds % 60;

        $this->context->smarty->assign([
            'aw_crt_minutes' => $minutes,
            'aw_crt_seconds' => $seconds,
            'aw_crt_words'   => $words,
            'aw_crt_wpm'     => $wpm,
            'aw_crt_label'   => $this->l('Estimated reading time'),
        ]);

        return $this->fetch('module:'.$this->name.'/views/templates/hook/readingtime.tpl');
    }

    protected function isCmsPage(): bool
    {
        return Tools::getValue('controller') === 'cms' && (int)Tools::getValue('id_cms') > 0;
    }

    protected function cleanHtmlToText(string $html): string
    {
        $html = preg_replace('#<script[^>]*>.*?</script>#is', ' ', $html);
        $html = preg_replace('#<style[^>]*>.*?</style>#is', ' ', $html);
        $text = strip_tags($html);
        $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = preg_replace('/\s+/u', ' ', $text);
        return trim($text);
    }

    protected function countWordsUtf8(string $text): int
    {
        if (preg_match_all('/[\p{L}\p{N}\']+/u', $text, $m)) {
            return count($m[0]);
        }
        return 0;
    }
}
