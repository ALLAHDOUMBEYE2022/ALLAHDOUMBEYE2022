<?php
/**
 * Connexion PDO unique (MySQL ou SQLite selon la configuration).
 */

/** Ouvre une connexion ; lève une PDOException en cas d'échec. */
function db_connect(string $driver, string $host, string $name, string $user, string $pass): PDO
{
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];
    if ($driver === 'sqlite') {
        $dir = dirname(DB_SQLITE_PATH);
        if (!is_dir($dir)) {
            mkdir($dir, 0775, true);
        }
        $pdo = new PDO('sqlite:' . DB_SQLITE_PATH, null, null, $options);
        $pdo->exec('PRAGMA foreign_keys = ON');
        return $pdo;
    }
    return new PDO('mysql:host=' . $host . ';dbname=' . $name . ';charset=utf8mb4', $user, $pass, $options);
}

function db(): PDO
{
    static $pdo = null;
    if ($pdo !== null) {
        return $pdo;
    }
    try {
        $pdo = db_connect(DB_DRIVER, DB_HOST, DB_NAME, DB_USER, DB_PASS);
    } catch (PDOException $e) {
        http_response_code(500);
        exit('<h1>Erreur de connexion à la base de données</h1><p>Relancez <code>install.php</code> ou vérifiez les paramètres de connexion.</p>'
            . '<p style="color:#888">' . htmlspecialchars($e->getMessage()) . '</p>');
    }
    return $pdo;
}

function db_all(string $sql, array $params = []): array
{
    $st = db()->prepare($sql);
    $st->execute($params);
    return $st->fetchAll();
}

function db_one(string $sql, array $params = []): ?array
{
    $st = db()->prepare($sql);
    $st->execute($params);
    $row = $st->fetch();
    return $row === false ? null : $row;
}

function db_value(string $sql, array $params = [])
{
    $st = db()->prepare($sql);
    $st->execute($params);
    return $st->fetchColumn();
}

function db_exec(string $sql, array $params = []): int
{
    $st = db()->prepare($sql);
    $st->execute($params);
    return $st->rowCount();
}

function is_installed(): bool
{
    return file_exists(ROOT_PATH . '/data/install.lock');
}
