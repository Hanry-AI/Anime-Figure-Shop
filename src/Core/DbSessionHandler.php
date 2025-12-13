<?php
namespace DACS\Core;

use SessionHandlerInterface;

class DbSessionHandler implements SessionHandlerInterface {
    private $db;

    public function __construct($dbConnection) {
        $this->db = $dbConnection;
    }

    public function open($path, $name): bool { return true; }
    public function close(): bool { return true; }

    public function read($id): string|false {
        $stmt = $this->db->prepare("SELECT data FROM sessions WHERE id = ?");
        $stmt->bind_param("s", $id);
        if ($stmt->execute()) {
            $res = $stmt->get_result();
            if ($row = $res->fetch_assoc()) return $row['data'];
        }
        return '';
    }

    public function write($id, $data): bool {
        $access = time();
        // Dùng REPLACE để tự động chèn mới hoặc ghi đè cũ
        $stmt = $this->db->prepare("REPLACE INTO sessions (id, access, data) VALUES (?, ?, ?)");
        $stmt->bind_param("sis", $id, $access, $data);
        return $stmt->execute();
    }

    public function destroy($id): bool {
        $stmt = $this->db->prepare("DELETE FROM sessions WHERE id = ?");
        $stmt->bind_param("s", $id);
        return $stmt->execute();
    }

    public function gc($max_lifetime): int|false {
        $old = time() - $max_lifetime;
        $stmt = $this->db->prepare("DELETE FROM sessions WHERE access < ?");
        $stmt->bind_param("i", $old);
        $stmt->execute();
        return $stmt->affected_rows;
    }
}