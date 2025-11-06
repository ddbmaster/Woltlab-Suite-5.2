<?php
namespace wcf\page;

use wcf\page\AbstractPage;
use wcf\system\WCF;
use wcf\system\exception\PermissionDeniedException;
use PDO;

class BanlistPage extends AbstractPage {
    public $templateName = 'banlist';
    public $bans = [];
    public $error = '';

    public function readData() {
        parent::readData();

        if (!WCF::getSession()->getPermission('user.cs2bannliste.canView')) {
            throw new PermissionDeniedException();
        }

        try {
            $pdo = $this->connect();
            $cfg = $this->getConfig();
            $table = ($cfg['prefix'] ?? '') . 'sa_bans';

            $stmt = $pdo->query("SELECT * FROM {$table} ORDER BY created DESC LIMIT 200");
            $this->bans = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\Throwable $e) {
            $this->error = $e->getMessage();
        }
    }

    public function assignVariables() {
        parent::assignVariables();
        WCF::getTPL()->assign([
            'bans' => $this->bans,
            'error' => $this->error,
            'canAdd' => WCF::getSession()->getPermission('user.cs2bannliste.canAdd'),
            'canEdit' => WCF::getSession()->getPermission('user.cs2bannliste.canEdit'),
            'canUnban' => WCF::getSession()->getPermission('user.cs2bannliste.canUnban')
        ]);
    }

    protected function getConfig() {
        $file = WCF_DIR . 'config/cs2bannliste.php';
        if (!file_exists($file)) throw new \RuntimeException('Konfigurationsdatei fehlt: ' . $file);
        return require $file;
    }

    protected function connect() {
        $cfg = $this->getConfig();
        $dsn = sprintf(
            'mysql:host=%s;port=%d;dbname=%s;charset=%s',
            $cfg['hostname'], $cfg['port'], $cfg['database'], $cfg['charset'] ?? 'utf8mb4'
        );
        return new PDO($dsn, $cfg['username'], $cfg['password'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]);
    }
}
