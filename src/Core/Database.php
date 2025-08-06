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
        error_log("Database::getAllData - Reading from: {$filePath}");
        
        if (!file_exists($filePath)) {
            error_log("Database::getAllData - File does not exist!");
            return [];
        }
        
        $content = file_get_contents($filePath);
        $data = json_decode($content, true) ?: [];
        
        error_log("Database::getAllData - Records found: " . count($data));
        
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
        
        // Debug para verificar o que está sendo salvo
        error_log("Database::insert - Tabela: {$table}, ID: {$id}");
        error_log("Database::insert - Dados: " . json_encode($data));
        
        $tableData[$id] = $data;
        $this->saveTable($table, $tableData);
        
        // Verificar se o dado foi salvo corretamente
        $savedData = $this->getTable($table);
        if (isset($savedData[$id])) {
            error_log("Database::insert - Registro salvo com sucesso");
        } else {
            error_log("Database::insert - ERRO: Registro não foi salvo!");
        }
        
        return $id;
    }

    public function update(string $table, string $id, array $data): bool
    {
        $this->logMessage("Database::update - Iniciando atualização - Tabela: {$table}, ID: {$id}");
        $this->logMessage("Database::update - Dados para atualizar: " . json_encode($data));
        
        $tableData = $this->getTable($table);
        
        if (!isset($tableData[$id])) {
            $this->logMessage("Database::update - ERRO: Registro não encontrado para ID: {$id}");
            return false;
        }

        $this->logMessage("Database::update - Registro encontrado. Dados atuais: " . json_encode($tableData[$id]));

        $data['updated_at'] = date('Y-m-d H:i:s');
        $tableData[$id] = array_merge($tableData[$id], $data);
        
        $this->logMessage("Database::update - Dados após merge: " . json_encode($tableData[$id]));
        
        $this->saveTable($table, $tableData);
        
        // Verificar se a atualização foi salva
        $verificationData = $this->getTable($table);
        if (isset($verificationData[$id])) {
            $this->logMessage("Database::update - Verificação: workflow_stage = " . ($verificationData[$id]['workflow_stage'] ?? 'não definido'));
        }
        
        $this->logMessage("Database::update - Atualização concluída com sucesso");
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

    public function getTable(string $table): array
    {
        $filePath = $this->dataPath . $table . '.json';
        
        // Usar error_log em vez de echo para debug
        error_log("Database::getTable - Reading from: {$filePath}");
        
        if (!file_exists($filePath)) {
            error_log("Database::getTable - File does not exist!");
            return [];
        }
        
        // Limpar cache do arquivo para garantir dados atualizados
        clearstatcache(true, $filePath);
        
        $content = file_get_contents($filePath);
        if (!$content) {
            error_log("Database::getTable - Empty or unreadable file: {$filePath}");
            return [];
        }
        
        $data = json_decode($content, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            error_log("Database::getTable - JSON decode error: " . json_last_error_msg());
            $data = [];
        } else {
            $data = $data ?: [];
        }
        
        error_log("Database::getTable - Records found: " . count($data));
        
        // Garantir que todos os registros tenham o ID correto
        foreach ($data as $id => &$record) {
            if (!isset($record['id'])) {
                $record['id'] = $id;
            }
        }
        
        return $data;
    }

    private function saveTable(string $table, array $data): void
    {
        $filePath = $this->dataPath . $table . '.json';
        $this->logMessage("Database::saveTable - Salvando em: {$filePath}");
        $this->logMessage("Database::saveTable - Número de registros: " . count($data));
        
        $jsonContent = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        
        if ($jsonContent === false) {
            $this->logMessage("Database::saveTable - ERRO: Falha ao codificar JSON - " . json_last_error_msg());
            return;
        }
        
        $bytesWritten = file_put_contents($filePath, $jsonContent);
        
        if ($bytesWritten === false) {
            $this->logMessage("Database::saveTable - ERRO: Falha ao escrever arquivo");
        } else {
            $this->logMessage("Database::saveTable - Arquivo salvo com sucesso. Bytes escritos: {$bytesWritten}");
        }
    }

    private function generateId(): string
    {
        return uniqid('', true);
    }

    private function logMessage(string $message): void
    {
        $logFile = dirname(__DIR__, 2) . '/database-debug.log';
        $timestamp = date('Y-m-d H:i:s');
        file_put_contents($logFile, "[$timestamp] $message\n", FILE_APPEND);
    }
}
