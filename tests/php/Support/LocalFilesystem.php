<?php

/** A real, temporary filesystem with fault injection at the WordPress boundary. */
final class Sitepulse_Test_Local_Filesystem {
    public $failure = null;
    public $writes = array();

    private function path($path) {
        if (strpos($path, ABSPATH) !== 0 || strpos($path, '..') !== false) {
            throw new RuntimeException('Test filesystem escaped its temporary WordPress directory.');
        }
        return $path;
    }

    public function is_dir($path) { return is_dir($this->path($path)); }
    public function exists($path) { return file_exists($this->path($path)); }
    public function is_readable($path) { return $this->failure !== 'not_readable' && is_readable($this->path($path)); }
    public function is_writable($path) { return $this->failure !== 'not_writable' && is_writable($this->path($path)); }
    public function mkdir($path, $mode = 0755) { return mkdir($this->path($path), $mode, true); }
    public function get_contents($path) { return $this->failure === 'read_failed' ? false : file_get_contents($this->path($path)); }
    public function chmod($path, $mode) { return chmod($this->path($path), $mode); }
    public function copy($from, $to, $overwrite = false) {
        $this->path($from);
        $this->path($to);
        if ($this->failure === 'backup_failed' || (file_exists($to) && !$overwrite)) { return false; }
        return copy($from, $to);
    }
    public function put_contents($path, $contents, $mode = 0644) {
        $this->path($path);
        $this->writes[] = array('path' => $path, 'bytes' => strlen($contents));
        if ($this->failure === 'write_failed') {
            file_put_contents($path, 'partial');
            return false;
        }
        return file_put_contents($path, $contents) !== false;
    }
    public function move($from, $to, $overwrite = false) {
        $this->path($from);
        $this->path($to);
        if ($this->failure === 'rename_failed' || (file_exists($to) && !$overwrite)) { return false; }
        return rename($from, $to);
    }
    public function delete($path, $recursive = false, $type = false) { return unlink($this->path($path)); }
}
