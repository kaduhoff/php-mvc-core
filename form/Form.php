<?php

namespace kadcore\tcphpmvc\form;


class Form
{
    private $formBegin;
    private $formContent = [];
    private $formEnd;

    public function __construct($action, $method, $options = '')
    {
        $this->formBegin = \sprintf('<form action="%s" method="%s" %s>', $action, $method, $options);
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
    
    public function addRow(array $itemsFormHtml, array $class = [])
    {   
        $rowClasses = $class['row'] ?? '';
        $colClasses = $class['col'] ?? '';
        $newRow = '<div class="row '.$rowClasses.'">';
        foreach ($itemsFormHtml as $key => $value) {
            $newRow .= '<div class="col '.$colClasses.'">';    
            $newRow .= $value;
            $newRow .= '</div>';
        }
        $newRow .= '</div>';
        $this->formContent[] = $newRow;
    }

}
