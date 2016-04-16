<!-- subnavi -->

<!-- form-search-contingent -->
{$this->form()->openTag($formSearchProduct)}
<div class="columns">
    <div class="column full">

        <div class="form-header">
            <h2>{$this->translate('BTN_ADD_PRODUCT')}</h2>
            {if $showProductLink}
                <a href="{$uriAddEntity}" class="button gray add"></a>
            {/if}
        </div>

        {if $showProductLink}
            {include file='../partials/form/select.tpl' fieldName='isSystem' form=$formSearchProduct class="system"}
        {/if}
        {include file='../partials/form/input_text.tpl' fieldName='search' form=$formSearchProduct}
        {include file='../partials/form/input_submit.tpl' fieldName='send' form=$formSearchProduct}

    </div>
</div>
{$this->form()->closeTag()}
<!-- /form-search-contingent -->

<ul class="ffb-accordion scrollable navi products">
    {foreach from=$products item=item}

        <li class="pane-navi-link-cnt">
            <span class="entry-action hidden"></span>
            <span class="entry-name">

                {$this->span('', 'edit', $item.span.attributes)}

                <a class="pane-navi-link product{if $item.link.isSystem} system{/if}"
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

<div class="productvariants-cnt">

</div>
<!-- /subnavi -->