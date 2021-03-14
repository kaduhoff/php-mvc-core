<?php

namespace app\core;

use app\controllers\AuthController;
use app\core\Controller;
use app\core\UserModel;
use app\core\db\Database;

/**
 * Class Application
 * 
 * @author Kadu <kaduhoff@gmail.com>
 * @package app\core
 * 
 */
class Application
{
    public static string $ROOT_DIR;
    public Router $router;
    public Request $request;
    public Response $response;
    public Session $session;
    public Database $db;
    public static Application $app;
    public Controller $controller;
    public UserModel $userLogged;
    public View $view;

    public function __construct($rootPath, array $config)
    {
        self::$ROOT_DIR = $rootPath;
        self::$app = $this;
        $this->db = new Database($config['db']);
        $this->request = new Request();
        $this->response = new Response();
        $this->session = new Session();
        $this->userLogged =  AuthController::getLoggedUser();
        //echo $this->userLogged->id;
        $this->router = new Router($this->request, $this->response);
        $this->view = new View();

        

    }

    public function setLoginUser(UserModel $user)
    {
        $this->userLogged = $user;
        $this->session->setUserLogged($user->id);
    }

    public function logoutUser()
    {
        $this->userLogged = new UserModel();
        $this->session->unsetUser();
    }

    public function run()
    {
        try {
            echo $this->router->resolve();
        } catch (\Throwable $th) {
            $this->response->setStatusCode($th->getCode());
            echo $this->view->renderView('_error', [
                'exception' => $th
            ]);
        }
    }

    /**
     * Get the value of controller
     */ 
    public function getController()
    {
        return $this->controller;
    }

    /**
     * Set the value of controller
     */ 
    public function setController($controller)
    {
        $this->controller = $controller;
    }
}