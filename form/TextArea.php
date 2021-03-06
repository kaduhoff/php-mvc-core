<?php

namespace kadcore\tcphpmvc\form;

class TextArea
{
    public function __construct(
        private string $title, 
        private string $name, 
        private string $value = '',
        private string $placeholder = '', 
        private string $help = '', 
        private bool $required = false, 
        private bool $disabled = false,
        private string $error = '',
        private int $rows = 4,
        )
    {
        
    }

    public function __toString()
    {
        $stringHtml = "<label for=\"$this->name\" class=\"form-label\">$this->title</label>";

        $fieldOptions = empty($this->placeholder) ? '' : ' placeholder="'.$this->placeholder.'"';
        $fieldOptions .= empty($this->help) ? '' : ' aria-describedby="'.$this->name.'Help"';
        $fieldOptions .= (!$this->required) ? '' : ' required';
        $fieldOptions .= (!$this->disabled) ? '' : ' disabled';
        $fieldOptions .= ' rows = "'.$this->rows.'"';

        $invalid = empty($this->error) ? '' : ' is-invalid';

        $stringHtml .= "<textarea class=\"form-control $invalid\"  id=\"$this->name\" name=\"$this->name\" $fieldOptions>$this->value</textarea>";

        $stringHtml .= empty($this->help) ? '' : \sprintf(
            '<div id="%sHelp" class="form-text">%s</div>',
            $this->name,
            $this->help
        );

        $stringHtml .= empty($this->error) ? '' : \sprintf(
            '<div class="invalid-feedback">%s</div>',
            $this->error
        );

        return $stringHtml;
    }
}


