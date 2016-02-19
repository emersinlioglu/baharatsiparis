<!-- admin/index.tpl -->
{$script=$this->headScript()->appendFile('js/backend/AdminController.js')}
{$script=$this->headScript()->appendFile('js/backend/form/UserForm.js')}
{$script=$this->headScript()->appendFile('js/backend/form/CompanyForm.js')}
{$script=$this->headScript()->appendFile('js/backend/form/HotelForm.js')}
{include file='../partials/scripts.tpl'}

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
    class="mainnavi-container simple{if $withSubnavi eq true} with-subnavi{/if}"
    data-url="{$uriGetList}"
>

    {$entitiesList}

</div>
<!-- /mainnavi-container -->

{$script=$this->inlineScript()->appendScript('AdminController.initIndex();')}
<!-- /admin/index.tpl -->