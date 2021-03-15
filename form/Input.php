<?php

namespace kadcore\tcphpmvc\form;

class Input
{
    public function __construct(
        private string $title, 
        private string $type, 
        private string $name, 
        private string $value = '',
        private string $placeholder = '', 
        private string $help = '', 
        private bool $required = false, 
        private bool $disabled = false,
        private string $error = '',
        private string $fieldOptions = '',
        )
    {
        
    }

    public function __toString()
    {
        $stringHtml = '<div class="mb-3">';
        $stringHtml .= "<label for=\"$this->name\" class=\"form-label\">$this->title</label>";

        $this->fieldOptions .= empty($this->placeholder) ? '' : ' placeholder="'.$this->placeholder.'"';
        $this->fieldOptions .= empty($this->help) ? '' : ' aria-describedby="'.$this->name.'Help"';
        $this->fieldOptions .= (!$this->required) ? '' : ' required';
        $this->fieldOptions .= (!$this->disabled) ? '' : ' disabled';

        $invalid = empty($this->error) ? '' : ' is-invalid';

        $stringHtml .= "<input type=\"$this->type\" name=\"$this->name\" class=\"form-control $invalid\" id=\"$this->name\" value=\"$this->value\" $this->fieldOptions>";

        $stringHtml .= empty($this->help) ? '' : \sprintf(
            '<div id="%sHelp" class="form-text">%s</div>',
            $this->name,
            $this->help
        );

        $stringHtml .= empty($this->error) ? '' : \sprintf(
            '<div class="invalid-feedback">%s</div>',
            $this->error
        );
        $stringHtml .= '</div>';

        return $stringHtml;
    }
}
