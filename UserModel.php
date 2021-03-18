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
    public string $name = '';
    public string $email = '';
    public string $password = '';
    public string $passRepeat = '';
    public int $status = self::STATUS_INACTIVE;
    
    public function tableName(): string
    {
        return "users";
    }

    public function insertNew(): bool
    {   
        $this->password = \password_hash($this->password, \PASSWORD_DEFAULT);
        return parent::insert(["name","email","password","status"]);
    }

    public function updateData(): bool
    {   
        if (\strlen($this->password) === 0) {
            return parent::update(["id"],["name","email","status"]);
        } elseif ((\strlen($this->password) >= 8) && (\strlen($this->password) <= 24) && ($this->password === $this->passRepeat)) {
            $this->password = \password_hash($this->password, \PASSWORD_DEFAULT);
            return parent::update(["id"],["name","email","password","status"]);
        } else {
            Alerts::setDanger("Senha preenchida inválida");
            return false;
        }
    }

    public function rules(): array 
    { 
        return [
            'name' => [self::RULE_REQUIRED],
            'email' => [self::RULE_REQUIRED, self::RULE_EMAIL, self::RULE_UNIQUE_DB],
            'password' => [self::RULE_REQUIRED, [self::RULE_MIN, 8], [self::RULE_MAX, 24]],
            'passRepeat' => [self::RULE_REQUIRED, [self::RULE_MATCH, 'password']],
        ];
    }
    
    public function attributes(): array
    {
        return ["id","name","email","password","status"];
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
