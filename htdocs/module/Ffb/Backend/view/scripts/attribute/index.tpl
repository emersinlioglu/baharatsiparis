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

                        <li class="pane-navi-link-cnt">
                            <span class="entry-action hidden"></span>
                            <span class="entry-name">

                                {$this->span('', 'edit', $item.span.attributes)}

                                <a class="pane-navi-link attributes"
                                   href="{$item.link.url}"
                                   title="{$item.link.masterTrans}"
                                   data-pane-title="{$item.link.paneTitle}"
                                   data-copy-url="{$item.link.copyUrl}"
                                   data-delete-url="{$item.link.deleteUrl}">

                                    {if $tab.type == 'templates'}

                                        {$item.link.masterTrans}

                                    {else}
                                        {foreach item="trans" from=$item.link.translations key="langCode"}
                                            <span class="tr lang-{$langCode}">
                                                {if !$trans}
                                                    <span class="no-trans">
                                                        {if $item.link.masterTrans}
                                                            {$item.link.masterTrans}
                                                        {else}
                                                            {$this->translate('LBL_NO_TRANSLATION')}
                                                        {/if}
                                                    </span>
                                                {else}
                                                    {$trans}
                                                {/if}
                                            </span>
                                        {/foreach}
                                    {/if}

                                </a>

                            </span>
                        </li>

                    {/foreach}
                </ul>
            {/if}

        </div>
    {/foreach}

</div>
<!-- /attribute/index.tpl -->

{$script=$this->inlineScript()->appendScript('AttributeController.initIndex();')}