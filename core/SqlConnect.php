<?php

declare(strict_types=1);

require_once __DIR__ . '/../shared/database_config.php';

/**
 * PDO wrapper class for SQL databases
 * 
 * @author mimimiku778 <0203.sub@gmail.com>
 * @license https://github.com/mimimiku778/MimimalCMS/blob/master/LICENSE.md
 */
class SqlConnect
{
    public PDO $pdo;

    /**
     * @throws PDOException
     */
    public function __construct()
    {
        $this->pdo = new PDO(
            'mysql:host=' . DatabaseConfig::HOST . ';dbname=' . DatabaseConfig::DB_NAME . ';charset=utf8mb4',
            DatabaseConfig::USER_NAME,
            DatabaseConfig::PASSWORD
        );

        // Enable PDO to throw exceptions on error
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    /**
     * Executes an SQL query and returns a PDOStatement object with bound values.
     *
     * @param string $query The SQL query to execute.
     * * *Example:* `'SELECT * FROM table WHERE category = :category LIMIT :offset, :limit'`
     * 
     * @param array|null $params [optional] An associative array of query parameters.
     * InvalidArgumentException will be thrown if any of the array values are not strings or numbers.
     * * *Example:* `['category' => 'foods', 'limit' => 20, 'offset' => 60]`
     * 
     * @return PDOStatement Returns a PDOStatement object containing the results of the query, or false.
     * 
     * @throws PDOException If an error occurs during the query execution.
     * @throws InvalidArgumentException If any of the array values are not strings or numbers.
     */
    public function execute(string $query, ?array $params = null): PDOStatement
    {
        $stmt = $this->pdo->prepare($query);

        if ($params === null) {
            $stmt->execute();
            return $stmt;
        }

        foreach ($params as $key => $value) {
            if (!is_string($value) && !is_numeric($value)) {
                throw new InvalidArgumentException(
                    "Invalid parameter value for key {$key}: only strings and numbers are allowed."
                );
            }

            $type = is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR;
            $stmt->bindValue($key, $value, $type);
        }

        $stmt->execute();
        return $stmt;
    }

    /**
     * Executes an SQL query and returns a single row as an associative array.
     * 
     * @param string $query The SQL query to execute.
     * * *Example:* `'SELECT * FROM table WHERE id = :id'`
     * 
     * @param array|null $params [optional] An associative array of query parameters.
     * InvalidArgumentException will be thrown if any of the array values are not strings or numbers.
     * * *Example:* `['id' => 10]`
     * 
     * @return array|false Returns a single row as an associative array or false if no rows.
     * 
     * @throws PDOException If an error occurs during the query execution.
     * @throws InvalidArgumentException If any of the array values are not strings or numbers.
     */
    public function fetch(string $query, ?array $params = null): array|false
    {
        return $this->execute($query, $params)->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Executes an SQL query and returns rows as associative arrays.
     * 
     * @param string $query The SQL query to execute.
     * * *Example:* `'SELECT * FROM table WHERE category = :category LIMIT :offset, :limit'`
     * 
     * @param array|null $params [optional] An associative array of query parameters.
     * InvalidArgumentException will be thrown if any of the array values are not strings or numbers.
     * * *Example:* `['category' => 'foods', 'limit' => 20, 'offset' => 60]`
     * 
     * @return array An empty array is returned if there are zero results to fetch.
     * 
     * @throws PDOException If an error occurs during the query execution.
     * @throws InvalidArgumentException If any of the array values are not strings or numbers.
     */
    public function fetchAll(string $query, ?array $params = null): array
    {
        return $this->execute($query, $params)->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     *　Executes an SQL query and returns the ID of the last inserted row or sequence value.
     * 
     * @param string $query The SQL query to execute.
     * * *Example:* `'INSERT INTO user (name) SELECT :name'`
     * 
     * @param array|null $params [optional] An associative array of query parameters.
     * InvalidArgumentException will be thrown if any of the array values are not strings or numbers.
     * * *Example:* `['name' => 'mimikyu']`
     * 
     * @return int Returns the row ID of the last row that was inserted into the database.
     * 
     * @throws PDOException If an error occurs during the query execution.
     * @throws InvalidArgumentException If any of the array values are not strings or numbers.
     */
    public function executeAndGetLastInsertId(string $query, ?array $params = null): int
    {
        $this->execute($query, $params);
        return (int) $this->pdo->lastInsertId();
    }

    /**
     * Executes a LIKE search query and returns a PDOStatement object with bound values.
     * 
     * @param callable $query A function that returns a string representing the SQL query. 
     * * *Example:* `fn (string $where): string => "SELECT * FROM table {$where} AND category = :category LIMIT :offset, :limit"`
     * 
     * @param callable $whereClauseQuery A function that returns a string representing the WHERE clause.
     * * *Example:* `fn (int $i): string => "(title LIKE :keyword{$i} OR text LIKE :keyword{$i})"`
     * 
     * @param string $keyword The keyword(s) to search for.
     * InvalidArgumentException will be thrown if the string is empty or only contains whitespace characters.
     * * *Example:* `'Split keywords by whitespace and search with LIKE'`
     * 
     * @param array|null $params [optional] An associative array of query parameters.
     * InvalidArgumentException will be thrown if any of the array values are not strings or numbers.
     * * *Example:* `['category' => 'foods', 'limit' => 20, 'offset' => 60]`
     * 
     * @return array An empty array is returned if there are zero results to search.
     * 
     * @throws PDOException If an error occurs during the query execution.
     * @throws LogicException If any of the given callbacks are invalid.
     * @throws InvalidArgumentException If any of the parameter values are invalid or the given callbacks are invalid.
     */
    public function executeLikeSearchQuery(
        callable $query,
        callable $whereClauseQuery,
        string $keyword,
        ?array $params = null
    ): array {
        $convertedKeyword = $this->escapeLike(
            preg_replace('/　/u', ' ', mb_convert_encoding($keyword, 'UTF-8', 'auto'))
        );

        if (empty(trim($convertedKeyword))) {
            throw new InvalidArgumentException('Please provide a non-empty search keyword.');
        }

        $keywords = explode(' ', $convertedKeyword);

        $whereClause = 'WHERE ';
        for ($i = 0, $size = count($keywords); $i < $size; $i++) {
            if ($i > 0) {
                $whereClause .= ' AND ';
            }

            $whereClauseQueryResult = $whereClauseQuery($i);

            if (!is_string($whereClauseQueryResult)) {
                throw new LogicException('whereClauseQuery must return a string');
            }

            $whereClause .= $whereClauseQueryResult;
        }

        $queryResult = $query($whereClause);

        if (!is_string($queryResult)) {
            throw new LogicException('Query callback must return a string');
        }

        $stmt = $this->pdo->prepare($queryResult);

        if (!preg_match('{:\w+}', $whereClause, $matches)) {
            throw new InvalidArgumentException('Invalid placeholder for WHERE clause.');
        }

        $whereClausePlaceholder = $matches[0];

        foreach ($keywords as $i => $word) {
            $stmt->bindValue($whereClausePlaceholder . (string) $i, "%{$word}%", PDO::PARAM_STR);
        }

        if ($params === null) {
            $stmt->execute();
            return $stmt;
        }

        foreach ($params as $key => $value) {
            if (!is_string($value) && !is_numeric($value)) {
                throw new InvalidArgumentException(
                    "Invalid parameter value for key {$key}: only strings and numbers are allowed."
                );
            }

            $type = is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR;
            $stmt->bindValue($key, $value, $type);
        }

        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);;
    }

    /**
     * Escapes special characters in a string for use in LIKE clause.
     *
     * @param string $value The string to be escaped.
     * @param string $char The escape character to use (defaults to backslash).
     * @return string The escaped string.
     */
    public function escapeLike(string $value, string $char = '\\'): string
    {
        $search  = [$char, '%', '_'];
        $replace = [$char . $char, $char . '%', $char . '_'];

        return str_replace($search, $replace, $value);
    }

    /**
     * Executes a full-text search query and returns the result as an array.
     *
     * @param callable $query A function that returns a string representing the SQL query. 
     * * *Example:* `fn (string $where): string => "SELECT * FROM table {$where} AND category = :category LIMIT :offset, :limit"`
     * 
     * @param string $whereClauseQuery The SQL query with a placeholder for the search keyword.
     * * *Example:* `'WHERE MATCH(title, text) AGAINST(:search IN BOOLEAN MODE)'`
     * 
     * @param string $keyword The search keyword to be used in the full-text search query.
     * InvalidArgumentException will be thrown if the string is empty or only contains whitespace characters.
     * * *Example:* `'Splits keywords by whitespace'`
     * 
     * @param array|null $params [optional] An associative array of query parameters.
     * InvalidArgumentException will be thrown if any of the array values are not strings or numbers.
     * * *Example:* `['category' => 'foods', 'limit' => 20, 'offset' => 60]`
     * 
     * @return array An array of rows returned by the query. An empty array is returned if there are no results to search.
     * 
     * @throws PDOException If an error occurs during the query execution.
     * @throws LogicException If any of the given callbacks are invalid.
     * @throws InvalidArgumentException If the search keyword is empty or the WHERE clause query has an invalid placeholder.
     */
    public function executeFulltextSearchQuery(
        callable $query,
        string $whereClauseQuery,
        string $keyword,
        ?array $params = null
    ): array {
        $convertedKeyword = preg_replace('/　/u', ' ', mb_convert_encoding($keyword, 'UTF-8', 'auto'));

        if (empty(trim($convertedKeyword))) {
            throw new InvalidArgumentException('Please provide a non-empty search keyword.');
        }

        if (!preg_match('{:\w+}', $whereClauseQuery, $matches)) {
            throw new InvalidArgumentException('Invalid placeholder for WHERE clause.');
        }

        $whereClausePlaceholder = $matches[0];

        $params[$whereClausePlaceholder] = '';
        foreach (explode(' ', $convertedKeyword) as $i => $word) {
            if (mb_strlen($word) < 2) {
                $word .= '*';
            }

            if ($i > 0) {
                $params[$whereClausePlaceholder] .= ' ';
            }

            $params[$whereClausePlaceholder] .= '+' . $word;
        }

        $queryResult = $query($whereClauseQuery);

        if (!is_string($queryResult)) {
            throw new LogicException('Query callback must return a string');
        }

        return $this->fetchAll($queryResult, $params);
    }
}
