<!-- product/index.tpl -->
{$script=$this->headScript()->appendFile('js/backend/ProductController.js')}
{$script = $this->headScript()->appendFile('js/backend/form/ProductForm.js')}
{$script = $this->headScript()->appendFile('js/backend/form/CategoryForm.js')}

<!-- controls -->
<div class="controls">

{*
    <a href="{$createEntityUrl}" class="button gray add">
        {$this->translate('BTN_ADD_ENTITY')}
    </a>
    <!-- design-select -->
    <div class="design-select default nav">
        <div class="wrap">
            <div class="value">{$this->translate('LBL_ENTITIES_FILTER_SHOW_ALL')}</div>
        </div>
        {$entitiesFilter}
    </div>
    <!-- /design-select -->
*}
</div>
<!-- /controls -->

<!-- mainnavi-container -->
<div
    class="mainnavi-container product-categories{if $withSubnavi eq true} with-subnavi{/if}"
    data-url="{$uriGetList}"
>

    <div class="tab-content-header">
        <h2>{$this->translate('TTL_CATEGORIES')}</h2>
        {$uriAddEntity}
    </div>

     {if $items}
        <ul class="ffb-accordion navi main-navi-list" >

            {foreach item=item from=$items}
                {include file="./partials/mainnavi_item.tpl" item=$item}
            {/foreach}

        </ul>
    {/if}

</div>
<!-- /mainnavi-container -->

{$script=$this->inlineScript()->appendScript('$(function() { ProductController.initIndex(); })')}
<!-- /product/index.tpl -->