<?php

declare(strict_types=1);

namespace App\Core;

use PDO;
use PDOException;

final class PostgresStore implements StoreInterface
{
    private readonly string $qualifiedTable;
    private readonly string $schemaName;
    private readonly string $tableName;

    public function __construct(
        private readonly PDO $pdo,
        string $schema = 'public',
        string $table = 'app_collections',
    ) {
        $this->schemaName = $this->sanitizeIdentifier($schema);
        $this->tableName = $this->sanitizeIdentifier($table);
        $this->qualifiedTable = sprintf('"%s"."%s"', $this->schemaName, $this->tableName);
        $this->ensureSchema($this->schemaName);
    }

    public function exists(string $collection): bool
    {
        $statement = $this->pdo->prepare(sprintf('SELECT 1 FROM %s WHERE name = :name LIMIT 1', $this->qualifiedTable));
        $statement->execute(['name' => $collection]);

        return (bool) $statement->fetchColumn();
    }

    public function read(string $collection, array $fallback = []): array
    {
        $statement = $this->pdo->prepare(sprintf('SELECT payload::text FROM %s WHERE name = :name LIMIT 1', $this->qualifiedTable));
        $statement->execute(['name' => $collection]);
        $payload = $statement->fetchColumn();

        if (! is_string($payload) || trim($payload) === '') {
            return $fallback;
        }

        $decoded = json_decode($payload, true);

        return is_array($decoded) ? $decoded : $fallback;
    }

    public function write(string $collection, array $data): void
    {
        $payload = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        if ($payload === false) {
            throw new PDOException('Nao foi possivel serializar os dados para PostgreSQL.');
        }

        $statement = $this->pdo->prepare(
            sprintf(
                'INSERT INTO %s (name, payload, updated_at) VALUES (:name, CAST(:payload AS jsonb), CURRENT_TIMESTAMP)
                 ON CONFLICT (name)
                 DO UPDATE SET payload = EXCLUDED.payload, updated_at = CURRENT_TIMESTAMP',
                $this->qualifiedTable
            )
        );
        $statement->execute([
            'name' => $collection,
            'payload' => $payload,
        ]);
    }

    public function nextId(array $records): int
    {
        $max = 0;

        foreach ($records as $record) {
            $max = max($max, (int) ($record['id'] ?? 0));
        }

        return $max + 1;
    }

    public function driver(): string
    {
        return 'postgresql';
    }

    public function importCollections(array $collections): void
    {
        $this->pdo->beginTransaction();

        try {
            foreach ($collections as $collection => $payload) {
                $this->write((string) $collection, is_array($payload) ? $payload : []);
            }

            $this->pdo->commit();
        } catch (\Throwable $exception) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }

            throw $exception;
        }
    }

    public function collectionNames(): array
    {
        $statement = $this->pdo->query(sprintf('SELECT name FROM %s ORDER BY name ASC', $this->qualifiedTable));

        if ($statement === false) {
            return [];
        }

        return array_map('strval', $statement->fetchAll(PDO::FETCH_COLUMN) ?: []);
    }

    private function ensureSchema(string $schema): void
    {
        $quotedSchema = sprintf('"%s"', $schema);
        $this->pdo->exec(sprintf('CREATE SCHEMA IF NOT EXISTS %s', $quotedSchema));

        $statement = $this->pdo->prepare(
            'SELECT 1 FROM information_schema.tables WHERE table_schema = :schema AND table_name = :table LIMIT 1'
        );
        $statement->execute([
            'schema' => $this->schemaName,
            'table' => $this->tableName,
        ]);

        if ($statement->fetchColumn()) {
            return;
        }

        try {
            $this->pdo->exec(
                sprintf(
                    "CREATE TABLE %s (
                        name VARCHAR(120) PRIMARY KEY,
                        payload JSONB NOT NULL DEFAULT '[]'::jsonb,
                        updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
                    )",
                    $this->qualifiedTable
                )
            );
        } catch (PDOException $exception) {
            if (! in_array((string) $exception->getCode(), ['23505', '42P07'], true)) {
                throw $exception;
            }
        }
    }

    private function sanitizeIdentifier(string $value): string
    {
        if (! preg_match('/^[A-Za-z_][A-Za-z0-9_]*$/', $value)) {
            throw new PDOException('Identificador PostgreSQL invalido.');
        }

        return $value;
    }
}
