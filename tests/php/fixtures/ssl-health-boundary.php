<?php
/** Contract double for the optional external Pro plugin, absent from this repository. */
class Sitepulse_SSL_Health {
    public static $result;
    public static function get_cached_result() { return self::$result; }
}
