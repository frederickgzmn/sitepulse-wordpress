<?php

/** Database boundary spy: tests supply returned rows and inspect emitted SQL/writes. */
final class Sitepulse_Test_Database {
    public $options = 'wp_options';
    public function db_version() { return '8.0.36'; }
    public $results = array();
    public $row = null;
    public $var = null;
    public $update_result = 1;
    public $queries = array();
    public $updates = array();

    public function prepare($query, ...$args) {
        if (count($args) === 1 && is_array($args[0])) { $args = $args[0]; }
        $position = 0;
        return preg_replace_callback('/%[ds]/', static function ($match) use ($args, &$position) {
            $value = $args[$position++];
            return $match[0] === '%d' ? (string) (int) $value : "'" . str_replace("'", "''", $value) . "'";
        }, $query);
    }

    public function get_results($query, $output = OBJECT) {
        $this->queries[] = $query;
        return $this->results;
    }

    public function get_row($query, $output = OBJECT) {
        $this->queries[] = $query;
        return $this->row;
    }

    public function get_var($query) {
        $this->queries[] = $query;
        return $this->var;
    }

    public function update($table, $data, $where, $format = null, $where_format = null) {
        $this->updates[] = compact('table', 'data', 'where', 'format', 'where_format');
        return $this->update_result;
    }
}
