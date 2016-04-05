{$script=$this->inlineScript()->prependScript('var myApp = new App(\''|cat:$this->locale|cat:'\');')}

{if $this->JSTranslations|strlen gt 0}
    {$script=$this->inlineScript()->prependScript('ffbTranslator.init('|cat:$this->JSTranslations|cat:');')}
{/if}

{$this->inlineScript()}