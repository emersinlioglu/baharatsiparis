<!-- admin/userform.tpl -->

<!-- form-user -->
{$this->form()->openTag($form)}

    <!-- columns -->
    <div class="columns">

        <div class="column full centered">
            {foreach item=trans from=$form->get('translations')->getFieldsets()}

                <div class="trans lang-{$trans->get('lang')->getValue()}">
                    {include file='../partials/form/input_hidden.tpl'
                        fieldName='lang'    form=$trans}
                    {include file='../partials/form/input_text.tpl'
                        fieldName='name'    form=$trans}
                    {include file='../partials/form/input_text.tpl'
                        fieldName='title'   form=$trans}
                    {include file='../partials/form/input_text.tpl'
                        fieldName='alias'   form=$trans}
                    {include file='../partials/form/input_text.tpl'
                        fieldName='unit'    form=$trans}
                </div>
            {/foreach}

{*            <hr>*}
{*            <h2>{$this->translate('LBL_ATTRIBUTE_SETTINGS')}</h2>*}
            {include file='../partials/form/input_hidden.tpl'   fieldName='id'}
            {include file='../partials/form/select.tpl'         fieldName='type'}

            <div class="additional-fields attribute-type-10{if $form->get('type')->getValue() !== 10} hide{/if}">
                {include file='../partials/form/select.tpl'         fieldName='isMultiSelect'}
                {include file='../partials/form/input_text.tpl'     fieldName='optionValues'}
            </div>
            {*
            <div class="length">
                {include file='../partials/form/input_text.tpl'     fieldName='length'}
                <span>{$this->translate('LBL_CHARS')}</span>
            </div>
            *}
            {*include file='../partials/form/select.tpl'         fieldName='isUppercase'*}
        </div>

        <div class="column full buttons">
            {include file='../partials/form/input_submit.tpl'   fieldName='send'}
        </div>

    </div>
    <!-- /columns -->


{$this->form()->closeTag()}
<!-- /form-user -->

{literal}
    <script>
    (function(scope) {
        new scope.AttributeForm($('.form-attribute:not(.active)').first());
    })(window);
    </script>
{/literal}
<!-- /admin/userform.tpl -->