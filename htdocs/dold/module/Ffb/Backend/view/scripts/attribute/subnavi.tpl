<!-- subnavi -->

<!-- form-search-contingent -->
{$this->form()->openTag($form)}
<div class="columns">
    <div class="column full">

        <div class="form-header">
            <h2>{$this->translate('BTN_ADD_ATTRIBUTE')}</h2>
            {if $showAttributeLink}
            <a href="{$uriAddEntity}" class="button gray add"></a>
            {/if}
        </div>

        {include file='../partials/form/input_text.tpl'     fieldName='search'}
        {include file='../partials/form/input_submit.tpl'   fieldName='send'}
    </div>
</div>
{$this->form()->closeTag()}
<!-- /form-search-contingent -->

<ul class="ffb-accordion navi scrollable attributes">
{foreach from=$attributes item=item}

    {if $item|is_array}
        <li>
            {if $item.title|strlen gt 0}
            <a class="accordion-title" href="#" class="title">{$item.title}</a>
            {/if}

            <ul class="accordion-content">
                {foreach from=$item.values item=value}
                <li class="pane-navi-link">{$value}</li>
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
<!-- /subnavi -->