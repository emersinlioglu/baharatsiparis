<!-- partials/form/control_fileupload_view.tpl -->
<div class="row fileupload">
{if $form}
    {assign var=formLabel value=$this->plugin('formLabel')}
    {assign var=field value=$form->get($fieldName)}
    {$formLabel->openTag()}{$this->translate($field->getOption('label'))}{$formLabel->closeTag()}
    <span class="value"></span>
{else}
    missing form
{/if}
</div>
<!-- /partials/form/control_fileupload_view.tpl -->