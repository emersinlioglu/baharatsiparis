<!-- admin/userform.tpl -->

<!-- form-user -->
{$this->form()->openTag($form)}

<!-- columns -->
<div class="columns">

    <div class="column full centered">
        {*
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
        *}
        {include file='../partials/form/input_hidden.tpl'   fieldName='id'}
        {include file='../partials/form/input_text.tpl'   fieldName='name'}
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
            {$this->translate('TTL_SORT_ATTRIBUTES')}
        </div>
        <div class="accordion-content">
            {*{include file='../partials/form/sortable.tpl'}*}
            {if 0 < $formSortEntities->get('templateAttributeGroups')->count()}
                {$this->form()->openTag($formSortEntities)}
                <ul class="sortable">
                    {*{$formSortEntities->get('templateAttributeGroups')}*}
                    {foreach item=assignment from=$formSortEntities->get('templateAttributeGroups')}
                        <li class="">
                            {$attributeGroup = $assignment->getObject()->getAttributeGroup()}
                            {$attributeGroup->getCurrentTranslation()->getName()}

                            {include file='../partials/form/input_hidden.tpl' fieldName='id'   form=$assignment}
                            {include file='../partials/form/input_hidden.tpl' fieldName='sort' form=$assignment}
                        </li>
                    {/foreach}
                </ul>
                {$this->form()->closeTag()}

            {else}

                <div class="form-default">
                    {$this->translate('MSG_NO_ATTRIBUTEGROUPS_ARE_ASSIGNED')}
                </div>

            {/if}
        </div>
    </div>

{/if}

{if $categoryList}
    <div class="ffb-accordion">
        <div class="accordion-title">
            {$this->translate('TTL_CATEGORY_ASSIGNMENT')}
        </div>
        <div class="accordion-content">
            <ul class=category-list>
                {foreach from=$categoryList item=entry}
                    <li class="pane-navi-link-cnt">
                        {$entry.checkbox}
                        {*<a class="pane-navi-link attribute-group" title="Mikro" href="#"*}
                        {*data-pane-title="">*}
                        {$entry.title}
                        {*</a>*}
                        <ul>
                            {foreach from=$entry.subitems item=subentry}
                                <li class="pane-navi-link-cnt second-level">
                                    {$subentry.checkbox}
                                    {*<a class="pane-navi-link attribute-group" title="Mikro" href="#"*}
                                    {*data-pane-title="">*}
                                    {$subentry.title}
                                    {*</a>*}
                                    <ul>
                                        {foreach from=$subentry.subitems item=subsubentry}
                                            <li class="pane-navi-link-cnt third-level">
                                                {$subsubentry.checkbox}
                                                {*<a class="pane-navi-link attribute-group" title="Mikro" href="#"*}
                                                {*data-pane-title="">*}
                                                {$subsubentry.title}
                                                {*</a>*}
                                            </li>
                                        {/foreach}
                                    </ul>
                                </li>
                            {/foreach}
                        </ul>
                    </li>
                {/foreach}
            </ul>
        </div>
    </div>
{/if}

{literal}
    <script>
        (function (scope) {
            new scope.TemplateForm($('.form-template:not(.active)').first());
        })(window);
    </script>
{/literal}
<!-- /admin/userform.tpl -->