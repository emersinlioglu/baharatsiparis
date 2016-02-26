<!-- partials/form/input_button.tpl -->
{if $form}
    {if $noRow eq null}<div class="row input-button">{/if}
        {assign var=field value=$form->get($fieldName)}
        {$this->formInput($field)}
    {if $noRow eq null}</div>{/if}
{else}
    missing form
{/if}
<!-- /partials/form/input_button.tpl -->