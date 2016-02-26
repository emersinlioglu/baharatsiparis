<!-- partials/form/input_radio_view.tpl -->
<div class="row radio">
    {assign var=field value=$form->get($fieldName)}
    {if $field->getLabel() neq NULL}{$this->formLabel($field)}{/if}
    <span class="value">
        {foreach $field->getValueOptions() as $value => $label}
            {if $field->getValue() eq $value}
                {$this->translate($label)}
            {/if}
        {/foreach}
    </span>
</div>
<!-- /partials/form/input_radio_view.tpl -->