<!-- partials/form/select_inplace.tpl -->
<div class="row editable select">
    {assign var=field value=$form->get($fieldName)}
    {if $field->getLabel() neq NULL}{$this->formLabel($field)}{/if}
    <span class="editable value">
        {foreach item=option from=$field->getValueOptions() key=i}
            {if $i eq $field->getValue()}
                {$this->translate($option)}
            {/if}
        {/foreach}
    </span>
    <span class="editable edit hide">
        {$this->formSelect($field)}
        {if $field->getAttribute('multiple') eq 'multiple'}
            {$fN = $field->getName()|cat:'[]'}
        {else}
            {$fN = $field->getName()}
        {/if}
        {include file="./validators.tpl" fieldName=$fN validators=$form->getFieldValidators($fieldName)}
    </span>
</div>
<!-- /partials/form/select_inplace.tpl -->