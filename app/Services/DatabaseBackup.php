<?php

namespace App\Services;

use PDO;

/**
 * Writes a complete, importable SQL dump of the application database straight to the browser as a .sql.gz file.
 * Pure PHP (no mysqldump needed on the server), streamed in small pieces, so memory stays flat however large the database is,
 * and nothing is ever written to the server's disk.
 *
 * The file contains: every table's exact CREATE TABLE, all rows as batched INSERTs (binary columns as hex), every view, and it
 * turns foreign-key checks off while importing so the order of tables does not matter. Import with phpMyAdmin or:
 *   gunzip < file.sql.gz | mysql -u USER -p DATABASE
 */
class DatabaseBackup
{
    /** Tables whose rows are throw-away (logins, cache, queued jobs): structure is kept, rows are left out unless asked for. */
    public const SKIPPABLE = ['sessions', 'cache', 'cache_locks', 'jobs', 'job_batches', 'failed_jobs'];

    private const ROWS_PER_INSERT = 200;

    private $deflate;

    /** @param callable(string):void $out receives each compressed piece */
    public function stream(callable $out, bool $skipThrowAway = true): void
    {
        $cfg = config('database.connections.' . config('database.default'));
        $db = $cfg['database'];
        // A separate, unbuffered connection: rows are read one at a time instead of loading a whole table into memory.
        $pdo = new PDO("mysql:host={$cfg['host']};port={$cfg['port']};dbname={$db};charset=utf8mb4", $cfg['username'], $cfg['password'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => false,
        ]);
        // A second, normal connection for the structure queries (an unbuffered connection cannot run another query mid-read).
        $meta = new PDO("mysql:host={$cfg['host']};port={$cfg['port']};dbname={$db};charset=utf8mb4", $cfg['username'], $cfg['password'], [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
        // Read TIMESTAMP columns in UTC, the same zone the file sets for the import, so restored times are exact (as mysqldump does).
        // Without this they would be written in the server's zone (e.g. +05:30) and come back shifted by that much.
        $pdo->exec("SET time_zone = '+00:00'");
        $meta->exec("SET time_zone = '+00:00'");

        $this->deflate = deflate_init(ZLIB_ENCODING_GZIP, ['level' => 6]);
        $w = function (string $s) use ($out) { $c = deflate_add($this->deflate, $s, ZLIB_NO_FLUSH); if ($c !== '') $out($c); };

        $w("-- Database backup of `$db`\n-- Made " . now()->toDateTimeString() . ' by ' . (auth('admin')->user()->email ?? 'console') . "\n"
            . "-- Import: phpMyAdmin > Import, or: gunzip < this-file.sql.gz | mysql -u USER -p DATABASE\n\n"
            . "SET NAMES utf8mb4;\nSET FOREIGN_KEY_CHECKS = 0;\nSET UNIQUE_CHECKS = 0;\nSET SQL_MODE = 'NO_AUTO_VALUE_ON_ZERO';\nSET time_zone = '+00:00';\nSTART TRANSACTION;\n\n");

        $tables = $meta->query("SELECT TABLE_NAME, TABLE_TYPE FROM information_schema.TABLES WHERE TABLE_SCHEMA = " . $meta->quote($db) . " ORDER BY TABLE_NAME")->fetchAll(PDO::FETCH_NUM);
        $views = [];
        foreach ($tables as [$table, $type]) {
            if ($type === 'VIEW') { $views[] = $table; continue; }
            $q = '`' . str_replace('`', '``', $table) . '`';
            $create = $meta->query("SHOW CREATE TABLE $q")->fetch(PDO::FETCH_NUM)[1];
            $w("-- ----------------------------\n-- $table\n-- ----------------------------\nDROP TABLE IF EXISTS $q;\n$create;\n\n");
            if ($skipThrowAway && in_array($table, self::SKIPPABLE, true)) { $w("-- rows of $table left out (sessions / cache / queue)\n\n"); continue; }

            // binary-ish columns are written as hex so nothing is mangled by the character set
            $binary = [];
            foreach ($meta->query("SELECT COLUMN_NAME, DATA_TYPE FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = " . $meta->quote($db) . " AND TABLE_NAME = " . $meta->quote($table) . " ORDER BY ORDINAL_POSITION")->fetchAll(PDO::FETCH_NUM) as [$col, $dt]) {
                $binary[] = in_array(strtolower($dt), ['binary', 'varbinary', 'blob', 'tinyblob', 'mediumblob', 'longblob', 'geometry', 'point', 'linestring', 'polygon'], true);
            }
            $stmt = $pdo->query("SELECT * FROM $q");
            $batch = []; $cols = null;
            while (($row = $stmt->fetch(PDO::FETCH_NUM)) !== false) {
                if ($cols === null) {
                    $names = [];
                    for ($i = 0; $i < $stmt->columnCount(); $i++) $names[] = '`' . str_replace('`', '``', $stmt->getColumnMeta($i)['name']) . '`';
                    $cols = '(' . implode(', ', $names) . ')';
                }
                $vals = [];
                foreach ($row as $i => $v) {
                    if ($v === null) $vals[] = 'NULL';
                    elseif (!empty($binary[$i])) $vals[] = $v === '' ? "''" : '0x' . bin2hex($v);
                    else $vals[] = $meta->quote((string) $v);
                }
                $batch[] = '(' . implode(',', $vals) . ')';
                if (count($batch) >= self::ROWS_PER_INSERT) { $w("INSERT INTO $q $cols VALUES\n" . implode(",\n", $batch) . ";\n"); $batch = []; }
            }
            $stmt->closeCursor();
            if ($batch) $w("INSERT INTO $q $cols VALUES\n" . implode(",\n", $batch) . ";\n");
            $w("\n");
        }
        foreach ($views as $view) {
            $q = '`' . str_replace('`', '``', $view) . '`';
            $create = $meta->query("SHOW CREATE VIEW $q")->fetch(PDO::FETCH_NUM)[1];
            // drop the DEFINER so the view imports under whichever user runs the import
            $create = preg_replace('/\sDEFINER=`[^`]*`@`[^`]*`/', '', $create);
            $w("DROP VIEW IF EXISTS $q;\n$create;\n\n");
        }
        $w("COMMIT;\nSET FOREIGN_KEY_CHECKS = 1;\nSET UNIQUE_CHECKS = 1;\n-- end of backup\n");
        $out(deflate_add($this->deflate, '', ZLIB_FINISH));
    }
}
