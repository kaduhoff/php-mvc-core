<?php

namespace kadcore\tcphpmvc\form;

class Select
{
    public function __construct(
        private string $title, 
        private string $name, 
        private array $values = [],
        private string $placeholder = '', 
        private string $help = '', 
        private bool $required = false, 
        private bool $disabled = false,
        private string $error = '',
        private bool|int $size = false, 
        private string $selectedValue = '', 
        private string $fieldOptions = '',
        )
    {
        
    }
/*
<select class="form-select" size="3" multiple aria-label="size 3 select example">
  <option selected>Open this select menu</option>
  <option value="1">One</option>
  <option value="2">Two</option>
  <option value="3">Three</option>
</select>
*/
    public function __toString()
    {
        $stringHtml = "<label for=\"$this->name\" class=\"form-label\">$this->title</label>";

        $this->fieldOptions .= empty($this->placeholder) ? '' : ' placeholder="'.$this->placeholder.'"';
        $this->fieldOptions .= empty($this->help) ? '' : ' aria-describedby="'.$this->name.'Help"';
        $this->fieldOptions .= (!$this->required) ? '' : ' required';
        $this->fieldOptions .= (!$this->disabled) ? '' : ' disabled';
        $this->fieldOptions .= (!$this->size) ? '' : ' size="'.$this->size.'"';

        $invalid = empty($this->error) ? '' : ' is-invalid';

        $stringHtml .= "<select name=\"$this->name\" class=\"form-control $invalid\" id=\"$this->name\" $this->fieldOptions>";
        foreach ($this->values as $key => $value) {
            $selected = ($this->selectedValue == $value) ? 'selected' : '';
            $stringHtml .= "<option value=\"$value\" $selected>$key</option>";
        }
        $stringHtml .= "</select>";

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
