<!-- partials/form/input_colorpicker.tpl -->
<div class="row editable text colorpicker">
{if $form}
    {assign var=field value=$form->get($fieldName)}
    {if $field->getLabel() neq NULL}{$this->formLabel($field)}{/if}
    <span class="editable value colorPicker-picker preview" style="background-color: {$field->getValue()};"></span>
    <span class="editable edit hide">
        {$this->formInput($field)}
        {include file="./validators.tpl" fieldName=$field->getName() validators=$form->getFieldValidators($fieldName)}
    </span>
    {if $removeTitle}
        <span class="remove {$removeClass}" data-value="1" title="{$removeTitle}"></span>
    {/if}
{else}
    missing form
{/if}
</div>
<!-- /partials/form/input_colorpicker_inplace.tpl -->