<?php
/** Stand-in for an autoloaded admin page class that cannot load before WordPress loads its parent (BackWPup's pages extend WP_List_Table). */
class Sitepulse_Test_Admin_Only_Callback extends Sitepulse_Test_Admin_Screen {
    public static function print_scripts($value) { usleep(1000); return $value . '!'; }
}
