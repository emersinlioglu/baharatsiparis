<!-- partials/form/control_directfileupload.tpl -->
<div class="row fileupload direct">
{if $form}
    {assign var=field value=$form->get($fieldName)}
    {if $field->getLabel() neq NULL}{$this->formLabel($field)}{/if}
    {$this->formHidden($field)}
    {include file="./validators.tpl" fieldName=$field->getName() validators=$form->getFieldValidators($fieldName)}
{else}
    missing form
{/if}
</div>
<!-- /partials/form/control_directfileupload.tpl -->