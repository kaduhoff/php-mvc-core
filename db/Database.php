<?php

namespace kadcore\tcphpmvc\db;

use PDO;
use kadcore\tcphpmvc\Application;

class Database
{
    public PDO $pdo;
    public function __construct(array $config)
    {
        $dsn = $config['dsn'] ?? '';
        $user = $config['user'] ?? '';
        $password = $config['password'] ?? '';
        try {
            $this->pdo = new PDO($dsn, $user, $password);
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->pdo->exec("SET SCHEMA 'public'");
        } catch (\Throwable $th) {
            $this->log('Erro ao conectar ao Banco de dados');
        }
        
    }

    public function applyMigrations()
    {
        $this->createMigrationsTable();
        $appliedMigrations = $this->getAppliedMigrations();

        $newMigrations = [];
        $files = scandir(Application::$ROOT_DIR.'/migrations');
        $toApplyMigrations = array_diff($files, $appliedMigrations);
        foreach ($toApplyMigrations as $migration) {
            if ($migration === '.' || $migration === '..') {
                continue;
            }
            
            require_once Application::$ROOT_DIR.'/migrations/'.$migration;
            $className = pathinfo($migration, \PATHINFO_FILENAME);
            $instance = new $className();
            $this->log("Aplicando a migration $migration");
            $instance->up();
            $this->log("Migration $migration aplicada");
            $newMigrations[] = $migration;
        }

        if (!empty($newMigrations)) {
            $this->saveMigrations($newMigrations);
        } else {
            $this->log("Todas as migrations estão aplicadas");
        }

    }

    public function createMigrationsTable()
    {
        $sql = "CREATE TABLE IF NOT EXISTS \"migrations\" (
            \"id\" SERIAL PRIMARY KEY,
            \"migration\" VARCHAR(255),
            \"created_at\" TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        );";
        $statement = $this->pdo->exec($sql);
    }

    public function getAppliedMigrations()
    {
        $sql = "SELECT \"migration\" FROM \"migrations\" ";
        $statement = $this->pdo->prepare($sql);
        $statement->execute();
        
        //como é uma só coluna, posso passar o FETCH_COLUMN, que retornará
        //um Array unidimensional com os resultados das linhas
        return $statement->fetchAll(PDO::FETCH_COLUMN);
    }

    public function saveMigrations(array $migrations)
    {
        //interessante a função abaixo
        //Arrow functions tem a forma básica fn (argument_list) => expr
        //função anonima implementada a partir do 7.4
        //ou seja, cada elemento do array $migrations passa pela função fn($m) 
        //que retorna Array( "('<conteudo1ElementoArray>')", "('<conteudo1ElementoArray>')", ...) 
        //e a função implode está convertendo o array em uma string separada por, nesse caso, ","
        //retornando isso: string "('<conteudo1ElementoArray>'), ('<conteudo1ElementoArray>')"
        $valuesFormattedArray = \implode(",", \array_map(fn($m) => "('$m')", $migrations));

        $sql = "INSERT INTO \"migrations\" (\"migration\") VALUES $valuesFormattedArray";
        $statement = $this->pdo->prepare($sql);
        //echo $sql;
        $statement->execute();
    }

    protected function log($message)
    {
        echo '['.date('D/M/Y H:i:s').'] - ' . $message . "\r\n";
    }

    public function prepare($sql)
    {
        return $this->pdo->prepare($sql);
    }
}
