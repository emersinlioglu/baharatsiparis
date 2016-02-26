<!-- partials/lang_switcher.tpl -->

<div class="lang-switcher-wrapper">

    <label>{$this->translate('LBL_LANGUAGE')}:</label>

    <select class="lang-switcher">
        {foreach item=language from=$languages}
            <option
                value="{$language->getLanguageCode()}"
                {if $currentLanguage and $currentLanguage eq $language->getLanguageCode()}
                    selected="selected"
                {/if}
            >{$this->translate($language->getName())}</option>
        {/foreach}
    </select>
</div>

<!-- /partials/lang_switcher.tpl -->