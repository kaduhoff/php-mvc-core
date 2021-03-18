<?php

namespace kadcore\tcphpmvc;

use kadcore\tcphpmvc\Application;
use kadcore\tcphpmvc\db\DbModel;

class UserModel extends DbModel
{
    const STATUS_ACTIVE = 0;
    const STATUS_ADMIN = 1;
    const STATUS_INACTIVE = 2;
    const STATUS_DELETED = 3;

    public int $id = 0;
    public string $nome = '';
    public string $email = '';
    public string $senha = '';
    public string $senha2 = '';
    public int $status = self::STATUS_INACTIVE;
    
    public function tableName(): string
    {
        return "users";
    }

    public function save(): bool
    {   
        $this->status = self::STATUS_INACTIVE;
        $this->senha = \password_hash($this->senha, \PASSWORD_DEFAULT);
        return parent::insert(["nome","email","senha","status"]);
    }

    public function rules(): array 
    { 
        return [
            'nome' => [self::RULE_REQUIRED],
            'email' => [self::RULE_REQUIRED, self::RULE_EMAIL, self::RULE_UNIQUE_DB],
            'senha' => [self::RULE_REQUIRED, [self::RULE_MIN, 8], [self::RULE_MAX, 24]],
            'senha2' => [self::RULE_REQUIRED, [self::RULE_MATCH, 'senha']],
        ];
    }
    
    public function attributes(): array
    {
        return ["id","nome","email","senha","status"];
    }

    public function isEmpty()
    {
        return ($this->id === 0);
    }

    public function isGuest()
    {
        return $this->isEmpty();
    }

}
