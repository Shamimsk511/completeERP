<?php
// Standalone maintenance endpoint. Protect with a strong token.
declare(strict_types=1);

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);
set_time_limit(0);
ignore_user_abort(true);
ini_set('output_buffering', '0');
ini_set('zlib.output_compression', '0');
while (ob_get_level() > 0) {
    ob_end_flush();
}
ob_implicit_flush(true);
header('Content-Type: text/plain; charset=UTF-8');
register_shutdown_function(function (): void {
    $error = error_get_last();
    if (!$error) {
        return;
    }
    $fatalTypes = [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR];
    if (in_array($error['type'], $fatalTypes, true)) {
        http_response_code(500);
        echo "FATAL: {$error['message']} in {$error['file']}:{$error['line']}\n";
    }
});

function getEnvValue(string $key, ?string $default = null): ?string
{
    $value = $_ENV[$key] ?? $_SERVER[$key] ?? getenv($key);
    if ($value !== false && $value !== null && $value !== '') {
        return (string) $value;
    }

    $envPath = dirname(__DIR__) . DIRECTORY_SEPARATOR . '.env';
    if (!is_file($envPath) || !is_readable($envPath)) {
        return $default;
    }

    $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if (!is_array($lines)) {
        return $default;
    }

    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#')) {
            continue;
        }
        if (!str_contains($line, '=')) {
            continue;
        }
        [$envKey, $envValue] = explode('=', $line, 2);
        $envKey = trim($envKey);
        if ($envKey !== $key) {
            continue;
        }
        $envValue = trim($envValue);
        if ($envValue !== '' && ($envValue[0] === '"' || $envValue[0] === "'")) {
            $envValue = trim($envValue, "\"'");
        }
        return $envValue;
    }

    return $default;
}

$expectedToken = getEnvValue('DB_SYNC_TOKEN', 'CHANGE_ME_TO_A_LONG_RANDOM_TOKEN');
$providedToken = $_GET['token'] ?? '';

if (!is_string($providedToken) || !hash_equals($expectedToken, $providedToken)) {
    http_response_code(404);
    echo "Not Found\n";
    exit;
}

$action = $_GET['action'] ?? 'migrate';
$seederInput = $_GET['seeder'] ?? $_GET['class'] ?? '';
$seederInput = is_string($seederInput) ? trim($seederInput) : '';
$allowedActions = [
    'migrate',
    'migrate-seed',
    'migrate-refresh',
    'migrate-refresh-seed',
    'migrate-fresh',
    'migrate-fresh-seed',
    'seed',
    'ledger-backfill',
    'ledger-backfill-ui',
    'ledger-backfill-adjustments',
    'ledger-backfill-adjustments-ui',
    'ping',
    'optimize-clear',
    'cache-clear',
    'config-clear',
    'route-clear',
    'view-clear',
];

$backfillFrom = $_GET['from'] ?? null;
$backfillTo = $_GET['to'] ?? null;
$backfillMonth = $_GET['month'] ?? null;
$backfillChunk = $_GET['chunk'] ?? null;
$backfillDryRun = $_GET['dry_run'] ?? null;
$backfillNoProgress = $_GET['no_progress'] ?? null;
$backfillMaxSeconds = $_GET['max_seconds'] ?? null;
$backfillStage = $_GET['stage'] ?? null;
$backfillInvoiceAfter = $_GET['invoice_after'] ?? null;
$backfillPaymentAfter = $_GET['payment_after'] ?? null;
$backfillTransactionAfter = $_GET['transaction_after'] ?? null;
$backfillCustomerAfter = $_GET['customer_after'] ?? null;

if (!in_array($action, $allowedActions, true)) {
    http_response_code(400);
    echo "Invalid action.\n";
    exit;
}

if ($action === 'ping') {
    echo "OK\n";
    exit;
}

if ($action === 'ledger-backfill-ui') {
    header('Content-Type: text/html; charset=UTF-8');

    $baseUrl = strtok($_SERVER['REQUEST_URI'] ?? '', '?') ?: 'secret-db-sync.php';
    $token = htmlspecialchars((string) $providedToken, ENT_QUOTES, 'UTF-8');

    echo "<!doctype html><html><head><meta charset=\"utf-8\"><title>Ledger Backfill</title>";
    echo "<style>body{font-family:Arial,Helvetica,sans-serif;padding:20px;background:#0b0b0b;color:#f1f1f1}button{display:inline-block;margin:6px;padding:10px 14px;background:#1e88e5;color:#fff;text-decoration:none;border-radius:6px;border:none;cursor:pointer}button.secondary{background:#455a64}button.danger{background:#c62828}label{display:inline-block;margin-right:12px}input{margin-left:6px;padding:6px;border-radius:4px;border:1px solid #333;background:#141414;color:#f1f1f1}progress{width:100%;height:18px}pre{white-space:pre-wrap;background:#111;padding:12px;border-radius:6px;border:1px solid #222;max-height:320px;overflow:auto}</style>";
    echo "</head><body>";
    echo "<h1>Ledger Backfill (Month by Month)</h1>";
    echo "<p>DB Sync Target: " . htmlspecialchars((string) getEnvValue('DB_SYNC_TARGET', 'rahmanti_erp'), ENT_QUOTES, 'UTF-8') . "</p>";
    echo "<h2>Range: 2025-05 to current month</h2>";
    echo "<div>";
    echo "<label>Chunk<input id=\"chunk\" type=\"number\" min=\"10\" step=\"10\" value=\"50\"></label>";
    echo "<label>Max seconds<input id=\"maxSeconds\" type=\"number\" min=\"5\" step=\"5\" value=\"15\"></label>";
    echo "<label><input id=\"dryRun\" type=\"checkbox\">Dry run</label>";
    echo "</div>";
    echo "<p><a style=\"color:#90caf9\" href=\"" . htmlspecialchars($baseUrl . "?action=ledger-backfill-adjustments-ui&token={$token}", ENT_QUOTES, 'UTF-8') . "\">Go to Adjustments/Returns Backfill</a></p>";
    echo "<div>";
    echo "<button id=\"startBtn\">Start Backfill</button>";
    echo "<button id=\"stopBtn\" class=\"danger\" disabled>Stop</button>";
    echo "<button id=\"clearBtn\" class=\"secondary\">Clear Log</button>";
    echo "</div>";
    echo "<p id=\"status\">Idle.</p>";
    echo "<progress id=\"progress\" value=\"0\" max=\"1\"></progress>";
    echo "<pre id=\"log\"></pre>";

    $baseUrlJson = json_encode($baseUrl);
    $tokenJson = json_encode($token);
    $script = <<<'JS'
(function() {
    const baseUrl = __BASE_URL__;
    const token = __TOKEN__;
    const startMonth = '2025-05';
    const maxLoopsPerMonth = 500;
    let stopRequested = false;

    const logEl = document.getElementById('log');
    const statusEl = document.getElementById('status');
    const progressEl = document.getElementById('progress');
    const startBtn = document.getElementById('startBtn');
    const stopBtn = document.getElementById('stopBtn');
    const clearBtn = document.getElementById('clearBtn');

    function appendLog(text) {
        logEl.textContent += text + '\n';
        logEl.scrollTop = logEl.scrollHeight;
    }

    function getMonthRange(start) {
        const months = [];
        const parts = start.split('-').map(Number);
        const sy = parts[0];
        const sm = parts[1];
        const now = new Date();
        const endY = now.getFullYear();
        const endM = now.getMonth() + 1;
        let y = sy;
        let m = sm;
        while (y < endY || (y === endY && m <= endM)) {
            const mm = String(m).padStart(2, '0');
            months.push(String(y) + '-' + mm);
            m++;
            if (m > 12) { m = 1; y++; }
        }
        return months;
    }

    function buildUrl(params) {
        const url = new URL(baseUrl, window.location.origin);
        Object.entries(params).forEach(([k, v]) => {
            if (v !== null && v !== undefined && v !== '') {
                url.searchParams.set(k, v);
            }
        });
        return url.toString();
    }

    async function runMonth(month, chunk, maxSeconds, dryRun) {
        let resume = null;
        let loop = 0;
        do {
            if (stopRequested) return;
            loop++;
            if (loop > maxLoopsPerMonth) {
                appendLog('Stopped: too many resume loops for ' + month);
                return;
            }
            const params = {
                action: 'ledger-backfill',
                token: token,
                month: month,
                chunk: chunk,
                max_seconds: maxSeconds
            };
            if (dryRun) params.dry_run = 1;
            if (resume) {
                params.stage = resume.stage;
                params.invoice_after = resume.invoice_after;
                params.payment_after = resume.payment_after;
                params.customer_after = resume.customer_after;
            }
            const url = buildUrl(params);
            const resp = await fetch(url, { cache: 'no-store' });
            const text = await resp.text();
            appendLog('--- ' + month + ' (loop ' + loop + ') ---');
            appendLog(text.trim());
            if (!resp.ok) {
                appendLog('HTTP ' + resp.status);
                return;
            }
            const match = text.match(/RESUME stage=(\w+) invoice_after=(\d+) payment_after=(\d+) customer_after=(\d+)/);
            if (match) {
                resume = {
                    stage: match[1],
                    invoice_after: match[2],
                    payment_after: match[3],
                    customer_after: match[4]
                };
            } else {
                resume = null;
            }
        } while (resume && !stopRequested);
    }

    async function start() {
        stopRequested = false;
        startBtn.disabled = true;
        stopBtn.disabled = false;
        const chunk = document.getElementById('chunk').value || 50;
        const maxSeconds = document.getElementById('maxSeconds').value || 15;
        const dryRun = document.getElementById('dryRun').checked;
        const months = getMonthRange(startMonth);
        progressEl.max = months.length;
        progressEl.value = 0;
        appendLog('Starting backfill ' + startMonth + ' -> now (' + months.length + ' months)');

        for (let i = 0; i < months.length; i++) {
            if (stopRequested) break;
            const month = months[i];
            statusEl.textContent = 'Running ' + month + ' (' + (i + 1) + '/' + months.length + ')';
            await runMonth(month, chunk, maxSeconds, dryRun);
            progressEl.value = i + 1;
        }

        if (stopRequested) {
            statusEl.textContent = 'Stopped.';
            appendLog('Stopped by user.');
        } else {
            statusEl.textContent = 'Done.';
            appendLog('Backfill complete.');
        }
        stopBtn.disabled = true;
        startBtn.disabled = false;
    }

    startBtn.addEventListener('click', start);
    stopBtn.addEventListener('click', function () { stopRequested = true; });
    clearBtn.addEventListener('click', function () { logEl.textContent = ''; });
})();
JS;
    $script = str_replace(['__BASE_URL__', '__TOKEN__'], [$baseUrlJson, $tokenJson], $script);
    echo "<script>{$script}</script>";

    echo "</body></html>";
    exit;
}

if ($action === 'ledger-backfill-adjustments-ui') {
    header('Content-Type: text/html; charset=UTF-8');

    $baseUrl = strtok($_SERVER['REQUEST_URI'] ?? '', '?') ?: 'secret-db-sync.php';
    $token = htmlspecialchars((string) $providedToken, ENT_QUOTES, 'UTF-8');

    echo "<!doctype html><html><head><meta charset=\"utf-8\"><title>Ledger Adjustments Backfill</title>";
    echo "<style>body{font-family:Arial,Helvetica,sans-serif;padding:20px;background:#0b0b0b;color:#f1f1f1}button{display:inline-block;margin:6px;padding:10px 14px;background:#1e88e5;color:#fff;text-decoration:none;border-radius:6px;border:none;cursor:pointer}button.secondary{background:#455a64}button.danger{background:#c62828}label{display:inline-block;margin-right:12px}input{margin-left:6px;padding:6px;border-radius:4px;border:1px solid #333;background:#141414;color:#f1f1f1}progress{width:100%;height:18px}pre{white-space:pre-wrap;background:#111;padding:12px;border-radius:6px;border:1px solid #222;max-height:320px;overflow:auto}</style>";
    echo "</head><body>";
    echo "<h1>Ledger Adjustments/Returns Backfill</h1>";
    echo "<p>DB Sync Target: " . htmlspecialchars((string) getEnvValue('DB_SYNC_TARGET', 'rahmanti_erp'), ENT_QUOTES, 'UTF-8') . "</p>";
    echo "<h2>Range: 2025-05 to current month</h2>";
    echo "<div>";
    echo "<label>Chunk<input id=\"chunk\" type=\"number\" min=\"10\" step=\"10\" value=\"50\"></label>";
    echo "<label>Max seconds<input id=\"maxSeconds\" type=\"number\" min=\"5\" step=\"5\" value=\"15\"></label>";
    echo "<label><input id=\"dryRun\" type=\"checkbox\">Dry run</label>";
    echo "</div>";
    echo "<p><a style=\"color:#90caf9\" href=\"" . htmlspecialchars($baseUrl . "?action=ledger-backfill-ui&token={$token}", ENT_QUOTES, 'UTF-8') . "\">Go to Invoice/Payment Backfill</a></p>";
    echo "<div>";
    echo "<button id=\"startBtn\">Start Backfill</button>";
    echo "<button id=\"stopBtn\" class=\"danger\" disabled>Stop</button>";
    echo "<button id=\"clearBtn\" class=\"secondary\">Clear Log</button>";
    echo "</div>";
    echo "<p id=\"status\">Idle.</p>";
    echo "<progress id=\"progress\" value=\"0\" max=\"1\"></progress>";
    echo "<pre id=\"log\"></pre>";

    $baseUrlJson = json_encode($baseUrl);
    $tokenJson = json_encode($token);
    $script = <<<'JS'
(function() {
    const baseUrl = __BASE_URL__;
    const token = __TOKEN__;
    const startMonth = '2025-05';
    const maxLoopsPerMonth = 500;
    let stopRequested = false;

    const logEl = document.getElementById('log');
    const statusEl = document.getElementById('status');
    const progressEl = document.getElementById('progress');
    const startBtn = document.getElementById('startBtn');
    const stopBtn = document.getElementById('stopBtn');
    const clearBtn = document.getElementById('clearBtn');

    function appendLog(text) {
        logEl.textContent += text + '\n';
        logEl.scrollTop = logEl.scrollHeight;
    }

    function getMonthRange(start) {
        const months = [];
        const parts = start.split('-').map(Number);
        const sy = parts[0];
        const sm = parts[1];
        const now = new Date();
        const endY = now.getFullYear();
        const endM = now.getMonth() + 1;
        let y = sy;
        let m = sm;
        while (y < endY || (y === endY && m <= endM)) {
            const mm = String(m).padStart(2, '0');
            months.push(String(y) + '-' + mm);
            m++;
            if (m > 12) { m = 1; y++; }
        }
        return months;
    }

    function buildUrl(params) {
        const url = new URL(baseUrl, window.location.origin);
        Object.entries(params).forEach(([k, v]) => {
            if (v !== null && v !== undefined && v !== '') {
                url.searchParams.set(k, v);
            }
        });
        return url.toString();
    }

    async function runMonth(month, chunk, maxSeconds, dryRun) {
        let resume = null;
        let loop = 0;
        do {
            if (stopRequested) return;
            loop++;
            if (loop > maxLoopsPerMonth) {
                appendLog('Stopped: too many resume loops for ' + month);
                return;
            }
            const params = {
                action: 'ledger-backfill-adjustments',
                token: token,
                month: month,
                chunk: chunk,
                max_seconds: maxSeconds
            };
            if (dryRun) params.dry_run = 1;
            if (resume) {
                params.stage = resume.stage;
                params.transaction_after = resume.transaction_after;
                params.customer_after = resume.customer_after;
            }
            const url = buildUrl(params);
            const resp = await fetch(url, { cache: 'no-store' });
            const text = await resp.text();
            appendLog('--- ' + month + ' (loop ' + loop + ') ---');
            appendLog(text.trim());
            if (!resp.ok) {
                appendLog('HTTP ' + resp.status);
                return;
            }
            const match = text.match(/RESUME stage=(\w+) transaction_after=(\d+) customer_after=(\d+)/);
            if (match) {
                resume = {
                    stage: match[1],
                    transaction_after: match[2],
                    customer_after: match[3]
                };
            } else {
                resume = null;
            }
        } while (resume && !stopRequested);
    }

    async function start() {
        stopRequested = false;
        startBtn.disabled = true;
        stopBtn.disabled = false;
        const chunk = document.getElementById('chunk').value || 50;
        const maxSeconds = document.getElementById('maxSeconds').value || 15;
        const dryRun = document.getElementById('dryRun').checked;
        const months = getMonthRange(startMonth);
        progressEl.max = months.length;
        progressEl.value = 0;
        appendLog('Starting adjustments backfill ' + startMonth + ' -> now (' + months.length + ' months)');

        for (let i = 0; i < months.length; i++) {
            if (stopRequested) break;
            const month = months[i];
            statusEl.textContent = 'Running ' + month + ' (' + (i + 1) + '/' + months.length + ')';
            await runMonth(month, chunk, maxSeconds, dryRun);
            progressEl.value = i + 1;
        }

        if (stopRequested) {
            statusEl.textContent = 'Stopped.';
            appendLog('Stopped by user.');
        } else {
            statusEl.textContent = 'Done.';
            appendLog('Backfill complete.');
        }
        stopBtn.disabled = true;
        startBtn.disabled = false;
    }

    startBtn.addEventListener('click', start);
    stopBtn.addEventListener('click', function () { stopRequested = true; });
    clearBtn.addEventListener('click', function () { logEl.textContent = ''; });
})();
JS;
    $script = str_replace(['__BASE_URL__', '__TOKEN__'], [$baseUrlJson, $tokenJson], $script);
    echo "<script>{$script}</script>";

    echo "</body></html>";
    exit;
}

try {
    require __DIR__ . '/../vendor/autoload.php';
    $app = require __DIR__ . '/../bootstrap/app.php';
    $kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
    $kernel->bootstrap();
} catch (Throwable $e) {
    http_response_code(500);
    echo "BOOT ERROR: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString();
    exit;
}

$seederClass = null;
if ($seederInput !== '') {
    if (!preg_match('/^[A-Za-z_\\\\][A-Za-z0-9_\\\\]*$/', $seederInput)) {
        http_response_code(400);
        echo "Invalid seeder class name.\n";
        exit;
    }

    $seederClass = str_contains($seederInput, '\\')
        ? $seederInput
        : 'Database\\Seeders\\' . $seederInput;

    if (!class_exists($seederClass)) {
        http_response_code(400);
        echo "Seeder class not found: {$seederClass}\n";
        exit;
    }

    if (!is_subclass_of($seederClass, Illuminate\Database\Seeder::class)) {
        http_response_code(400);
        echo "Seeder class is not a valid Laravel seeder: {$seederClass}\n";
        exit;
    }
}

// Force target database for this run (default: rahmanti_erp)
$targetDb = getEnvValue('DB_SYNC_TARGET', 'rahmanti_erp');
if ($targetDb !== '') {
    putenv("DB_DATABASE={$targetDb}");
    $app['config']->set('database.connections.mysql.database', $targetDb);
}

echo "DB: " . $app['config']->get('database.connections.mysql.database') . "\n";
echo "Action: {$action}\n\n";
if ($seederClass !== null) {
    echo "Seeder: {$seederClass}\n\n";
}

if (is_string($backfillMonth) && preg_match('/^\d{4}-\d{2}$/', $backfillMonth)) {
    try {
        $monthDate = new DateTimeImmutable($backfillMonth . '-01');
        $backfillFrom = $monthDate->format('Y-m-d');
        $backfillTo = $monthDate->modify('last day of this month')->format('Y-m-d');
    } catch (Throwable $e) {
        // Ignore invalid month input; fallback to provided from/to
    }
}

try {
    $exitCode = null;
    switch ($action) {
        case 'migrate':
            $exitCode = $kernel->call('migrate', ['--force' => true]);
            break;
        case 'migrate-seed':
            $options = ['--force' => true, '--seed' => true];
            if ($seederClass !== null) {
                $options['--seeder'] = $seederClass;
            }
            $exitCode = $kernel->call('migrate', $options);
            break;
        case 'migrate-refresh':
            $exitCode = $kernel->call('migrate:refresh', ['--force' => true]);
            break;
        case 'migrate-refresh-seed':
            $options = ['--force' => true, '--seed' => true];
            if ($seederClass !== null) {
                $options['--seeder'] = $seederClass;
            }
            $exitCode = $kernel->call('migrate:refresh', $options);
            break;
        case 'migrate-fresh':
            $exitCode = $kernel->call('migrate:fresh', ['--force' => true]);
            break;
        case 'migrate-fresh-seed':
            $options = ['--force' => true, '--seed' => true];
            if ($seederClass !== null) {
                $options['--seeder'] = $seederClass;
            }
            $exitCode = $kernel->call('migrate:fresh', $options);
            break;
        case 'seed':
            $options = ['--force' => true];
            if ($seederClass !== null) {
                $options['--class'] = $seederClass;
            }
            $exitCode = $kernel->call('db:seed', $options);
            break;
        case 'ledger-backfill':
            $options = [];
            if (is_string($backfillFrom) && $backfillFrom !== '') {
                $options['--from'] = $backfillFrom;
            }
            if (is_string($backfillTo) && $backfillTo !== '') {
                $options['--to'] = $backfillTo;
            }
            if (is_string($backfillStage) && $backfillStage !== '') {
                $options['--stage'] = $backfillStage;
            }
            if (is_string($backfillInvoiceAfter) && $backfillInvoiceAfter !== '') {
                $options['--invoice-after'] = $backfillInvoiceAfter;
            }
            if (is_string($backfillPaymentAfter) && $backfillPaymentAfter !== '') {
                $options['--payment-after'] = $backfillPaymentAfter;
            }
            if (is_string($backfillCustomerAfter) && $backfillCustomerAfter !== '') {
                $options['--customer-after'] = $backfillCustomerAfter;
            }
            if (is_string($backfillChunk) && $backfillChunk !== '') {
                $options['--chunk'] = $backfillChunk;
            }
            if (is_string($backfillMaxSeconds) && $backfillMaxSeconds !== '') {
                $options['--max-seconds'] = $backfillMaxSeconds;
            }
            if ($backfillDryRun === '1' || $backfillDryRun === 'true') {
                $options['--dry-run'] = true;
            }
            if ($backfillNoProgress === '1' || $backfillNoProgress === 'true') {
                $options['--no-progress'] = true;
            }
            $exitCode = $kernel->call('ledger:backfill', $options);
            break;
        case 'ledger-backfill-adjustments':
            $options = [];
            if (is_string($backfillFrom) && $backfillFrom !== '') {
                $options['--from'] = $backfillFrom;
            }
            if (is_string($backfillTo) && $backfillTo !== '') {
                $options['--to'] = $backfillTo;
            }
            if (is_string($backfillStage) && $backfillStage !== '') {
                $options['--stage'] = $backfillStage;
            }
            if (is_string($backfillTransactionAfter) && $backfillTransactionAfter !== '') {
                $options['--transaction-after'] = $backfillTransactionAfter;
            }
            if (is_string($backfillCustomerAfter) && $backfillCustomerAfter !== '') {
                $options['--customer-after'] = $backfillCustomerAfter;
            }
            if (is_string($backfillChunk) && $backfillChunk !== '') {
                $options['--chunk'] = $backfillChunk;
            }
            if (is_string($backfillMaxSeconds) && $backfillMaxSeconds !== '') {
                $options['--max-seconds'] = $backfillMaxSeconds;
            }
            if ($backfillDryRun === '1' || $backfillDryRun === 'true') {
                $options['--dry-run'] = true;
            }
            if ($backfillNoProgress === '1' || $backfillNoProgress === 'true') {
                $options['--no-progress'] = true;
            }
            $exitCode = $kernel->call('ledger:backfill-adjustments', $options);
            break;
        case 'optimize-clear':
            $exitCode = $kernel->call('optimize:clear');
            break;
        case 'cache-clear':
            $exitCode = $kernel->call('cache:clear');
            break;
        case 'config-clear':
            $exitCode = $kernel->call('config:clear');
            break;
        case 'route-clear':
            $exitCode = $kernel->call('route:clear');
            break;
        case 'view-clear':
            $exitCode = $kernel->call('view:clear');
            break;
        default:
            http_response_code(400);
            echo "Unsupported action.\n";
            exit;
    }

    echo $kernel->output();
    if ($exitCode !== null) {
        echo "\nExit code: {$exitCode}\n";
    }
} catch (Throwable $e) {
    http_response_code(500);
    echo "ERROR: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString();
}
