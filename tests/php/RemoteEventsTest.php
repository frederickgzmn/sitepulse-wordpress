<?php
require_once __DIR__ . '/Support/RemoteManagement.php';

final class RemoteEventsTest extends Sitepulse_Test_Case {
    private $files = array();

    protected function setUp(): void {
        parent::setUp();
        update_option('sitepulse_license_key', SITEPULSE_TEST_LICENSE);
        update_option(Sitepulse_Remote_Management::STATE_OPTION, array('active' => true));
        Sitepulse_Remote_Events::init();
    }

    protected function tearDown(): void {
        foreach (array_reverse($this->files) as $path) { is_dir($path) ? rmdir($path) : unlink($path); }
        parent::tearDown();
    }

    /** A plugin on disk; its header comes from Sitepulse_Test_WP::$plugins. */
    private function plugin(string $file, string $name, string $version): void {
        Sitepulse_Test_WP::$plugins[$file] = array('Name' => $name, 'Version' => $version);
        $path = WP_PLUGIN_DIR . '/' . $file;
        if (!is_dir(dirname($path))) { mkdir(dirname($path)); $this->files[] = dirname($path); }
        if (!is_file($path)) { touch($path); $this->files[] = $path; }
    }

    private function theme(string $stylesheet, string $version): void {
        $directory = get_theme_root() . '/' . $stylesheet;
        if (!is_dir($directory)) { mkdir($directory); $this->files[] = $directory; $this->files[] = $directory . '/style.css'; }
        file_put_contents($directory . '/style.css', "/*\nTheme Name: " . ucfirst($stylesheet) . "\nVersion: $version */\n");
    }

    private function events(): array { return get_option(Sitepulse_Remote_Management::EVENTS_OPTION, array()); }

    public function test_listeners_are_registered(): void {
        $this->assertNotFalse(has_filter('upgrader_pre_install', array('Sitepulse_Remote_Events', 'remember_version')));
        foreach (array('upgrader_process_complete' => 'update_completed', '_core_updated_successfully' => 'core_updated', 'activated_plugin' => 'plugin_activated', 'deactivated_plugin' => 'plugin_deactivated', 'switch_theme' => 'theme_switched') as $hook => $method) {
            $this->assertNotFalse(has_action($hook, array('Sitepulse_Remote_Events', $method)), $hook);
        }
    }

    public function test_plugin_updates_report_versions_before_and_after_and_failures(): void {
        $this->plugin('seo/seo.php', 'SEO', '1.0');
        $this->plugin('forms/forms.php', 'Forms', '2.0');
        foreach (array('seo/seo.php', 'forms/forms.php') as $file) {
            $this->assertTrue(apply_filters('upgrader_pre_install', true, array('plugin' => $file, 'temp_backup' => array())));
        }
        $this->plugin('seo/seo.php', 'SEO', '1.1');

        do_action('upgrader_process_complete', new stdClass(), array('action' => 'update', 'type' => 'plugin', 'bulk' => true, 'plugins' => array('seo/seo.php', 'forms/forms.php', 'gone/gone.php')));

        $events = $this->events();
        $this->assertCount(1, $events);
        $this->assertSame('update.completed', $events[0]['type']);
        $this->assertSame(array('kind' => 'plugin', 'initiator' => 'user', 'items' => array(
            array('id' => 'seo/seo.php', 'from' => '1.0', 'to' => '1.1', 'ok' => true),
            array('id' => 'forms/forms.php', 'from' => '2.0', 'to' => '2.0', 'ok' => false),
            array('id' => 'gone/gone.php', 'from' => null, 'to' => null, 'ok' => false),
        )), $events[0]['data']);
        $this->assertMatchesRegularExpression('/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/', $events[0]['id']);
        $this->assertEqualsWithDelta(time(), strtotime($events[0]['occurred_at']), 2);
        $this->assertTrue(Sitepulse_Remote_Management::state()['inventory_due']);
    }

    public function test_automatic_theme_updates_read_the_new_version_from_disk(): void {
        $this->theme('astra', '4.11.0');
        apply_filters('upgrader_pre_install', true, array('theme' => 'astra'));
        $this->theme('astra', '4.11.1');
        add_action('wp_maybe_auto_update', static function () {
            do_action('upgrader_process_complete', new stdClass(), array('action' => 'update', 'type' => 'theme', 'theme' => 'astra'));
        });

        do_action('wp_maybe_auto_update');

        $this->assertSame(array('kind' => 'theme', 'initiator' => 'auto', 'items' => array(array('id' => 'astra', 'from' => '4.11.0', 'to' => '4.11.1', 'ok' => true))), $this->events()[0]['data']);
    }

    public function test_translation_updates_list_each_language_pack(): void {
        do_action('upgrader_process_complete', new stdClass(), array('action' => 'update', 'type' => 'translation', 'translations' => array(
            array('language' => 'de_DE', 'type' => 'plugin', 'slug' => 'akismet', 'version' => '5.3'),
            (object) array('language' => 'es_ES', 'type' => 'core', 'slug' => 'default'),
        )));

        $this->assertSame(array(
            array('id' => 'plugin:akismet:de_DE', 'from' => null, 'to' => '5.3', 'ok' => true),
            array('id' => 'core:default:es_ES', 'from' => null, 'to' => null, 'ok' => false),
        ), $this->events()[0]['data']['items']);
    }

    public function test_core_updates_report_the_version_the_request_started_with(): void {
        $GLOBALS['wp_version'] = '6.9';
        do_action('upgrader_process_complete', new stdClass(), array('action' => 'update', 'type' => 'core'));
        $this->assertSame(array(), $this->events(), 'Core updates are reported when WordPress confirms them.');

        do_action('_core_updated_successfully', '6.8.3');

        $this->assertSame(array('kind' => 'core', 'initiator' => 'user', 'items' => array(array('id' => 'wordpress', 'from' => '6.8', 'to' => '6.8.3', 'ok' => true))), $this->events()[0]['data']);
    }

    public function test_new_plugins_and_themes_refresh_the_inventory_without_an_event(): void {
        do_action('upgrader_process_complete', new stdClass(), array('action' => 'install', 'type' => 'plugin'));
        do_action('upgrader_process_complete', new stdClass(), 'not details');
        $this->assertSame(array(), $this->events());
        $this->assertTrue(Sitepulse_Remote_Management::state()['inventory_due']);
        $this->assertSame('response', apply_filters('upgrader_pre_install', 'response', null));
    }

    public function test_plugin_activation_and_theme_switch_are_reported(): void {
        $this->plugin('shop/shop.php', 'Shop', '9.1');
        do_action('activated_plugin', 'shop/shop.php', false);
        do_action('deactivated_plugin', 'shop/shop.php', false);
        do_action('deactivated_plugin', 'deleted/deleted.php', false);
        Sitepulse_Test_WP::$themes = array('astra' => array('Name' => 'Astra', 'Version' => '4.11.0'), 'twentytwentyfive' => array('Name' => 'Twenty Twenty-Five', 'Version' => '1.2'));
        do_action('switch_theme', 'Astra', wp_get_theme('astra'), wp_get_theme('twentytwentyfive'));

        $events = $this->events();
        $this->assertSame(array('plugin.activated', 'plugin.deactivated', 'plugin.deactivated', 'theme.switched'), array_column($events, 'type'));
        $this->assertSame(array('file' => 'shop/shop.php', 'name' => 'Shop', 'version' => '9.1'), $events[0]['data']);
        $this->assertSame(array('file' => 'deleted/deleted.php', 'name' => '', 'version' => ''), $events[2]['data']);
        $this->assertSame(array('stylesheet' => 'astra', 'name' => 'Astra', 'version' => '4.11.0', 'previous' => 'twentytwentyfive'), $events[3]['data']);
    }

    public function test_nothing_is_recorded_while_remote_management_is_off(): void {
        update_option(Sitepulse_Remote_Management::STATE_OPTION, array('active' => false));
        $this->plugin('shop/shop.php', 'Shop', '9.1');
        do_action('activated_plugin', 'shop/shop.php', false);
        do_action('upgrader_process_complete', new stdClass(), array('action' => 'install', 'type' => 'theme'));
        $this->assertSame(array(), $this->events());
        $this->assertFalse(Sitepulse_Remote_Management::state()['inventory_due']);
    }
}
