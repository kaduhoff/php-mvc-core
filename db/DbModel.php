<?php

namespace kadcore\tcphpmvc\db;

use kadcore\tcphpmvc\Model;
use Dotenv\Parser\Value;
use kadcore\tcphpmvc\Application;

abstract class DbModel extends Model
{
    public const RULE_UNIQUE_DB = 'unique';

    abstract public function tableName(): string;
    abstract public function attributes(): array;
    
    protected array $lastErrors = [];

    public function insert($attributes): bool
    {
        
        $tableName = $this->tableName();
        //coloca aspas duplas para os nomes das colunas (padrão case-sensitive)
        $colNames = \array_map(fn($x) => '"'.$x.'"', $attributes);
        //transforma array em uma unica string separada por virgula
        $colNames = \implode(",", $colNames);

        //parametros para prepare são com ":" para evitar sql injection, então cria e separa com virgula em unica string
        $params = \array_map(fn($x) => ':'.$x, $attributes);
        $params = \implode(',', $params);
        $sqlInsert = "INSERT INTO $tableName ($colNames) VALUES ($params)";
        //echo $sqlInsert; exit;
        $statement = self::prepare($sqlInsert);
        
        //adicionando os valores ao prepare
        foreach ($attributes as $attribute) {
            $statement->bindValue(":$attribute", $this->{$attribute});
        }

        try {
            $statement->execute();
            return true;
        } catch (\Throwable $th) {
            //throw $th;
            $this->lastErrors = $statement->errorInfo();
            return false;
        }
        
    }

    /**
     * Atualiza registros indicados nas colunas, baseado na condição passada
     * @param array $condition condição do update (WHERE)
     * @param array $attributes fieldnames a atualizar
     * @return bool retruna true se deu certo
     */
    public function update(array $conditions, array $attributes): bool
    {
        
        $tableName = $this->tableName();
        //coloca aspas duplas para os nomes das colunas (padrão case-sensitive)
        $colNames = \array_map(fn($x) => '"'.$x.'" = :'.$x, $attributes);
        //transforma array em uma unica string 
        $colNames = \implode(", ", $colNames);

        //parametros para prepare são com "?" para evitar sql injection
        $conditionWhere = \array_map(fn($x) => '"'.$x.'" = :'.$x, $conditions);
        $conditionWhere = \implode(',', $conditionWhere);
        $sql = "UPDATE $tableName SET $colNames WHERE $conditionWhere";

        //echo $sql; exit;
        $statement = self::prepare($sql);
        
        //adicionando os valores ao prepare
        foreach ($attributes as $attribute) {
            $statement->bindValue(":$attribute", $this->{$attribute});
        }

        //adicionando os valores ao prepare (condições where)
        foreach ($conditions as $condition) {
            $statement->bindValue(":$condition", $this->{$condition});
        }

        try {
            $statement->execute();
            //echo $sql; exit;
            return true;
        } catch (\Throwable $th) {
            //throw $th;
            $this->lastErrors = $statement->errorInfo();
            return false;
        }
        
    }

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
                if ($ruleName === self::RULE_UNIQUE_DB) {
                    //se essa regra for um array bidimensional, terá o nome de outro
                    //atributo na segunda posição para comparar com esse valor.
                    $uniqueAttribute = \is_array($rule) ? $rule[1] : $attribute;
                    $contRegFound = $this->contaRegistros($uniqueAttribute, $value);
                    if ($contRegFound > 0) {
                        $this->addError($attribute, $ruleName, $value);
                    } 
                }
            }
        }   
        //roda validações herdadas do modelo pai
        parent::validate();

        //retorna true se algum erro foi preenchido
        return empty($this->errors);
    }

    public function errorMessages($ruleName): string
    {
        //deve chamar a função pai em defalt
        return match ($ruleName) {
            self::RULE_UNIQUE_DB => 'Já existe um registro {unique}, ele deve ser único',
            default => parent::errorMessages($ruleName),
        };

    }

    public function getLastErrors(): array
    {
        return $this->lastErrors;
    }

    public static function prepare($sql)
    {
        return Application::$app->db->pdo->prepare($sql);
    }

    /**
     * Retorna a quantidade de registros do modelo db
     * @param string $fieldName (opcional) campo a procurar
     * @param mixed $value (opcional) valor a procurar
     * @return int quantidade de registros encontrados
     */
    public function contaRegistros(string $fieldName = '', mixed $value = false) : int 
    {   
        try {
            $tableName = $this->tableName();
            $sql = "SELECT COUNT(*) FROM $tableName";
            if ($value !== false) {
                $statement = self::prepare($sql." WHERE $fieldName = :attr");
                $statement->bindValue(":attr", $value);
            } else {
                $statement = self::prepare($sql);
            }
            $statement->execute();
            $cont = $statement->fetchColumn();
            return $cont;
        } catch (\Throwable $th) {
            $this->lastErrors = (array) $th;
            throw $th;
        } 
        return 0;
    }      

    public function getPrimaryKeysColsNames() : array
    {   
        try {
            $tableName = $this->tableName();
            $sql = "SELECT kcu.column_name as key_column,
                    kcu.table_schema,
                    kcu.table_name,
                    tco.constraint_name,
                    kcu.ordinal_position as position
                from information_schema.table_constraints tco
                join information_schema.key_column_usage kcu 
                    on kcu.constraint_name = tco.constraint_name
                    and kcu.constraint_schema = tco.constraint_schema
                    and kcu.constraint_name = tco.constraint_name
                where tco.constraint_type = 'PRIMARY KEY'
                    and kcu.table_schema = '" .  Application::$app->db->schemaname . "'
                    and kcu.table_name = ?
                order by kcu.table_schema,
                        kcu.table_name,
                position;";
            $statement = self::prepare($sql);
            $statement->execute(array($tableName));
            $colNames [] = $statement->fetchColumn(0);
            //se não encontrado chaves retorna false, dai dispara erro
            if (!$colNames[0]) throw new \Exception("Tabela $tableName não existe no DB, ou não possui chaves primarias", -500);
            return $colNames;
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    /**
     * retorna um DBmodel ou false pela(s) chaves(s) passada(s) como argumento(s)
     * @param mixed $keyValue 
     * @return exit 
     */
    public function getByKey(...$keyValue) : bool
    {
        $tableName = $this->tableName();
        $pkColsNames = $this->getPrimaryKeysColsNames();
        
        //coloca aspas duplas para os nomes das colunas (padrão case-sensitive)
        //e = :parametro para prepare
        $conditions = \array_map(fn($x) => '"'.$x.'"= ?', $pkColsNames);
        //transforma em string unica separada por virgulas p/ adicionar ao sql
        $conditions = \implode(", ", $conditions);
        $sql = "SELECT * FROM $tableName WHERE $conditions ;";
        //echo $sql;
                
        $statement = self::prepare($sql);
        
        //adiciona os parametros ao prepare
        foreach ($keyValue as $key => $value) {
            $statement->bindValue($key+1, $value);
        }

        try {
            $statement->execute();
            $results = $statement->fetchObject();
            //verifica os atributos da classe que são vinculados
            $attributes = $this->attributes();
            foreach ($attributes as $attribute => $bindName) {
                if (\property_exists($this, $bindName)) {
                    $this->$bindName = $results->$bindName ?? null;
                }
            }
            return true;
        } catch (\Throwable $th) {
            $this->lastErrors = (array) $th;
            throw $th;
            return false;
        }
        return false;
    }
}
