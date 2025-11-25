<?php
/**
 * Base Model Class
 */

namespace App\Core;

use App\Core\Database;

abstract class Model
{
    protected $db;
    protected $table;
    protected $primaryKey = 'id';

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Find record by ID
     * 
     * @param int $id
     * @return array|null
     */
    public function find($id)
    {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE {$this->primaryKey} = :id");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch() ?: null;
    }

    /**
     * Find all records
     * 
     * @param array $conditions
     * @param string $orderBy
     * @param int $limit
     * @param int $offset
     * @return array
     */
    public function findAll($conditions = [], $orderBy = '', $limit = null, $offset = null)
    {
        $sql = "SELECT * FROM {$this->table}";
        $params = [];

        if (!empty($conditions)) {
            $where = [];
            foreach ($conditions as $key => $value) {
                $where[] = "{$key} = :{$key}";
                $params[$key] = $value;
            }
            $sql .= " WHERE " . implode(' AND ', $where);
        }

        if ($orderBy) {
            $sql .= " ORDER BY {$orderBy}";
        }

        if ($limit) {
            $sql .= " LIMIT {$limit}";
            if ($offset) {
                $sql .= " OFFSET {$offset}";
            }
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Create record
     * 
     * @param array $data
     * @return int Inserted ID
     */
    public function create($data)
    {
        // Comprehensive list of SQL reserved words that need escaping
        $reservedWords = [
            'order', 'values', 'group', 'select', 'table', 'where', 'from', 'join', 
            'key', 'index', 'user', 'database', 'schema', 'column', 'constraint',
            'default', 'primary', 'foreign', 'references', 'trigger', 'procedure',
            'function', 'view', 'grant', 'revoke', 'alter', 'create', 'drop',
            'insert', 'update', 'delete', 'truncate', 'union', 'all', 'distinct',
            'having', 'limit', 'offset', 'as', 'on', 'using', 'inner', 'left',
            'right', 'outer', 'cross', 'natural', 'case', 'when', 'then', 'else',
            'end', 'if', 'exists', 'in', 'like', 'between', 'is', 'null', 'not',
            'and', 'or', 'xor', 'div', 'mod', 'interval', 'match', 'against',
            'fulltext', 'boolean', 'enum', 'set', 'auto_increment', 'charset',
            'collate', 'comment', 'engine', 'temporary', 'if', 'exists'
        ];
        
        $fields = array_keys($data);
        $escapedFields = array_map(function($field) use ($reservedWords) {
            $fieldLower = strtolower($field);
            return in_array($fieldLower, $reservedWords) ? "`{$field}`" : $field;
        }, $fields);
        
        $values = ':' . implode(', :', $fields);
        $fieldsStr = implode(', ', $escapedFields);

        $sql = "INSERT INTO `{$this->table}` ({$fieldsStr}) VALUES ({$values})";
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute($data);
            return $this->db->lastInsertId();
        } catch (\PDOException $e) {
            // Log the SQL for debugging
            \App\Core\Logger::error('SQL INSERT Error', [
                'sql' => $sql,
                'table' => $this->table,
                'data_keys' => array_keys($data),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * Update record
     * 
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function update($id, $data)
    {
        // Comprehensive list of SQL reserved words that need escaping
        $reservedWords = [
            'order', 'values', 'group', 'select', 'table', 'where', 'from', 'join', 
            'key', 'index', 'user', 'database', 'schema', 'column', 'constraint',
            'default', 'primary', 'foreign', 'references', 'trigger', 'procedure',
            'function', 'view', 'grant', 'revoke', 'alter', 'create', 'drop',
            'insert', 'update', 'delete', 'truncate', 'union', 'all', 'distinct',
            'having', 'limit', 'offset', 'as', 'on', 'using', 'inner', 'left',
            'right', 'outer', 'cross', 'natural', 'case', 'when', 'then', 'else',
            'end', 'if', 'exists', 'in', 'like', 'between', 'is', 'null', 'not',
            'and', 'or', 'xor', 'div', 'mod', 'interval', 'match', 'against',
            'fulltext', 'boolean', 'enum', 'set', 'auto_increment', 'charset',
            'collate', 'comment', 'engine', 'temporary', 'if', 'exists'
        ];
        
        $set = [];
        foreach ($data as $key => $value) {
            // Escape column names that are reserved words
            $keyLower = strtolower($key);
            $escapedKey = in_array($keyLower, $reservedWords) ? "`{$key}`" : $key;
            $set[] = "{$escapedKey} = :{$key}";
        }
        $set = implode(', ', $set);

        $data['id'] = $id;
        $sql = "UPDATE `{$this->table}` SET {$set} WHERE `{$this->primaryKey}` = :id";
        
        try {
            $stmt = $this->db->prepare($sql);
            return $stmt->execute($data);
        } catch (\PDOException $e) {
            // Log the SQL for debugging
            \App\Core\Logger::error('SQL UPDATE Error', [
                'sql' => $sql,
                'table' => $this->table,
                'id' => $id,
                'data_keys' => array_keys($data),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * Delete record
     * 
     * @param int $id
     * @return bool
     */
    public function delete($id)
    {
        $stmt = $this->db->prepare("DELETE FROM {$this->table} WHERE {$this->primaryKey} = :id");
        return $stmt->execute(['id' => $id]);
    }

    /**
     * Count records
     * 
     * @param array $conditions
     * @return int
     */
    public function count($conditions = [])
    {
        $sql = "SELECT COUNT(*) as count FROM {$this->table}";
        $params = [];

        if (!empty($conditions)) {
            $where = [];
            foreach ($conditions as $key => $value) {
                $where[] = "{$key} = :{$key}";
                $params[$key] = $value;
            }
            $sql .= " WHERE " . implode(' AND ', $where);
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch();
        return (int)$result['count'];
    }
}

