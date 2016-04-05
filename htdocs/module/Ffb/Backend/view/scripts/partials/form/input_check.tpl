{*
Checkbox form element template.

Template variables:
-------------------
- fieldName   (string)  The field name attribute value
- setChecked  (bool)  Optional, Flag to mark element as checked, feasible true or false
*}
<!-- partials/form/input_check.tpl -->
<div class="row checkbox {$class}">
{if $form}
    {assign var=field value=$form->get($fieldName)}
    {if $field->getLabel() neq NULL}
        {$renderresult = $field->setAttribute('id', $this->formLabel()->getId($field))}
        {$this->formLabel($field)}
    {/if}
    {if $setChecked and $setChecked eq true}
        {$field=$field->setChecked(true)}
    {/if}
    {$this->formCheckbox($field)}
    {include file="./validators.tpl" fieldName=$fieldName validators=$form->getFieldValidators($fieldName)}
{else}
    missing form
{/if}
</div>
<!-- /partials/form/input_check.tpl -->