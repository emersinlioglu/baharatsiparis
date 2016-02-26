<!-- partials/form/input_radio.tpl -->
{if $form}
    {assign var=field value=$form->get($fieldName)}
    <div class="row radio{if $field->getLabel() eq NULL} no-label{/if}">
        {if $field->getLabel() neq NULL}{$this->formLabel($field)}{/if}
        {$this->formRadio($field)}
        {include file="./validators.tpl" fieldName=$field->getName() validators=$form->getFieldValidators($fieldName)}
    </div>
{else}
    missing form
{/if}
<!-- /partials/form/input_radio.tpl -->