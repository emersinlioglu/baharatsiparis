<!-- partials/form/view_date.tpl -->
<div class="row text {$class|escape:'htmlall'}">
{if $form}
    {assign var=formLabel value=$this->plugin('formLabel')}
    {assign var=field value=$form->get($fieldName)}
    {$formLabel->openTag()}{$this->translate($field->getOption('label'))}{$formLabel->closeTag()}
    <span class="value {$class|escape:'htmlall'}">
        {$this->htmlDateFormat($field->getValue(), $format)}
    </span>
{else}
    missing form
{/if}
</div>
<!-- /partials/form/view_date.tpl -->