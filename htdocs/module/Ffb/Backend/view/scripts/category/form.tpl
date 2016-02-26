<!-- category/form.tpl -->

<!-- form-category -->
{$this->form()->openTag($categoryForm)}

    <!-- columns -->
    <div class="columns">

        <div class="column full centered">
            {include file='../partials/form/select.tpl'         fieldName='parent' form=$categoryForm}
{*            {include file='../partials/form/select.tpl'         fieldName='template'}*}
            {foreach item=trans from=$categoryForm->get('translations')->getFieldsets()}

                <div class="trans lang-{$trans->get('lang')->getValue()}">
                    {include file='../partials/form/input_hidden.tpl' fieldName='lang' form=$trans}
                    {include file='../partials/form/input_text.tpl' fieldName='name' form=$trans}
                </div>
            {/foreach}
            {include file='../partials/form/input_hidden.tpl'   fieldName='id' form=$categoryForm}
        </div>

        <div class="column full buttons">
            {include file='../partials/form/input_submit.tpl'   fieldName='send' form=$categoryForm}
        </div>

    </div>
    <!-- /columns -->

{$this->form()->closeTag()}
<!-- /form-category -->

<!-- form-product -->
{if $isCategoryPersisted}
    {include file="../product/form.tpl"}
{/if}
<!-- /form-product -->

{literal}
    <script>
    (function(scope) {
        new scope.CategoryForm($('.form-category:not(.active)').first());
    })(window);
    </script>
{/literal}
<!-- /category/form.tpl -->