<?php

namespace App\Core;

class Database
{
    private string $dataPath;

    public function __construct()
    {
        $config = require __DIR__ . '/../../config/app.php';
        $this->dataPath = $config['database']['path'];
        
        if (!is_dir($this->dataPath)) {
            mkdir($this->dataPath, 0755, true);
        }
    }

    public function find(string $table, string $id): ?array
    {
        $data = $this->getTable($table);
        return $data[$id] ?? null;
    }

    public function findBy(string $table, string $field, $value): ?array
    {
        $data = $this->getTable($table);
        foreach ($data as $item) {
            if (isset($item[$field]) && $item[$field] === $value) {
                return $item;
            }
        }
        return null;
    }

    public function findAll(string $table, array $criteria = []): array
    {
        $data = $this->getTable($table);
        
        if (empty($criteria)) {
            return $data;
        }

        $filtered = [];
        foreach ($data as $id => $item) {
            $match = true;
            foreach ($criteria as $field => $value) {
                if (!isset($item[$field]) || $item[$field] !== $value) {
                    $match = false;
                    break;
                }
            }
            if ($match) {
                $filtered[$id] = $item;
            }
        }

        return $filtered;
    }

    public function insert(string $table, array $data): string
    {
        $tableData = $this->getTable($table);
        $id = $data['id'] ?? $this->generateId();
        $data['id'] = $id;
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['updated_at'] = date('Y-m-d H:i:s');
        
        $tableData[$id] = $data;
        $this->saveTable($table, $tableData);
        
        return $id;
    }

    public function update(string $table, string $id, array $data): bool
    {
        $tableData = $this->getTable($table);
        
        if (!isset($tableData[$id])) {
            return false;
        }

        $data['updated_at'] = date('Y-m-d H:i:s');
        $tableData[$id] = array_merge($tableData[$id], $data);
        $this->saveTable($table, $tableData);
        
        return true;
    }

    public function delete(string $table, string $id): bool
    {
        $tableData = $this->getTable($table);
        
        if (!isset($tableData[$id])) {
            return false;
        }

        unset($tableData[$id]);
        $this->saveTable($table, $tableData);
        
        return true;
    }

    private function getTable(string $table): array
    {
        $filePath = $this->dataPath . $table . '.json';
        
        if (!file_exists($filePath)) {
            return [];
        }

        $content = file_get_contents($filePath);
        return json_decode($content, true) ?: [];
    }

    private function saveTable(string $table, array $data): void
    {
        $filePath = $this->dataPath . $table . '.json';
        file_put_contents($filePath, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }

    private function generateId(): string
    {
        return uniqid('', true);
    }
}
