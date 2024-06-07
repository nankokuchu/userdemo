<?php

namespace func;

use PDO;
use PDOException;
use PDOStatement as PDOStatementAlias;

class Db {
    private string $type;
    private string $host;
    private string $port;
    private string $dbName;
    private string $charset;
    private string $username;
    private string $password;
    private PDO $pdo;

    private static ?self $instance = null;
    private static string $tableName;
    private static PDOStatementAlias $stmt;
    private static string $where = '';

    private function __construct($params) {
        $this->initParameter($params);
        $this->initPDO();
    }

    private function __clone() {
    }

    public static function getInstance($params): self {
        if (!self::$instance instanceof self) {
            self::$instance = new self($params);
        }
        return self::$instance;
    }

    private function initParameter($params): void {
        $this->type = $params['type'] ?? 'mysql';
        $this->host = $params['host'] ?? '127.0.0.1';
        $this->port = $params['port'] ?? '3306';
        $this->dbName = $params['dbName'] ?? 'userdemo';
        $this->charset = $params['charset'] ?? 'utf8mb4';
        $this->username = $params['username'] ?? 'root';
        $this->password = $params['password'] ?? '';
    }

    private function initPDO(): void {
        try {
            $dsn = "{$this->type}:host={$this->host};
            port={$this->port};
            dbname={$this->dbName};
            charset={$this->charset}";

            $this->pdo = new PDO($dsn, $this->username, $this->password);

            $this->initException();

        } catch (PDOException $ex) {
            $this->showException($ex);
            exit;
        }
    }

    private function initException(): void {
        $this->pdo->setattribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    private function showException(PDOException $ex, string $sql = ''): void {
        if ($sql !== '') {
            echo 'SQL実行失敗<br>';
            echo '間違ったSQL文は：' . $sql, '<br>';
        }
        echo 'エラーコード:' . $ex->getcode() . '<br>';
        echo 'エラーライン番号:' . $ex->getLine() . '<br>';
        echo 'エラーファイル:' . $ex->getFile() . '<br>';
        echo 'エラーメッセージ:' . $ex->getmessage() . '<br>';

    }

    public static function table($tableName): Db {
        self::$tableName = $tableName;
        return self::getInstance(null);
    }

    public function where(array $condition) {
        $where = '';
        if (!empty($condition)) {

        }
        self::$where = $where;
        return $this;
    }

    // select *
    public function select() {
        $sql = "SELECT * FROM " . self::$tableName . " " . self::$where;
        self::$stmt = $this->pdo->prepare($sql);
        self::$stmt->execute();
        $result = self::$stmt->fetchAll(PDO::FETCH_ASSOC);
        self::$stmt->closeCursor();
        return $result;
    }
}
