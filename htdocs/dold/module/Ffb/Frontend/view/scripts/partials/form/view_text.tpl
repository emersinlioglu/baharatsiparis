<!-- partials/form/view_text.tpl -->
<div class="row text view{if !$showParent} hide{/if}">
{if $form}
    {assign var=field value=$form->get($fieldName)}
    {if $field->getLabel() neq NULL}{$this->formLabel($field)}{/if}
    <span class="value">
        {$fieldAttributes = $field->getAttributes()}
        {$fieldAttributes['data-parent']}
    </span>
{else}
    missing form
{/if}
</div>
<!-- /partials/form/view_text.tpl -->