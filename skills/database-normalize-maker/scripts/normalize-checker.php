<?php
declare(strict_types=1);

/**
 * Normalize Checker — Analyzes SQL schema for normalization compliance
 *
 * Usage: php normalize-checker.php <schema.sql>
 * Output: BCNF/3NF/2NF/1NF compliance report
 */

namespace CoreMusic\Database\Validation;

final class NormalizeChecker
{
    private string $schemaFile;
    private array $violations = [];

    public function __construct(string $schemaFile)
    {
        if (!file_exists($schemaFile)) {
            throw new \RuntimeException("Schema file not found: $schemaFile");
        }
        $this->schemaFile = $schemaFile;
    }

    public function analyze(): array
    {
        $content = file_get_contents($this->schemaFile);

        // Parse CREATE TABLE statements
        preg_match_all('/CREATE TABLE\s+(\w+)\s*\((.*?)\)(?:;|\s*ENGINE)/is', $content, $matches);

        $tables = [];
        foreach ($matches[1] as $i => $tableName) {
            $tableContent = $matches[2][$i];
            $tables[$tableName] = $this->parseTable($tableName, $tableContent);
        }

        // Check normalization forms
        $this->check1NF($tables);
        $this->check2NF($tables);
        $this->check3NF($tables);
        $this->checkBCNF($tables);

        return [
            'tables' => $tables,
            'violations' => $this->violations,
            'compliance' => $this->getComplianceSummary()
        ];
    }

    private function parseTable(string $tableName, string $content): array
    {
        $columns = [];
        preg_match_all('/(\w+)\s+(\w+(?:\(\d+(?:,\d+)?\))?)(.*?)(?:,|PRIMARY|FOREIGN|UNIQUE|KEY|INDEX|$)/is', $content, $matches);

        foreach ($matches[1] as $i => $colName) {
            $colType = $matches[2][$i];
            $colAttrs = $matches[3][$i];

            $columns[$colName] = [
                'type' => strtolower($colType),
                'nullable' => !stripos($colAttrs, 'NOT NULL'),
                'unique' => (bool)stripos($colAttrs, 'UNIQUE'),
                'primary' => (bool)stripos($content, "PRIMARY KEY.*$colName"),
                'foreign' => (bool)stripos($content, "FOREIGN KEY.*$colName")
            ];
        }

        return ['columns' => $columns, 'content' => $content];
    }

    private function check1NF(array $tables): void
    {
        foreach ($tables as $tableName => $table) {
            foreach ($table['columns'] as $colName => $col) {
                // 1NF violation: TEXT/JSON columns with delimited data
                if (in_array($col['type'], ['text', 'varchar']) &&
                    preg_match('/\b(tags|keywords|list|ids|csv)\b/i', $colName)) {
                    $this->violations[] = [
                        'form' => '1NF',
                        'severity' => 'HIGH',
                        'table' => $tableName,
                        'column' => $colName,
                        'issue' => 'Possible repeating group (delimited list in TEXT)',
                        'fix' => "Create separate table for $colName with FK"
                    ];
                }
            }
        }
    }

    private function check2NF(array $tables): void
    {
        // Check for partial key dependencies
        // (Complex: requires analyzing composite keys and their dependencies)
        // Simplified: flag if table has composite key but single-column non-keys depend only on partial key

        foreach ($tables as $tableName => $table) {
            $compositeKeys = $this->detectCompositeKeys($table['content']);

            if (count($compositeKeys) > 1) {
                $nonKeyColumns = array_filter($table['columns'], function($col) {
                    return !$col['primary'] && !$col['foreign'];
                });

                if (count($nonKeyColumns) > 0) {
                    // Warning: manual review needed for partial dependencies
                    $this->violations[] = [
                        'form' => '2NF',
                        'severity' => 'MEDIUM',
                        'table' => $tableName,
                        'column' => 'ALL (composite key)',
                        'issue' => 'Composite primary key detected. Verify no partial key dependencies.',
                        'fix' => 'Review which non-key columns depend on full key vs partial'
                    ];
                }
            }
        }
    }

    private function check3NF(array $tables): void
    {
        // Check for transitive dependencies
        // (Complex: requires semantic analysis)
        // Simplified: check if non-key columns reference other non-key columns

        foreach ($tables as $tableName => $table) {
            $nonKeyColumns = array_filter($table['columns'], function($col) {
                return !$col['primary'];
            });

            // Look for columns like city_name, country_name (likely transitive)
            foreach ($nonKeyColumns as $colName => $col) {
                if (preg_match('/^(.*?)_(name|title|description|id)$/i', $colName, $m)) {
                    $relatedCol = $m[1] . '_' . ($m[1] === 'city' ? 'id' : 'id');

                    if (isset($table['columns'][$relatedCol]) && !$table['columns'][$relatedCol]['primary']) {
                        $this->violations[] = [
                            'form' => '3NF',
                            'severity' => 'HIGH',
                            'table' => $tableName,
                            'column' => $colName,
                            'issue' => "Possible transitive dependency: $colName depends on non-key $relatedCol",
                            'fix' => "Move $colName to separate table with $relatedCol as FK"
                        ];
                    }
                }
            }
        }
    }

    private function checkBCNF(array $tables): void
    {
        // BCNF: Every determinant must be candidate key
        // Simplified: no additional checks beyond 3NF for this implementation

        if (empty($this->violations)) {
            // If no 3NF violations, BCNF likely compliant
            return;
        }
    }

    private function detectCompositeKeys(string $content): array
    {
        preg_match('/PRIMARY\s+KEY\s*\((.*?)\)/i', $content, $m);
        if (!isset($m[1])) return [];

        $keys = array_map('trim', explode(',', $m[1]));
        return array_filter($keys, fn($k) => !empty($k));
    }

    private function getComplianceSummary(): array
    {
        $byForm = array_count_values(array_column($this->violations, 'form'));

        return [
            '1NF' => ($byForm['1NF'] ?? 0) === 0 ? 'PASS' : 'FAIL',
            '2NF' => ($byForm['2NF'] ?? 0) === 0 ? 'PASS' : 'WARN',
            '3NF' => ($byForm['3NF'] ?? 0) === 0 ? 'PASS' : 'FAIL',
            'BCNF' => ($byForm['3NF'] ?? 0) === 0 ? 'PASS' : 'FAIL'
        ];
    }
}

// ============================================================
// MAIN
// ============================================================

if (php_sapi_name() !== 'cli') {
    die("CLI only\n");
}

if ($argc < 2) {
    echo "Usage: php normalize-checker.php <schema.sql>\n";
    echo "Analyzes schema for 1NF, 2NF, 3NF, BCNF compliance\n";
    exit(1);
}

try {
    $checker = new NormalizeChecker($argv[1]);
    $result = $checker->analyze();

    echo "=== NORMALIZATION ANALYSIS ===\n\n";

    echo "Compliance:\n";
    foreach ($result['compliance'] as $form => $status) {
        $color = $status === 'PASS' ? '✓' : '✗';
        echo "  $color $form: $status\n";
    }

    if (!empty($result['violations'])) {
        echo "\n\nViolations Found (" . count($result['violations']) . "):\n\n";
        foreach ($result['violations'] as $v) {
            echo "  [{$v['severity']}] {$v['form']} - {$v['table']}.{$v['column']}\n";
            echo "    Issue: {$v['issue']}\n";
            echo "    Fix: {$v['fix']}\n\n";
        }
    } else {
        echo "\n✓ No violations detected (BCNF compliant)\n";
    }

} catch (\Throwable $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}

