<?php

namespace kadcore\tcphpmvc\form;

class Button
{
    public function __construct(
        private string $caption = 'Enviar',
        private string $type = 'sbmit',
        private string $classButton = 'btn-primary'
    )
    {
        
    }

    public function __toString()
    {
        $itemHtml = "<button type=\"$this->type\" class=\"btn $this->classButton\">$this->caption</button>";
        return $itemHtml;
    }
}
