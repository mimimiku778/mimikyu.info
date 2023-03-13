<?php

class BoardModel
{
    /**
     * @param array $value `['offset' => , 'limit' => ]`
     * @return array `[['id' => , 'text' => , 'user' => ]]`
     */
    public function get(array $value): array
    {
        return DB::fetchAll(
            'SELECT id, time, text FROM board ORDER BY id DESC LIMIT :offset, :limit',
            $value
        );
    }

    /**
     * @param array $value `['text' => , 'user' => ]`
     */
    public function write(array $value)
    {
        DB::execute(
            'INSERT INTO board (text, ip, ua) VALUES (:text, :ip, :ua)',
            $value
        );
    }

    /**
     * @return int Record count
     */
    public function getRecordCount(): int
    {
        //return (int) DB::execute(
        //    'SELECT TABLE_ROWS FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME = "board"'
        //)->fetchColumn();

        return (int) DB::execute('SELECT count(*) FROM board')->fetchColumn();
    }

    public function limitInterval(): bool
    {
        $query = 'SELECT COUNT(*) FROM board WHERE ip = :ip AND time > DATE_SUB(NOW(), INTERVAL :interval MINUTE)';

        $requestCount = (int) DB::execute(
            $query,
            ['ip' => ($_SERVER["REMOTE_ADDR"] ?? ''), 'interval' => DatabaseConfig::BOARD_INTERVAL]
        )->fetchColumn();

        return $requestCount < DatabaseConfig::BOARD_MAX_REQUESTS;
    }
}
