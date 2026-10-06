<?php
/** Regression tests for configuration persistence and onboarding dismissal. */
final class CoreRegressionTest extends Sitepulse_Test_Case {
    public function test_dismissing_onboarding_persists_dismissed_state(): void {
        $onboarding = new Sitepulse_Onboarding();
        $onboarding->mark_onboarding_dismissed();
        $this->assertTrue($onboarding->is_onboarding_dismissed(), 'Dismissal must persist true so activation no longer redirects to the wizard.');
    }
    public function test_already_prefixed_option_name_is_not_prefixed_again(): void {
        $setup = new Sitepulse_Setup();
        $setup->setOption('wpsp_custom_setting', 'saved');
        $this->assertSame('saved', get_option('wpsp_custom_setting'), 'Passing an already namespaced key must update that same key.');
        $this->assertFalse(get_option('_wpsp_custom_setting'));
    }
    public function test_empty_existing_option_can_be_updated(): void {
        update_option('wpsp_empty', '');
        $this->assertTrue((new Sitepulse_Setup())->setOption('empty', 'filled'));
        $this->assertSame('filled', get_option('wpsp_empty'));
    }
}
