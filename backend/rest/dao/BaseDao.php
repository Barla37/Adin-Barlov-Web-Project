<?php
require_once __DIR__ . '/../../config/config.php';

/**
 * BaseDao: generic CRUD for one table. Every entity DAO extends this class.
 * All SQL values go through prepared statements (placeholders), never string concatenation.
 */
class BaseDao
{
    private static $connection = null;
    protected $table_name;
    protected $columns;

    /**
     * @param string $table_name table this DAO works with (fixed in code, never user input)
     * @param array  $columns    columns that may be written by add()/update() (whitelist, without id)
     */
    public function __construct($table_name, $columns = [])
    {
        $this->table_name = $table_name;
        $this->columns = $columns;
    }

    /** One shared PDO connection for the whole request. */
    public static function connection()
    {
        if (self::$connection === null) {
            self::$connection = new PDO(
                "mysql:host=" . Config::DB_HOST() . ";port=" . Config::DB_PORT() .
                ";dbname=" . Config::DB_NAME() . ";charset=utf8mb4",
                Config::DB_USER(),
                Config::DB_PASSWORD(),
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                ]
            );
        }
        return self::$connection;
    }

    /** Runs a SELECT and returns all rows. */
    protected function query($query, $params = [])
    {
        $statement = self::connection()->prepare($query);
        $statement->execute($params);
        return $statement->fetchAll();
    }

    /** Runs a SELECT and returns the first row or null. */
    protected function query_unique($query, $params = [])
    {
        $results = $this->query($query, $params);
        return $results[0] ?? null;
    }

    /** Runs INSERT/UPDATE/DELETE and returns the number of affected rows. */
    protected function execute($query, $params = [])
    {
        $statement = self::connection()->prepare($query);
        $statement->execute($params);
        return $statement->rowCount();
    }

    /** Keeps only whitelisted columns, so a request can never write to a column we did not allow. */
    protected function only_columns($entity)
    {
        return array_intersect_key($entity, array_flip($this->columns));
    }

    // ---------------------------------------------------------------- CRUD

    /** READ: all rows. */
    public function get_all()
    {
        return $this->query("SELECT * FROM " . $this->table_name);
    }

    /** READ: one row by primary key, or null. */
    public function get_by_id($id)
    {
        return $this->query_unique("SELECT * FROM " . $this->table_name . " WHERE id = :id", ['id' => $id]);
    }

    /** CREATE: inserts a row and returns it, including the new id. */
    public function add($entity)
    {
        $data = $this->only_columns($entity);
        if (empty($data)) {
            throw new InvalidArgumentException('No valid columns to insert.');
        }
        $names = array_keys($data);
        $query = "INSERT INTO " . $this->table_name .
            " (" . implode(', ', $names) . ") VALUES (:" . implode(', :', $names) . ")";
        $this->execute($query, $data);
        $data['id'] = (int) self::connection()->lastInsertId();
        return $data;
    }

    /** UPDATE: changes the given columns of one row. Returns the number of changed rows. */
    public function update($entity, $id)
    {
        $data = $this->only_columns($entity);
        if (empty($data)) {
            throw new InvalidArgumentException('No valid columns to update.');
        }
        $set = [];
        foreach (array_keys($data) as $name) {
            $set[] = $name . " = :" . $name;
        }
        $data['id'] = $id;
        return $this->execute(
            "UPDATE " . $this->table_name . " SET " . implode(', ', $set) . " WHERE id = :id",
            $data
        );
    }

    /** DELETE: removes one row. Returns the number of deleted rows. */
    public function delete($id)
    {
        return $this->execute("DELETE FROM " . $this->table_name . " WHERE id = :id", ['id' => $id]);
    }

    // -------------------------------------------------------- Transactions

    public function begin_transaction()
    {
        return self::connection()->beginTransaction();
    }

    public function commit()
    {
        return self::connection()->commit();
    }

    public function rollback()
    {
        return self::connection()->rollBack();
    }
}
