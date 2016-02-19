<!-- admin/userform.tpl -->

<!-- form-user -->
{$this->form()->openTag($form)}

    <!-- columns -->
    <div class="columns">

        <div class="column full centered">
            {foreach item=trans from=$form->get('translations')->getFieldsets()}

                <div class="trans lang-{$trans->get('lang')->getValue()}">
                    {include file='../partials/form/input_hidden.tpl' fieldName='lang' form=$trans}
                    {include file='../partials/form/input_text.tpl' fieldName='name' form=$trans}
                    {include file='../partials/form/input_text.tpl' fieldName='title' form=$trans}
                    {include file='../partials/form/input_text.tpl' fieldName='alias' form=$trans}
                </div>
            {/foreach}
            {include file='../partials/form/input_hidden.tpl'   fieldName='id'}
{*            {include file='../partials/form/select.tpl'         fieldName='type'}*}
        </div>

        <div class="column full buttons">
            {include file='../partials/form/input_submit.tpl'   fieldName='send'}
        </div>

    </div>
    <!-- /columns -->

{$this->form()->closeTag()}
<!-- /form-user -->
{if $formSortEntities}
  <div class="ffb-accordion">
    <div class="accordion-title">
      {$this->translate('TTL_SORT_ATTRIBUTE_GROUPS')}
    </div>
    <div class="accordion-content">

      {include file='../partials/form/sortable.tpl'}

    </div>
  </div>
{/if}
{literal}
    <script>
    (function(scope) {
        new scope.AttributeGroupForm($('.form-attribute-group:not(.active)').first());
    })(window);
    </script>
{/literal}
<!-- /admin/userform.tpl -->