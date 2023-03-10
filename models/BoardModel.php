<?php

class BoardModel
{
    /**
     * @param array $value `['offset' => , 'limit' => ]`
     * @return array `[['id' => , 'text' => , 'user' => ]]`
     */
    public function get(array $value): array
    {
        return DB::fetchAll('SELECT id, time, text FROM board LIMIT :offset, :limit', $value);
    }

    /**
     * @param array $value `['text' => , 'user' => ]`
     * @return int Row count
     */
    public function write(array $value): int
    {
        return DB::execute('INSERT INTO board (text, user) VALUES (:text, :user)', $value)->rowCount();
    }
}
