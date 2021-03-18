<?php

namespace kadcore\tcphpmvc\middlewares;

use kadcore\tcphpmvc\Application;
use Exception;
use kadcore\tcphpmvc\UserModel;

/** 
 * Middleware de autorizações de usuários
 * @author Kadu Hoffmann <kaduhoff@gmail.com>
 * @package kadcore\tcphpmvc\middlewares 
 * */
class UserAdminMiddleware extends BaseMiddleware
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
        if (!Application::$app->userLogged->status == UserModel::STATUS_ADMIN) {
            if (empty($this->actions) || in_array(Application::$app->controller->action, $this->actions)) {
                throw new \Exception("Você não tem permissão esse acesso", 403);
            }
        }
    }
}
