<?php

namespace app\core;

/**
 * Classe base para models com funções essenciais e comuns
 * 
 * @author Kadu Hoffmann <kaduhoff@gmail.com>
 * @package app\core
 */
abstract class Model
{
    public const RULE_NONE = '';
    public const RULE_REQUIRED = 'required';
    public const RULE_EMAIL = 'email';
    public const RULE_MIN = 'min';
    public const RULE_MAX = 'max';
    public const RULE_MATCH = 'match';

    public function loadData($data)
    {
        foreach ($data as $key => $value) {
            if (\property_exists($this, $key)) {
                $this->{$key} = $value;
            }
        }
    }

    /** 
     * Regras devem ser em arrays multidimencionais. Ex:
     *  [
     *      'email' => [self::RULE_REQUIRED, self::RULE_EMAIL, self::RULE_UNIQUE_DB],
     *      'senha' => [self::RULE_REQUIRED, [self::RULE_MIN, 8], [self::RULE_MAX, 24]],
     *  ]
     * @return array  */
    abstract public function rules(): array;

    public array $errors = [];

    
    /**
     * Valida com array das regras aplicadas em rules()
     * @return bool  
     * */
    public function validate(): bool
    {
        foreach ($this->rules() as $attribute => $rules) {
            //valor do atributo do modelo 
            $value = $this->{$attribute};
            foreach ($rules as $rule) {
                //para cada regra pega o nome da regra
                $ruleName = $rule;
                //algumas são array, então capta aqui
                if (!is_string($rule)) {
                    $ruleName = $rule[0]; //pega o primeiro item do array que deverá ser o nome da regra
                }
                //validações de erros
                
                if ($ruleName === self::RULE_REQUIRED && !$value) {
                    $this->addError($attribute, $ruleName);
                }
                if ($ruleName === self::RULE_EMAIL && !filter_var($value, \FILTER_VALIDATE_EMAIL)) {
                    $this->addError($attribute, $ruleName);
                } 
                if ($ruleName === self::RULE_MIN && strlen($value) < $rule[1]) {
                    $this->addError($attribute, $ruleName, $rule[1]);
                } 
                if ($ruleName === self::RULE_MAX && strlen($value) > $rule[1]) {
                    $this->addError($attribute, $ruleName, $rule[1]);
                }
                if ($ruleName === self::RULE_MATCH && $value !== $this->{$rule[1]}) {
                    $this->addError($attribute, $ruleName, $rule[1]);
                }
                  
            }
            
        }
        //retornna true se errors estiver vazia, senão, false
        return empty($this->errors);
    }

    public function addError(string $attribute, string $ruleName, $value = '')
    {
        $message = $this->errorMessages($ruleName) ?? '';
        $message = \str_replace("{{$ruleName}}", $value, $message);
        $this->errors[$attribute][] = $message;
    }

    public function errorMessages($ruleName): string
    {
        return match ($ruleName) {
            self::RULE_REQUIRED => 'Esse campo é obrigatório',
            self::RULE_EMAIL => 'O e-mail deve ser preenchido corretamente',
            self::RULE_MIN => 'O campo deve possuir no mínimo {min} caracteres',
            self::RULE_MAX => 'O campo deve possuir no máxmimo {max} caracteres',
            self::RULE_MATCH => 'O campo deve ser igual ao campo {match}',
            default => ''
        };
    }

    /** 
     * Retorna se possui algum erro de validação 
     * (o metodo vaidate() deve ser chamado antes)
     * @return bool  
     * */
    public function hasValidadtionErrors(){
        return (\sizeof($this->errors) > 0);
    }

    /**
     * Retorna se possui algum erro de um atributo do modelo 
     * (o metodo vaidate() deve ser chamado antes)
     * @param mixed $attribute nome do atributo
     * @return bool 
     */
    public function hasError($attribute)
    {
        return !isset($this->errors[$attribute]);
    }

    /**
     * Retorna o primeiro erro encontrado
     * (o metodo vaidate() deve ser chamado antes)
     * @param mixed $attribute 
     * @return mixed 
     */
    public function getError($attribute)
    {
        return $this->errors[$attribute][0] ?? false;
    }
}
