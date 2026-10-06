<?php
abstract class Sitepulse_Test_Case extends PHPUnit\Framework\TestCase {
    protected function setUp(): void { parent::setUp(); Sitepulse_Test_WP::reset(); }
    protected function ajaxResponse(callable $callback): Sitepulse_Test_Json_Response {
        try { $callback(); } catch (Sitepulse_Test_Json_Response $response) { return $response; }
        $this->fail('Expected the handler to send a terminating JSON response.');
    }
    /** Turn the current request into a Page Analysis request carrying a valid one-time token. */
    protected function startAnalysisRequest($token = 'abcdefghijklmnopqrstuvwxyz012345', $analysis = 'analysis1'): string {
        set_transient('sitepulse_pa_token_' . $token, $analysis); $_GET['sitepulse_analyze'] = $token; Sitepulse_Page_Tracker::init(); return $token;
    }
    protected function setStaticProperty($class, $name, $value): void {
        $property = new ReflectionProperty($class, $name); if (PHP_VERSION_ID < 80100) { $property->setAccessible(true); } $property->setValue(null, $value);
    }
}
