<!-- category/sort.tpl -->

<!-- form-sort-categories -->
<form action="{$formAction}" method="post" class="form-default form-sort-entities form-sort-categories">

    <h2>{$this->translate('TTL_SORT_CATEGORIES')}</h2>

    <div class="row submit">
        <input type="submit" name="send" class="button gray" value="{$this->translate('BTN_SAVE')}">
    </div>

    {foreach item="categoryLangSortTree" from=$categorySortTree key='iso'}
        <p>
            {$iso}
        </p>
    <ul class="sortable category-list">
        {foreach item="entry" from=$categoryLangSortTree}
            <li id="category_{$entry.id}" class="first-level hasItems">

                {*{$entry.hiddenInput}*}
                {$entry.name}
                <input name="category[{$entry.id}]" value="{$entry.sort}" type="hidden">

                {*{if $entry.hasSubitems}*}
                    {*<ul class="sortable">*}
                        {*{foreach item="subentry" from=$entry.subitems}*}
                            {*<li id="category_{$subentry.id}" class="second-level{if $subentry.hasSubitems} hasItems{/if}">*}

                                {*{$subentry.hiddenInput}*}
                                {*{$subentry.title}*}

                                {*{if $subentry.hasSubitems}*}
                                    {*<ul class="sortable">*}
                                        {*{foreach from=$subentry.subitems item=subsubentry}*}
                                            {*<li id="category_{$subsubentry.id}" class="third-level">*}

                                                {*{$subsubentry.hiddenInput}*}
                                                {*{$subsubentry.title}*}

                                            {*</li>*}
                                        {*{/foreach}*}
                                    {*</ul>*}
                                {*{/if}*}
                            {*</li>*}
                        {*{/foreach}*}
                    {*</ul>*}
                {*{/if}*}
            </li>
        {/foreach}
    </ul>
    {/foreach}

</form>
<!-- /form-sort-categories -->

{literal}
    <script>
        (function(scope) {
            new scope.SortCategoriesForm($('.form-sort-categories:not(.active)').first());
        })(window);
    </script>
{/literal}
<!-- /category/sort.tpl -->