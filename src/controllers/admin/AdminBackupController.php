<?php

class AdminBackupController {

    /**
     * Show the backup page.
     */
    public function index(): void {
        view('admin/backup/index', [], 'admin');
    }

    /**
     * Generate and stream a full SQL dump of the database.
     */
    public function downloadDb(): void {
        csrf_verify();

        $db       = getDB();
        $dbName   = DB_NAME;
        $filename = 'backup_db_' . date('Ymd_His') . '.sql';

        // Fetch all table names
        $tables = $db->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);

        $sql  = "-- Comel Baby Store — Database Backup\n";
        $sql .= "-- Generated: " . date('Y-m-d H:i:s') . "\n";
        $sql .= "-- Database : " . $dbName . "\n\n";
        $sql .= "SET FOREIGN_KEY_CHECKS = 0;\n";
        $sql .= "SET SQL_MODE = 'NO_AUTO_VALUE_ON_ZERO';\n";
        $sql .= "SET NAMES utf8mb4;\n";
        $sql .= "SET time_zone = '+08:00';\n\n";

        foreach ($tables as $table) {
            // DROP + CREATE
            $createStmt = $db->query("SHOW CREATE TABLE `{$table}`")->fetch(PDO::FETCH_NUM);
            $sql .= "-- --------------------------------------------------------\n";
            $sql .= "-- Table: `{$table}`\n";
            $sql .= "-- --------------------------------------------------------\n";
            $sql .= "DROP TABLE IF EXISTS `{$table}`;\n";
            $sql .= $createStmt[1] . ";\n\n";

            // Data rows
            $rows = $db->query("SELECT * FROM `{$table}`")->fetchAll(PDO::FETCH_ASSOC);
            if (!empty($rows)) {
                $cols    = array_keys($rows[0]);
                $colList = implode(', ', array_map(fn($c) => "`{$c}`", $cols));
                $sql    .= "INSERT INTO `{$table}` ({$colList}) VALUES\n";
                $chunks  = [];
                foreach ($rows as $row) {
                    $vals = array_map(function ($v) use ($db) {
                        if ($v === null) return 'NULL';
                        return $db->quote($v);
                    }, array_values($row));
                    $chunks[] = '(' . implode(', ', $vals) . ')';
                }
                $sql .= implode(",\n", $chunks) . ";\n\n";
            }
        }

        $sql .= "SET FOREIGN_KEY_CHECKS = 1;\n";

        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . strlen($sql));
        header('Cache-Control: no-cache, no-store, must-revalidate');
        header('Pragma: no-cache');
        header('Expires: 0');
        echo $sql;
        exit;
    }

    /**
     * Zip the uploads folder and stream it as a download.
     */
    public function downloadFiles(): void {
        csrf_verify();

        if (!class_exists('ZipArchive')) {
            flash('error', 'ZipArchive tidak tersedia pada pelayan ini. Sila hubungi hosting provider anda.');
            redirect('/admin/backup');
            return;
        }

        $uploadsDir = ROOT_PATH . '/public/uploads';
        if (!is_dir($uploadsDir)) {
            flash('error', 'Folder uploads tidak dijumpai.');
            redirect('/admin/backup');
            return;
        }

        $tmpFile  = tempnam(sys_get_temp_dir(), 'comel_backup_files_');
        $filename = 'backup_files_' . date('Ymd_His') . '.zip';

        $zip = new ZipArchive();
        if ($zip->open($tmpFile, ZipArchive::OVERWRITE) !== true) {
            flash('error', 'Gagal mencipta fail ZIP. Semak kebenaran folder sementara.');
            redirect('/admin/backup');
            return;
        }

        $this->addDirToZip($zip, $uploadsDir, 'uploads');
        $zip->close();

        header('Content-Type: application/zip');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . filesize($tmpFile));
        header('Cache-Control: no-cache, no-store, must-revalidate');
        header('Pragma: no-cache');
        header('Expires: 0');

        readfile($tmpFile);
        unlink($tmpFile);
        exit;
    }

    /**
     * Recursively add a directory to a ZipArchive.
     */
    private function addDirToZip(ZipArchive $zip, string $dir, string $zipPath): void {
        $zip->addEmptyDir($zipPath);
        $items = scandir($dir);
        foreach ($items as $item) {
            if ($item === '.' || $item === '..') continue;
            $fullPath    = $dir . DIRECTORY_SEPARATOR . $item;
            $zipItemPath = $zipPath . '/' . $item;
            if (is_dir($fullPath)) {
                $this->addDirToZip($zip, $fullPath, $zipItemPath);
            } else {
                $zip->addFile($fullPath, $zipItemPath);
            }
        }
    }
}
