const fs = require('node:fs');
const path = require('node:path');
const { createInstrumenter } = require('istanbul-lib-instrument');
const { createCoverageMap } = require('istanbul-lib-coverage');

const coverage = createCoverageMap({});
const sources = new Map();
const directory = path.resolve(__dirname, '../../../assets/js');

// Seed every first-party script with zero coverage, including scripts that a
// test never loads. No production statements, functions or branches are ignored.
for (const entry of fs.readdirSync(directory).filter(entry => entry.endsWith('.js'))) {
  const filename = path.join(directory, entry);
  const instrumenter = createInstrumenter({ compact: false, preserveComments: true });
  sources.set(filename, instrumenter.instrumentSync(fs.readFileSync(filename, 'utf8'), filename));
  coverage.addFileCoverage(instrumenter.lastFileCoverage());
}

process.on('exit', () => {
  fs.writeFileSync(process.env.SITEPULSE_COVERAGE_FILE, JSON.stringify(coverage.toJSON()));
});

module.exports = {
  source(filename) { return sources.get(filename); },
  merge(result) { if (result) coverage.merge(result); }
};
