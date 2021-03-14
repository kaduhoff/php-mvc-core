<?php

namespace app\core;

use app\core\Application;
use app\core\middlewares\BaseMiddleware;

/**
 * Controller padrão da aplicação
 * 
 * @author Kadu Hoffmann <kaduhoff@gmail.com>
 * @package app\controllers
 */
class Controller
{
    public const defaultTemplate = 'Simples/main.php';
    //public const defaultTemplate = 'Groovin/index.html';
    public string $templateAtual = self::defaultTemplate;

    /**
     * array de Middlewares dos controllers
     * @var app\core\middlewares\BaseMiddleware[]
     */
    protected array $middlewares = [];
    public string $action = '';
    
    /**
     * Muda o template do site, arquivos devem estar armazenados em /templates/
     *
     * @param string $template caminho relativo ao arquivo .php ex: 'Simples/main.php'
     * @return void
     */
    public function setTemplate(string $template)
    {
        $this->templateAtual = $template;
    }

    public function render($view, $params = [])
    {
        return Application::$app->view->renderView($view, $params);
    }

    public function registerMiddleware(BaseMiddleware $middleware)
    {
        $this->middlewares[] = $middleware;
    }

    /**
     * Get array de Middlewares dos controllers
     *
     * @return  app\core\middlewares\BaseMiddleware[]
     */ 
    public function getMiddlewares()
    {
        return $this->middlewares;
    }
}
