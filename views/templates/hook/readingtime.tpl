{*
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
*}

{if isset($aw_crt_minutes)}
    <div id="aw-readingtime" class="aw-readingtime">
        <span class="aw-readingtime__icon" aria-hidden="true">⏱</span>
        <span class="aw-readingtime__label">
            {$aw_crt_label|escape:'html':'UTF-8'} :
        </span>

        {if $aw_crt_minutes > 0}
            <strong class="aw-readingtime__value">
                {$aw_crt_minutes} {l s='min' mod='aw_cmsreadtime'}
                {if $aw_crt_seconds > 0}
                    {$aw_crt_seconds}s
                {/if}
            </strong>
        {else}
            <strong class="aw-readingtime__value">
                &lt; 1 {l s='min' mod='aw_cmsreadtime'}
            </strong>
        {/if}
    </div>
{/if}
