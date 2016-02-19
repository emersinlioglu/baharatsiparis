<!-- subnavi -->

<h2>{$this->translate('TTL_ATTRIBUTE_GROUP')}</h2>

<ul class="ffb-accordion navi attribute-groups">
{foreach from=$attributeGroups item=item}

    <li class="pane-navi-link-cnt">{$item}</li>

{/foreach}
</ul>
<!-- /subnavi -->