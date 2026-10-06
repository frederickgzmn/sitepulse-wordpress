const fs = require('node:fs');
const path = require('node:path');
const { spawnSync } = require('node:child_process');
const { createCoverageMap } = require('istanbul-lib-coverage');
const { createContext } = require('istanbul-lib-report');
const reports = require('istanbul-reports');

const root = path.resolve(__dirname, '..');
const directory = path.join(root, 'coverage/js');
const destination = path.join(directory, 'coverage-final.json');
fs.mkdirSync(directory, { recursive: true });
if (fs.existsSync(destination)) fs.unlinkSync(destination);
const tests = fs.readdirSync(path.join(root, 'tests/js'))
  .filter(filename => filename.endsWith('.test.cjs'))
  .map(filename => path.join(root, 'tests/js', filename));
const result = spawnSync(process.execPath, ['--test', '--experimental-test-isolation=none', ...tests], {
  cwd: root,
  stdio: 'inherit',
  env: { ...process.env, SITEPULSE_COVERAGE_FILE: destination }
});
if (result.error) throw result.error;
if (!fs.existsSync(destination)) {
  console.error('JavaScript test process did not produce a coverage report.');
  process.exit(result.status || 1);
}
const coverage = createCoverageMap(JSON.parse(fs.readFileSync(destination, 'utf8')));
const context = createContext({ dir: directory, coverageMap: coverage });
for (const reporter of ['text', 'html', 'lcovonly', 'json-summary']) reports.create(reporter).execute(context);
const lines = coverage.getCoverageSummary().lines;
const minimumMet = lines.total > 0 && lines.covered === lines.total;
if (process.argv.includes('--check') && !minimumMet) {
  console.error('JavaScript executable-line coverage must be 100%.');
}
process.exit(result.status || (result.signal || (process.argv.includes('--check') && !minimumMet) ? 1 : 0));
