<?php

/** Read exact Clover counters and require every production PHP file in the report. */
function sitepulse_read_php_coverage(string $root, string $report): array {
    if (!is_file($report)) { throw new RuntimeException('Coverage report was not generated: ' . $report); }
    $root = realpath($root);
    if ($root === false) { throw new RuntimeException('Production source directory does not exist.'); }
    $xml = new DOMDocument();
    $previous = libxml_use_internal_errors(true);
    try {
        $valid = $xml->load($report, LIBXML_NONET);
    } finally {
        libxml_clear_errors();
        libxml_use_internal_errors($previous);
    }
    if (!$valid || $xml->doctype !== null || $xml->documentElement->tagName !== 'coverage') {
        throw new RuntimeException('Invalid Clover coverage report: ' . $report);
    }
    $expected = array();
    foreach (array('class', 'inc', 'templates') as $directory) {
        $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root . '/' . $directory, FilesystemIterator::SKIP_DOTS));
        foreach ($files as $file) {
            if ($file->isFile() && $file->getExtension() === 'php') { $expected[$file->getRealPath()] = true; }
        }
    }
    foreach (array('loader.php', 'uninstall.php') as $file) {
        $path = realpath($root . '/' . $file);
        if ($path === false) { throw new RuntimeException('Production source file does not exist: ' . $file); }
        $expected[$path] = true;
    }
    ksort($expected);
    $result = array(
        'files' => 0,
        'lines' => array('covered' => 0, 'total' => 0),
        'methods' => array('covered' => 0, 'total' => 0),
        'branches' => array('covered' => 0, 'total' => 0),
        'errors' => array(),
    );
    $seen = array();
    $xpath = new DOMXPath($xml);
    foreach ($xpath->query('/coverage/project//file') as $file) {
        $path = realpath($file->getAttribute('name'));
        if ($path === false || !isset($expected[$path])) {
            $result['errors'][] = 'Unexpected source file in coverage report: ' . $file->getAttribute('name');
            continue;
        }
        if (isset($seen[$path])) { throw new RuntimeException('Duplicate file in Clover report: ' . $path); }
        $seen[$path] = true;
        $result['files']++;
        $metrics = $xpath->query('metrics', $file)->item(0);
        if (!$metrics) { throw new RuntimeException('Missing Clover metrics: ' . $path); }
        $counts = array();
        foreach (array('lines' => 'statements', 'methods' => 'methods', 'branches' => 'conditionals') as $label => $attribute) {
            foreach (array('total' => $attribute, 'covered' => 'covered' . $attribute) as $kind => $name) {
                $value = $metrics->getAttribute($name);
                if ($value === '' || !ctype_digit($value)) { throw new RuntimeException('Invalid Clover counter ' . $name . ': ' . $path); }
                $counts[$label][$kind] = (int) $value;
                $result[$label][$kind] += (int) $value;
            }
            if ($counts[$label]['covered'] > $counts[$label]['total']) { throw new RuntimeException('Covered Clover counter exceeds total: ' . $path); }
        }
        if ($counts['lines']['covered'] !== $counts['lines']['total']) {
            $result['errors'][] = substr($path, strlen($root) + 1) . ': ' . $counts['lines']['covered'] . '/' . $counts['lines']['total'] . ' executable lines covered';
        }
    }
    foreach (array_diff_key($expected, $seen) as $path => $_) {
        $result['errors'][] = 'Missing production file: ' . substr($path, strlen($root) + 1);
    }
    if ($result['lines']['total'] === 0) { $result['errors'][] = 'No executable PHP lines were reported.'; }
    return $result;
}
