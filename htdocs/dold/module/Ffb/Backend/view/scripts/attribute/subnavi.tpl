<!-- subnavi -->

<!-- form-search -->
{$this->form()->openTag($form)}
<div class="columns">
    <div class="column full">

        <div class="form-header">
            <h2 class="subnavi">{$this->translate('BTN_ADD_ATTRIBUTE')}</h2>
            {if $showAttributeLink}
            <a href="{$uriAddEntity}" class="button gray add"></a>
            {/if}
        </div>

        {include file='../partials/form/input_text.tpl'     fieldName='search'}
        {include file='../partials/form/input_submit.tpl'   fieldName='send'}
    </div>
</div>
{$this->form()->closeTag()}
<!-- /form-search -->

<ul class="ffb-accordion navi scrollable attributes">
{foreach from=$attributes item=item}

    <li class="pane-navi-link-cnt">
        <span class="entry-action hidden"></span>
        <span class="entry-name">

            {if $item.checkbox}
                {$item.checkbox}
            {/if}

            <a class="pane-navi-link attribute"
               href="{$item.link.url}"
               title="{$item.link.masterTrans}"
               data-pane-title="{$item.link.paneTitle}"
               {*data-copy-url="{$item.link.copyUrl}"*}
               data-delete-url="{$item.link.deleteUrl}">

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
            </a>

        </span>
    </li>

{/foreach}
</ul>
<!-- /subnavi -->