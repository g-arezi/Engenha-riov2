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
    
    // Method to get all data from a table without filtering
    public function getAllData(string $table): array
    {
        $filePath = $this->dataPath . $table . '.json';
        
        // Verificação extra para debug
        echo "<!-- DEBUG Database::getAllData - Reading from: {$filePath} -->\n";
        
        if (!file_exists($filePath)) {
            echo "<!-- DEBUG Database::getAllData - File does not exist! -->\n";
            return [];
        }
        
        $content = file_get_contents($filePath);
        $data = json_decode($content, true) ?: [];
        
        echo "<!-- DEBUG Database::getAllData - Records found: " . count($data) . " -->\n";
        
        return $data;
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
        
        // Log for debugging
        error_log("Database::findAll - Table: {$table}, Total records: " . count($data) . ", Criteria: " . json_encode($criteria));
        
        // Log all records IDs for debugging
        $allIds = array_keys($data);
        error_log("All records IDs: " . implode(", ", $allIds));
        
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

        // Log filtered records IDs for debugging
        $filteredIds = array_keys($filtered);
        error_log("Filtered records IDs: " . implode(", ", $filteredIds));
        error_log("Database::findAll - Filtered records: " . count($filtered));
        return $filtered;
    }

    public function insert(string $table, array $data): string
    {
        $tableData = $this->getTable($table);
        
        // Se for a tabela projects e o ID já foi definido, use-o
        if ($table === 'projects' && isset($data['id'])) {
            $id = $data['id'];
        } else {
            // Para outras tabelas ou se o ID não foi definido
            $id = $data['id'] ?? $this->generateId();
        }
        
        $data['id'] = $id;
        
        if (!isset($data['created_at'])) {
            $data['created_at'] = date('Y-m-d H:i:s');
        }
        
        if (!isset($data['updated_at'])) {
            $data['updated_at'] = date('Y-m-d H:i:s');
        }
        
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
        
        echo "<!-- DEBUG Database::getTable - Reading from: {$filePath} -->\n";
        
        if (!file_exists($filePath)) {
            echo "<!-- DEBUG Database::getTable - File does not exist! -->\n";
            return [];
        }

        $content = file_get_contents($filePath);
        $data = json_decode($content, true) ?: [];
        
        echo "<!-- DEBUG Database::getTable - Records found: " . count($data) . " -->\n";
        
        return $data;
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
