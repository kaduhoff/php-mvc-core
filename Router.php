<?php

namespace kadcore\tcphpmvc;

use kadcore\tcphpmvc\Controller;
use Exception;
use kadcore\tcphpmvc\events\EventTypes;

/**
 * Router para os caminhos da url
 * 
 * @author Kaduhoff <kadu@gmail.com>
 * @package kadcore\tcphpmvc
 */
class Router
{
    public Request $request;
    public Response $response;
    protected array $routes = [];

    public function __construct(Request $request, Response $response)
    {
        $this->request = $request;
        $this->response = $response;
    }

    /**
     * Atribui uma função ou uma view no método get ao app que posteriormente poderá ser chamado por resolv() ou diretamente
     *
     * @param $path caminho que for digitado na URI
     * @param $callback se sitring ativa view "$path".php, se for função executa a função
     * @return void
     */
    public function get($path, $callback)
    {
        $this->routes['get'][$path] = $callback;
    }

    public function post($path, $callback)
    {
        $this->routes['post'][$path] = $callback;
    }

    public function resolve()
    {
        Application::$app->eventListeners->triggerEvent(EventTypes::EVENT_BEFORE_REQUEST);

        $path = $this->request->getPath();
        $method =  $this->request->method();
        $callback = $this->routes[$method][$path] ?? false;

        Application::$app->eventListeners->triggerEvent(EventTypes::EVENT_AFTER_REQUEST);

        if ($callback === false) {
            throw new Exception("Página $path não encontrada", 404);
        }
        if (\is_string($callback)) {
            //se for passada uma string, chama a view com o nome da string
            //exemplo: routes['get']['home'] => 'pagina'
            //        abre '/views/pagina.php'
            return Application::$app->view->renderView($callback);
        }
        if (\is_array($callback)) {
            //se for passada uma array no callback, o 
            //primeiro elemento deve ser uma classe e o segundo o nome do metodo
            //exemplo: routes['get']['home'] =  
            //    [SiteController::class, home]
            //      isso instancia a classe SiteControler
            //      e chama o metodo 'home()'
            
            /**
             * @var Controller $controller
             */
            $controller = new $callback[0]();
            Application::$app->controller = $controller;
            $controller->action = $callback[1];
            foreach ($controller->getMiddlewares() as $middleware) {
                $middleware->execute();
            }
            $callback[0] = $controller;
        }
        return \call_user_func($callback, $this->request, $this->response);
    }

    

}
