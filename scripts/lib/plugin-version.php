<?php

/** Raise the plugin version in every file a release states it in. */

/**
 * Release files and the patterns that find the version in them; group 1 precedes the version, group 2 is the version.
 * Test fixtures keep their own version and are not listed.
 */
function sitepulse_version_locations(): array {
    return array(
        'loader.php' => array(
            'plugin header' => '/^([ \t]*\*[ \t]*Version:[ \t]*)(\d+\.\d+\.\d+)\b/m',
            'SITEPULSE_VERSION constant' => '/(define\(\s*\'SITEPULSE_VERSION\',\s*\')(\d+\.\d+\.\d+)(?=\')/',
        ),
        'readme.txt' => array('Stable tag' => '/^(Stable tag:[ \t]*)(\d+\.\d+\.\d+)\b/m'),
        'languages/sitepulse.pot' => array('Project-Id-Version' => '/^("Project-Id-Version: [^"\r\n]* )(\d+\.\d+\.\d+)(?=\\\\n")/m'),
    );
}

/** The version after $current: "patch", "minor", "major" or an explicit higher X.Y.Z version. */
function sitepulse_next_version(string $current, string $request): string {
    if (!preg_match('/^(\d+)\.(\d+)\.(\d+)$/', $current, $parts)) { throw new RuntimeException('The current version ' . $current . ' is not in X.Y.Z form.'); }
    list(, $major, $minor, $patch) = array_map('intval', $parts);
    if ($request === 'patch') { $next = $major . '.' . $minor . '.' . ($patch + 1); }
    elseif ($request === 'minor') { $next = $major . '.' . ($minor + 1) . '.0'; }
    elseif ($request === 'major') { $next = ($major + 1) . '.0.0'; }
    elseif (preg_match('/^\d+\.\d+\.\d+$/', $request)) { $next = $request; }
    else { throw new InvalidArgumentException('Unknown version "' . $request . '". Use patch, minor, major or a version such as 1.5.0.'); }
    if (version_compare($next, $current, '<=')) { throw new InvalidArgumentException('Version ' . $next . ' is not higher than the current version ' . $current . '.'); }
    return $next;
}

/**
 * Work out a version change without writing anything.
 *
 * @return array from, to, root, files (relative path => new contents), changes and warnings for display
 */
function sitepulse_plan_version_bump(string $root, string $request): array {
    $contents = array();
    $found = array();
    foreach (sitepulse_version_locations() as $file => $patterns) {
        $text = is_file($root . '/' . $file) ? file_get_contents($root . '/' . $file) : false;
        if ($text === false) { throw new RuntimeException('Cannot read ' . $file . '.'); }
        $contents[$file] = $text;
        foreach ($patterns as $label => $pattern) {
            $count = preg_match_all($pattern, $text, $matches);
            if ($count !== 1) { throw new RuntimeException('Expected one ' . $label . ' version in ' . $file . ', found ' . $count . '.'); }
            $found[] = array('file' => $file, 'label' => $label, 'pattern' => $pattern, 'version' => $matches[2][0]);
        }
    }

    // The constant is what the running plugin reports, so the new version follows it.
    $current = array_column($found, 'version', 'label')['SITEPULSE_VERSION constant'];
    $next = sitepulse_next_version($current, $request);
    $changes = array();
    foreach ($found as $location) {
        $contents[$location['file']] = preg_replace_callback($location['pattern'], static function ($match) use ($next) { return $match[1] . $next; }, $contents[$location['file']]);
        $changes[] = sprintf('%s (%s): %s -> %s', $location['file'], $location['label'], $location['version'], $next);
    }

    $warnings = array();
    if (!preg_match('/^= ' . preg_quote($next, '/') . ' =[ \t]*\r?$/m', $contents['readme.txt'])) {
        $warnings[] = 'readme.txt has no changelog section "= ' . $next . ' =". Add the release notes under == Changelog ==.';
    }

    return array('from' => $current, 'to' => $next, 'root' => $root, 'files' => $contents, 'changes' => $changes, 'warnings' => $warnings);
}

/** Write a planned version change. */
function sitepulse_apply_version_bump(array $plan): void {
    foreach ($plan['files'] as $file => $text) {
        if (file_put_contents($plan['root'] . '/' . $file, $text) === false) { throw new RuntimeException('Cannot write ' . $file . '.'); }
    }
}
