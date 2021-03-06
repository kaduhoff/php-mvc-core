<?php

namespace kadcore\tcphpmvc;

use kadcore\tcphpmvc\UserModel;
use kadcore\tcphpmvc\db\Database;
use kadcore\tcphpmvc\events\EventListener;
use kadcore\tcphpmvc\events\EventTypes;

/**
 * Class Application
 * 
 * @author Kadu <kaduhoff@gmail.com>
 * @package kadcore\tcphpmvc
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
    public EventListener $eventListeners;

    public function __construct($rootPath, array $config)
    {
        self::$ROOT_DIR = $rootPath;
        self::$app = $this;
        $this->db = new Database($config['db']);
        $this->request = new Request();
        $this->response = new Response();
        $this->session = new Session();
        $this->userLogged =  self::getLoggedUser();
        $this->eventListeners = new EventListener();
        //echo $this->userLogged->id;
        $this->router = new Router($this->request, $this->response);
        $this->view = new View();

        

    }


    public function setLoginUser(UserModel $user)
    {
        $this->userLogged = $user;
        $this->session->setUserLogged($user->id);
    }

    public static function getLoggedUser() 
    {
        $userId = Application::$app->session->getUserLogged();
        $user = new UserModel();
        if ($userId) {
            $user->getByKey($userId);
        }
        return $user;
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
            $errCode = \is_numeric($th->getCode()) ? $th->getCode() : 1;
            $this->response->setStatusCode($errCode);
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
