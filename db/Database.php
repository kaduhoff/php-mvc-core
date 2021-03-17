<?php

namespace kadcore\tcphpmvc\db;

use PDO;
use kadcore\tcphpmvc\Application;

class Database
{
    const schemaname = "public";
    public PDO $pdo;
    public function __construct(array $config)
    {
        $dsn = $config['dsn'] ?? '';
        $user = $config['user'] ?? '';
        $password = $config['password'] ?? '';
        try {
            $this->pdo = new PDO($dsn, $user, $password);
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (\Throwable $th) {
            $this->log('Erro ao conectar ao Banco de dados');
        }
        
    }

    public function applyMigrations(?array $files = null)
    {
        $this->createMigrationsTable();
        $appliedMigrations = $this->getAppliedMigrations();

        $newMigrations = [];
        $files = $files ?? scandir(Application::$ROOT_DIR.'/migrations');
        $toApplyMigrations = \array_diff($files, $appliedMigrations);
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
            $this->log("Nada instalado pois todas as migrations já estavam aplicadas.");
        }

    }

    public function unapplyMigrations(?array $files = null)
    {
        $this->createMigrationsTable();
        $appliedMigrations = $this->getAppliedMigrations();

        $newMigrations = [];
        $files = $files ?? \scandir(Application::$ROOT_DIR.'/migrations');
        $toUnapplyMigrations = \array_intersect($files, $appliedMigrations);
        foreach ($toUnapplyMigrations as $migration) {
            if ($migration === '.' || $migration === '..') {
                continue;
            }
            
            require_once Application::$ROOT_DIR.'/migrations/'.$migration;
            $className = pathinfo($migration, \PATHINFO_FILENAME);
            $instance = new $className();
            $this->log("Desinstalando a migration $migration");
            $instance->down();
            $this->log("Migration $migration desinstalada");
            $newMigrations[] = $migration;
        }

        if (!empty($newMigrations)) {
            $this->removeMigrations($newMigrations);
        } else {
            $this->log("Não há o que desinstalar pois não foram encontradas 'migrations' aplicadas.");
        }

    }

    public function createMigrationsTable()
    {
        $sql = "CREATE TABLE IF NOT EXISTS \"".self::schemaname."\".\"migrations\" (
            \"id\" SERIAL PRIMARY KEY,
            \"migration\" VARCHAR(255),
            \"created_at\" TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        );";
        $statement = $this->pdo->prepare($sql);
        $statement->execute();
    }

    public function getAppliedMigrations()
    {
        $sql = "SELECT \"migration\" FROM \"".self::schemaname."\".\"migrations\" ";
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

        $sql = "INSERT INTO \"".self::schemaname."\".\"migrations\" (\"migration\") VALUES $valuesFormattedArray";
        $statement = $this->pdo->prepare($sql);
        //echo $sql;
        $statement->execute();
    }

    public function removeMigrations(array $migrations)
    {
        //interessante a função abaixo
        //Arrow functions tem a forma básica fn (argument_list) => expr
        //função anonima implementada a partir do 7.4
        //ou seja, cada elemento do array $migrations passa pela função fn($m) 
        //que retorna Array( "('<conteudo1ElementoArray>')", "('<conteudo1ElementoArray>')", ...) 
        //e a função implode está convertendo o array em uma string separada por, nesse caso, ","
        //retornando isso: string "('<conteudo1ElementoArray>'), ('<conteudo1ElementoArray>')"
        $valuesFormattedArray = \implode(",", \array_map(fn($m) => "('$m')", $migrations));

        $sql = "DELETE FROM \"".self::schemaname."\".\"migrations\" WHERE \"migration\" IN $valuesFormattedArray";
        $statement = $this->pdo->prepare($sql);
        //echo $sql;
        $statement->execute();
    }

    protected function log($message)
    {
        echo "[".date("D/M/Y H:i:s")."] - " . $message . "\r\n";
    }

    public function prepare($sql)
    {
        return $this->pdo->prepare($sql);
    }
}
