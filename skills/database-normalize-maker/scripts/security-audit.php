<?php
declare(strict_types=1);

/**
 * Security Audit — Analyzes SQL schema for OWASP + encryption compliance
 *
 * Usage: php security-audit.php <schema.sql>
 * Output: OWASP Top10:2025 compliance report
 */

namespace CoreMusic\Database\Security;

final class SecurityAudit
{
    private string $schemaFile;
    private array $findings = [];

    public function __construct(string $schemaFile)
    {
        if (!file_exists($schemaFile)) {
            throw new \RuntimeException("Schema file not found: $schemaFile");
        }
        $this->schemaFile = $schemaFile;
    }

    public function audit(): array
    {
        $content = file_get_contents($this->schemaFile);

        $this->checkPrimaryKeys($content);
        $this->checkForeignKeys($content);
        $this->checkEncryption($content);
        $this->checkAuditTrail($content);
        $this->checkConstraints($content);
        $this->checkHardcodedSecrets($content);
        $this->checkOWASP($content);

        return $this->getSummary();
    }

    private function checkPrimaryKeys(string $content): void
    {
        preg_match_all('/CREATE TABLE\s+(\w+)/i', $content, $m);
        $tables = $m[1];

        foreach ($tables as $table) {
            if (!preg_match("/CREATE TABLE\s+$table\s*\((.*?PRIMARY KEY)/is", $content)) {
                $this->findings[] = [
                    'severity' => 'HIGH',
                    'check' => 'Primary Key',
                    'table' => $table,
                    'issue' => 'Missing PRIMARY KEY',
                    'fix' => 'Add PRIMARY KEY constraint',
                    'owasp' => 'A08:2025 Data Integrity'
                ];
            }
        }
    }

    private function checkForeignKeys(string $content): void
    {
        preg_match_all('/\b(\w+_id)\b.*?(?:VARCHAR|INT)/i', $content, $m);
        $fkCandidates = array_unique($m[1]);

        foreach ($fkCandidates as $fk) {
            if (!preg_match("/FOREIGN KEY.*$fk/i", $content)) {
                $this->findings[] = [
                    'severity' => 'MEDIUM',
                    'check' => 'Foreign Key',
                    'column' => $fk,
                    'issue' => 'FK column without FOREIGN KEY constraint',
                    'fix' => "Add FOREIGN KEY constraint for $fk",
                    'owasp' => 'A08:2025 Data Integrity'
                ];
            }
        }
    }

    private function checkEncryption(string $content): void
    {
        $piiPatterns = [
            'email' => 'Email address',
            'phone' => 'Phone number',
            'ssn' => 'Social Security Number',
            'password' => 'Password (should be hashed)',
            'credit_card' => 'Credit card (should be tokenized)',
            'health' => 'Health information (must encrypt)',
            'medical' => 'Medical data (must encrypt)'
        ];

        preg_match_all('/(\w+)\s+(?:VARCHAR|TEXT|BLOB)/i', $content, $m);
        $columns = $m[1];

        foreach ($columns as $col) {
            foreach ($piiPatterns as $pattern => $desc) {
                if (stripos($col, $pattern) !== false) {
                    if (!preg_match("/$col.*(?:encrypted|hash|token)/i", $content)) {
                        $this->findings[] = [
                            'severity' => 'CRITICAL',
                            'check' => 'Encryption',
                            'column' => $col,
                            'issue' => "$desc not marked as encrypted",
                            'fix' => "Add comment: /* encrypted_aes_256_gcm */ or use schema-level encryption",
                            'owasp' => 'A02:2025 Cryptographic Failures'
                        ];
                    }
                }
            }
        }
    }

    private function checkAuditTrail(string $content): void
    {
        preg_match_all('/CREATE TABLE\s+(\w+)/i', $content, $m);
        $tables = array_filter($m[1], fn($t) => $t !== 'audit' && !stripos($t, 'audit'));

        foreach ($tables as $table) {
            $hasCreatedBy = (bool)preg_match("/$table.*created_by/i", $content);
            $hasCreatedAt = (bool)preg_match("/$table.*created_at/i", $content);
            $hasUpdatedAt = (bool)preg_match("/$table.*updated_at/i", $content);

            if (!($hasCreatedBy && $hasCreatedAt && $hasUpdatedAt)) {
                $this->findings[] = [
                    'severity' => 'HIGH',
                    'check' => 'Audit Trail',
                    'table' => $table,
                    'issue' => 'Missing audit columns (created_by, created_at, updated_at)',
                    'fix' => 'Add audit columns to enable tracking',
                    'owasp' => 'A09:2025 Logging & Monitoring'
                ];
            }
        }

        if (!preg_match('/CREATE TABLE.*audit/i', $content)) {
            $this->findings[] = [
                'severity' => 'MEDIUM',
                'check' => 'Audit Trail',
                'issue' => 'No dedicated audit table for immutable logs',
                'fix' => 'Create audit table with (id, table_name, action, changed_by, changed_at)',
                'owasp' => 'A09:2025 Logging & Monitoring'
            ];
        }
    }

    private function checkConstraints(string $content): void
    {
        // Check for NOT NULL on critical columns
        $criticalColumns = ['email', 'username', 'password', 'id'];

        foreach ($criticalColumns as $col) {
            if (preg_match("/$col\s+\w+(?!.*NOT NULL)/i", $content)) {
                $this->findings[] = [
                    'severity' => 'MEDIUM',
                    'check' => 'Constraints',
                    'column' => $col,
                    'issue' => "Missing NOT NULL constraint on critical column $col",
                    'fix' => "Add NOT NULL to $col column definition",
                    'owasp' => 'A04:2025 Insecure Design'
                ];
            }
        }

        // Check for CHECK constraints on status fields
        if (preg_match('/status\s+VARCHAR/i', $content) && !preg_match('/CHECK.*status/i', $content)) {
            $this->findings[] = [
                'severity' => 'MEDIUM',
                'check' => 'Constraints',
                'issue' => 'Status column missing CHECK constraint',
                'fix' => "Add CHECK (status IN ('pending', 'active', ...)) to validate values",
                'owasp' => 'A04:2025 Insecure Design'
            ];
        }
    }

    private function checkHardcodedSecrets(string $content): void
    {
        $secretPatterns = [
            'password\s*=\s*[\'"]' => 'Hardcoded password',
            'key\s*=\s*[\'"]' => 'Hardcoded encryption key',
            'secret\s*=\s*[\'"]' => 'Hardcoded secret',
            'token\s*=\s*[\'"]' => 'Hardcoded token',
            'api_key.*=.*[\'"]' => 'Hardcoded API key'
        ];

        foreach ($secretPatterns as $pattern => $desc) {
            if (preg_match("/$pattern/i", $content)) {
                $this->findings[] = [
                    'severity' => 'CRITICAL',
                    'check' => 'Hardcoded Secrets',
                    'issue' => $desc,
                    'fix' => 'Use environment variables (.env) instead',
                    'owasp' => 'A05:2025 Security Misconfiguration'
                ];
            }
        }
    }

    private function checkOWASP(string $content): void
    {
        // A01:2025 - Broken Access Control
        if (!preg_match('/role|permission|rbac|grant/i', $content)) {
            $this->findings[] = [
                'severity' => 'HIGH',
                'check' => 'OWASP A01:2025',
                'issue' => 'No role-based access control schema detected',
                'fix' => 'Add roles, permissions, and user_role tables',
                'owasp' => 'A01:2025 Broken Access Control'
            ];
        }

        // A07:2025 - Identification & Authentication Failures
        if (!preg_match('/password_hash|password.*argon|password.*bcrypt/i', $content)) {
            $this->findings[] = [
                'severity' => 'HIGH',
                'check' => 'OWASP A07:2025',
                'issue' => 'Password column name unclear (should be password_hash)',
                'fix' => 'Use password_hash column with Argon2id in application',
                'owasp' => 'A07:2025 Authentication Failures'
            ];
        }
    }

    private function getSummary(): array
    {
        $bySeverity = array_count_values(array_column($this->findings, 'severity'));
        $byOWASP = array_count_values(array_column($this->findings, 'owasp'));

        return [
            'findings' => $this->findings,
            'summary' => [
                'total_findings' => count($this->findings),
                'critical' => $bySeverity['CRITICAL'] ?? 0,
                'high' => $bySeverity['HIGH'] ?? 0,
                'medium' => $bySeverity['MEDIUM'] ?? 0,
                'by_owasp' => $byOWASP,
                'compliance_status' => (($bySeverity['CRITICAL'] ?? 0) + ($bySeverity['HIGH'] ?? 0)) === 0 ? 'PASS' : 'FAIL'
            ]
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
    echo "Usage: php security-audit.php <schema.sql>\n";
    echo "Analyzes schema for OWASP Top 10:2025 + encryption compliance\n";
    exit(1);
}

try {
    $audit = new SecurityAudit($argv[1]);
    $result = $audit->audit();

    echo "=== SECURITY AUDIT REPORT ===\n\n";

    echo "Overall Status: " . $result['summary']['compliance_status'] . "\n";
    echo "Critical: " . $result['summary']['critical'] . "\n";
    echo "High: " . $result['summary']['high'] . "\n";
    echo "Medium: " . $result['summary']['medium'] . "\n";

    if (!empty($result['findings'])) {
        echo "\n\nFindings:\n\n";
        foreach ($result['findings'] as $f) {
            echo "[{$f['severity']}] {$f['check']}\n";
            echo "  Issue: {$f['issue']}\n";
            echo "  OWASP: {$f['owasp']}\n";
            echo "  Fix: {$f['fix']}\n\n";
        }
    } else {
        echo "\n✓ No security findings\n";
    }

} catch (\Throwable $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}

