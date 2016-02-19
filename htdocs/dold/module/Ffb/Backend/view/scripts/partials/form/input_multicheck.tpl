{*
Checkbox form element template.

Template variables:
-------------------
- fieldName   (string)  The field name attribute value
- setChecked  (bool)  Optional, Flag to mark element as checked, feasible true or false
*}
<!-- partials/form/input_check.tpl -->
<div class="row checkbox multi">
{if $form}
    {assign var=field value=$form->get($fieldName)}
    {if $field->getLabel() neq NULL}{$this->formLabel($field)}{/if}
    {if isset($setChecked) && $setChecked == true}
        {$field=$field->setChecked(true)}
    {/if}
    {$this->formMultiCheckbox($field)}
    {include file="./validators.tpl" fieldName=$field->getName() validators=$form->getFieldValidators($fieldName)}
{else}
    missing form
{/if}
</div>
<!-- /partials/form/input_check.tpl -->