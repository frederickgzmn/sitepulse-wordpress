<?php
abstract class Sitepulse_Test_Case extends PHPUnit\Framework\TestCase {
    protected function setUp(): void { parent::setUp(); Sitepulse_Test_WP::reset(); }
    protected function ajaxResponse(callable $callback): Sitepulse_Test_Json_Response {
        try { $callback(); } catch (Sitepulse_Test_Json_Response $response) { return $response; }
        $this->fail('Expected the handler to send a terminating JSON response.');
    }
    protected function setStaticProperty($class, $name, $value): void {
        $property = new ReflectionProperty($class, $name); if (PHP_VERSION_ID < 80100) { $property->setAccessible(true); } $property->setValue(null, $value);
    }
}
