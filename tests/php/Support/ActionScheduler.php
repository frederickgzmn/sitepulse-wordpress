<?php
/** External Action Scheduler boundary. Loaded only by isolated tests. */
final class ActionScheduler_Store {
    const STATUS_PENDING = 'pending';
    public static $past_due_ids = array();
    public static $queries = array();
    public static $throw_on_query = false;
    public static function instance() { return new self(); }
    public function query_actions($query, $query_type = 'select') {
        self::$queries[] = array('query' => $query, 'type' => $query_type);
        if (self::$throw_on_query) { throw new RuntimeException('Scheduler unavailable'); }
        return self::$past_due_ids;
    }
}
