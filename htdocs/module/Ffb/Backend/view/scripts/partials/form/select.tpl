<!-- partials/form/select.tpl -->
{if $noRow eq null}<div class="row select {$class}">{/if}
{if $form}
    {assign var=field value=$form->get($fieldName)}
    {if $field->getLabel() neq NULL}{$this->formLabel($field)}{/if}
    {$this->formSelect($field)}
    {if $field->getAttribute('multiple') eq 'multiple'}
        {$fN = $field->getName()|cat:'[]'}
    {else}
        {$fN = $field->getName()}
    {/if}
    {include file="./validators.tpl" fieldName=$fN validators=$form->getFieldValidators($fieldName)}
{else}
    missing form
{/if}
{if $noRow eq null}</div>{/if}
<!-- /partials/form/select.tpl -->