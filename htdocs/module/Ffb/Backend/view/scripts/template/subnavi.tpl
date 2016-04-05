<!-- subnavi -->

<!-- form-search -->
{$this->form()->openTag($form)}
    <div class="columns">
        <div class="column full">

            <div class="form-header">

                <h2>{$this->translate('TTL_ATTRIBUTE_GROUP')}</h2>

            </div>
        </div>
    </div>
{$this->form()->closeTag()}
<!-- /form-search -->

<ul class="ffb-accordion navi attribute-groups">
{foreach from=$attributeGroups item=item}

    <li class="pane-navi-link-cnt">
        {if $item.checkbox}
            {$item.checkbox}
        {/if}

        <a class="pane-navi-link attribute-group"
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
    </li>

{/foreach}
</ul>
<!-- /subnavi -->