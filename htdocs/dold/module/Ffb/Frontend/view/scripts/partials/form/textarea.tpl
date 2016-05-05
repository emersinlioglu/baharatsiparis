<!-- partials/form/textarea.tpl -->
<div class="row textarea{if $showParent} hide{/if}">
{if $form}
    {if $form->has($fieldName)}
        {assign var=field value=$form->get($fieldName)}
        {if $field->getLabel() neq NULL}{$this->formLabel($field)}{/if}
        {$this->formTextarea($field)}
        {include file="./validators.tpl" fieldName=$field->getName() validators=$form->getFieldValidators($fieldName)}
    {else}
        Cannot find {$fieldName}!
    {/if}
{else}
    missing form
{/if}
</div>
<!-- /partials/form/textarea.tpl -->