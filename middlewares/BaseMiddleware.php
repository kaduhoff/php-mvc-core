<?php

namespace app\core\middlewares;

/**
 * Classe base para criação de middleware 
 * @author Kadu Hoffmann <kaduhoff@gmail.com>
 * @package app\core\middlewares 
 * */
abstract class BaseMiddleware
{
    abstract public function execute();
}
