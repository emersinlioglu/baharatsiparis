{*
Checkbox form element with a switch template.

Template variables:
-------------------
- fieldName   (string)  The field name attribute value
- setChecked  (bool)  Optional, Flag to mark element as checked, feasible true or false
*}
<!-- partials/form/input_check.tpl -->
<div class="row switch">
{if $form}
    {assign var=field value=$form->get($fieldName)}
    {if $field->getLabel() neq NULL}{$this->formLabel($field)}{/if}
    {if isset($setChecked) && $setChecked eq true}
        {$field=$field->setChecked(true)}
    {/if}
    <div class="hide">
        {$this->formCheckbox($field)}
    </div>

    <div class="options {$field->getAttribute('class')}">
        <span class="checked{if $field->isChecked()} active{/if}">
            {$this->translate($field->getAttribute('data-option-checked'))}
        </span>
        <span class="not-checked{if !$field->isChecked()} active{/if}">
            {$this->translate($field->getAttribute('data-option-unchecked'))}
        </span>
    </div>
    {include file="./validators.tpl" fieldName=$field->getName() validators=$form->getFieldValidators($fieldName)}
{else}
    missing form
{/if}
</div>
<!-- /partials/form/input_check.tpl -->