<!-- admin/userform.tpl -->

<!-- form-user -->
{$this->form()->openTag($form)}

    <!-- columns -->
    <div class="columns">

        <div class="column full centered">
            {include file='../partials/form/select.tpl'         fieldName='parent'}
            {foreach item=trans from=$form->get('translations')->getFieldsets()}

                <div class="trans lang-{$trans->get('lang')->getValue()}">
                    {include file='../partials/form/input_hidden.tpl' fieldName='lang' form=$trans}
                    {include file='../partials/form/input_text.tpl' fieldName='name' form=$trans}
                </div>
            {/foreach}
            {include file='../partials/form/input_hidden.tpl'   fieldName='id'}
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
        new scope.ProductGroupForm($('.form-product-group:not(.active)').first());
    })(window);
    </script>
{/literal}
<!-- /admin/userform.tpl -->