<?php

require_once dirname(__DIR__, 2) . '/scripts/lib/plugin-version.php';

final class VersionBumpTest extends PHPUnit\Framework\TestCase {
    private const LOADER = "<?php\n/**\n * Plugin Name:       SitePulse\n * Version:           1.4.6\n */\ndefine( 'SITEPULSE_VERSION',               '1.4.6' );\ndefine( 'SITEPULSE_OTHER', '1.4.6' );\n";
    private const README = "=== SitePulse ===\nStable tag: 1.4.6\n\n== Changelog ==\n= 1.4.7 =\n* Fixed: recent warnings.\n\n= 1.4.6 =\n* New: Page Analysis.\n";
    private const POT = "msgid \"\"\nmsgstr \"\"\n\"Project-Id-Version: SitePulse - Performance Monitor and AI Diagnostics 1.4.6\\n\"\n\"X-Domain: sitepulse\\n\"\n";

    private $directory;

    protected function setUp(): void {
        $this->directory = sys_get_temp_dir() . '/sitepulse-version-' . bin2hex(random_bytes(8));
        mkdir($this->directory . '/languages', 0777, true);
        mkdir($this->directory . '/scripts/lib', 0777, true);
        $this->write(array('loader.php' => self::LOADER, 'readme.txt' => self::README, 'languages/sitepulse.pot' => self::POT));
    }

    protected function tearDown(): void {
        $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($this->directory, FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::CHILD_FIRST);
        foreach ($files as $file) { $file->isDir() ? rmdir($file->getPathname()) : unlink($file->getPathname()); }
        rmdir($this->directory);
    }

    private function write(array $files): void {
        foreach ($files as $file => $text) { file_put_contents($this->directory . '/' . $file, $text); }
    }

    private function read(string $file): string {
        return file_get_contents($this->directory . '/' . $file);
    }

    /** Run the real command from a copy inside the fixture, so it changes only the fixture files. */
    private function command(string ...$arguments): array {
        foreach (array('scripts/bump-version.php', 'scripts/lib/plugin-version.php') as $script) { copy(dirname(__DIR__, 2) . '/' . $script, $this->directory . '/' . $script); }
        $process = proc_open(array_merge(array(PHP_BINARY, $this->directory . '/scripts/bump-version.php'), $arguments), array(1 => array('pipe', 'w'), 2 => array('pipe', 'w')), $pipes);
        $output = stream_get_contents($pipes[1]); fclose($pipes[1]);
        $error = stream_get_contents($pipes[2]); fclose($pipes[2]);
        return array('status' => proc_close($process), 'output' => $output, 'error' => $error);
    }

    public function test_patch_minor_major_and_explicit_versions_go_up(): void {
        $this->assertSame(array('1.4.7', '1.5.0', '2.0.0'), array(sitepulse_next_version('1.4.6', 'patch'), sitepulse_next_version('1.4.6', 'minor'), sitepulse_next_version('1.4.6', 'major')));
        $this->assertSame('1.4.10', sitepulse_next_version('1.4.9', 'patch'));
        $this->assertSame('1.10.0', sitepulse_next_version('1.9.3', '1.10.0'));
    }

    /** @dataProvider rejectedRequests */
    public function test_versions_that_do_not_go_up_or_are_malformed_are_rejected(string $current, string $request, string $exception, string $message): void {
        $this->expectException($exception);
        $this->expectExceptionMessage($message);
        sitepulse_next_version($current, $request);
    }

    public static function rejectedRequests(): array {
        return array(
            'same version' => array('1.4.6', '1.4.6', InvalidArgumentException::class, 'Version 1.4.6 is not higher than the current version 1.4.6.'),
            'older version' => array('1.4.6', '1.4.5', InvalidArgumentException::class, 'is not higher'),
            'unknown keyword' => array('1.4.6', 'next', InvalidArgumentException::class, 'Use patch, minor, major or a version such as 1.5.0.'),
            'two-part version' => array('1.4.6', '1.5', InvalidArgumentException::class, 'Unknown version "1.5"'),
            'current not X.Y.Z' => array('1.4', 'patch', RuntimeException::class, 'The current version 1.4 is not in X.Y.Z form.'),
        );
    }

    public function test_plan_changes_only_release_versions_and_writes_nothing_until_applied(): void {
        $plan = sitepulse_plan_version_bump($this->directory, 'patch');
        $this->assertSame(array('1.4.6', '1.4.7'), array($plan['from'], $plan['to']));
        $this->assertSame(array(
            'loader.php (plugin header): 1.4.6 -> 1.4.7',
            'loader.php (SITEPULSE_VERSION constant): 1.4.6 -> 1.4.7',
            'readme.txt (Stable tag): 1.4.6 -> 1.4.7',
            'languages/sitepulse.pot (Project-Id-Version): 1.4.6 -> 1.4.7',
        ), $plan['changes']);
        $this->assertSame(array(), $plan['warnings'], 'readme.txt already has release notes for 1.4.7.');
        $this->assertSame(array(self::LOADER, self::README, self::POT), array($this->read('loader.php'), $this->read('readme.txt'), $this->read('languages/sitepulse.pot')));

        sitepulse_apply_version_bump($plan);
        $this->assertSame(str_replace(array(':           1.4.6', "',               '1.4.6'"), array(':           1.4.7', "',               '1.4.7'"), self::LOADER), $this->read('loader.php'));
        $this->assertSame(str_replace('Stable tag: 1.4.6', 'Stable tag: 1.4.7', self::README), $this->read('readme.txt'), 'Changelog headings stay as written.');
        $this->assertSame(str_replace('Diagnostics 1.4.6', 'Diagnostics 1.4.7', self::POT), $this->read('languages/sitepulse.pot'));
    }

    public function test_line_endings_are_kept_and_drift_and_missing_release_notes_are_reported(): void {
        $this->write(array('loader.php' => str_replace("\n", "\r\n", self::LOADER), 'readme.txt' => str_replace('Stable tag: 1.4.6', 'Stable tag: 1.4.5', self::README)));
        $plan = sitepulse_plan_version_bump($this->directory, 'minor');
        $this->assertContains('readme.txt (Stable tag): 1.4.5 -> 1.5.0', $plan['changes']);
        $this->assertSame(array('readme.txt has no changelog section "= 1.5.0 =". Add the release notes under == Changelog ==.'), $plan['warnings']);
        sitepulse_apply_version_bump($plan);
        $this->assertSame(str_replace(array(':           1.4.6', "',               '1.4.6'", "\n"), array(':           1.5.0', "',               '1.5.0'", "\r\n"), self::LOADER), $this->read('loader.php'));
    }

    /** @dataProvider brokenReleaseFiles */
    public function test_a_missing_or_ambiguous_version_stops_before_writing(string $file, ?string $text, string $message): void {
        $text === null ? unlink($this->directory . '/' . $file) : $this->write(array($file => $text));
        try {
            sitepulse_plan_version_bump($this->directory, 'patch');
            $this->fail('A broken release file must stop the version change.');
        } catch (RuntimeException $error) {
            $this->assertSame($message, $error->getMessage());
        }
        $this->assertStringContainsString('Version:           1.4.6', $this->read('loader.php'));
    }

    public static function brokenReleaseFiles(): array {
        return array(
            'no stable tag' => array('readme.txt', "=== SitePulse ===\n", 'Expected one Stable tag version in readme.txt, found 0.'),
            'two constants' => array('loader.php', self::LOADER . "define( 'SITEPULSE_VERSION', '1.4.6' );\n", 'Expected one SITEPULSE_VERSION constant version in loader.php, found 2.'),
            'no translation template' => array('languages/sitepulse.pot', null, 'Cannot read languages/sitepulse.pot.'),
        );
    }

    public function test_command_previews_raises_and_rejects_versions(): void {
        $preview = $this->command('--dry-run');
        $this->assertSame(0, $preview['status'], $preview['error']);
        $this->assertStringContainsString('loader.php (plugin header): 1.4.6 -> 1.4.7', $preview['output']);
        $this->assertStringContainsString('Dry run: no files were changed.', $preview['output']);
        $this->assertSame(self::LOADER, $this->read('loader.php'));

        $minor = $this->command('minor');
        $this->assertSame(0, $minor['status'], $minor['error']);
        $this->assertStringContainsString('SitePulse is now version 1.5.0.', $minor['output']);
        $this->assertStringContainsString('Warning: readme.txt has no changelog section "= 1.5.0 =".', $minor['error']);
        $this->assertStringContainsString("Stable tag: 1.5.0\n", $this->read('readme.txt'));

        $again = $this->command('1.5.0');
        $this->assertSame(1, $again['status']);
        $this->assertSame('Version 1.5.0 is not higher than the current version 1.5.0.' . PHP_EOL, $again['error']);

        $help = $this->command('--help');
        $this->assertSame(0, $help['status']); $this->assertStringStartsWith('Usage: php scripts/bump-version.php', $help['output']);
        $extra = $this->command('patch', 'minor');
        $this->assertSame(1, $extra['status']); $this->assertStringStartsWith('Usage:', $extra['error']);
    }
}
