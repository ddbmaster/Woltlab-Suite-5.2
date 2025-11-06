<?php
namespace wcf\page;

use wcf\page\AbstractPage;
use wcf\system\WCF;
use wcf\system\exception\PermissionDeniedException;
use PDO;

class BanDeletePage extends AbstractPage {
    public $templateName = 'banlist'; // zeigt danach wieder die Bannliste
    public $error = '';
    public $success = '';
    public $bans = [];

    public function readData() {
        parent::readData();

        if (!WCF::getSession()->getPermission('user.cs2bannliste.canDelete')) {
            throw new PermissionDeniedException();
        }

        $banId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

        try {
            $pdo = $this->connect();
            $cfg = $this->getConfig();
            $table = ($cfg['prefix'] ?? '') . ($cfg['table'] ?? 'sa_bans');

            if ($banId > 0) {
                // Optional: Unban-Einträge löschen
                $pdo->prepare("DELETE FROM sa_unbans WHERE ban_id = ?")->execute([$banId]);

                // Bann löschen
                $stmt = $pdo->prepare("DELETE FROM {$table} WHERE id = ?");
                $stmt->execute([$banId]);

                $this->success = "✅ Bann erfolgreich gelöscht.";
            }

            // Bannliste erneut laden
            $stmt = $pdo->query("SELECT * FROM {$table} ORDER BY created DESC LIMIT 200");
            $this->bans = $stmt->fetchAll();
        } catch (\Throwable $e) {
            $this->error = "Fehler: " . $e->getMessage();
        }
    }

    public function assignVariables() {
        parent::assignVariables();

        WCF::getTPL()->assign([
            'bans'      => $this->bans,
            'error'     => $this->error,
            'success'   => $this->success,
            'canAdd'    => WCF::getSession()->getPermission('user.cs2bannliste.canAdd'),
            'canEdit'   => WCF::getSession()->getPermission('user.cs2bannliste.canEdit'),
            'canUnban'  => WCF::getSession()->getPermission('user.cs2bannliste.canUnban'),
            'canDelete' => WCF::getSession()->getPermission('user.cs2bannliste.canDelete')
        ]);
    }

    private function getConfig() {
        return require WCF_DIR . 'config/cs2bannliste.php';
    }

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
