<?php
require_once __DIR__ . '/Support/RemoteManagement.php';
require_once __DIR__ . '/Support/Database.php';

final class RemoteInventoryTest extends Sitepulse_Test_Case {
    protected function setUp(): void {
        parent::setUp();
        Sitepulse_Test_Backups::reset();
        $GLOBALS['wpdb'] = new Sitepulse_Test_Database();
    }
    protected function tearDown(): void { Sitepulse_Test_Backups::reset(); unset($GLOBALS['wpdb']); parent::tearDown(); }

    private function updates(): array { return Sitepulse_Remote_Inventory::collect()['updates']; }

    public function test_inventory_describes_the_site_and_its_backups(): void {
        Sitepulse_Test_Backups::$backwpup_version = '4.1.7';
        $inventory = Sitepulse_Remote_Inventory::collect();

        $this->assertSame(1, $inventory['schema']);
        $this->assertEqualsWithDelta(time(), strtotime($inventory['collected_at']), 2);
        $this->assertMatchesRegularExpression('/^\d{4}-\d\d-\d\dT\d\d:\d\d:\d\dZ$/', $inventory['collected_at']);
        $this->assertSame(array('wp_version' => '6.8', 'php_version' => PHP_VERSION, 'db_version' => '8.0.36', 'multisite' => false, 'locale' => 'en_US'), $inventory['site']);
        $this->assertSame(array('provider' => array('id' => 'backwpup', 'version' => '4.1.7', 'supported' => false)), $inventory['backups']);
        $this->assertSame(array('can_update', 'reason', 'core', 'plugins', 'themes', 'translations_pending'), array_keys($inventory['updates']));
    }

    public function test_updates_are_possible_when_wordpress_can_write_files_directly(): void {
        $updates = $this->updates();
        $this->assertTrue($updates['can_update']);
        $this->assertNull($updates['reason']);
    }

    public function test_updates_are_blocked_by_disallowed_file_changes_or_missing_filesystem_credentials(): void {
        add_filter('file_mod_allowed', static function ($allowed, $context) { return $context !== 'sitepulse_remote_updates'; }, 10, 2);
        $this->assertSame(array(false, 'file_mods_disabled'), array_values(array_slice($this->updates(), 0, 2)));

        Sitepulse_Test_WP::reset();
        $GLOBALS['wpdb'] = new Sitepulse_Test_Database();
        Sitepulse_Test_WP::$filesystem_method = 'ftpext';
        $this->assertSame(array(false, 'filesystem_credentials'), array_values(array_slice($this->updates(), 0, 2)));
    }

    public function test_core_offers_each_release_once_as_minor_or_major(): void {
        Sitepulse_Test_WP::$core_updates = array(
            (object) array('response' => 'upgrade', 'current' => '6.8.3', 'locale' => 'en_US'),
            (object) array('response' => 'upgrade', 'current' => '6.8.3', 'locale' => 'de_DE'),
            (object) array('response' => 'upgrade', 'current' => '6.9', 'locale' => 'en_US'),
            (object) array('response' => 'latest', 'current' => '6.8', 'locale' => 'en_US'),
            (object) array('response' => 'upgrade'),
            'not an offer',
        );

        $core = $this->updates()['core'];

        $this->assertSame('6.8', $core['current']);
        $this->assertSame(array(array('version' => '6.8.3', 'type' => 'minor'), array('version' => '6.9', 'type' => 'major')), $core['offers']);
        $this->assertSame('minor', $core['auto_update']);
    }

    public function test_no_core_offers_before_wordpress_checked_for_updates(): void {
        $this->assertSame(array(), $this->updates()['core']['offers']);
    }

    /** @dataProvider coreAutoUpdates */
    public function test_core_auto_update_follows_wordpress_settings_and_filters(callable $configure, string $expected): void {
        $configure();
        $this->assertSame($expected, $this->updates()['core']['auto_update']);
    }

    public static function coreAutoUpdates(): array {
        return array(
            'major releases switched on' => array(static function () { Sitepulse_Test_WP::$site_options['auto_update_core_major'] = 'enabled'; }, 'major'),
            'minor releases switched off' => array(static function () { Sitepulse_Test_WP::$site_options['auto_update_core_minor'] = 'disabled'; }, 'disabled'),
            'automatic updater disabled' => array(static function () { add_filter('automatic_updater_disabled', '__return_true'); }, 'disabled'),
            'automatic updater may not change files' => array(static function () { add_filter('file_mod_allowed', static function ($allowed, $context) { return $context !== 'automatic_updater'; }, 10, 2); }, 'disabled'),
            'major releases allowed by a plugin' => array(static function () { add_filter('allow_major_auto_core_updates', '__return_true'); }, 'major'),
            'minor releases refused by a plugin' => array(static function () { add_filter('allow_minor_auto_core_updates', '__return_false'); }, 'disabled'),
        );
    }

    public function test_plugins_list_versions_activity_auto_updates_and_offered_updates(): void {
        Sitepulse_Test_WP::$plugins = array(
            'elementor/elementor.php' => array('Name' => 'Elementor', 'Version' => '3.30.1'),
            'premium/premium.php' => array('Name' => 'Premium', 'Version' => '2.0'),
            'hello.php' => array(),
        );
        Sitepulse_Test_WP::$options['active_plugins'] = array('elementor/elementor.php');
        Sitepulse_Test_WP::$site_options['auto_update_plugins'] = array('premium/premium.php');
        Sitepulse_Test_WP::$site_transients['update_plugins'] = (object) array('response' => array(
            'elementor/elementor.php' => (object) array('new_version' => '3.31.0', 'package' => 'https://downloads.wordpress.org/plugin/elementor.3.31.0.zip', 'requires' => '6.6', 'requires_php' => '7.4', 'tested' => '6.9'),
            'premium/premium.php' => (object) array('new_version' => '2.1', 'package' => ''),
        ));

        $this->assertSame(array(
            array('file' => 'elementor/elementor.php', 'name' => 'Elementor', 'version' => '3.30.1', 'active' => true, 'auto_update' => false,
                'update' => array('version' => '3.31.0', 'requires_wp' => '6.6', 'requires_php' => '7.4', 'tested' => '6.9', 'download' => true)),
            array('file' => 'premium/premium.php', 'name' => 'Premium', 'version' => '2.0', 'active' => false, 'auto_update' => true,
                'update' => array('version' => '2.1', 'requires_wp' => null, 'requires_php' => null, 'tested' => null, 'download' => false)),
            array('file' => 'hello.php', 'name' => '', 'version' => '', 'active' => false, 'auto_update' => false, 'update' => null),
        ), $this->updates()['plugins']);
    }

    public function test_themes_list_the_theme_in_use_its_parent_and_offered_updates(): void {
        Sitepulse_Test_WP::$theme = array('stylesheet' => 'astra-child', 'template' => 'astra');
        Sitepulse_Test_WP::$themes = array(
            'astra' => array('Name' => 'Astra', 'Version' => '4.11.0'),
            'astra-child' => array('Name' => 'Astra Child', 'Version' => '1.0'),
            'twentytwentyfive' => array('Name' => 'Twenty Twenty-Five', 'Version' => '1.2'),
        );
        Sitepulse_Test_WP::$site_options['auto_update_themes'] = array('twentytwentyfive');
        Sitepulse_Test_WP::$site_transients['update_themes'] = (object) array('response' => array(
            'twentytwentyfive' => array('theme' => 'twentytwentyfive', 'new_version' => '1.3', 'package' => 'https://downloads.wordpress.org/theme/twentytwentyfive.1.3.zip', 'requires' => '6.7', 'requires_php' => '7.2'),
        ));
        Sitepulse_Test_WP::$translation_updates = array((object) array('slug' => 'astra'), (object) array('slug' => 'default'));

        $updates = $this->updates();

        $this->assertSame(array(
            array('stylesheet' => 'astra', 'name' => 'Astra', 'version' => '4.11.0', 'active' => true, 'auto_update' => false, 'update' => null),
            array('stylesheet' => 'astra-child', 'name' => 'Astra Child', 'version' => '1.0', 'active' => true, 'auto_update' => false, 'update' => null),
            array('stylesheet' => 'twentytwentyfive', 'name' => 'Twenty Twenty-Five', 'version' => '1.2', 'active' => false, 'auto_update' => true,
                'update' => array('version' => '1.3', 'requires_wp' => '6.7', 'requires_php' => '7.2', 'tested' => null, 'download' => true)),
        ), $updates['themes']);
        $this->assertSame(2, $updates['translations_pending']);
    }

    public function test_update_lists_ignore_a_corrupt_update_cache(): void {
        Sitepulse_Test_WP::$plugins = array('a/a.php' => array('Name' => 'A', 'Version' => '1'));
        Sitepulse_Test_WP::$site_transients['update_plugins'] = (object) array('response' => 'corrupt');
        Sitepulse_Test_WP::$site_transients['update_themes'] = 'corrupt';
        $updates = $this->updates();
        $this->assertNull($updates['plugins'][0]['update']);
        $this->assertSame(array(), $updates['themes']);
    }
}
