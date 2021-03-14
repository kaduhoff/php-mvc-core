<?php

namespace kadcore\tcphpmvc\form;


class Form
{
    private $formBegin;
    private $formContent = [];
    private $formEnd;
    private $model;

    public function __construct($model, $action, $method)
    {
        $this->model = $model;
        $this->formBegin = \sprintf('<form action="%s" method="%s" >', $action, $method);
        $this->formEnd = "</form>";
      
    }
    
    public function __toString()
    {
        $stringReturn = $this->formBegin;
        foreach ($this->formContent as $key => $content) {
            $stringReturn .= $content;    
        }
        $stringReturn .= $this->formEnd;
        return $stringReturn;
    }

    public function printForm()
    {
        echo $this;
    }

    public function add(string $itemFormHtml)
    {
        $this->formContent[] = $itemFormHtml;
    }
    
    public function addRow(array $itemsFormHtml)
    {
        $newRow = '<div class="row">';
        foreach ($itemsFormHtml as $key => $value) {
            $newRow .= '<div class="col">';    
            $newRow .= $value;
            $newRow .= '</div>';
        }
        $newRow .= '</div>';
        $this->formContent[] = $newRow;
    }

}
