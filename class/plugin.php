<?php
/**
 * The core plugin class.
 *
 */
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class Sitepulse_Plugin extends Sitepulse_Setup {
	
	public function __construct() {
		add_action('init', array( &$this, 'init' ) );

        // Admin bar menu
        add_action('wp_enqueue_scripts', [&$this, 'enqueue_styles_and_scripts']);
        add_action('admin_enqueue_scripts', [&$this, 'enqueue_styles_and_scripts']);
	}

	public function init() {
		if( is_admin() ) {
			// The class responsible for defining all actions that occur in the admin area.
			require_once SITEPULSE_CLASS_PATH . 'backend.php';
			$plugin_backend = new Sitepulse_Backend($this);
		}

		// The class responsible for defining all actions that occur in the public-facing side of the site.
		require_once SITEPULSE_CLASS_PATH . 'frontend.php';
		$plugin_frontend = new Sitepulse_Frontend($this);
	}

	
    public function enqueue_styles_and_scripts() {
		wp_enqueue_script('jquery'); // use WP core jQuery
		wp_enqueue_style($this->setPrefix('wpsp_admin_bar'), SITEPULSE_ADMIN_ASSETS_CSS_URL . 'sitepulse_general.css', [], filemtime(SITEPULSE_ADMIN_ASSETS_CSS_PATH . 'sitepulse_general.css'), 'all');
	}

	/**
	 * Return memory info for display.
	 *
	 * @return array {
	 *   @type int    allocated    memory_get_usage(true) bytes
	 *   @type int    actual       memory_get_usage(false) bytes
	 *   @type int    peak         memory_get_peak_usage(true) bytes
	 *   @type string limit_raw    ini_get('memory_limit') raw value
	 *   @type int    limit_bytes  memory limit in bytes (-1 = unlimited)
	 *   @type string percent      used percent or '—'
	 *   @type string formatted    ready-to-echo formatted string
	 * }
	 */
	public function sp_get_memory_info(): array {
		$allocated = memory_get_usage(true);
		$actual    = memory_get_usage(false);
		$peak      = memory_get_peak_usage(true);
		$limit_raw = ini_get('memory_limit');

		if ( function_exists('wp_convert_hr_to_bytes') ) {
			$limit_bytes = wp_convert_hr_to_bytes( $limit_raw );
		} else {
			$limit_bytes = (string) $limit_raw === '-1' ? -1 : (function($v){
				$v = trim($v);
				$last = strtolower(substr($v, -1));
				$num  = (int) $v;
				switch ($last) {
					case 'g': $num *= 1024;
					case 'm': $num *= 1024;
					case 'k': $num *= 1024;
				}
				return $num;
			})($limit_raw);
		}

		$format_mb = static function(int $bytes): string {
			return number_format($bytes / 1048576, 2);
		};

		$limit_label = $limit_bytes === -1 ? 'unlimited' : $format_mb($limit_bytes) . ' MB';
		$percent = ($limit_bytes > 0) ? round( ($allocated / $limit_bytes) * 100, 1 ) . '%' : '—';

		// remove the % sign in the $percent
		$percent_check = rtrim($percent, '%');

		if ($percent_check > 90) {
			$badge_class = 'bg-danger';
		} elseif ($percent_check > 70) {
			$badge_class = 'bg-warning text-dark';
		} elseif ($percent_check > 50) {
			$badge_class = 'bg-warning text-dark';
		} elseif ($percent_check > 20) {
			$badge_class = 'bg-secondary';
		} else {
			$badge_class = 'bg-success';
		}

		$formatted = sprintf(
			'Memory: %s MB (real %s MB) — peak %s MB — limit %s — %s',
			$format_mb($allocated),
			$format_mb($actual),
			$format_mb($peak),
			$limit_label,
			$percent
		);

		return [
			'allocated'   => $allocated,
			'actual'      => $actual,
			'peak'        => $peak,
			'limit_raw'   => $limit_raw,
			'limit_bytes' => $limit_bytes,
			'percent'     => $percent,
			'percent_class' => $badge_class,
			'formatted'   => $formatted,
		];
	}

	/**
	 * Measure disk write time by writing a temporary file (WP_Filesystem version).
	 *
	 * @param string $dir         Directory to write into. Defaults to sys_get_temp_dir().
	 * @param int    $size_bytes  Bytes to write per iteration (max 50MB).
	 * @param int    $iterations  Number of iterations (max 5).
	 * @return array              Results: success, dir, size_bytes, iterations, times_ms[], avg_ms, min_ms, max_ms, total_written_bytes, error (optional)
	 */
	function sitepulse_disk_write_test( int $size_bytes = 1048576, int $iterations = 1 ): array {

        // Safety limits
        $size_bytes = max(1, min($size_bytes, 50 * 1024 * 1024)); // 1..50MB
        $iterations = max(1, min($iterations, 5)); // 1..5

        // Initialize WP_Filesystem
        global $wp_filesystem;
        if ( ! $wp_filesystem ) {
            require_once ABSPATH . 'wp-admin/includes/file.php';
            WP_Filesystem();
        }

        if ( ! $wp_filesystem ) {
            return [
                'success' => false,
                'error'   => 'wp_filesystem_unavailable',
            ];
        }

        // create a folder in the upload file with wp
        $upload_dir = wp_upload_dir();
        $test_dir = $upload_dir['basedir'] . '/sitepulse/';
        if ( $wp_filesystem && ! $wp_filesystem->is_dir( $test_dir ) ) {
            $wp_filesystem->mkdir( $test_dir, FS_CHMOD_DIR );
        }
        // Map to WP_Filesystem path if possible
        $fs_dir = $test_dir;

        if ( empty($fs_dir) ) {
            $fs_dir = $test_dir; // fallback if find_folder can't map
        }

        // Validate directory and writability via WP_Filesystem
        if ( ! $wp_filesystem->is_dir($fs_dir) || ( method_exists($wp_filesystem, 'is_writable') && ! $wp_filesystem->is_writable($fs_dir) ) ) {
            return [
                'success' => false,
                'error'   => 'dir_not_writable',
                'dir'     => $fs_dir,
            ];
        }

        // Prepare data chunk once
        $pattern = "0123456789abcdef";
        $repeat  = (int) ceil($size_bytes / strlen($pattern));
        $chunk   = substr(str_repeat($pattern, $repeat), 0, $size_bytes);

        $times = [];
        $total_written = 0;

        for ( $i = 0; $i < $iterations; $i++ ) {
            $tmp = $fs_dir . 'spdisk_' . uniqid('', true) . '.tmp';

            $start = microtime(true);
            $write_result = $wp_filesystem->put_contents($tmp, $chunk, FS_CHMOD_FILE);
            $end = microtime(true);

            if ( ! $write_result ) {
                // Best effort cleanup in case file was partially created
                if ( $wp_filesystem->exists($tmp) ) {
                    $wp_filesystem->delete($tmp, false, 'f');
                }
                return [
                    'success' => false,
                    'error'   => 'put_contents_failed',
                    'file'    => $tmp,
                ];
            }

            $elapsed_ms = round(($end - $start) * 1000, 3);
            $times[] = $elapsed_ms;

            // put_contents doesn't return bytes written; assume full chunk
            $total_written += strlen($chunk);

            // Cleanup
            $wp_filesystem->delete($tmp, false, 'f');
        }

        $avg = $times ? round(array_sum($times) / count($times), 3) : 0.0;
        $min = $times ? min($times) : 0.0;
        $max = $times ? max($times) : 0.0;

        return [
            'success'             => true,
            'dir'                 => $fs_dir,
            'size_bytes'          => $size_bytes,
            'iterations'          => $iterations,
            'times_ms'            => $times,
            'avg_ms'              => $avg,
            'min_ms'              => $min,
            'max_ms'              => $max,
            'total_written_bytes' => $total_written,
        ];
    }

    /**
     * Inspect $wpdb->queries (requires SAVEQUERIES = true) and return queries slower than threshold.
     *
     * @param float $threshold_ms Minimum elapsed time in milliseconds to consider "slow". Default 100ms.
     * @param int   $limit        Max number of results to return (sorted desc by time). Default 50.
     * @return array              [
     *   'success' => bool,
     *   'error'   => string|null,
     *   'results' => array of [ 'sql' => string, 'time_ms' => float, 'caller' => string|null ]
     * ]
     */
    public function sp_get_slow_queries( float $threshold_ms = 100.0, int $limit = 50 ): array {
        global $wpdb;

        if ( ! defined( 'SAVEQUERIES' ) || ! SAVEQUERIES ) {
            return [
                'success' => false,
                'error'   => 'savequeries_disabled',
                'message' => 'Enable SAVEQUERIES (e.g. define(\'SAVEQUERIES\', true) in wp-config.php) to collect query timings.',
            ];
        }

        if ( empty( $wpdb->queries ) || ! is_array( $wpdb->queries ) ) {
            return [
                'success' => true,
                'results' => [],
            ];
        }

        $found = [];
        foreach ( $wpdb->queries as $entry ) {
            // WP stores queries as [0 => sql, 1 => time, 2 => caller] in many installs.
            if ( is_array( $entry ) ) {
                $sql = isset( $entry[0] ) ? $entry[0] : '';
                $time_seconds = isset( $entry[1] ) ? (float) $entry[1] : 0.0;
                $caller = isset( $entry[2] ) ? $entry[2] : null;
            } else {
                // Fallback: unexpected format, skip
                continue;
            }

            $time_ms = $time_seconds * 1000.0;
            if ( $time_ms >= $threshold_ms ) {
                $found[] = [
                    'sql'     => $sql,
                    'time_ms' => round( $time_ms, 3 ),
                    'caller'  => $caller,
                ];
            }
        }

        // sort by time desc
        usort( $found, function( $a, $b ) {
            return $b['time_ms'] <=> $a['time_ms'];
        } );

        if ( $limit > 0 ) {
            $found = array_slice( $found, 0, $limit );
        }

        return [
            'success' => true,
            'results' => $found,
        ];
    }

	/**
     * Safely enable/disable SAVEQUERIES by editing wp-config.php.
     *
     * This function:
     *  - validates permissions and file accessibility
     *  - creates a timestamped backup before modifying
     *  - replaces an existing define(...) or inserts it before wp-settings include
     *  - writes atomically and attempts opcache invalidation
     *  - rolls back on failure
     *
     * @param bool $enable Set true to enable SAVEQUERIES, false to disable.
     * @param bool $force  If true, rewrite even if existing value matches.
     * @return array       ['success'=>bool, 'changed'=>bool, 'previous'=>bool|null, 'new'=>bool, 'backup'=>string|null, 'message'=>string, 'error'=>string|null]
     */
    public function sp_set_savequeries( bool $enable, bool $force = false ): array {
        // Use WP_Filesystem to check writability
        global $wp_filesystem;
        if ( ! $wp_filesystem ) {
            require_once ABSPATH . 'wp-admin/includes/file.php';
            WP_Filesystem();
        }

        if ( ! current_user_can( 'manage_options' ) ) {
            return [ 'success' => false, 'error' => 'forbidden', 'message' => 'Insufficient permissions.' ];
        }

        $wp_config = ABSPATH . 'wp-config.php';
        if ( ! file_exists( $wp_config ) || ! is_file( $wp_config ) ) {
            return [ 'success' => false, 'error' => 'missing_file', 'message' => 'wp-config.php not found.', 'backup' => null ];
        }

        // Ensure readable and writable
        if ( ! $wp_filesystem->is_readable( $wp_config ) ) {
            return [ 'success' => false, 'error' => 'not_readable', 'message' => 'wp-config.php is not readable.' ];
        }
        if ( ! $wp_filesystem || ! $wp_filesystem->is_writable( $wp_config ) ) {
            return [ 'success' => false, 'error' => 'not_writable', 'message' => 'wp-config.php is not writable. Adjust filesystem permissions.' ];
        }

        $contents = $wp_filesystem->get_contents( $wp_config );
        if ( $contents === false ) {
            return [ 'success' => false, 'error' => 'read_failed', 'message' => 'Failed to read wp-config.php' ];
        }

        // detect existing define line and previous value if present
        $prev_value = null;
        $define_regex = "/define\\s*\\(\\s*(['\"])SAVEQUERIES\\1\\s*,\\s*(true|false)\\s*\\)\\s*;?/i";
        if ( preg_match( $define_regex, $contents, $m ) ) {
            $prev_value = ( strtolower( $m[2] ) === 'true' );
        }

        // If nothing to change and not forcing, return no-op
        if ( $prev_value === $enable && ! $force ) {
            return [
                'success'  => true,
                'changed'  => false,
                'previous' => $prev_value,
                'new'      => $enable,
                'backup'   => null,
                'message'  => 'No change required; SAVEQUERIES already set to requested value.',
            ];
        }

        $new_define = "define('SAVEQUERIES', " . ( $enable ? 'true' : 'false' ) . ");";

        // If defined -> replace first occurrence, otherwise insert before wp-settings include
        if ( preg_match( $define_regex, $contents ) ) {
            $new_contents = preg_replace( $define_regex, $new_define, $contents, 1 );
        } else {
            // find require_once ABSPATH . 'wp-settings.php';
            $require_regex = "/^\\s*require_once\\s*\\(\\s*ABSPATH\\s*\\.\\s*(['\"])wp-settings\\.php\\1\\s*\\)\\s*;\\s*$/im";
            if ( preg_match( $require_regex, $contents, $m, PREG_OFFSET_CAPTURE ) ) {
                $pos = $m[0][1];
                // insert define above that line with safe newline
                $new_contents = substr( $contents, 0, $pos ) . "\n" . $new_define . "\n\n" . substr( $contents, $pos );
            } else {
                // fallback: insert after opening <?php or at top
                if ( preg_match( "/<\\?php\\s*/i", $contents, $pm, PREG_OFFSET_CAPTURE ) ) {
                    $insert_pos = $pm[0][1] + strlen( $pm[0][0] );
                    $new_contents = substr( $contents, 0, $insert_pos ) . "\n" . $new_define . "\n\n" . substr( $contents, $insert_pos );
                } else {
                    // extremely unusual file without PHP tag: prepend a php block
                    $new_contents = "<?php\n" . $new_define . "\n\n" . $contents;
                }
            }
        }

        if ( $new_contents === null ) {
            return [ 'success' => false, 'error' => 'compose_failed', 'message' => 'Failed to build new wp-config.php contents.' ];
        }

        // create backup
        $bak = dirname( $wp_config ) . DIRECTORY_SEPARATOR . 'wp-config.sitepulse.bak.' . gmdate( 'Ymd_His' ) . '.php';
        if ( ! $wp_filesystem->copy( $wp_config, $bak ) ) {
            return [ 'success' => false, 'error' => 'backup_failed', 'message' => 'Failed to create backup of wp-config.php', 'backup' => null ];
        }

        // write atomically to temp then rename
        // Use uploads directory for temporary file to avoid writing to plugin/ABSPATH directory
        $upload_dir = wp_upload_dir();
        $temp_dir = $upload_dir['basedir'] . '/sitepulse/';
        
        // Ensure temp directory exists
        if ( ! $wp_filesystem->is_dir( $temp_dir ) ) {
            $wp_filesystem->mkdir( $temp_dir, FS_CHMOD_DIR );
        }
        
        $tmp = $temp_dir . 'wp-config.tmp.' . uniqid('', true);
        $written = $wp_filesystem->put_contents( $tmp, $new_contents, FS_CHMOD_FILE );
        if ( $written === false ) {
            // Clean up temp file if it exists
            if ( $wp_filesystem->exists( $tmp ) ) {
                $wp_filesystem->delete( $tmp, false, 'f' );
            }
            // attempt rollback (restore from backup) if needed
            $wp_filesystem->copy( $bak, $wp_config );
            return [ 'success' => false, 'error' => 'write_failed', 'message' => 'Failed to write temporary wp-config file', 'backup' => $bak ];
        }

        // attempt to preserve original permissions using WP_Filesystem
        $perms = fileperms( $wp_config ) & 0777;
        if ( $wp_filesystem && method_exists( $wp_filesystem, 'chmod' ) ) {
            $wp_filesystem->chmod( $tmp, $perms );
        }

        // replace original
        if ( ! $wp_filesystem->move( $tmp, $wp_config, true ) ) {
            // Clean up temp file
            if ( $wp_filesystem->exists( $tmp ) ) {
                $wp_filesystem->delete( $tmp, false, 'f' );
            }
            // rollback
            $wp_filesystem->copy( $bak, $wp_config );
            return [ 'success' => false, 'error' => 'rename_failed', 'message' => 'Failed to replace wp-config.php atomically; rolled back', 'backup' => $bak ];
        }

        // try to invalidate opcache for the file
        if ( function_exists( 'opcache_invalidate' ) ) {
            @opcache_invalidate( $wp_config, true );
        }

        return [
            'success'  => true,
            'changed'  => true,
            'previous' => $prev_value,
            'new'      => $enable,
            'backup'   => $bak,
            'message'  => 'SAVEQUERIES updated successfully.',
        ];
    }

    /**
     * Run a controlled DB-delay test using MySQL SLEEP().
     *
     * Safety:
     *  - Only administrators may run this.
     *  - Runs only when WP_DEBUG is true or when $force === true.
     *  - Seconds are clamped to a safe range by default (0.1 .. 10s).
     *  - This performs a harmless SELECT SLEEP(...) query (no data changes) but will block the DB connection.
     *
     * @param float $seconds Seconds to sleep (fractional allowed). Default 1.0.
     * @param bool  $force   If true will bypass WP_DEBUG restriction (still requires manage_options).
     * @return array         ['success'=>bool,'requested'=>float,'elapsed_ms'=>float,'sql'=>string,'result'=>mixed,'error'=>string|null]
     */
    public function sp_db_delay_test( float $seconds = 1.0, bool $force = false ): array {
        if ( ! current_user_can( 'manage_options' ) ) {
            return [ 'success' => false, 'error' => 'forbidden', 'message' => 'Insufficient permissions.' ];
        }

        // Only allow on dev/debug environments by default
        if ( ! $force && ( ! defined( 'WP_DEBUG' ) || ! WP_DEBUG ) ) {
            return [
                'success'   => false,
                'error'     => 'disabled_in_production',
                'message'   => 'DB delay tests are disabled unless WP_DEBUG is true or $force is used.',
            ];
        }

        // clamp to safe bounds unless forced
        $min = 0.1;
        $max = 10.0;
        if ( $force ) {
            $max = 60.0; // admin override upper bound
        }

        $secs = max( $min, min( $max, (float) $seconds ) );

        global $wpdb;

        // ensure numeric value only (no injection risk)
        $secs_str = number_format( $secs, 3, '.', '' );

        $sql = "SELECT SLEEP({$secs_str})";

        $start = microtime( true );
        $error = null;
        $result = null;

        // run query and capture possible WP DB errors
        $old_wp_error_reporting = $wpdb->suppress_errors;
        $wpdb->suppress_errors = false;
        // Use $wpdb->prepare() for safety, even though $secs_str is numeric
        // No caching is possible for this query, as SLEEP() is a direct DB delay test.
        $query_result = $wpdb->query( $wpdb->prepare("SELECT SLEEP(%f)", (float)$secs_str));
        $wpdb->suppress_errors = $old_wp_error_reporting;

        if ( $query_result === false ) {
            $error = $wpdb->last_error ?: 'unknown_db_error';
        } else {
            $result = $query_result;
        }

        $end = microtime( true );
        $elapsed_ms = round( ( $end - $start ) * 1000, 3 );

        return [
            'success'   => $error === null,
            'requested' => (float) $secs_str,
            'elapsed_ms'=> $elapsed_ms,
            'sql'       => $sql,
            'result'    => $result,
            'error'     => $error,
        ];
    }
}