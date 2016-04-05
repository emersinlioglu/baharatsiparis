{assign var=field value=$attributeValueFs->get('value')}
{$fieldAttributes = $field->getAttributes()}
<div class="row files view{if !$showParent} hide{/if}" data-files='{$fieldAttributes['parentValue']}'>
    {if $field->getLabel() neq NULL}{$this->formLabel($field)}{/if}
</div>