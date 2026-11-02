<?php

/**
 * Raise the SitePulse version in loader.php, readme.txt and the translation template.
 *
 * php scripts/bump-version.php [patch|minor|major|X.Y.Z] [--dry-run]
 */

require_once __DIR__ . '/lib/plugin-version.php';

$arguments = array_slice($argv, 1);
$dryRun = in_array('--dry-run', $arguments, true);
$requests = array_values(array_diff($arguments, array('--dry-run')));
$usage = 'Usage: php scripts/bump-version.php [patch|minor|major|X.Y.Z] [--dry-run]' . PHP_EOL
    . 'Without a version, the patch number goes up (1.4.6 becomes 1.4.7). --dry-run shows the changes without writing them.' . PHP_EOL;

if (array_intersect($requests, array('-h', '--help'))) {
    echo $usage;
    exit(0);
}
if (count($requests) > 1) {
    fwrite(STDERR, $usage);
    exit(1);
}

try {
    $plan = sitepulse_plan_version_bump(dirname(__DIR__), $requests[0] ?? 'patch');
    foreach ($plan['changes'] as $change) { echo $change, PHP_EOL; }
    foreach ($plan['warnings'] as $warning) { fwrite(STDERR, 'Warning: ' . $warning . PHP_EOL); }
    if ($dryRun) {
        echo 'Dry run: no files were changed.', PHP_EOL;
        exit(0);
    }
    sitepulse_apply_version_bump($plan);
    echo 'SitePulse is now version ', $plan['to'], '.', PHP_EOL;
} catch (Exception $error) {
    fwrite(STDERR, $error->getMessage() . PHP_EOL);
    exit(1);
}
