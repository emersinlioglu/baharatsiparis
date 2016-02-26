<!-- partials/form/select_view.tpl -->
<div class="row select">
    {assign var=formLabel value=$this->plugin('formLabel')}
    {assign var=field value=$form->get($fieldName)}
    {$formLabel->openTag()}{$this->translate($field->getOption('label'))}{$formLabel->closeTag()}
    <span class="value">
        {foreach item=option from=$field->getValueOptions() key=i}
            {if $i eq $field->getValue()}
                {$this->translate($option)}
            {/if}
        {/foreach}
    </span>
</div>
<!-- /partials/form/select_view.tpl -->