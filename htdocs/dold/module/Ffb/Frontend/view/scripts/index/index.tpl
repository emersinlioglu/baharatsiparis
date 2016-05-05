<!-- product/index.tpl -->
{$script=$this->headScript()->appendFile('js/frontend/IndexController.js')}
{*{$script = $this->headScript()->appendFile('js/frontend/form/ProductForm.js')}*}
{*{$script = $this->headScript()->appendFile('js/frontend/form/CategoryForm.js')}*}
{*{$script = $this->headScript()->appendFile('js/frontend/form/SortCategoriesForm.js')}*}

<form action="#" method="post" class="form-inline">

    <div class="row products">

        {$cnt = 0}
        {$cntPerColumn = 63}
        {$cntPerColumn = 85}
        {$chunks = array_chunk($groupedProductLangs, $cntPerColumn)}
        {$lastCategoryId = 0}

        {foreach item=chunk from=$chunks}
            <div class="col-md-4">
                <table class="table">

                    {foreach item=prod from=$chunk}

                        {if $lastCategoryId != $prod.categoryId || $prod@iteration == 1}
                            <tr class="{if $prod@iteration == 1}no-border{/if}">
                                <td colspan="5">
                                    <h4>{$prod.categoryName}</h4>
                                </td>
                            </tr>
                            {$lastCategoryId = $prod.categoryId}
                        {/if}

                            <tr>
                                <td>
                                    {if $prod.productImageUrl}
                                    <img
                                            title="<img src=&quot;{$prod.productImageUrl}&quot; alt=&quot;Bild&quot; style=&quot;width:200px;&quot;>"
                                            data-toggle="tooltip"
                                            data-html="true"
                                            data-placement="right"

                                            src="{$prod.productImageUrl}"
                                            alt="Bild"
                                            style="width:25px;">
                                    {/if}
                                </td>
                                <td>
                                    <span>
                                        {$prod.productName}
                                    </span>
                                </td>
                                <td>{$prod.productAmount}</td>
                                <td>{$prod.productPrice}</td>
                                <td>
                                    {*<input type="text" name="product[{$prod.productId}]">*}
                                    {*<div class="form-group">*}
                                        {*<label for="beispielFeldEmail1">Email-Adresse</label>*}
                                        <input type="email" class="form-control form-control-small" placeholder="">
                                    {*</div>*}
                                </td>
                            </tr>

                    {/foreach}
                </table>
            </div>
        {/foreach}

    </div>

</form>

{$script=$this->inlineScript()->appendScript('$(function() { IndexController.initIndex(); })')}

{literal}
    <script>
        IndexController.initIndex();
    </script>
{/literal}
<!-- /product/index.tpl -->
