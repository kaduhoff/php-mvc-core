<?php

namespace kadcore\tcphpmvc;

class View
{
    public function __construct(
        public string $title = ''
    )
    {
        # code...
    }

    public function renderView($view, $params = [])
    {
        $viewContent = $this->renderOnlyView($view, $params);
        $templatePadrao = $this->templateAtual();
        $conteudoPadrao = '{{bodyContent}}';
        $alertaPadrao = '{{alerts}}';

        //verifica se as tags obrigatórias constam no arquivo do template
        $layout = Application::$app->controller->templateAtual ?? Controller::defaultTemplate;
        if (!\str_contains($templatePadrao, $conteudoPadrao) || !\str_contains($templatePadrao, $alertaPadrao)) {
            echo "Erro fatal: O arquivo $layout não contem as strings obrigatórias $conteudoPadrao e $alertaPadrao" ;
            exit(500);
        }
        
        $htmlReturn = \str_replace(
            $conteudoPadrao, 
            $viewContent, 
            $templatePadrao
        );
        //se tem algum alerta, renderiza aqui abaixo
        $viewAlerts = $this->renderAlerts(); 
        $htmlReturn = \str_replace(
            $alertaPadrao, 
            $viewAlerts, 
            $htmlReturn
        );
        
        //renderiza outras views passada pelas chaves duplas
        do {
            preg_match('/{\{([a-zA-Z].*)\}\}/m', $htmlReturn, $matches);
            if ($matches) {
                //echo $matches[0].'.'.$matches[1];    
                $viewRendered = $this->renderOnlyView($matches[1], $params); 
                $htmlReturn = \str_replace(
                    $matches[0], 
                    $viewRendered, 
                    $htmlReturn
                );
            }
        } while ($matches);
        return $htmlReturn;
    }

    protected function templateAtual()
    {
        $layout = Application::$app->controller->templateAtual ?? Controller::defaultTemplate;
        \ob_start();
        include_once Application::$ROOT_DIR . "/templates/$layout";
        return \ob_get_clean();
    }

    protected function renderOnlyView($view, $params)
    {
        foreach ($params as $key => $value) {
            $$key = $value;
        }
        $fileViewPath = Application::$ROOT_DIR . "/views/$view.php";
        if (file_exists($fileViewPath)) {
            \ob_start();
            include_once Application::$ROOT_DIR . "/views/$view.php";
            return \ob_get_clean();
        }
        return 'View '.$view.'.php não encontrada';
    }

    public function renderAlerts() 
    {
        return Alerts::getFlashAlerts();
    }

}
