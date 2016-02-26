<!-- admin/userform.tpl -->
{$script = $this->headScript()->appendFile('js/backend/ProductController.js')}
{$script = $this->headScript()->appendFile('js/backend/form/ProductForm.js')}
{$script = $this->headScript()->appendFile('js/backend/form/ProductGroupForm.js')}
<!-- form-user -->
{$this->form()->openTag($form)}

    <!-- columns -->
    <div class="columns">

        <div class="column full">
            <div class="row header">
                <span>Basisdaten:</span>
                <span class="active-lang">{$this->translate('LBL_LANGUAGE')}: <span class="language-code">de</span></span>
            </div>
        </div>

        <div class="row full category-breadcrumb">
            <ul>
                {foreach item="category" from=$categories}
                    <li>
                        {foreach item="trans" from=$category.translations key="langCode"}
                            <span class="tr lang-{$langCode}">
                                {if !$trans}
                                    <span class="no-trans">
                                        {if $item.link.masterTrans}
                                            {$item.link.masterTrans}
                                        {else}
                                            {$this->translate('LBL_NO_TRANSLATION')}
                                        {/if}
                                    </span>
                                {else}
                                    {$trans}
                                {/if}
                            </span>
                        {/foreach}
                    </li>
                {/foreach}
            </ul>
        </div>

        <div class="column full centered">

            {foreach item=trans from=$form->get('translations')->getFieldsets()}
                <div class="trans lang-{$trans->get('lang')->getValue()}">
                    {include file='../partials/form/input_hidden.tpl' fieldName='lang'        form=$trans}
                    {include file='../partials/form/input_text.tpl'   fieldName='name'        form=$trans}
                    {include file='../partials/form/input_text.tpl'   fieldName='description' form=$trans}
                    {include file='../partials/form/input_text.tpl'   fieldName='alias'       form=$trans}
                </div>
            {/foreach}

            {include file='../partials/form/select.tpl'       fieldName='isSystem' class="is-system"}
            {include file='../partials/form/select.tpl'       fieldName='parent' class="parent"}
            {include file='../partials/form/select.tpl'       fieldName='online' class="online"}
            {include file='../partials/form/input_hidden.tpl' fieldName='isRoot'}

            {include file='../partials/form/input_submit.tpl'   fieldName='send'}

        </div>

        <div class="column full details">
            <div class="row header">
                <h2>
                    {if $product}
                        {if $product->getParent()}
                        <i>(Produktvariante)</i>
                        {/if}
                        {$product->getCurrentTranslation()->getName()}
                        <span class="icon-edit"></span>
                        <span class="info">
                            | <span class="text">ID: {$product->getId()}</span>
                        </span>
                    {/if}
                </h2>
            </div>
        </div>

        <hr>

        {if $form->get('id')->getValue()}
        <div class="column full">
            {* attributeGroups *}
            {foreach from=$attributeGroups item=attributeGroup key=attributeGroupId}

                {*$inheritCheckboxList = array()*}

                {* lamella *}
                <div class="ffb-accordion lamella">
                    <div class="accordion-title">
                        <span class="title">{$attributeGroup.name}</span>
                        <span class="delete">&nbsp;</span>
                    </div>
                    <div class="accordion-content">

                        {* attributes *}
                        {foreach item=attribute from=$attributeGroup.attributes key=attributeId}

                            {* translations *}
                            {foreach item=productLangFs from=$form->get('translations')}

                                {* variables *}
                                {$productLangLangId     = $productLangFs->get('lang')->getValue()}

                                {* attributeValues *}
                                {foreach item=attributeValueFs from=$productLangFs->get('attributeValues')}

                                    {* fieldsets *}
                                    {*$productLangFs         = $productLangFs*}
                                    {*$attributeValueFs      = $attributeValueFs*}
                                    {$attributeGroupFs      = $attributeValueFs->get('attributeGroup')}
                                    {$attributeLangFs       = $attributeValueFs->get('attributeLang')}
                                    {$attributeFs           = $attributeLangFs->get('translationTarget')}

                                    {* variables *}
                                    {$formAttributeId       = $attributeFs->get('id')->getValue()}
                                    {$formAttributeGroupId  = $attributeGroupFs->get('id')->getValue()}
                                    {$attributeLangLangId   = $attributeLangFs->get('lang')->getValue()}
                                    {*$productLangId         = $productLangFs->get('id')->getValue()*}
                                    {*$attributeLangId       = $attributeLangFs->get('id')->getValue()*}

                                    {* show attribute *}
                                    {if $formAttributeId eq $attributeId
                                        and $formAttributeGroupId eq $attributeGroupId
                                        and $productLangLangId eq $attributeLangLangId
                                    }

                                        <div class="trans lang-{$productLangFs->get('lang')->getValue()}">

                                            {* get attribute type *}
                                            {$attributeType = $attributeFs->get('type')->getValue()}

                                            {* fields *}
                                            {$field    = $attributeValueFs->get('value')}
                                            {$fieldMin = $attributeValueFs->get('valueMin')}
                                            {$fieldMax = $attributeValueFs->get('valueMax')}

                                            {include file='../partials/form/input_hidden.tpl' fieldName='id'         form=$attributeValueFs}
                                            {include file='../partials/form/input_check.tpl' fieldName='isInherited' form=$attributeValueFs class='is-inherited'}

                                            {if $attributeType == 6 or $attributeType == 7}
                                                {* TYPE_RANGE_INT   = 6; *}
                                                {* TYPE_RANGE_FLOAT = 7; *}
                                                <div class="range-values">
                                                    {*{$attributeType} :*}
                                                    {$this->formAttributeValue($fieldMin)}
                                                    {$this->formAttributeValue($fieldMax)}
                                                </div>
                                            {else}

                                                {* default *}
                                                {$this->formAttributeValue($field)}

                                            {/if}

                                            {if $attributeType == 8 or $attributeType == 9}
                                                {* TYPE_IMAGE    = 8; *}
                                                {* TYPE_DOCUMENT = 9; *}
                                                {include file='../partials/form/input_hidden.tpl' fieldName='referenceType' form=$attributeValueFs}
                                            {/if}

                                        </div>
                                    {/if}

                                {/foreach}
                                {* /attributeValues *}

                            {/foreach}
                            {* /translations *}

                        {/foreach}
                        {* /attributes *}

                    </div>
                </div>
                {* /lamella *}

            {/foreach}
            {* /attributeGroups *}

        </div>
        {/if}

    </div>
    <!-- /columns -->

    {if $product && $product->getId()}
        <!-- tabs -->
        <div class="tabs">
            {if $product->getIsSystem()}
                <div class="tab active" data-content="linked-products">
                    <span>{$this->translate('TTL_PRODUCT_LINKED_PRODUCTS')}</span>
                </div>
            {/if}
            <div class="tab{if !$product->getIsSystem()} active{/if}" data-content="accessory-products">
                <span>{$this->translate('TTL_PRODUCT_ACCESSORIES')}</span>
            </div>
            <div class="tab" data-content="multiple-usage">
                <span>{$this->translate('TTL_PRODUCT_MULTIPLE_USAGE')}</span>
            </div>
        </div>
        {if $product->getIsSystem()}
            <div class="tab-content assigned-products linked-products" data-content="linked-products"
                 data-search-url="{$dataSearchProductUrl}"
                 data-add-url="{$dataAddLinkedProductUrl}">
                <div class="row text controlls">
                    <input class="search-product">
                </div>
                <div class="results">
                    {$linkedProductList}
                </div>
            </div>
        {/if}
        <div class="tab-content assigned-products accessory-products" data-content="accessory-products"
            data-search-url="{$dataSearchProductUrl}"
            data-add-url="{$dataAddAccessoryProductUrl}">
            <div class="row text controlls">
               <input class="search-product">
            </div>
            <div class="results">
               {$accessoryProductsList}
            </div>
        </div>
        <div class="tab-content multiple-usage" data-content="multiple-usage">
            <ul class=category-list>
                {foreach from=$multipleUsageCategoryTree item=entry}
                    <li class="pane-navi-link-cnt">
                        {*{$entry.checkbox}*}
                        {*<a class="pane-navi-link attribute-group" title="Mikro" href="#"*}
                           {*data-pane-title="">*}
                            {$entry.title}
                        {*</a>*}
                        <ul>
                            {foreach from=$entry.subitems item=subentry}
                                <li class="pane-navi-link-cnt second-level">
                                    {*{$subentry.checkbox}*}
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
        <!-- /tabs -->

        {* Log AttributeValues *}
        {if $showLog}
            {include file='./log.tpl'}
        {/if}
    {/if}

{$this->form()->closeTag()}
<!-- /form-user -->

{literal}
    <script>
    (function(scope) {
        new scope.ProductForm($('.form-product:not(.active)').first());
    })(window);
    </script>
{/literal}
<!-- /admin/userform.tpl -->