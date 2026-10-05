<?php

/** Collect real Xdebug hits from manual child-process fixtures, including early exit. */
if (getenv('SITEPULSE_CHILD_COVERAGE') && extension_loaded('xdebug')) {
    $root = dirname(__DIR__, 3);
    xdebug_set_filter(XDEBUG_FILTER_CODE_COVERAGE, XDEBUG_PATH_INCLUDE, array(
        $root . '/class/', $root . '/inc/', $root . '/templates/', $root . '/loader.php', $root . '/uninstall.php',
    ));
    $flags = XDEBUG_CC_UNUSED | XDEBUG_CC_DEAD_CODE;
    if (getenv('SITEPULSE_CHILD_PATH_COVERAGE') === '1') { $flags |= XDEBUG_CC_BRANCH_CHECK; }
    xdebug_start_code_coverage($flags);
    register_shutdown_function(static function () {
        register_shutdown_function(static function () {
            file_put_contents(getenv('SITEPULSE_CHILD_COVERAGE'), json_encode(xdebug_get_code_coverage()));
        });
    });
}

/** Invoke fixtures safely and attach child execution to the current PHPUnit test. */
function sitepulse_test_subprocess(PHPUnit\Framework\TestCase $test, $fixture, array $input, array $ini = array()): array {
    $coverage = $test->getTestResultObject()->getCodeCoverage();
    $coverageFile = $coverage ? tempnam(sys_get_temp_dir(), 'sitepulse-coverage-') : null;
    $previous = getenv('SITEPULSE_CHILD_COVERAGE');
    $previousPathCoverage = getenv('SITEPULSE_CHILD_PATH_COVERAGE');
    $pathCoverage = $coverage && $coverage->collectsBranchAndPathCoverage();
    $command = array(PHP_BINARY);
    $ini = array_merge(array('memory_limit' => ini_get('memory_limit')), $ini);
    foreach ($ini as $name => $value) {
        $command[] = '-d';
        $command[] = $name . '=' . $value;
    }
    if ($coverage) {
        putenv('SITEPULSE_CHILD_COVERAGE=' . $coverageFile);
        putenv('SITEPULSE_CHILD_PATH_COVERAGE=' . ($pathCoverage ? '1' : '0'));
        $command[] = '-d';
        $command[] = 'auto_prepend_file=' . __FILE__;
    }
    $command[] = $fixture;
    $command[] = json_encode($input);
    try {
        $process = proc_open($command, array(0 => array('pipe', 'r'), 1 => array('pipe', 'w'), 2 => array('pipe', 'w')), $pipes);
        if (!is_resource($process)) { throw new RuntimeException('Unable to launch isolated PHP fixture.'); }
        fclose($pipes[0]);
        $output = stream_get_contents($pipes[1]); fclose($pipes[1]);
        $error = stream_get_contents($pipes[2]); fclose($pipes[2]);
        $status = proc_close($process);
        if ($coverage) {
            $raw = json_decode(file_get_contents($coverageFile), true);
            if (!is_array($raw)) { throw new RuntimeException('Child process failed to provide coverage: ' . $error); }
            $data = $pathCoverage
                ? SebastianBergmann\CodeCoverage\RawCodeCoverageData::fromXdebugWithPathCoverage($raw)
                : SebastianBergmann\CodeCoverage\RawCodeCoverageData::fromXdebugWithoutPathCoverage($raw);
            $coverage->append($data, $test);
        }
        return array('status' => $status, 'output' => $output, 'error' => $error);
    } finally {
        $previous === false ? putenv('SITEPULSE_CHILD_COVERAGE') : putenv('SITEPULSE_CHILD_COVERAGE=' . $previous);
        $previousPathCoverage === false ? putenv('SITEPULSE_CHILD_PATH_COVERAGE') : putenv('SITEPULSE_CHILD_PATH_COVERAGE=' . $previousPathCoverage);
        if ($coverageFile && file_exists($coverageFile)) { unlink($coverageFile); }
    }
}
