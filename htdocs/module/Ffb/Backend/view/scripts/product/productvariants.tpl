<!-- productvariants -->
<!-- form-search-contingent -->
<form>
    <div class="columns">
        <div class="column full">

            <div class="row form-header">
                <h2>
                    {$this->translate('TTL_PRODUCT_VARIANTS')}
                </h2>
                <a href="{$uriAddEntity}" class="button gray add productvariant"></a>
            </div>

        </div>
    </div>
</form>
<!-- /form-search-contingent -->

<ul class="ffb-accordion navi scrollable productvariants">
    {foreach from=$productvariants item=item}

        {if $item|is_array}
            <li>
                {if $item.name|strlen gt 0}
                    <a class="accordion-title" href="#" class="title">{$item.name}</a>
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
<!-- /productvariants -->