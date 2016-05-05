<!-- upload/index.tpl -->
{$this->form()->openTag($form)}
    {include file='../partials/form/input_hidden.tpl' fieldName='token'}
    {include file='../partials/form/input_hidden.tpl' fieldName='referenceType'}
    {include file='../partials/form/input_hidden.tpl' fieldName='referenceId'}
    {include file='../partials/form/input_hidden.tpl' fieldName='uploadType'}
    {include file='../partials/form/input_hidden.tpl' fieldName='destination'}
    <div class="columns">
        <div class="column left">
            <div class="upload-info">
                <p>
                    {$this->translate($info)}
                </p>
            </div>
            <span class="please-select">
                {$this->translate('TTL_PLEASE_SELECT_FILE')}
            </span>
            {include file='../partials/form/input_text.tpl' fieldName='filepath'}
            {include file='../partials/form/control_fileupload_upload_area.tpl' fieldName='upload'}
        </div>
        <div class="column right">
            {*include file='../partials/form/textarea.tpl'   fieldName='description'*}
        </div>
        <div class="column full max-file-size hide">
            <div class="row">
                {$this->translate('MSG_INVALID_FILE_SIZE')}
            </div>
        </div>
        <div class="column full">
            <div class="files-list"></div>
        </div>
    </div>
{$this->form()->closeTag()}
<!-- /upload/index.tpl -->