{*
* Project : everpsshippingperpostcode
* @author Team EVER
* @copyright Team EVER
* @license   Tous droits réservés / Le droit d'auteur s'applique (All rights reserved / French copyright law applies)
* @link https://www.team-ever.com
*}

<div class="panel">
    <h3><i class="icon icon-credit-card"></i> {l s='Ever Shipping per postcode' mod='everpsshippingperpostcode'}</h3>
    {* <img id="everlogo" src="{$everpsshippingperpostcode_dir|escape:'htmlall':'UTF-8'}logo.png" style="max-width: 120px;"> *}
    <p>
        <strong>{l s='Welcome to Go carrier shipping !' mod='gocarrier'}</strong><br />
        {l s='Thanks for using Go carrier module' mod='gocarrier'}</a>.<br />{l s='Please configure this form and make sure your server has a enough longer max_execution_time' mod='gocarrier'}<br />
    </p>
    {if isset($moduleConfUrl) && $moduleConfUrl}
        <p>
            <a href="{$moduleConfUrl|escape:'htmlall':'UTF-8'}"
                class="btn btn-success">{l s='Direct link to module configuration' mod='gocarrier'}</a>
        </p>
    {/if}
</div>