    {if $item.hasSubitems}
        <li class="pane-navi-link-cnt">
            <span class="entry-action hidden"></span>
            <span class="accordion-title arrow open" class="title"></span>
            <span class="entry-name">

                {$this->span('', 'edit', $item.span.attributes)}

                <a class="pane-navi-link category"
                   href="{$item.link.url}"
                   title="{$item.link.masterTrans}"
                   data-pane-title="{$item.link.paneTitle}"
                   data-copy-url="{$item.link.copyUrl}"
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
            <ul class="accordion-content open">
                {foreach from=$item.subitems item=subItem}
                    {include file="./mainnavi_item.tpl" item=$subItem}
                {/foreach}
            </ul>
        </li>
    {else}
        <li class="pane-navi-link-cnt">
            <span class="entry-action hidden"></span>
            <span class="entry-name">

                {$this->span('', 'edit', $item.span.attributes)}

                <a class="pane-navi-link category"
                   href="{$item.link.url}"
                   title="{$item.link.masterTrans}"
                   data-pane-title="{$item.link.paneTitle}"
                   data-copy-url="{$item.link.copyUrl}"
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
    {/if}
{*{/foreach}*}