<!-- partials/form/input_number.tpl -->
<div class="row number">
{if $form}
    {assign var=formLabel value=$this->plugin('formLabel')}
    {assign var=field value=$form->get($fieldName)}
    {$formLabel->openTag()}{$this->translate($field->getOption('label'))}{$formLabel->closeTag()}
    <span class="value {$class|escape:'htmlall'}">
        {$this->htmlNumberFormat($field->getValue(), $format, $form->getLocale())}
    </span>
{else}
    missing form
{/if}
</div>
<!-- /partials/form/input_number.tpl -->