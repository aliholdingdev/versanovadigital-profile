<?php
declare(strict_types=1);

/**
 * Schema to Diagram — Converts SQL schema to Mermaid ER diagram
 *
 * Usage: php schema-to-diagram.php <schema.sql> [output.md]
 * Output: Mermaid ER diagram in markdown
 */

namespace CoreMusic\Database\Diagram;

final class SchemaToDiagram
{
    private string $schemaFile;
    private array $tables = [];
    private array $relationships = [];

    public function __construct(string $schemaFile)
    {
        if (!file_exists($schemaFile)) {
            throw new \RuntimeException("Schema file not found: $schemaFile");
        }
        $this->schemaFile = $schemaFile;
    }

    public function generate(): string
    {
        $content = file_get_contents($this->schemaFile);

        $this->parseTables($content);
        $this->parseRelationships($content);

        return $this->buildMermaidDiagram();
    }

    private function parseTables(string $content): void
    {
        preg_match_all(
            '/CREATE TABLE\s+(?:IF NOT EXISTS\s+)?(\w+)\s*\((.*?)\)\s*(?:ENGINE|;|$)/is',
            $content,
            $matches,
            PREG_SET_ORDER
        );

        foreach ($matches as $match) {
            $tableName = $match[1];
            $tableBody = $match[2];

            $columns = $this->parseColumns($tableBody);
            $this->tables[$tableName] = [
                'name' => $tableName,
                'columns' => $columns
            ];
        }
    }

    private function parseColumns(string $tableBody): array
    {
        $columns = [];

        preg_match_all(
            '/(\w+)\s+(\w+(?:\(\d+(?:,\d+)?\))?)(.*?)(?:,|PRIMARY|FOREIGN|UNIQUE|KEY|INDEX|CHECK|$)/is',
            $tableBody,
            $matches,
            PREG_SET_ORDER
        );

        foreach ($matches as $match) {
            $colName = $match[1];
            $colType = $match[2];
            $colAttrs = $match[3];

            // Skip constraint lines that aren't actual columns
            if (in_array(strtoupper($colName), ['PRIMARY', 'FOREIGN', 'UNIQUE', 'KEY', 'INDEX', 'CONSTRAINT', 'CHECK'])) {
                continue;
            }

            $isPK = stripos($colAttrs, 'PRIMARY') !== false || stripos($tableBody, "PRIMARY KEY.*$colName") !== false;
            $isFK = stripos($colAttrs, 'FOREIGN') !== false;
            $isUnique = stripos($colAttrs, 'UNIQUE') !== false;

            $columns[$colName] = [
                'name' => $colName,
                'type' => strtoupper($colType),
                'isPK' => $isPK,
                'isFK' => $isFK,
                'isUnique' => $isUnique
            ];
        }

        return $columns;
    }

    private function parseRelationships(string $content): void
    {
        preg_match_all(
            '/FOREIGN KEY\s*\((\w+)\)\s*REFERENCES\s+(\w+)\s*\((\w+)\)/i',
            $content,
            $matches,
            PREG_SET_ORDER
        );

        foreach ($matches as $match) {
            $this->relationships[] = [
                'from_col' => $match[1],
                'to_table' => $match[2],
                'to_col' => $match[3]
            ];
        }
    }

    private function buildMermaidDiagram(): string
    {
        $md = "# ER Diagram (Mermaid)\n\n";
        $md .= "```mermaid\nerDiagram\n";

        // Add entities
        foreach ($this->tables as $table) {
            $md .= "    " . strtoupper($table['name']) . " {\n";

            foreach ($table['columns'] as $col) {
                $attrs = '';
                if ($col['isPK']) $attrs .= ' PK';
                if ($col['isFK']) $attrs .= ' FK';
                if ($col['isUnique']) $attrs .= ' UK';

                $md .= "        {$col['type']} {$col['name']}{$attrs}\n";
            }

            $md .= "    }\n";
        }

        // Add relationships
        foreach ($this->relationships as $rel) {
            // Find source table
            $sourceTable = null;
            foreach ($this->tables as $table) {
                if (isset($table['columns'][$rel['from_col']])) {
                    $sourceTable = $table['name'];
                    break;
                }
            }

            if ($sourceTable) {
                $md .= "    " . strtoupper($sourceTable) . " ||--o{ " . strtoupper($rel['to_table']) . " : has\n";
            }
        }

        $md .= "```\n\n";

        return $md;
    }
}

// ============================================================
// MAIN
// ============================================================

if (php_sapi_name() !== 'cli') {
    die("CLI only\n");
}

if ($argc < 2) {
    echo "Usage: php schema-to-diagram.php <schema.sql> [output.md]\n";
    echo "Generates Mermaid ER diagram from SQL schema\n";
    exit(1);
}

try {
    $generator = new SchemaToDiagram($argv[1]);
    $diagram = $generator->generate();

    if ($argc >= 3) {
        file_put_contents($argv[2], $diagram);
        echo "ER diagram written to: " . $argv[2] . "\n";
    } else {
        echo $diagram;
    }

} catch (\Throwable $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}

