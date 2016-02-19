<!-- partials/form/stars.tpl -->
<div class="row">
{if $form}
    {assign var=field value=$form->get($fieldName)}
    {if $field->getLabel() neq NULL}{$this->formLabel($field)}{/if}
    {$this->formHidden($field)}
    <div class="rating-stars">
    {foreach item=i from=array(1,2,3,4,5)}
        {if $i lte $field->getValue()}
        <span class="active"></span>
        {else}
        <span></span>
        {/if}
    {/foreach}
        <a class="reset"></a>
    </div>
{else}
    missing form
{/if}
</div>
<!-- /partials/form/stars.tpl -->