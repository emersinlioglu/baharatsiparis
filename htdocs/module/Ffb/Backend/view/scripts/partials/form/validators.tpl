{if $validators|count gt 0}
<!-- partials/form/validators.tpl -->
    {if $form}
        {$isInvalid = false}
        {if $form->getAttribute('method') eq 'post'}
            {$invalidFields = $form->getInvalidFields()}
        {/if}

        {foreach from=$validators item=validator key=name}            

            {if $invalidFields neq null and $invalidFields.$fieldName neq null}
                {$isInvalid = true}
            {/if}

            {if $name eq 'Zend\Validator\NotEmpty' or $name eq 'not_empty' or $name eq 'NotEmpty'}
                <span class="validator{if $isInvalid eq true and $invalidFields.$fieldName['isEmpty'] neq NULL}{else} hide{/if}" data-validator="{$fieldName}, required;">
                    {$this->translate('VAL_REQUIRED')}
                </span>
            {/if}
            {if $name eq 'Zend\Validator\Regex' or $name eq 'Regex'}
                <span class="validator{if $isInvalid eq true}{else} hide{/if}" data-validator="{$fieldName}, regex, {$validator.pattern|escape};">
                    {$this->translate('VAL_REGEXP')}
                </span>
            {/if}
            {if $name eq 'Zend\Validator\StringLength' or $name eq 'StringLength' or $name eq 'string_length'}
                <span class="validator{if $isInvalid eq true}{else} hide{/if}" data-validator="{$fieldName}, length, {$validator.min}, {$validator.max};">
                    {$this->translate('VAL_LENGTH')}
                </span>
            {/if}
            {if $name eq 'Zend\Validator\EmailAddress' or $name eq 'EmailAddress'}
                <span class="validator{if $isInvalid eq true}{else} hide{/if}" data-validator="{$fieldName}, email;">
                    {$this->translate('VAL_EMAIL')}
                </span>
            {/if}
            {if $name eq 'Zend\I18n\Validator\Float' or $name eq 'Float'}
                <span class="validator{if $isInvalid eq true}{else} hide{/if}" data-validator="{$fieldName}, number;">
                    {$this->translate('VAL_NUMBER')}
                </span>
            {/if}
            {if $name eq 'Zend\I18n\Validator\Int' or $name eq 'Int'}
                <span class="validator{if $isInvalid eq true}{else} hide{/if}" data-validator="{$fieldName}, integer;">
                    {$this->translate('VAL_INTEGER')}
                </span>
            {/if}
            {if $name eq 'Zend\Validator\Date' or $name eq 'Date'}
                <span class="validator{if $isInvalid eq true and $invalidFields.$fieldName['dateInvalidDate'] neq NULL}{else} hide{/if}" data-validator="{$fieldName}, date, {$validator.locale};">
                    {$this->translate('VAL_DATE')}
                </span>
            {/if}
        {/foreach}
    {else}
        missing form
    {/if}
<!-- /partials/form/validators.tpl -->
{/if}
