<!-- partials/form/input_hidden.tpl -->
{if $form}
    {$field=$form->get($fieldName)}
    {$this->formHidden($field)}
{*    <p>{$field->getName()} : {$field->getValue()}</p>*}
    {include file="./validators.tpl" fieldName=$field->getName() validators=$form->getFieldValidators($fieldName)}
{else}
    missing form
{/if}
<!-- /partials/form/input_hidden.tpl -->