<?php
namespace wcf\page;

use wcf\page\AbstractPage;
use wcf\system\WCF;
use wcf\system\exception\PermissionDeniedException;
use PDO;

class BanEditPage extends AbstractPage {
    public $templateName = 'banedit';
    public $error = '';
    public $success = '';
    public $ban = [];

    private $host = 'xxxxxxx.de';
    private $db   = 'xxxxxxx';
    private $user = 'xxxxxxx';
    private $pass = 'xxxxxxxxxxxxx';
    private $table = 'sa_bans';

    public function readData() {
        parent::readData();

        if (!WCF::getSession()->getPermission('user.cs2bannliste.canEdit')) {
            throw new PermissionDeniedException();
        }

        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        if (!$id) {
            $this->error = "Ungültige Bann-ID.";
            return;
        }

        try {
            $pdo = $this->connect();
            $stmt = $pdo->prepare("SELECT * FROM {$this->table} WHERE id = ?");
            $stmt->execute([$id]);
            $this->ban = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$this->ban) {
                $this->error = "Bann nicht gefunden.";
                return;
            }

            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $admin_name = WCF::getUser()->username;
                $admin_steamid = $this->resolveAdminSteamId();

                if (!$admin_steamid) {
                    $this->error = "⚠ Keine Steam-ID im Profil eingetragen (Profilfeld 117).";
                    return;
                }

                $newStatus = ($this->ban['status'] === 'UNBANNED') ? 'ACTIVE' : $this->ban['status'];

                $stmt = $pdo->prepare("
                    UPDATE {$this->table}
                    SET player_name=?, player_steamid=?, reason=?, duration=?,
                        admin_name=?, admin_steamid=?, status=?
                    WHERE id=?
                ");
                $stmt->execute([
                    $_POST['player_name'],
                    $_POST['player_steamid'],
                    $_POST['reason'],
                    $_POST['duration'],
                    $admin_name,
                    $admin_steamid,
                    $newStatus,
                    $id
                ]);

                $this->success = "✅ Bann aktualisiert.";

                $stmt = $pdo->prepare("SELECT * FROM {$this->table} WHERE id = ?");
                $stmt->execute([$id]);
                $this->ban = $stmt->fetch(PDO::FETCH_ASSOC);
            }

        } catch (\Throwable $e) {
            $this->error = $e->getMessage();
        }
    }

    public function assignVariables() {
        parent::assignVariables();
        WCF::getTPL()->assign([
            'ban' => $this->ban,
            'error' => $this->error,
            'success' => $this->success
        ]);
    }

    private function resolveAdminSteamId() {
        $userID = (int)WCF::getUser()->userID;
        try {
            $stmt = WCF::getDB()->prepareStatement("
                SELECT userOption117 FROM wcf1_user_option_value WHERE userID = ?
            ");
            $stmt->execute([$userID]);
            $row = $stmt->fetchArray();
            if (!empty($row['userOption117'])) return (string)$row['userOption117'];
        } catch (\Throwable $e) {}
        return null;
    }

    private function connect() {
        return new PDO(
            "mysql:host={$this->host};dbname={$this->db};charset=utf8mb4",
            $this->user,
            $this->pass,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
            ]
        );
    }
}
