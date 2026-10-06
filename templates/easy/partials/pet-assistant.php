<?php
/**
 * Easy Mode — Dr. Pulse: Robot Doctor Assistant.
 *
 * A floating AI-powered robot doctor that:
 *  - Provides medical-themed diagnoses based on real site metrics
 *  - Integrates AI diagnostic report findings as "specialist referrals"
 *  - Gives prescription-style fix recommendations
 *  - Contextually explains each page
 *  - Shows a heartbeat-style vitals sidebar
 *
 * @var string $system_php_version Optional host version supplied by the view context.
 *
 * @package SitePulse
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// ── Gather Metrics ──────────────────────────────────────────
$dr_load_time   = isset( $total_load_time ) ? (float) $total_load_time : 0;
$dr_memory_pct  = isset( $mem['percent'] ) ? (float) str_replace( '%', '', $mem['percent'] ) : 0;
$dr_error_count = isset( $error_count ) ? (int) $error_count : 0;
$dr_plugins     = isset( $active_plugins_count ) ? (int) $active_plugins_count : 0;
$dr_view        = isset( $easy_view ) ? $easy_view : 'home';
$dr_ssl_ok      = is_ssl();
$dr_score       = isset( $overall_score ) ? (int) $overall_score : 0;

// SSL loopback health (certificate CN mismatch detection)
$dr_ssl_health      = class_exists( 'Sitepulse_SSL_Health' ) ? Sitepulse_SSL_Health::get_cached_result() : false;
$dr_ssl_loopback_ok = $dr_ssl_health ? ! empty( $dr_ssl_health['loopback_ok'] ) : true;
$dr_ssl_cn_mismatch = $dr_ssl_health && ! empty( $dr_ssl_health['cn_mismatch'] );
$dr_ssl_external_ok = $dr_ssl_health ? ! empty( $dr_ssl_health['external_ok'] ) : true;

// Helper: Dr. Pulse should never blame SitePulse itself
$dr_is_self = function( $name ) {
	$n = strtolower( trim( $name ) );
	return ( strpos( $n, 'sitepulse' ) !== false || strpos( $n, 'nilbug' ) !== false );
};

// AI diagnostic report
$dr_ai_report   = isset( $ai_diagnostic_report ) && is_array( $ai_diagnostic_report ) ? $ai_diagnostic_report : [];
$dr_ai_summary  = ! empty( $dr_ai_report['summary'] ) ? $dr_ai_report['summary'] : '';
$dr_ai_status   = ! empty( $dr_ai_report['status'] ) ? $dr_ai_report['status'] : '';
$dr_ai_score    = isset( $dr_ai_report['metrics_summary']['overall_health_score'] ) ? (int) $dr_ai_report['metrics_summary']['overall_health_score'] : null;
$dr_ai_quick    = ! empty( $dr_ai_report['quick_wins'] ) ? $dr_ai_report['quick_wins'] : [];
$dr_ai_actions  = ! empty( $dr_ai_report['action_items'] ) ? $dr_ai_report['action_items'] : [];
$dr_ai_insights = ! empty( $dr_ai_report['insights'] ) ? $dr_ai_report['insights'] : [];
$dr_has_report  = ! empty( $dr_ai_summary );

// ── Overall Diagnosis ───────────────────────────────────────
if ( $dr_score >= 80 ) {
	$dr_condition    = __( 'Looking Great!', 'sitepulse' );
	$dr_condition_cl = 'success';
	$dr_greeting     = __( 'Great news! Your website is healthy and running smoothly. Everything looks good from my end — keep up the great work!', 'sitepulse' );
} elseif ( $dr_score >= 60 ) {
	$dr_condition    = __( 'A Few Things to Check', 'sitepulse' );
	$dr_condition_cl = 'warning';
	$dr_greeting     = __( 'Your site is working, but I noticed a few things that could be better. Take a look at my suggestions below — they\'re easy to fix!', 'sitepulse' );
} else {
	$dr_condition    = __( 'Needs Your Attention', 'sitepulse' );
	$dr_condition_cl = 'danger';
	$dr_greeting     = __( 'I found some important issues that could be affecting your visitors\' experience. Don\'t worry — I\'ll walk you through what to do!', 'sitepulse' );
}

// ── Build Prescriptions ─────────────────────────────────────
$dr_prescriptions = [];

// From real metrics — using friendly, non-technical language
if ( $dr_load_time > 3000 ) {
	$dr_prescriptions[] = [
		'severity' => 'critical',
		'organ'    => __( 'Page Speed', 'sitepulse' ),
		'symptom'  => sprintf( __( 'Your pages take %s to load — visitors may leave before they see your content', 'sitepulse' ), number_format( $dr_load_time / 1000, 1 ) . 's' ),
		'rx'       => __( 'Ask your hosting provider about enabling caching, or install a free caching plugin from the Plugins page. Also check if you have any plugins you\'re not actually using — removing them helps!', 'sitepulse' ),
	];
} elseif ( $dr_load_time > 1500 ) {
	$dr_prescriptions[] = [
		'severity' => 'moderate',
		'organ'    => __( 'Page Speed', 'sitepulse' ),
		'symptom'  => sprintf( __( 'Pages take %s to load — a bit slower than ideal', 'sitepulse' ), number_format( $dr_load_time / 1000, 1 ) . 's' ),
		'rx'       => __( 'Try compressing your images before uploading, and consider using a caching plugin. These two simple steps can make a big difference!', 'sitepulse' ),
	];
}

if ( $dr_memory_pct > 80 ) {
	$dr_prescriptions[] = [
		'severity' => 'critical',
		'organ'    => __( 'Server Resources', 'sitepulse' ),
		'symptom'  => sprintf( __( 'Your site is using %s%% of its available memory — it could crash under heavy traffic', 'sitepulse' ), $dr_memory_pct ),
		'rx'       => __( 'Contact your hosting provider and ask them to increase the PHP memory limit. Also review your active plugins — some may be using too much memory.', 'sitepulse' ),
	];
} elseif ( $dr_memory_pct > 60 ) {
	$dr_prescriptions[] = [
		'severity' => 'moderate',
		'organ'    => __( 'Server Resources', 'sitepulse' ),
		'symptom'  => sprintf( __( 'Memory usage is at %s%% — getting a bit high', 'sitepulse' ), $dr_memory_pct ),
		'rx'       => __( 'Keep an eye on this. If it keeps climbing, consider removing plugins you don\'t need or asking your host about a plan upgrade.', 'sitepulse' ),
	];
}

if ( $dr_error_count > 5 ) {
	$dr_prescriptions[] = [
		'severity' => 'critical',
		'organ'    => __( 'Site Errors', 'sitepulse' ),
		'symptom'  => sprintf( __( '%d errors found recently — something isn\'t working right', 'sitepulse' ), $dr_error_count ),
		'rx'       => __( 'Go to Plugins and update everything to the latest version. If the errors started after installing a new plugin, try deactivating it. You can also contact your developer for help.', 'sitepulse' ),
	];
} elseif ( $dr_error_count > 0 ) {
	$dr_prescriptions[] = [
		'severity' => 'moderate',
		'organ'    => __( 'Site Errors', 'sitepulse' ),
		'symptom'  => sprintf( __( '%d small issue(s) found — nothing urgent, but worth a look', 'sitepulse' ), $dr_error_count ),
		'rx'       => __( 'Make sure all your plugins and themes are up to date. Outdated software is the most common cause of these small issues.', 'sitepulse' ),
	];
}

if ( $dr_plugins > 30 ) {
	$dr_prescriptions[] = [
		'severity' => 'moderate',
		'organ'    => __( 'Plugin Count', 'sitepulse' ),
		'symptom'  => sprintf( __( 'You have %d plugins active — that\'s a lot! Each one slows things down a bit.', 'sitepulse' ), $dr_plugins ),
		'rx'       => __( 'Go to Plugins and look for anything you installed but aren\'t really using. Deactivate and delete those to speed things up.', 'sitepulse' ),
	];
}

if ( ! $dr_ssl_ok ) {
	$dr_prescriptions[] = [
		'severity' => 'critical',
		'organ'    => __( 'Security', 'sitepulse' ),
		'symptom'  => __( 'Your site doesn\'t have a security certificate — visitors will see a "Not Secure" warning', 'sitepulse' ),
		'rx'       => __( 'Contact your hosting provider and ask them to enable SSL (it\'s usually free!). This adds the padlock icon to your site and protects your visitors\' data.', 'sitepulse' ),
	];
}

// SSL loopback certificate mismatch (cPanel/AWS NAT issue)
if ( $dr_ssl_cn_mismatch ) {
	$dr_prescriptions[] = [
		'severity' => 'critical',
		'organ'    => __( 'Internal Connections', 'sitepulse' ),
		'symptom'  => sprintf(
			__( 'Your server uses the wrong security certificate (%1$s) for internal connections — this silently breaks scheduled tasks like backups, updates, and emails', 'sitepulse' ),
			esc_html( $dr_ssl_health['loopback_cert_cn'] ?? '' )
		),
		'rx'       => __( 'This is a server configuration issue. Contact your hosting provider and tell them: "The server\'s internal DNS is serving the wrong SSL certificate on loopback requests. The domain needs to be added to /etc/hosts pointing to the server\'s private IP address."', 'sitepulse' ),
	];
} elseif ( ! $dr_ssl_loopback_ok && $dr_ssl_health ) {
	$dr_prescriptions[] = [
		'severity' => 'critical',
		'organ'    => __( 'Internal Connections', 'sitepulse' ),
		'symptom'  => __( 'Your server can\'t connect to itself securely — scheduled tasks like backups and updates are not running', 'sitepulse' ),
		'rx'       => __( 'Contact your hosting provider and mention that internal SSL loopback requests are failing. They may need to fix the server\'s SSL configuration or /etc/hosts file.', 'sitepulse' ),
	];
}

// External SSL connectivity failure
if ( ! $dr_ssl_external_ok && $dr_ssl_health ) {
	$dr_prescriptions[] = [
		'severity' => 'critical',
		'organ'    => __( 'Outbound Security', 'sitepulse' ),
		'symptom'  => __( 'Your server can\'t make secure connections to external services — plugin updates, payments, and API calls may fail', 'sitepulse' ),
		'rx'       => __( 'Contact your hosting provider and ask them to update the server\'s CA certificate bundle. This is needed for secure outbound connections.', 'sitepulse' ),
	];
}

// From AI diagnostic report — labeled as "Smart Tip" for friendliness
if ( ! empty( $dr_ai_quick ) ) {
	foreach ( array_slice( $dr_ai_quick, 0, 2 ) as $qw ) {
		$dr_prescriptions[] = [
			'severity' => 'ai',
			'organ'    => __( 'Smart Tip', 'sitepulse' ),
			'symptom'  => $qw['title'] ?? '',
			'rx'       => $qw['fix'] ?? ( $qw['impact'] ?? '' ),
		];
	}
}

if ( ! empty( $dr_ai_actions ) ) {
	foreach ( array_slice( $dr_ai_actions, 0, 2 ) as $act ) {
		$p = $act['priority'] ?? 'moderate';
		$dr_prescriptions[] = [
			'severity' => ( $p === 'critical' || $p === 'high' ) ? 'critical' : 'moderate',
			'organ'    => __( 'AI Recommendation', 'sitepulse' ),
			'symptom'  => $act['title'] ?? '',
			'rx'       => $act['fix'] ?? ( $act['problem'] ?? '' ),
		];
	}
}

$dr_total_issues = count( $dr_prescriptions );

// ── Page-Specific Smart Recommendations ─────────────────────
// These appear ONLY on the relevant page using data from that view.
$dr_page_recs = [];

switch ( $dr_view ) {

	case 'home':
		if ( isset( $speed_score ) && (int) $speed_score < 60 ) {
			$dr_page_recs[] = [
				'icon' => '⚡',
				'text' => sprintf( __( 'Speed score: %d — try a caching plugin and image optimization.', 'sitepulse' ), (int) $speed_score ),
			];
		}
		if ( isset( $plugins_score ) && (int) $plugins_score < 60 ) {
			$dr_page_recs[] = [
				'icon' => '🔌',
				'text' => sprintf( __( 'Plugin health: %d — deactivate plugins you\'re not using.', 'sitepulse' ), (int) $plugins_score ),
			];
		}
		if ( isset( $memory_score ) && (int) $memory_score < 60 ) {
			$dr_page_recs[] = [
				'icon' => '🧠',
				'text' => sprintf( __( 'Memory score: %d — ask your host to increase the PHP memory limit.', 'sitepulse' ), (int) $memory_score ),
			];
		}
		if ( ! empty( $show_performance_warnings ) ) {
			$dr_page_recs[] = [
				'icon' => '🔍',
				'text' => __( 'Slow hooks detected. Check Performance page for details.', 'sitepulse' ),
			];
		}
		if ( ! empty( $weekly_trends ) && is_array( $weekly_trends ) ) {
			$latest = end( $weekly_trends );
			$first  = reset( $weekly_trends );
			if ( isset( $latest['score'], $first['score'] ) ) {
				$trend_diff = (int) $latest['score'] - (int) $first['score'];
				if ( $trend_diff < -10 ) {
					$dr_page_recs[] = [
						'icon' => '📉',
						'text' => sprintf( __( 'Health dropped %d pts this week. Check History for changes.', 'sitepulse' ), abs( $trend_diff ) ),
					];
				} elseif ( $trend_diff > 10 ) {
					$dr_page_recs[] = [
						'icon' => '📈',
						'text' => sprintf( __( 'Up %d pts this week — great progress!', 'sitepulse' ), $trend_diff ),
					];
				}
			}
		}
		if ( $dr_has_report && ! empty( $dr_ai_report['status'] ) && $dr_ai_report['status'] === 'critical' ) {
			$dr_page_recs[] = [
				'icon' => '🤖',
				'text' => __( 'AI flagged critical issues. Review the AI Diagnostic card above.', 'sitepulse' ),
			];
		}
		if ( ! empty( $has_valid_pagespeed ) && isset( $ps_performance_score ) && (int) $ps_performance_score > 0 ) {
			$pss = (int) $ps_performance_score;
			if ( $pss < 50 ) {
				$dr_page_recs[] = [
					'icon' => '🌐',
					'text' => sprintf( __( 'Google PageSpeed: %d/100 (poor). Go to PageSpeed for fixes.', 'sitepulse' ), $pss ),
				];
			} elseif ( $pss < 80 ) {
				$dr_page_recs[] = [
					'icon' => '🌐',
					'text' => sprintf( __( 'Google PageSpeed: %d/100. Room for improvement.', 'sitepulse' ), $pss ),
				];
			}
		}
		if ( $dr_load_time > 0 && $dr_load_time < 1000 && $dr_memory_pct < 50 && $dr_error_count === 0 ) {
			$dr_page_recs[] = [
				'icon' => '🏆',
				'text' => __( 'Great shape! Fast load, low memory, zero errors.', 'sitepulse' ),
			];
		}
		break;

	case 'performance':
		if ( ! empty( $all_slow_items ) && is_array( $all_slow_items ) ) {
			// Find the slowest non-SitePulse item
			$slowest = null;
			foreach ( $all_slow_items as $si ) {
				$si_name = $si['name'] ?? $si['hook'] ?? '';
				if ( ! $dr_is_self( $si_name ) ) {
					$slowest = $si;
					break;
				}
			}
			if ( $slowest ) {
				$slow_name = $slowest['name'] ?? $slowest['hook'] ?? 'Unknown';
				$slow_time = isset( $slowest['time'] ) ? number_format( (float) $slowest['time'], 0 ) : '?';
				$dr_page_recs[] = [
					'icon' => '🐌',
					'text' => sprintf( __( 'Slowest: "%s" at %sms. Update or replace it.', 'sitepulse' ), $slow_name, $slow_time ),
				];
			}
			// Count non-self slow items
			$external_slow = array_filter( $all_slow_items, function( $i ) use ( $dr_is_self ) {
				return ! $dr_is_self( $i['name'] ?? $i['hook'] ?? '' );
			} );
			if ( count( $external_slow ) > 3 ) {
				$dr_page_recs[] = [
					'icon' => '📋',
					'text' => sprintf( __( '%d slow items total. Fix the top 3 for 80%% of the gain.', 'sitepulse' ), count( $external_slow ) ),
				];
			}
		}
		if ( ! empty( $plugin_profiler_stats ) && is_array( $plugin_profiler_stats ) ) {
			$heaviest_plugin = null;
			$heaviest_time   = 0;
			foreach ( $plugin_profiler_stats as $ps ) {
				$pname = $ps['plugin_name'] ?? $ps['name'] ?? '';
				if ( $dr_is_self( $pname ) ) continue; // Skip self
				$pt = isset( $ps['total_time'] ) ? (float) $ps['total_time'] : 0;
				if ( $pt > $heaviest_time ) {
					$heaviest_time   = $pt;
					$heaviest_plugin = $pname;
				}
			}
			if ( $heaviest_plugin && $heaviest_time > 100 ) {
				$dr_page_recs[] = [
					'icon' => '🏋️',
					'text' => sprintf( __( 'Heaviest plugin: "%s" (%sms). Consider a lighter alternative.', 'sitepulse' ), $heaviest_plugin, number_format( $heaviest_time, 0 ) ),
				];
			}
		}
		if ( isset( $total_load_time ) && (float) $total_load_time > 2000 ) {
			$dr_page_recs[] = [
				'icon' => '💡',
				'text' => sprintf( __( 'Load time: %ss. A caching plugin can cut this by 50-80%%.', 'sitepulse' ), number_format( (float) $total_load_time / 1000, 1 ) ),
			];
		}
		break;

	case 'resource-load':
		if ( ! empty( $stats ) && is_array( $stats ) ) {
			$dr_page_recs[] = [
				'icon' => '📊',
				'text' => sprintf( __( '%d hooks tracked. Look for items above 500ms.', 'sitepulse' ), count( $stats ) ),
			];
		}
		$dr_page_recs[] = [
			'icon' => '🎯',
			'text' => __( 'One page feels slow? Page Analysis shows what runs on that page only.', 'sitepulse' ),
		];
		$dr_page_recs[] = [
			'icon' => '💡',
			'text' => __( 'Sort by "Time" to find slowest items first.', 'sitepulse' ),
		];
		break;

	case 'page-analysis':
		$dr_page_recs[] = [
			'icon' => '🛒',
			'text' => __( 'Start with the pages that earn money: product, cart and checkout.', 'sitepulse' ),
		];
		$dr_page_recs[] = [
			'icon' => '🔁',
			'text' => __( 'Re-run an analysis after changing a plugin to see the difference.', 'sitepulse' ),
		];
		break;


	case 'security':
		if ( ! empty( $vulnerabilities ) && is_array( $vulnerabilities ) ) {
			$vuln_count = 0;
			foreach ( $vulnerabilities as $v ) {
				if ( ! empty( $v['vulnerabilities'] ) ) {
					$vuln_count += count( $v['vulnerabilities'] );
				}
			}
			if ( $vuln_count > 0 ) {
				$dr_page_recs[] = [
					'icon' => '🛡️',
					'text' => sprintf( __( '%d vulnerability(ies) found! Update affected plugins now.', 'sitepulse' ), $vuln_count ),
				];
			} else {
				$dr_page_recs[] = [
					'icon' => '✅',
					'text' => __( 'No known vulnerabilities. You\'re up to date!', 'sitepulse' ),
				];
			}
		}
		if ( ! empty( $error_log ) && is_array( $error_log ) ) {
			$fatal = isset( $error_log['fatal_count'] ) ? (int) $error_log['fatal_count'] : 0;
			$warns = isset( $error_log['warning_count'] ) ? (int) $error_log['warning_count'] : 0;
			if ( $fatal > 0 ) {
				$dr_page_recs[] = [
					'icon' => '🚨',
					'text' => sprintf( __( '%d fatal error(s). Deactivate the plugin causing them.', 'sitepulse' ), $fatal ),
				];
			}
			if ( $warns > 3 ) {
				$dr_page_recs[] = [
					'icon' => '⚠️',
					'text' => sprintf( __( '%d warnings in log. Update everything to latest versions.', 'sitepulse' ), $warns ),
				];
			}
		}
		if ( $dr_error_count === 0 && empty( $vulnerabilities ) ) {
			$dr_page_recs[] = [
				'icon' => '🎉',
				'text' => __( 'All clear! No errors, no vulnerabilities.', 'sitepulse' ),
			];
		}
		break;

	case 'insights':
		if ( ! empty( $has_valid_pagespeed ) && ! empty( $ps_data ) ) {
			$perf = isset( $ps_data['scores']['performance'] ) ? (int) ( $ps_data['scores']['performance'] * 100 ) : 0;
			if ( $perf < 50 ) {
				$dr_page_recs[] = [
					'icon' => '🔴',
					'text' => sprintf( __( 'Score: %d/100 (poor). Visitors experience slow loading.', 'sitepulse' ), $perf ),
				];
			} elseif ( $perf < 80 ) {
				$dr_page_recs[] = [
					'icon' => '🟡',
					'text' => sprintf( __( 'Score: %d/100. Compress images and reduce JavaScript.', 'sitepulse' ), $perf ),
				];
			} else {
				$dr_page_recs[] = [
					'icon' => '🟢',
					'text' => sprintf( __( 'Score: %d/100 — top tier! Most WP sites score 30-60.', 'sitepulse' ), $perf ),
				];
			}
			// Core Web Vitals
			$cwv = isset( $ps_data['core_web_vitals'] ) ? $ps_data['core_web_vitals'] : [];
			if ( ! empty( $cwv['lcp'] ) && isset( $cwv['lcp']['score'] ) && (float) $cwv['lcp']['score'] < 0.5 ) {
				$dr_page_recs[] = [
					'icon' => '📊',
					'text' => sprintf( __( 'LCP: %s — compress your hero image or add lazy loading.', 'sitepulse' ), $cwv['lcp']['display_value'] ?? '' ),
				];
			}
			if ( ! empty( $cwv['tbt'] ) && isset( $cwv['tbt']['score'] ) && (float) $cwv['tbt']['score'] < 0.5 ) {
				$dr_page_recs[] = [
					'icon' => '📊',
					'text' => __( 'TBT is high — defer non-essential JavaScript.', 'sitepulse' ),
				];
			}
			if ( ! empty( $ps_data['opportunities'] ) && is_array( $ps_data['opportunities'] ) ) {
				$dr_page_recs[] = [
					'icon' => '💡',
					'text' => sprintf( __( '%d Google-recommended fixes below. Start with the first one.', 'sitepulse' ), count( $ps_data['opportunities'] ) ),
				];
			}
		} else {
			$dr_page_recs[] = [
				'icon' => '🔍',
				'text' => __( 'No data yet. Average time to show data is 4-8 hours.', 'sitepulse' ),
			];
		}
		break;

	case 'api-monitor':
		if ( ! empty( $slow_api_requests ) && is_array( $slow_api_requests ) ) {
			$dr_page_recs[] = [
				'icon' => '🌐',
				'text' => sprintf( __( '%d slow connection(s). Slow services delay your whole site.', 'sitepulse' ), count( $slow_api_requests ) ),
			];
			$slowest_api = $slow_api_requests[0] ?? null;
			if ( $slowest_api ) {
				$api_host = ! empty( $slowest_api['url'] ) ? wp_parse_url( $slowest_api['url'], PHP_URL_HOST ) : 'unknown';
				$dr_page_recs[] = [
					'icon' => '🔗',
					'text' => sprintf( __( 'Slowest: %s. Disable if non-essential.', 'sitepulse' ), $api_host ),
				];
			}
		} elseif ( ! empty( $curl_events ) ) {
			$dr_page_recs[] = [
				'icon' => '✅',
				'text' => __( 'All connections healthy. No action needed.', 'sitepulse' ),
			];
		} else {
			$dr_page_recs[] = [
				'icon' => '📡',
				'text' => __( 'No external request data yet.', 'sitepulse' ),
			];
		}
		break;

	case 'system':
		if ( function_exists( 'phpversion' ) ) {
			$php_ver = isset( $system_php_version ) ? $system_php_version : phpversion();
			if ( version_compare( $php_ver, '8.0', '<' ) ) {
				$dr_page_recs[] = [
					'icon' => '⬆️',
					'text' => sprintf( __( 'PHP %s is outdated. Upgrade to 8.0+ for 20-30%% speed boost.', 'sitepulse' ), $php_ver ),
				];
			} elseif ( version_compare( $php_ver, '8.2', '<' ) ) {
				$dr_page_recs[] = [
					'icon' => '💡',
					'text' => sprintf( __( 'PHP %s — good. PHP 8.2+ available for extra speed.', 'sitepulse' ), $php_ver ),
				];
			} else {
				$dr_page_recs[] = [
					'icon' => '✅',
					'text' => sprintf( __( 'PHP %s — latest and fastest.', 'sitepulse' ), $php_ver ),
				];
			}
		}
		// SSL loopback mismatch on system page
		if ( $dr_ssl_cn_mismatch ) {
			$dr_page_recs[] = [
				'icon' => '🔐',
				'text' => sprintf(
					__( 'SSL mismatch: server sees "%1$s" instead of "%2$s". Ask hosting to fix /etc/hosts.', 'sitepulse' ),
					esc_html( $dr_ssl_health['loopback_cert_cn'] ?? '' ),
					esc_html( $dr_ssl_health['expected_domain'] ?? '' )
				),
			];
		} elseif ( ! $dr_ssl_loopback_ok && $dr_ssl_health ) {
			$dr_page_recs[] = [
				'icon' => '🔐',
				'text' => __( 'SSL loopback failing — cron and internal requests are broken.', 'sitepulse' ),
			];
		}
		if ( ! $dr_ssl_external_ok && $dr_ssl_health ) {
			$dr_page_recs[] = [
				'icon' => '🌐',
				'text' => __( 'Outbound SSL failing — updates and API calls blocked.', 'sitepulse' ),
			];
		}
		$dr_page_recs[] = [
			'icon' => '📋',
			'text' => __( 'Share this info with support teams when reporting issues.', 'sitepulse' ),
		];
		break;

	case 'settings':
		if ( empty( $sp_ai_external_api_enabled ) ) {
			$dr_page_recs[] = [
				'icon' => '🤖',
				'text' => __( 'External API is off. Enable it for AI-powered diagnostics.', 'sitepulse' ),
			];
		}
		if ( ! empty( $current_settings ) && is_array( $current_settings ) && empty( $current_settings['alerts_enabled'] ) ) {
			$dr_page_recs[] = [
				'icon' => '🔔',
				'text' => __( 'Alerts disabled. Turn on to get emailed when speed drops.', 'sitepulse' ),
			];
		}
		break;
}

// Limit page recs to 4 for readability but allow enough depth
$dr_page_recs = array_slice( $dr_page_recs, 0, 4 );


// ── Page help — friendly, non-technical, unique per page ────
$dr_page_help = [
	'home'          => [
		'where' => __( '📍 Dashboard', 'sitepulse' ),
		'what'  => __( 'Your site\'s health at a glance — overall score, speed, memory, and errors.', 'sitepulse' ),
		'tip'   => __( 'Check this weekly to catch issues early.', 'sitepulse' ),
	],
	'performance'   => [
		'where' => __( '📍 Performance', 'sitepulse' ),
		'what'  => __( 'Shows which plugins and hooks are slowing your site down.', 'sitepulse' ),
		'tip'   => __( 'Items in red or above 500ms are your main bottlenecks.', 'sitepulse' ),
	],
	'resource-load' => [
		'where' => __( '📍 Plugin Activity', 'sitepulse' ),
		'what'  => __( 'Step-by-step breakdown of everything that runs during a page load.', 'sitepulse' ),
		'tip'   => __( 'Sort by Time column to find the slowest items.', 'sitepulse' ),
	],
	'security'      => [
		'where' => __( '📍 Security Center', 'sitepulse' ),
		'what'  => __( 'Scans plugins and themes for known vulnerabilities and tracks errors.', 'sitepulse' ),
		'tip'   => __( 'Keep everything updated — it\'s the #1 security measure.', 'sitepulse' ),
	],
	'insights'      => [
		'where' => __( '📍 PageSpeed Insights', 'sitepulse' ),
		'what'  => __( 'Google Lighthouse scores showing how fast your site feels to real visitors.', 'sitepulse' ),
		'tip'   => __( 'Aim for 80+. Check the Opportunities section for fixes.', 'sitepulse' ),
	],
	'api-monitor'   => [
		'where' => __( '📍 External Requests', 'sitepulse' ),
		'what'  => __( 'External connections your site makes — payment, email, analytics services.', 'sitepulse' ),
		'tip'   => __( 'Slow or failing connections can delay your entire page load.', 'sitepulse' ),
	],
	'system'        => [
		'where' => __( '📍 System Info', 'sitepulse' ),
		'what'  => __( 'Server specs, PHP version, WordPress version, and environment details.', 'sitepulse' ),
		'tip'   => __( 'Share this with support teams when reporting hosting issues.', 'sitepulse' ),
	],
	'settings'      => [
		'where' => __( '📍 Settings', 'sitepulse' ),
		'what'  => __( 'Control monitoring, alerts, and SitePulse features.', 'sitepulse' ),
		'tip'   => __( 'Enable alerts to get notified when performance drops.', 'sitepulse' ),
	],
];
$dr_page_data = isset( $dr_page_help[ $dr_view ] ) ? $dr_page_help[ $dr_view ] : $dr_page_help['home'];
?>

<!-- Dr. Pulse: Robot Doctor Assistant -->
<div id="sp-pet-assistant" class="sp-dr sp-dr--<?php echo esc_attr( $dr_condition_cl ); ?>" data-issues="<?php echo esc_attr( $dr_total_issues ); ?>">

	<!-- Floating Doctor Button -->
	<button type="button" id="sp-pet-trigger" class="sp-dr-trigger" title="<?php echo esc_attr__( 'Dr. Pulse — Site Doctor', 'sitepulse' ); ?>">
		<svg class="sp-dr-icon" viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg">
			<!-- Head/Body -->
			<rect x="14" y="18" width="36" height="32" rx="10" fill="currentColor" opacity="0.12"/>
			<rect x="14" y="18" width="36" height="32" rx="10" stroke="currentColor" stroke-width="2.5"/>
			<!-- Doctor hat / head mirror -->
			<rect x="20" y="10" width="24" height="10" rx="5" fill="currentColor" opacity="0.2"/>
			<rect x="20" y="10" width="24" height="10" rx="5" stroke="currentColor" stroke-width="2"/>
			<!-- Head mirror circle -->
			<circle cx="32" cy="8" r="4" fill="currentColor" opacity="0.3" stroke="currentColor" stroke-width="1.5"/>
			<!-- Cross on mirror -->
			<line x1="32" y1="6" x2="32" y2="10" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
			<line x1="30" y1="8" x2="34" y2="8" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
			<!-- Eyes — LED style -->
			<circle class="sp-dr-eye sp-dr-eye-l" cx="24" cy="30" r="3.5" fill="currentColor"/>
			<circle class="sp-dr-eye sp-dr-eye-r" cx="40" cy="30" r="3.5" fill="currentColor"/>
			<!-- Eye glint -->
			<circle cx="25.5" cy="28.5" r="1" fill="var(--sp-bg-card)" opacity="0.8"/>
			<circle cx="41.5" cy="28.5" r="1" fill="var(--sp-bg-card)" opacity="0.8"/>
			<!-- Mouth — changes state -->
			<path class="sp-dr-mouth sp-dr-mouth-happy" d="M25 39 Q32 44 39 39" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round"/>
			<path class="sp-dr-mouth sp-dr-mouth-concern" d="M25 40 L39 40" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" style="display:none;"/>
			<!-- Stethoscope -->
			<path d="M10 34 Q6 34 6 40 Q6 48 14 50" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round"/>
			<circle class="sp-dr-stethoscope" cx="14" cy="51" r="3" fill="currentColor" opacity="0.5" stroke="currentColor" stroke-width="1.5"/>
			<!-- Heartbeat line on chest -->
			<polyline class="sp-dr-heartbeat" points="20,36 24,36 26,32 28,40 30,34 32,36 36,36 38,32 40,38 42,36 44,36" stroke="currentColor" stroke-width="1.5" fill="none" stroke-linecap="round" stroke-linejoin="round" opacity="0.6"/>
		</svg>
		<?php if ( $dr_total_issues > 0 ) : ?>
			<span class="sp-dr-badge"><?php echo esc_html( $dr_total_issues ); ?></span>
		<?php endif; ?>
		<!-- Heartbeat ring -->
		<span class="sp-dr-pulse-ring"></span>
	</button>

	<!-- Doctor Panel -->
	<div id="sp-pet-panel" class="sp-dr-panel" style="display: none;">

		<!-- Header with vitals bar -->
		<div class="sp-dr-panel-header">
			<div class="sp-dr-header-top">
				<div class="sp-flex sp-items-center sp-gap-10">
					<div class="sp-dr-avatar sp-dr-avatar--<?php echo esc_attr( $dr_condition_cl ); ?>">
						<svg width="24" height="24" viewBox="0 0 64 64" fill="none">
							<rect x="14" y="18" width="36" height="32" rx="10" fill="var(--sp-accent)" opacity="0.2"/>
							<rect x="14" y="18" width="36" height="32" rx="10" stroke="var(--sp-accent)" stroke-width="2.5"/>
							<rect x="20" y="10" width="24" height="10" rx="5" stroke="var(--sp-accent)" stroke-width="2" fill="none"/>
							<circle cx="32" cy="8" r="3.5" stroke="var(--sp-accent)" stroke-width="1.5" fill="var(--sp-accent)" opacity="0.3"/>
							<circle cx="24" cy="30" r="3" fill="var(--sp-accent)"/>
							<circle cx="40" cy="30" r="3" fill="var(--sp-accent)"/>
							<path d="M25 39 Q32 44 39 39" stroke="var(--sp-accent)" stroke-width="2" fill="none" stroke-linecap="round"/>
						</svg>
					</div>
					<div>
						<p class="sp-dr-name"><?php echo esc_html__( 'Dr. Pulse', 'sitepulse' ); ?></p>
						<p class="sp-dr-title"><?php echo esc_html__( 'Site Health Specialist', 'sitepulse' ); ?></p>
					</div>
				</div>
				<button type="button" id="sp-pet-close" class="sp-pet-close">&times;</button>
			</div>

			<!-- Vitals strip -->
			<div class="sp-dr-vitals-strip">
				<div class="sp-dr-vital">
					<span class="sp-dr-vital-label"><?php echo esc_html__( 'HEALTH', 'sitepulse' ); ?></span>
					<span class="sp-dr-vital-value sp-dr-vital--<?php echo $dr_score >= 80 ? 'good' : ( $dr_score >= 60 ? 'warn' : 'bad' ); ?>">
						<?php echo esc_html( $dr_score ); ?>
					</span>
				</div>
				<div class="sp-dr-vital-divider"></div>
				<div class="sp-dr-vital">
					<span class="sp-dr-vital-label"><?php echo esc_html__( 'SPEED', 'sitepulse' ); ?></span>
					<span class="sp-dr-vital-value sp-dr-vital--<?php echo $dr_load_time < 1500 ? 'good' : ( $dr_load_time < 3000 ? 'warn' : 'bad' ); ?>">
						<?php echo $dr_load_time > 0 ? esc_html( number_format( $dr_load_time / 1000, 1 ) . 's' ) : '—'; ?>
					</span>
				</div>
				<div class="sp-dr-vital-divider"></div>
				<div class="sp-dr-vital">
					<span class="sp-dr-vital-label"><?php echo esc_html__( 'MEM', 'sitepulse' ); ?></span>
					<span class="sp-dr-vital-value sp-dr-vital--<?php echo $dr_memory_pct < 60 ? 'good' : ( $dr_memory_pct < 80 ? 'warn' : 'bad' ); ?>">
						<?php echo esc_html( $dr_memory_pct . '%' ); ?>
					</span>
				</div>
				<div class="sp-dr-vital-divider"></div>
				<div class="sp-dr-vital">
					<span class="sp-dr-vital-label"><?php echo esc_html__( 'ERRORS', 'sitepulse' ); ?></span>
					<span class="sp-dr-vital-value sp-dr-vital--<?php echo $dr_error_count === 0 ? 'good' : ( $dr_error_count <= 5 ? 'warn' : 'bad' ); ?>">
						<?php echo esc_html( $dr_error_count ); ?>
					</span>
				</div>
			</div>
		</div>

		<!-- Body -->
		<div class="sp-dr-panel-body">

			<!-- PAGE-SPECIFIC: This Page (always first) -->
			<div class="sp-dr-page-context">
				<p class="sp-dr-page-label"><?php echo esc_html( $dr_page_data['where'] ); ?></p>
				<p class="sp-dr-page-desc"><?php echo esc_html( $dr_page_data['what'] ); ?></p>
			</div>

			<?php if ( ! empty( $dr_page_recs ) ) : ?>
			<!-- Page-Specific Findings -->
			<div class="sp-dr-section">
				<div class="sp-dr-section-header">
					<span class="dashicons dashicons-lightbulb" style="color:var(--sp-accent);font-size:14px;width:14px;height:14px;"></span>
					<span><?php echo esc_html__( 'My Findings', 'sitepulse' ); ?></span>
				</div>
				<?php foreach ( $dr_page_recs as $rec ) : ?>
				<div class="sp-dr-rec">
					<span class="sp-dr-rec-icon"><?php echo esc_html( $rec['icon'] ); ?></span>
					<p class="sp-dr-rec-text"><?php echo esc_html( $rec['text'] ); ?></p>
				</div>
				<?php endforeach; ?>
			</div>
			<?php endif; ?>

			<!-- Page Tip -->
			<div class="sp-dr-tip-bar">
				<span class="sp-dr-tip-icon">💡</span>
				<p class="sp-dr-tip-text"><?php echo esc_html( $dr_page_data['tip'] ); ?></p>
			</div>

			<!-- GLOBAL: Collapsible Health Summary -->
			<details class="sp-dr-global-section" <?php echo ( $dr_view === 'home' ) ? 'open' : ''; ?>>
				<summary class="sp-dr-global-toggle">
					<span class="sp-dr-global-toggle-label">
						<span class="dashicons dashicons-heart" style="font-size:13px;width:13px;height:13px;color:var(--sp-text-muted);"></span>
						<?php echo esc_html__( 'General Health', 'sitepulse' ); ?>
					</span>
					<span class="sp-dr-diagnosis-inline sp-dr-vital--<?php echo $dr_score >= 80 ? 'good' : ( $dr_score >= 60 ? 'warn' : 'bad' ); ?>">
						<?php echo esc_html( $dr_score ); ?>
					</span>
					<span class="dashicons dashicons-arrow-down-alt2 sp-dr-global-chevron"></span>
				</summary>
				<div class="sp-dr-global-body">

					<?php if ( ! empty( $dr_prescriptions ) ) : ?>
					<!-- Prescriptions -->
					<?php foreach ( $dr_prescriptions as $rx ) :
						$rx_cl = $rx['severity'] === 'critical' ? 'danger' : ( $rx['severity'] === 'ai' ? 'accent' : 'warning' );
					?>
					<details class="sp-dr-rx">
						<summary class="sp-dr-rx-summary sp-dr-rx--<?php echo esc_attr( $rx_cl ); ?>">
							<span class="sp-dr-rx-dot sp-dr-rx-dot--<?php echo esc_attr( $rx_cl ); ?>"></span>
							<div class="sp-dr-rx-info">
								<span class="sp-dr-rx-organ"><?php echo esc_html( $rx['organ'] ); ?></span>
								<span class="sp-dr-rx-symptom"><?php echo esc_html( $rx['symptom'] ); ?></span>
							</div>
							<span class="dashicons dashicons-arrow-down-alt2 sp-dr-rx-chevron"></span>
						</summary>
						<div class="sp-dr-rx-detail">
							<div class="sp-dr-rx-prescription">
								<span class="sp-dr-rx-label">℞</span>
								<p class="sp-dr-rx-text"><?php echo esc_html( $rx['rx'] ); ?></p>
							</div>
						</div>
					</details>
					<?php endforeach; ?>
					<?php else : ?>
					<div class="sp-dr-allclear-mini">
						<span style="color:var(--sp-success);">✓</span>
						<span><?php echo esc_html__( 'All clear — no issues found.', 'sitepulse' ); ?></span>
					</div>
					<?php endif; ?>

					<?php if ( $dr_has_report ) : ?>
					<div class="sp-dr-bubble sp-dr-bubble--ai" style="margin-top:8px;">
						<p class="sp-dr-bubble-label"><?php echo esc_html__( 'AI Report', 'sitepulse' ); ?></p>
						<p class="sp-dr-bubble-text"><?php echo esc_html( $dr_ai_summary ); ?></p>
					</div>
					<?php endif; ?>

				</div>
			</details>

		</div>

		<!-- Footer -->
		<div class="sp-dr-panel-footer">
			<div class="sp-dr-footer-status">
				<span class="sp-dr-ecg-line">
					<svg width="60" height="16" viewBox="0 0 60 16">
						<polyline class="sp-dr-ecg-anim" points="0,8 8,8 12,2 16,14 20,8 28,8 32,4 36,12 40,8 48,8 52,2 56,14 60,8" stroke="var(--sp-accent)" stroke-width="1.5" fill="none" stroke-linecap="round" stroke-linejoin="round"/>
					</svg>
				</span>
				<span class="sp-text-xs sp-text-muted"><?php echo esc_html__( 'Monitoring...', 'sitepulse' ); ?></span>
			</div>
		</div>
	</div>
</div>
