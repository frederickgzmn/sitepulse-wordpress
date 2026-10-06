<?php
final class SetupOnboardingTest extends Sitepulse_Test_Case {
    /** @dataProvider versions */
    public function test_activation_installs_or_upgrades_but_does_not_downgrade($version, $expected): void {
        if ($version !== null) { update_option('wpsp_version', $version); }
        $setup = new Sitepulse_Setup(); $setup->activate(); $this->assertSame($expected, get_option('wpsp_version'));
    }
    public static function versions(): array { return array(array(null, '1.4.6'), array('1.0.0', '1.4.6'), array('1.4.5', '1.4.6'), array('1.4.6', '1.4.6'), array('99.0.0', '99.0.0')); }
    public function test_options_are_namespaced_without_affecting_unrelated_options(): void {
        $setup = new Sitepulse_Setup(); update_option('theme', 'unrelated'); $setup->setOption('theme', 'dark');
        $this->assertSame('dark', get_option('wpsp_theme')); $this->assertSame('unrelated', get_option('theme'));
        $this->assertSame('dark', $setup->getOption('theme')); $this->assertSame('fallback', $setup->getOption('missing', 'fallback'));
    }
    public function test_admin_body_class_is_only_added_on_plugin_or_explicitly_registered_screens(): void {
        $setup = new Sitepulse_Setup(); $this->assertSame('admin', $setup->add_sitepulse_body_class('admin'));
        Sitepulse_Test_WP::$screen = (object) array('id' => 'dashboard'); $this->assertSame('admin', $setup->add_sitepulse_body_class('admin'));
        Sitepulse_Test_WP::$screen->id = 'toplevel_page_wpsp_sitepulse'; $this->assertStringContainsString('sitepulse_css_class', $setup->add_sitepulse_body_class('admin'));
        Sitepulse_Test_WP::$screen->id = 'custom-page'; $this->assertStringContainsString('sitepulse_css_class', $setup->add_sitepulse_body_class('admin', array('custom-page')));
    }
    public function test_completing_onboarding_clears_progress_and_reset_clears_persisted_flags(): void {
        $onboarding = new Sitepulse_Onboarding(); $onboarding->set_current_step('3'); $this->assertSame(3, $onboarding->get_current_step());
        $onboarding->mark_onboarding_completed(); $this->assertTrue($onboarding->is_onboarding_completed()); $this->assertArrayNotHasKey('sitepulse_onboarding_current_step', Sitepulse_Test_WP::$options);
        update_option('sitepulse_onboarding_dismissed', true); $onboarding->reset_onboarding();
        $this->assertFalse($onboarding->is_onboarding_completed()); $this->assertFalse($onboarding->is_onboarding_dismissed()); $this->assertSame(1, $onboarding->get_current_step());
    }
    public function test_activation_redirect_is_one_time_and_targets_onboarding(): void {
        set_transient('sitepulse_activation_redirect', true, 30); $onboarding = new Sitepulse_Onboarding();
        try { $onboarding->maybe_redirect_to_onboarding(); $this->fail('Expected redirect'); } catch (Sitepulse_Test_Redirect $response) { $this->assertSame('https://example.test/wp-admin/admin.php?page=wpsp_sitepulse_onboarding', $response->getMessage()); }
        $this->assertFalse(get_transient('sitepulse_activation_redirect')); $onboarding->maybe_redirect_to_onboarding(); $this->assertCount(1, Sitepulse_Test_WP::$redirects);
    }
    /** @dataProvider redirectGuards */
    public function test_redirect_guard_does_not_redirect_and_consumes_activation_transient($capability, $completed, $dismissed): void {
        Sitepulse_Test_WP::$capabilities['manage_options'] = $capability; update_option('sitepulse_onboarding_completed', $completed); update_option('sitepulse_onboarding_dismissed', $dismissed); set_transient('sitepulse_activation_redirect', true, 30);
        (new Sitepulse_Onboarding())->maybe_redirect_to_onboarding(); $this->assertSame(array(), Sitepulse_Test_WP::$redirects); $this->assertFalse(get_transient('sitepulse_activation_redirect'));
    }
    public static function redirectGuards(): array { return array(array(false, false, false), array(true, true, false), array(true, false, true)); }
    public function test_onboarding_assets_only_load_on_wizard_and_supply_rest_credentials(): void {
        $onboarding = new Sitepulse_Onboarding(); $onboarding->enqueue_onboarding_assets('dashboard'); $this->assertSame(array(), Sitepulse_Test_WP::$scripts);
        $onboarding->enqueue_onboarding_assets('admin_page_wpsp_sitepulse_onboarding');
        $config = Sitepulse_Test_WP::$localized['wpsp_onboarding_script']['SitePulseOnboarding'];
        $this->assertSame('https://example.test/wp-json/', $config['rest_url']); $this->assertSame('nonce-wp_rest', $config['nonce']);
        $this->assertContains('sitepulse_bootstrap_js', Sitepulse_Test_WP::$scripts['wpsp_onboarding_script']['deps']);
    }
    public function test_onboarding_render_rejects_nonadministrators_before_rendering(): void {
        Sitepulse_Test_WP::$capabilities['manage_options'] = false; $this->expectException(Sitepulse_Test_Die::class); (new Sitepulse_Onboarding())->render_onboarding_page();
    }
}
