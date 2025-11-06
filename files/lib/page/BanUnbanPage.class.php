<?php
namespace wcf\page;

use wcf\page\AbstractPage;
use wcf\system\WCF;
use wcf\system\exception\PermissionDeniedException;
use PDO;

class BanUnbanPage extends AbstractPage {
    public $templateName = 'banunban';
    public $error = '';
    public $success = '';

    public function readData() {
        parent::readData();

        if (!WCF::getSession()->getPermission('user.cs2bannliste.canUnban')) {
            throw new PermissionDeniedException();
        }

        $banId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $pdo = $this->connect();

                // Steam-ID des aktuellen Foren-Admins holen
                $adminSteamId = $this->resolveAdminSteamId();
                if (!$adminSteamId) {
                    $this->error = "⚠ Keine Steam-ID im Profil hinterlegt (Profilfeld 117).";
                    return;
                }

                // CS2-Admin-ID anhand der Steam-ID bestimmen
                $adminId = $this->getAdminIdBySteamId($adminSteamId);
                if ($adminId === null) {
                    $this->error = "⚠ Kein Eintrag für diese Steam-ID in der CS2-Admin-Tabelle gefunden.";
                    return;
                }

                // Bannstatus ändern
                $stmt = $pdo->prepare("UPDATE sa_bans SET status = 'UNBANNED' WHERE id = ?");
                $stmt->execute([$banId]);

                // Eintrag in sa_unbans setzen
                $stmt = $pdo->prepare("
                    INSERT INTO sa_unbans (ban_id, admin_id, reason, date)
                    VALUES (?, ?, 'Entbannt über Forum', NOW())
                ");
                $stmt->execute([$banId, $adminId]);

                $this->success = "✅ Spieler erfolgreich entbannt!";
            } catch (\Throwable $e) {
                $this->error = "Fehler: " . $e->getMessage();
            }
        }
    }

    public function assignVariables() {
        parent::assignVariables();
        WCF::getTPL()->assign([
            'error'   => $this->error,
            'success' => $this->success
        ]);
    }

    /** Holt die CS2-Admin-ID aus der Tabelle sa_admins anhand der Steam-ID */
    private function getAdminIdBySteamId($steamId) {
        $pdo = $this->connect();
        $stmt = $pdo->prepare("SELECT id FROM sa_admins WHERE player_steamid = ?");
        $stmt->execute([$steamId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? (int)$row['id'] : null;
    }

    /** Holt die Steam-ID (Profilfeld 117) des eingeloggten Forum-Benutzers */
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

    /** Lädt die Datenbank-Konfiguration aus config/cs2bannliste.php */
    private function getConfig() {
        return require WCF_DIR . 'config/cs2bannliste.php';
    }

    /** Erstellt PDO-Verbindung zur CS2-Datenbank */
    private function connect() {
        $cfg = $this->getConfig();
        return new PDO(
            "mysql:host={$cfg['hostname']};dbname={$cfg['database']};charset=utf8mb4",
            $cfg['username'],
            $cfg['password'],
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
            ]
        );
    }
}

