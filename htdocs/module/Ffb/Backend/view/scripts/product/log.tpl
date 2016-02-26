<div class="ffb-accordion log">
    <div class="accordion-title">
        <span class="title">Log</span>
        <span class="delete">&nbsp;</span>
    </div>
    <div class="accordion-content">
        <div class="log-filter">
            {$selectHtml}
        </div>

        <table class="table-default attribute-value-log">
            {if count($attributeValueLog) > 0}
                <tr>
                    <th>{$this->translate('TTL_AV_LOG_DATE')}:</th>
                    <th>{$this->translate('TTL_AV_LOG_LANGUAGE')}:</th>
                    <th>{$this->translate('TTL_AV_LOG_ATTRIBUTE_GROUP')}:</th>
                    <th>{$this->translate('TTL_AV_LOG_ATTRIBUTE_NAME')}:</th>
    {*                <th>{$this->translate('TTL_AV_LOG_ACTION')}:</th>*}
                    <th>{$this->translate('TTL_AV_LOG_OLD_VALUE')}:</th>
                    <th>{$this->translate('TTL_AV_LOG_NEW_VALUE')}:</th>
                    <th>{$this->translate('TTL_AV_LOG_USER')}:</th>
                </tr>
            {/if}

            {if count($attributeValueLog) > 0}
                {foreach item="avLog" from=$attributeValueLog}
                    <tr class="{*trans lang-{$avLog.langId}*}">
                        <td>{$this->htmlDateFormat($avLog.date, 'long')}</td>
                        <td>{$avLog.langIso}</td>
                        <td>{$avLog.attributeGroup}</td>
                        <td>{$avLog.attribute}</td>
{*                        <td>{$avLog.action}</td>*}
                        <td data-tooltip="{$avLog['oldValue']}">
                            {$avLog.oldValue|truncate:25}
                        </td>
                        <td data-tooltip="{$avLog['newValue']}">
                            {$avLog.newValue|truncate:25}
                        </td>
                        <td>{$avLog.user}</td>
                    </tr>
                {/foreach}
            {else}
                <tr class="no-result">
                    <td colspan="7">{$this->translate('MSG_AV_LOG_NO_RESULTS')}</td>
                </tr>
            {/if}

        </table>


        <div class="more button{if count($attributeValueLog) == 0} hide{/if}"></div>

    </div>
</div>