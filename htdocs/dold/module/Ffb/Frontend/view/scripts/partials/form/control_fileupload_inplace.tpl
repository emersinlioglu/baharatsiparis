<!-- partials/form/control_fileupload_inplace.tpl -->
<div class="row editable fileupload">
{if $form}
    {assign var=field value=$form->get($fieldName)}
    {if $field->getLabel() neq NULL}{$this->formLabel($field)}{/if}
    <span class="editable value"></span>
    <span class="editable edit hide">
        {$this->formHidden($field)}
        {include file="./validators.tpl" fieldName=$field->getName() validators=$form->getFieldValidators($fieldName)}
    </span>
{else}
    missing form
{/if}
</div>
<!-- /partials/form/control_fileupload_inplace.tpl -->
