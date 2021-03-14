<?php

namespace app\core\middlewares;

use app\core\Controller;
use app\core\Application;
use app\core\exceptions\ForbiddenException;
use Exception;

/** 
 * Middleware de autorizações de usuários
 * @author Kadu Hoffmann <kaduhoff@gmail.com>
 * @package app\core\middlewares 
 * */
class AuthMiddleware extends BaseMiddleware
{
    public function __construct(
        public array $actions = []
        )
    {
    }

    /**
     * regras do meu middleware para usuarios autenticados
     * @return void 
     * @throws Exception 403 - Forbidden (sem permissão)
     */
    public function execute()
    {
        if (Application::$app->userLogged->isGuest()) {
            if (empty($this->actions) || in_array(Application::$app->controller->action, $this->actions)) {
                throw new \Exception("Você não tem permissão esse acesso", 403);
            }
        }
    }
}
