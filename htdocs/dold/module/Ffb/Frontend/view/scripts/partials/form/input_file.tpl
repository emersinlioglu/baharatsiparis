<!-- partials/form/input_file.tpl -->
<div class="row file">
{if $form}
    {assign var=field value=$form->get($fieldName)}
    <div class="button gray file">
        {$this->translate('BTN_BROWSE')}
        {$this->formFile($field)}
    </div>
{else}
    missing form
{/if}
</div>
<!-- /partials/form/input_file.tpl -->