<?php

namespace app\core;

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

        $htmlReturn = \str_replace(
            '{{bodyContent}}', 
            $viewContent, 
            $templatePadrao
        );
        //se tem algum alerta, renderiza aqui abaixo
        $viewAlerts = $this->renderAlerts(); 
        $htmlReturn = \str_replace(
            '{{alerts}}', 
            $viewAlerts, 
            $htmlReturn
        );
        //renderiza menu Login ou Usuário
        $menuLogin = $this->renderOnlyView("menuLogin", $params);; 
        $htmlReturn = \str_replace(
            '{{menuLogin}}', 
            $menuLogin, 
            $htmlReturn
        );
        return $htmlReturn;
    }

    public function renderContent($viewContent)
    {
        $templatePadrao = $this->templateAtual();
        return \str_replace('{{bodyContent}}', $viewContent, $templatePadrao);
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
        \ob_start();
        include_once Application::$ROOT_DIR . "/views/$view.php";
        return \ob_get_clean();
    }

    public function renderAlerts() 
    {
        return Alerts::getFlashAlerts();
    }

}
