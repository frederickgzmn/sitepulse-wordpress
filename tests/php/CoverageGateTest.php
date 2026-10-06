<?php

require_once dirname(__DIR__, 2) . '/scripts/lib/php-coverage.php';

final class CoverageGateTest extends PHPUnit\Framework\TestCase {
    private $directory;

    protected function setUp(): void {
        $this->directory = sys_get_temp_dir() . '/sitepulse-gate-' . bin2hex(random_bytes(8));
        mkdir($this->directory);
        foreach (array('class', 'inc', 'templates') as $directory) { mkdir($this->directory . '/' . $directory); }
        foreach ($this->sources() as $file) { file_put_contents($this->directory . '/' . $file, '<?php echo 1;'); }
    }

    protected function tearDown(): void {
        $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($this->directory, FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::CHILD_FIRST);
        foreach ($files as $file) { $file->isDir() ? rmdir($file->getPathname()) : unlink($file->getPathname()); }
        rmdir($this->directory);
    }

    private function sources(): array {
        return array('class/example.php', 'inc/example.php', 'templates/example.php', 'loader.php', 'uninstall.php');
    }

    private function report(array $overrides = array(), array $omitted = array()): string {
        $xml = '<coverage><project>';
        foreach ($this->sources() as $source) {
            if (in_array($source, $omitted, true)) { continue; }
            $metrics = array_merge(array('statements' => 1, 'coveredstatements' => 1, 'methods' => 1, 'coveredmethods' => 1, 'conditionals' => 2, 'coveredconditionals' => 1), $overrides[$source] ?? array());
            $xml .= '<file name="' . htmlspecialchars($this->directory . '/' . $source, ENT_QUOTES, 'UTF-8') . '"><metrics';
            foreach ($metrics as $name => $value) { $xml .= ' ' . $name . '="' . $value . '"'; }
            $xml .= '/></file>';
        }
        $report = $this->directory . '/clover.xml';
        file_put_contents($report, $xml . '</project></coverage>');
        return $report;
    }

    public function test_complete_line_coverage_passes_without_requiring_complete_branch_coverage(): void {
        $result = sitepulse_read_php_coverage($this->directory, $this->report());
        $this->assertSame(array(), $result['errors']);
        $this->assertSame(array('covered' => 5, 'total' => 5), $result['lines']);
        $this->assertSame(array('covered' => 5, 'total' => 5), $result['methods']);
        $this->assertSame(array('covered' => 5, 'total' => 10), $result['branches']);
        $this->assertSame(5, $result['files']);
    }

    public function test_one_uncovered_line_fails_even_if_rounded_percentage_would_be_one_hundred(): void {
        $result = sitepulse_read_php_coverage($this->directory, $this->report(array('loader.php' => array('statements' => 100000, 'coveredstatements' => 99999))));
        $this->assertSame(array('loader.php: 99999/100000 executable lines covered'), $result['errors']);
    }

    public function test_an_omitted_source_file_cannot_make_a_partial_report_pass(): void {
        $result = sitepulse_read_php_coverage($this->directory, $this->report(array(), array('uninstall.php')));
        $this->assertSame(array('Missing production file: uninstall.php'), $result['errors']);
    }

    public function test_nested_new_production_files_are_included_in_the_inventory(): void {
        mkdir($this->directory . '/templates/partials');
        file_put_contents($this->directory . '/templates/partials/new.php', '<?php echo 2;');
        $result = sitepulse_read_php_coverage($this->directory, $this->report());
        $this->assertSame(array('Missing production file: templates/partials/new.php'), $result['errors']);
    }

    public function test_a_missing_report_fails_instead_of_reusing_other_artifacts(): void {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Coverage report was not generated');
        sitepulse_read_php_coverage($this->directory, $this->directory . '/missing.xml');
    }

    public function test_malformed_xml_is_rejected(): void {
        $report = $this->directory . '/clover.xml';
        file_put_contents($report, '<coverage>');
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Invalid Clover coverage report');
        sitepulse_read_php_coverage($this->directory, $report);
    }

    public function test_missing_metrics_are_rejected_instead_of_counted_as_zero(): void {
        $report = $this->directory . '/clover.xml';
        file_put_contents($report, '<coverage><project><file name="' . $this->directory . '/loader.php"/></project></coverage>');
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Missing Clover metrics');
        sitepulse_read_php_coverage($this->directory, $report);
    }

    public function test_an_empty_report_cannot_pass_as_zero_of_zero_covered(): void {
        $report = $this->directory . '/clover.xml';
        file_put_contents($report, '<coverage><project/></coverage>');
        $result = sitepulse_read_php_coverage($this->directory, $report);
        $this->assertContains('No executable PHP lines were reported.', $result['errors']);
        $this->assertCount(6, $result['errors']);
    }
}
