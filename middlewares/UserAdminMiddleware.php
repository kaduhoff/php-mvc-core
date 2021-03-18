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
        $pdo = Application::$app->db->pdo;
        $sql = 'SELECT COUNT(*) FROM users WHERE "status" = '.UserModel::STATUS_ADMIN;
        $statement = $pdo->prepare($sql);
        $statement->execute();
        $count = $statement->fetchColumn(0);
        //só entra se já tiver admin cadastrado
        if ($count > 0) {
            if (Application::$app->userLogged->status !== UserModel::STATUS_ADMIN) {
                if (empty($this->actions) || in_array(Application::$app->controller->action, $this->actions)) {
                    throw new \Exception("Erro de permissão acesso. Somente administradores podem acessar essa área.", 403);
                }
            }
        }
    }
}
