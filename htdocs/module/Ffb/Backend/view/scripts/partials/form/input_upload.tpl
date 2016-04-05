<!-- partials/form/input_file.tpl -->
<div class="row file">
{if $form}
    {assign var=field value=$form->get($fieldName)}
    {*<div class="button gray file">*}
    {if $field->getLabel() neq NULL}{$this->formLabel($field)}{/if}
    {$this->formFile($field)}

    {include file="./validators.tpl" fieldName=$field->getName() validators=$form->getFieldValidators($fieldName)}
    {*</div>*}
{else}
    missing form
{/if}
</div>
<!-- /partials/form/input_file.tpl -->
