<!-- partials/form/input_number.tpl -->
{if $noRow eq null}<div class="row number">{/if}
{if $form}
    {assign var=field value=$form->get($fieldName)}
    {if $field->getLabel() neq NULL}{$this->formLabel($field)}{/if}
    {$this->formNumberFormatted($field, $format, $form->getLocale())}
    {include file="./validators.tpl" fieldName=$field->getName() validators=$form->getFieldValidators($fieldName)}
{else}
    missing form
{/if}
{if $noRow eq null}</div>{/if}
<!-- /partials/form/input_number.tpl -->