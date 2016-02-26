<!-- partials/form/control_fileupload_upload_area.tpl -->
<div class="row fileupload">
{if $form}
    {assign var=field value=$form->get($fieldName)}
    <div class="button gray file">
        {$this->translate('BTN_BROWSE')}
        {$this->formFile($field)}
    </div>
    <div class="drop-area"></div>
    <iframe name="extUploadFormIframe"></iframe>
{else}
    missing form
{/if}
</div>
<!-- /partials/form/control_fileupload_upload_area.tpl -->