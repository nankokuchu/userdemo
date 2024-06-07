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
    private static array $executeDate;

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

    public function where(array $condition): Db {
        $where = '';
        // WHERE xxx
        $whereArray = [];
        $executeDate = [];
        if (!empty($condition)) {
            foreach ($condition as $key => $value) {

                if ($value[1] === 'between') { // between
                    $whereArray[] = "$value[0] $value[1] ? AND ?";
                    $executeDate[] = $value[2][0];
                    $executeDate[] = $value[2][1];
                } elseif ($value[1] === 'in') { // in
                    $rtrim = rtrim(str_repeat('?,', count($value[2])), ',');
                    $whereArray[] = "$value[0] $value[1] ($rtrim)";
                    foreach ($value[2] as $vv) {
                        $executeDate[] = $vv;
                    }
                } else {
                    // where
                    $whereArray[] = "$value[0] $value[1] ? ";
                    $executeDate[] = $value[2];
                }
            }

            $where = implode(' AND ', $whereArray);

            // データを整える
            if (isset(self::$executeDate)) {
                self::$executeDate = array_merge(self::$executeDate, $executeDate);
            } else {
                self::$executeDate = $executeDate;
            }
        }
        $oldWhere = self::$where;
        if ($where !== '') {
            if (!str_contains($oldWhere, 'WHERE')) {
                if ($oldWhere !== '') {
                    $where = 'WHERE ' . $oldWhere . ' AND ' . $where;
                } else {
                    $where = 'WHERE ' . $where;
                }

            } else {
                $where = $oldWhere . ' AND ' . $where;
            }
            self::$where = $where;
        }
        return $this;
    }

    // select *
    public function select(): bool|array {
        $sql = "SELECT * FROM " . self::$tableName . " " . self::$where;
        echo $sql;
        self::$stmt = $this->pdo->prepare($sql);
        if (isset(self::$executeDate)) {
            self::$stmt->execute(self::$executeDate);
        } else {
            self::$stmt->execute();
        }
        $result = self::$stmt->fetchAll(PDO::FETCH_ASSOC);
        self::$stmt->closeCursor();
        return $result;
    }
}

//$result = DB::table('users')->where([
//    [ 'username', 'like', '%23%' ],
//])->select();
//$result = DB::table('users')->where([
//    [ 'create_time', 'between', [ '2024-06-07 14:44:43', '2024-06-07 14:59:12' ] ],
//])->select();
$result = DB::table('users')->where([
    [ 'id', 'in', [ 1, 10 ] ],
])->select();
var_dump($result);
