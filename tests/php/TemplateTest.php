<?php

final class TemplateTest extends Sitepulse_Test_Case {
    private function render($partial, array $variables): string {
        extract($variables, EXTR_SKIP);
        ob_start();
        try { require SITEPULSE_PATH . 'templates/easy/partials/' . $partial . '.php'; return ob_get_contents(); }
        finally { ob_end_clean(); }
    }

    public static function scores(): array {
        return array(array(0, 'Needs Work'), array(39, 'Needs Work'), array(40, 'Fair'), array(59, 'Fair'), array(60, 'Good'), array(79, 'Good'), array(80, 'Excellent'), array(100, 'Excellent'));
    }

    /** @dataProvider scores */
    public function test_score_ring_renders_status_at_boundaries($score, $status): void {
        $html = $this->render('score-ring', array('ring_score' => $score));
        $this->assertStringContainsString('data-score="' . $score . '"', $html);
        $this->assertStringContainsString('>' . $status . '</span>', $html);
    }

    public function test_score_ring_escapes_labels_and_accepts_only_known_sizes(): void {
        $html = $this->render('score-ring', array('ring_label' => '<script>alert(1)</script>', 'ring_size' => '" onmouseover="bad'));
        $this->assertStringNotContainsString('<script>', $html);
        $this->assertStringContainsString('&lt;script&gt;', $html);
        $this->assertStringNotContainsString('onmouseover', $html);
        $this->assertStringContainsString('sp-ring-sm', $this->render('score-ring', array('ring_size' => 'sm')));
    }

    public function test_metric_card_escapes_values_and_falls_back_for_unknown_colors(): void {
        $html = $this->render('metric-card', array('metric_color' => 'red; background:url(evil)', 'metric_value' => '<img src=x>', 'metric_label' => '<b>Metric</b>', 'metric_trend' => '<script>trend</script>'));
        $this->assertStringContainsString('var(--sp-accent-soft)', $html);
        $this->assertStringNotContainsString('url(evil)', $html);
        $this->assertStringNotContainsString('<img', $html);
        $this->assertStringNotContainsString('<b>', $html);
        $this->assertStringNotContainsString('<script>', $html);
        $this->assertStringContainsString('&lt;img src=x&gt;', $html);
    }

    public function test_metric_card_omits_absent_label_and_trend(): void {
        $html = $this->render('metric-card', array());
        $this->assertStringNotContainsString('sp-metric-label', $html);
        $this->assertStringNotContainsString('sp-metric-trend', $html);
        $this->assertStringContainsString('—', $html);
    }
}
