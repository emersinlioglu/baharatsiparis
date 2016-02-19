<!-- admin/users.tpl -->

<!-- form-default -->
<div class="form-default form-search-subnavi form-search-users-subnavi">
    <div class="columns">
        <div class="column full">

            <div class="form-header">
                <h2>{$this->translate('TTL_USERS')}</h2>
                <a href="{$uriAddEntity}" class="button gray add"
                   data-pane-title="{$this->translate('TTL_USER_DATA')}">
                </a>
            </div>
                
        </div>
    </div>
</div>
<!-- /form-default -->

<!-- scroll-container -->
<div class="scroll-container">
    <div class="scroll">

        <ul class="ffb-accordion navi users">
        {foreach from=$items.values item=item}

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
                <li class="pane-navi-link-cnt">{$item}</li>
            {/if}

        {/foreach}
        </ul>

    </div>
</div>
<!-- /scroll-container -->

<!-- /admin/users.tpl -->