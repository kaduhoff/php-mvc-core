<?php

namespace kadcore\tcphpmvc\middlewares;

/**
 * Classe base para criação de middleware 
 * @author Kadu Hoffmann <kaduhoff@gmail.com>
 * @package kadcore\tcphpmvc\middlewares 
 * */
abstract class BaseMiddleware
{
    abstract public function execute();
}
