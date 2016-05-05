<!-- partials/form/view_check.tpl -->
<div class="row checkbox">
{if $form}
    {assign var=field value=$form->get($fieldName)}
    {if $field->getLabel() neq NULL}{$this->formLabel($field)}{/if}
    {if isset($setChecked) && $setChecked eq true}
        {$field=$field->setChecked(true)}
    {/if}
    {$field=$field->setAttribute('disabled', 'disabled')}
    {$this->formCheckbox($field)}
    {include file="./validators.tpl" fieldName=$field->getName() validators=$form->getFieldValidators($fieldName)}
{else}
    missing form
{/if}
</div>
<!-- /partials/form/view_check.tpl -->