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
     * @return int Row count
     */
    public function write(array $value)
    {
        return DB::execute(
            'INSERT INTO board (text, user) VALUES (:text, :user)',
            $value
        );
    }

    /**
     * @return int Record count
     */
    public function getRecordCount(): int
    {
        return (int) DB::execute(
            'SELECT TABLE_ROWS FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME = "board"'
        )->fetchColumn();

        //return (int) DB::execute(
        //    'SELECT count(*) FROM board'
        //)->fetchColumn();
    }
}
