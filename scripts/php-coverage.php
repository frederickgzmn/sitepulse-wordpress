<?php

require_once __DIR__ . '/lib/php-coverage.php';

$root = dirname(__DIR__);
$pathCoverage = in_array('--path-coverage', $argv, true);
$stem = $pathCoverage ? 'php-branches' : 'php';
$report = $root . '/coverage/' . $stem . '.xml';

try {
    if (!extension_loaded('xdebug') || version_compare(phpversion('xdebug'), '3.0', '<')) {
        throw new RuntimeException('PHP coverage requires Xdebug 3. Install/enable it and run XDEBUG_MODE=coverage composer test:coverage.');
    }
    $modes = getenv('XDEBUG_MODE');
    $modes = $modes === false ? ini_get('xdebug.mode') : $modes;
    if (!in_array('coverage', array_map('trim', explode(',', $modes)), true)) {
        throw new RuntimeException('Xdebug coverage is disabled. Run XDEBUG_MODE=coverage composer test:coverage.');
    }
    if (!is_file($root . '/vendor/bin/phpunit')) { throw new RuntimeException('Run composer install before collecting coverage.'); }
    if (!is_dir($root . '/coverage') && !mkdir($root . '/coverage', 0777, true)) { throw new RuntimeException('Cannot create coverage output directory.'); }
    if (is_file($report) && !unlink($report)) { throw new RuntimeException('Cannot remove the previous coverage report.'); }
    $command = array(PHP_BINARY, '-d', 'memory_limit=' . ini_get('memory_limit'));
    $command = array_merge($command, array($root . '/vendor/bin/phpunit', '--coverage-clover', $report, '--coverage-html', $root . '/coverage/' . $stem));
    if ($pathCoverage) { $command[] = '--path-coverage'; }
    $process = proc_open($command, array(STDIN, STDOUT, STDERR), $pipes, $root);
    if (!is_resource($process)) { throw new RuntimeException('Unable to start PHPUnit.'); }
    $status = proc_close($process);
    if ($status !== 0) { exit($status > 0 ? $status : 1); }
    $result = sitepulse_read_php_coverage($root, $report);
    echo PHP_EOL . 'PHP coverage across ' . $result['files'] . ' production files:' . PHP_EOL;
    foreach (array('lines' => 'Executable lines', 'methods' => 'Fully covered class methods', 'branches' => 'Branches') as $key => $label) {
        $counts = $result[$key];
        if ($key === 'branches' && !$pathCoverage) {
            echo '  Branches: not collected; run composer test:coverage:branches with XDEBUG_MODE=coverage.' . PHP_EOL;
            continue;
        }
        $percentage = $counts['total'] ? sprintf('%.2f%%', 100 * $counts['covered'] / $counts['total']) : 'n/a';
        echo '  ' . $label . ': ' . $counts['covered'] . '/' . $counts['total'] . ' (' . $percentage . ')' . PHP_EOL;
    }
    if ($result['errors']) {
        fwrite(STDERR, 'PHP executable-line coverage must be exactly 100%:' . PHP_EOL . implode(PHP_EOL, $result['errors']) . PHP_EOL);
        exit(1);
    }
    echo 'PHP executable-line coverage gate passed (100%).' . PHP_EOL;
} catch (Throwable $error) {
    fwrite(STDERR, $error->getMessage() . PHP_EOL);
    exit(1);
}
