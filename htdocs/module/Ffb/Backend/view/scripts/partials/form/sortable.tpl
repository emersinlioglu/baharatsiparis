{if $formSortEntities}
    {$this->form()->openTag($formSortEntities)}
        <ul class="sortable">
            {foreach item=assignment from=$formSortEntities->get('attributeGroupAttributes')}

                <li class="">
                    {$attribute = $assignment->getObject()->getAttribute()}
                    {$attribute->getCurrentTranslation()->getName()}

                    {include file='../partials/form/input_hidden.tpl' fieldName='id'   form=$assignment}
                    {include file='../partials/form/input_hidden.tpl' fieldName='sort' form=$assignment}
                </li>
            {/foreach}
        </ul>
    {$this->form()->closeTag()}
{/if}
