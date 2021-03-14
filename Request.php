<?php

namespace kadcore\tcphpmvc;

/**
 * Pega dados das requisições web
 * @package kadcore\tcphpmvc
 */
class Request
{
    /**
     * Retorna o caminho da URI
     * @return string path 
     */
    public function getPath(): string
    {
        //pega o conteudo da variavel global e se não teiver esse 
        // preenche com barra
        $path = $_SERVER['REQUEST_URI'] ?? '/';
        //pega o ? na uri e só passa o que tem antes
        $posInterr = strpos($path, '?');
        if ($posInterr !== false) {
            $path = substr($path, 0, $posInterr);
        }
        //verifica se termina com barra e retira a barra
        //não executa se a string contiver só a barra '/'
        if ((strlen($path) > 1) && (\str_ends_with($path, '/'))) {
            $path = substr($path, 0, -1);
        }
        return $path;
    }

    /**
     * Retorna o metodo ex: get, post
     * @return string metodo 
     */
    public function method(): string
    {
        return strtolower($_SERVER['REQUEST_METHOD']);
    }

    /**
     * Retorna verdadeiro se o metodo request é GET
     * @return bool 
     */
    public function isGet(): bool
    {
        return $this->method() === 'get';
    }

    /**
     * Retorna verdadeiro se o metodo request é POST
     * @return bool 
     */
    public function isPost(): bool
    {
        return $this->method() === 'post';
    }

    public function getVars()
    {
        $vars = [];
        if ($this->method() === 'get') {
            foreach ($_GET as $key => $value) {
                $vars[$key] = \filter_input(\INPUT_GET, $key, \FILTER_SANITIZE_SPECIAL_CHARS);
            }
        }
        if ($this->method() === 'post') {
            foreach ($_POST as $key => $value) {
                $vars[$key] = \filter_input(\INPUT_POST, $key, \FILTER_SANITIZE_SPECIAL_CHARS);
            }
        }
        return $vars;
    }
}
