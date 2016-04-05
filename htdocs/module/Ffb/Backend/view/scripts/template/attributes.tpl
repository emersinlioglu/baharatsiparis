<div class="template-attribute-groups">
    <h2>{$this->translate('TTL_ASSIGNED_ATTRIBUTES')}</h2>

    <ul class="default-list assigned-attributes">
        {if count($attributeGroupAttributes) > 0}
            {foreach from=$attributeGroupAttributes item=attributeGroupAttribute}
                <li>
                    {assign var=attribute value=$attributeGroupAttribute->getAttribute()}
                    {$attribute->getCurrentTranslation()->getName()}
                </li>
            {/foreach}
        {else}
            <li>
                {$this->translate('MSG_NO_ASSIGNED_ATTRIBUTE_GROUP')}
            </li>
        {/if}
    </ul>
</div>