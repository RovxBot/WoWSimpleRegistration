<?php
// Read-only statistics integration tests against an isolated in-memory SQLite database.
require_once __DIR__ . '/../../application/include/functions.php';
require_once __DIR__ . '/../../application/include/realm-stats.php';
date_default_timezone_set('UTC');

class RealmStatsTestResult
{
    public function __construct(private PDOStatement $statement) {}
    public function fetchAssociative() { return $this->statement->fetch(PDO::FETCH_ASSOC); }
    public function fetchAllAssociative() { return $this->statement->fetchAll(PDO::FETCH_ASSOC); }
    public function fetchOne() { return $this->statement->fetchColumn(); }
    public function iterateAssociative() { while ($row = $this->fetchAssociative()) { yield $row; } }
}
class RealmStatsTestConnection
{
    public function __construct(public PDO $pdo) {}
    public function executeQuery($sql, $params = []) {
        $statement = $this->pdo->prepare($sql);
        $statement->execute($params);
        return new RealmStatsTestResult($statement);
    }
}
class database { public static $chars; public static $auth; }
$config = array_replace($config ?? [], ['realmlists'=>[['realmid'=>1,'realmname'=>'Test realm']], 'realm_stats_cache_ttl'=>0,
    'disable_top_players'=>false,'disable_online_players'=>false]);
$pdo = new PDO('sqlite::memory:');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$pdo->sqliteCreateFunction('UNIX_TIMESTAMP', static fn($value) => strtotime($value));
$pdo->sqliteCreateFunction('FROM_UNIXTIME', static fn($value) => gmdate('Y-m-d H:i:s', (int)$value));
$pdo->exec("CREATE TABLE characters (guid INTEGER PRIMARY KEY,name TEXT,race INTEGER,class INTEGER,level INTEGER,online INTEGER,deleteDate INTEGER,totalKills INTEGER,todayKills INTEGER,yesterdayKills INTEGER);
CREATE TABLE guild (guildid INTEGER PRIMARY KEY,name TEXT,leaderguid INTEGER);
CREATE TABLE guild_member (guildid INTEGER,guid INTEGER);
CREATE TABLE arena_team (arenaTeamId INTEGER PRIMARY KEY,name TEXT,captainGuid INTEGER,type INTEGER,rating INTEGER,seasonGames INTEGER,seasonWins INTEGER,weekGames INTEGER,weekWins INTEGER);
CREATE TABLE arena_team_member (arenaTeamId INTEGER,guid INTEGER);
CREATE TABLE uptime (realmid INTEGER,starttime INTEGER,uptime INTEGER,maxplayers INTEGER);
CREATE TABLE realmlist (id INTEGER,flag INTEGER);
CREATE TABLE log_encounter (time TEXT,map INTEGER,difficulty INTEGER,creditType INTEGER,creditEntry INTEGER,playersInfo TEXT);
INSERT INTO characters VALUES (1,'Aster',1,1,80,1,NULL,200,12,4),(2,'Bram',2,8,80,1,NULL,500,8,1),(3,'Cinder',1,1,70,0,NULL,10,0,0),(4,'Deleted',1,1,80,0,1,9999,0,0);
INSERT INTO guild VALUES (1,'Wayfarers',1),(2,'Nightwatch',2);
INSERT INTO guild_member VALUES (1,1),(1,3),(2,2);
INSERT INTO arena_team VALUES (1,'Bronze',1,2,1500,20,12,4,3),(2,'Gold',2,2,1900,0,0,0,0),(3,'Trio',1,3,2100,10,8,2,2),(4,'Five',2,5,1800,10,5,2,1);
INSERT INTO arena_team_member VALUES (1,1),(1,3),(2,2),(3,1),(4,2);
INSERT INTO realmlist VALUES (1,0);");
$pdo->prepare('INSERT INTO uptime VALUES (1,?,?,6)')->execute([time()-3650,3600]);
database::$chars = [1=>new RealmStatsTestConnection($pdo)];
database::$auth = database::$chars[1];
