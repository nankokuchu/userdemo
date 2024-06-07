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
            $dsn = "$this->type:host=$this->host;
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

    public function where(array $condition, string $andOrNot = 'AND'): Db {
        $where = '';
        // WHERE xxx
        $whereArray = [];
        $executeDate = [];
        if (!empty($condition)) {
            foreach ($condition as $key => $value) {

                if (strtolower($value[1]) === 'between') { // between
                    $whereArray[] = "$value[0] $value[1] ? AND ?";
                    $executeDate[] = $value[2][0];
                    $executeDate[] = $value[2][1];
                } elseif (strtolower($value[1]) === 'in') { // in
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

            if ($andOrNot !== 'NOT' && $andOrNot !== 'ORNOT') {
                $where = implode(" $andOrNot ", $whereArray);
            } else {
                //TODO
                if ($andOrNot === 'ORNOT'){
                    $where = implode(" OR ", $whereArray);
                }else {
                    $where = implode(" AND ", $whereArray);
                }
                $where = 'NOT(' . $where . ')';
            }


            // データを整える
            if (isset(self::$executeDate)) {
                self::$executeDate = array_merge(self::$executeDate, $executeDate);
            } else {
                self::$executeDate = $executeDate;
            }
        }
        $this->buildWhere($where, $andOrNot);
        return $this;
    }

    public function whereOr(array $condition): Db {
        return $this->where($condition, 'OR');
    }

    public function whereNot(array $condition): Db {
        return $this->where($condition, 'NOT');
    }

    public function whereOrNot(array $condition): Db {
        return $this->where($condition, 'ORNOT');
    }

    public function whereNull($name): Db {
        $where = "$name is null";
        $this->buildWhere($where);
        return $this;
    }

    public function whereNotNull($name): Db {
        $where = "$name is not null";
        $this->buildWhere($where);
        return $this;
    }

    private function buildWhere($where, string $andOrNot = 'AND'): void {
        $oldWhere = self::$where;
        if ($where !== '') {
            if (!str_contains($oldWhere, 'WHERE')) {
                if ($oldWhere !== '') {
                    $where = 'WHERE ' . $oldWhere . ' ' . $andOrNot . ' ' . $where;
                } else {
                    $where = 'WHERE ' . $where;
                }

            } else {
                $where = $oldWhere . ' ' . $andOrNot . ' ' . $where;
            }
            self::$where = $where;
        }
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

$result = DB::table('users')
    ->where([
        [ 'username', '=', '1' ],
    ])
    ->whereOr([
        [ 'create_time', 'between', [ '2024-06-07 14:44:43', '2024-06-07 14:59:12' ] ]
    ])
    ->select();
//$result = DB::table('users')->where([
//    [ 'create_time', 'between', [ '2024-06-07 14:44:43', '2024-06-07 14:59:12' ] ],
//    [ 'id', 'in', [ 1, 10 ] ],
//])->whereNull('username')->select();
var_dump($result);
