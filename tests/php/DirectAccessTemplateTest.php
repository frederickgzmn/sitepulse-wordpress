<?php
require_once __DIR__ . '/Support/SubprocessCoverage.php';
final class DirectAccessTemplateTest extends Sitepulse_Test_Case {
    public static function files(): array {
        $files = array('class/backend.php');
        foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator(dirname(__DIR__, 2) . '/templates', FilesystemIterator::SKIP_DOTS)) as $file) {
            if ($file->getExtension() === 'php') { $files[] = 'templates/' . substr($file->getPathname(), strlen(dirname(__DIR__, 2) . '/templates/')); }
        }
        return array_map(static function ($file) { return array($file); }, $files);
    }
    /** @dataProvider files */
    public function test_php_views_refuse_direct_access_without_wordpress($file): void {
        $result = sitepulse_test_subprocess($this, __DIR__ . '/fixtures/direct-access.php', array('file' => $file));
        $this->assertSame(array('status' => 0, 'output' => '', 'error' => ''), $result, $file);
    }
}
