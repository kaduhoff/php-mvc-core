<?php

namespace kadcore\tcphpmvc;

/**
 * Ativa os coockies de sessão, possui também 
 * cookies exclusivos da aplicação. 
 * 
 * @author Kadu Hoffmann <kaduhoff@gmail.com>
 * @package kadcore\tcphpmvc 
 * */
class Session
{
    protected const FLASH_KEY = 'flash_messages';
    protected const USER_ID = 'UserIdLogged';
    public function __construct()
    {
        session_start();    
        //variaveis passadas durante uma apresentação da página
        $flashMessages = $_SESSION[self::FLASH_KEY] ?? [];
        foreach ($flashMessages as $key => &$flashMessage) {
            //Mark to be removed
            $flashMessage['remove'] = true;
        }
        //\var_dump($_SESSION[self::FLASH_KEY]);
        $_SESSION[self::FLASH_KEY] = $flashMessages;

    }

    public function setFlash($key, $message)
    {
        $_SESSION[self::FLASH_KEY][$key] = [
            'remove' => false,
            'value' => $message
        ];
    }

    public function getFlash($key)
    {
        //se usar a variavel marca para apagar instananeamente
        $retorno = $_SESSION[self::FLASH_KEY][$key]['value'] ?? false;
        if ($retorno) {
            $_SESSION[self::FLASH_KEY][$key]['remove'] = true;
        };
        return $retorno;
    }

    public function __destruct()
    {
        //iterate marked remove and remove
        $flashMessages = $_SESSION[self::FLASH_KEY] ?? [];
        foreach ($flashMessages as $key => $flashMessage) {
            if ($flashMessage['remove']) {
                unset($_SESSION[self::FLASH_KEY][$key]);
            }
        }
    }

    /**
     * Adiciona usuario logado à sessão
     * @param mixed $userId 
     * @return void 
     */
    public function setUserLogged($userId)
    {
        $this->set(self::USER_ID, $userId);
    }

    /**
     * Retorna Id do usuario logado à sessão
     * @return int id 
     */
    public function getUserLogged()
    {
        return $this->get(self::USER_ID);
    }

    /**
     * Logoff de usuario da sessão 
     * @return void 
     */
    public function unsetUser()
    {
        $this->unset(self::USER_ID);
    }

    public function set(string $key ,$value)
    {
        $_SESSION[$key] = $value;
    }

    public function get($key): mixed
    {
        return $_SESSION[$key] ?? false;
    }

    public function unset(string $key)
    {
        unset($_SESSION[$key]);
    }
}


