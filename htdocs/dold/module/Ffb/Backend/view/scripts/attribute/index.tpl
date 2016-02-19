<!-- attribute/index.tpl -->
{$script=$this->headScript()->appendFile('js/backend/AttributeController.js')}
{$script = $this->headScript()->appendFile('js/backend/form/AttributeForm.js')}
{$script = $this->headScript()->appendFile('js/backend/form/SortEntitiesForm.js')}
{$script = $this->headScript()->appendFile('js/backend/form/TemplateForm.js')}
{$script = $this->headScript()->appendFile('js/backend/form/AttributeGroupForm.js')}

<div class="mainnavi-container main-navi-list {if $withSubnavi eq true} with-subnavi{/if}"
     data-url="{$uriGetList}">

    {*{ show tabs }*}
    <div class="tabs">
        {foreach item=tab from=$tabs}
            {if $tab.label}
                <div class="tab{if $tab.active} active{/if} {$tab.type}" data-content="{$tab.type|escape:'htmlall'}">
                    <span>{$tab.label}</span>
                </div>
            {/if}
        {/foreach}
    </div>

    {*{ show panels }*}
    {foreach item=tab from=$tabs}
        <div class="{$tab.type} tab-content{if !$tab.active} hidden{/if}"
             data-content="{$tab.type|escape:'htmlall'}"
             >

            {*{if $tab.controls}*}
            {*{include file='./partials/attribute_controls.tpl' controls=$tab.controls}*}
            {*{/if}*}

            <div class="tab-content-header">
                <h2>{$this->translate($tab.itemListTitleKey)}</h2>
                {$tab.uriAddEntity}
            </div>

            {if $tab.items}
                <ul class="ffb-accordion navi main-navi-list dont-init">
                    {foreach item=item from=$tab.items}
                        {if $item|is_array}
                            <li>
                                {if $item.title|strlen gt 0}
                                    <a class="accordion-title" href="#" class="title">{$item.title}</a>
                                {/if}
                                <ul class="accordion-content">
                                    {foreach from=$item.values item=value}
                                        <li class="pane-navi-link-cnt">{$value}</li>
                                    {/foreach}
                                </ul>
                            </li>
                        {else}
                            <li class="pane-navi-link-cnt">
                                <span class="entry-action hidden"></span>
                                <span class="entry-name">{$item}</span>
                            </li>
                        {/if}
                    {/foreach}
                </ul>
            {/if}

        </div>
    {/foreach}

</div>
<!-- /attribute/index.tpl -->

{$script=$this->inlineScript()->appendScript('AttributeController.initIndex();')}