<!-- partials/form/control_fileupload.tpl -->
<div class="row fileupload{if $showParent} hide{/if}">
{if $form}
    {assign var=field value=$form->get($fieldName)}
    {if $field->getLabel() neq NULL}{$this->formLabel($field)}{/if}
    {$this->formHidden($field)}
{else}
    missing form
{/if}
</div>
<!-- /partials/form/control_fileupload.tpl -->