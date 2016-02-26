<!-- partials/form/input_submit.tpl -->
{if $form}
    {if $noRow eq null}<div class="row submit">{/if}
        {assign var=field value=$form->get($fieldName)}
        {$this->formInput($field)}
    {if $noRow eq null}</div>{/if}
{else}
    missing form
{/if}
<!-- /partials/form/input_submit.tpl -->