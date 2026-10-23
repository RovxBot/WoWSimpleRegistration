<?php

/** Read-only public realm statistics. No account credentials or raw logs leave this class. */
class realm_stats
{
    public const ENCOUNTER_LIMIT = 1000;
    private static $memo = [];

    public static function realms()
    {
        $realms = [];
        foreach ((array) get_config('realmlists') as $realm) {
            $id = (int) ($realm['realmid'] ?? 0);
            if ($id > 0) {
                $realms[$id] = $realm;
            }
        }
        return $realms;
    }

    private static function connection($realmId)
    {
        if (!isset(self::realms()[$realmId], database::$chars[$realmId])) {
            throw new RuntimeException('Realm unavailable.');
        }
        return database::$chars[$realmId];
    }

    private static function cached($realmId, $kind, $callback)
    {
        $realm = self::realms()[$realmId] ?? [];
        $key = hash('sha256', json_encode([5, $realmId, $kind, get_config('db_auth_host'), get_config('db_auth_dbname'), $realm['db_host'] ?? '', $realm['db_name'] ?? '']));
        if (isset(self::$memo[$key])) {
            return self::$memo[$key];
        }
        $ttl = get_config('realm_stats_cache_ttl');
        $ttl = $ttl === false ? 30 : max(0, min(300, (int) $ttl));
        // Arbitrary search strings must not create unbounded cache files.
        if (str_starts_with($kind, 'pvp-')) {
            $ttl = 0;
        }
        $directory = get_config('realm_stats_cache_path') ?: sys_get_temp_dir() . '/grim-public-realm-stats';
        $file = $directory . '/' . $key . '.json';
        if ($ttl > 0 && is_file($file) && filesize($file) < 2097152) {
            $cached = json_decode((string) @file_get_contents($file), true);
            if (is_array($cached) && ($cached['saved'] ?? 0) + $ttl > time() && is_array($cached['data'] ?? null)) {
                return self::$memo[$key] = $cached['data'];
            }
        }
        try {
            $data = $callback();
            $data['available'] = true;
        } catch (Throwable $error) {
            // Exception text can contain connection details; never include it in a public response.
            return self::$memo[$key] = ['available' => false];
        }
        if ($ttl > 0 && (is_dir($directory) || @mkdir($directory, 0700, true))) {
            $temporary = $file . '.' . bin2hex(random_bytes(6));
            if (@file_put_contents($temporary, json_encode(['saved' => time(), 'data' => $data]), LOCK_EX) !== false) {
                @chmod($temporary, 0600);
                if (!@rename($temporary, $file)) {
                    @unlink($temporary);
                }
            }
        }
        return self::$memo[$key] = $data;
    }

    public static function summary($realmId)
    {
        return self::cached($realmId, 'summary', static function () use ($realmId) {
            $db = self::connection($realmId);
            $counts = $db->executeQuery("SELECT COUNT(*) AS characters, COALESCE(SUM(online = 1),0) AS online,
                COALESCE(SUM(online = 1 AND race IN (1,3,4,7,11)),0) AS alliance,
                COALESCE(SUM(online = 1 AND race IN (2,5,6,8,10)),0) AS horde
                FROM characters WHERE name <> '' AND (deleteDate IS NULL OR deleteDate = 0)")->fetchAssociative();
            $guilds = (int) $db->executeQuery('SELECT COUNT(*) FROM guild')->fetchOne();
            $heartbeat = database::$auth->executeQuery('SELECT starttime, uptime, maxplayers FROM uptime WHERE realmid = :realm ORDER BY starttime DESC LIMIT 1', ['realm' => $realmId])->fetchAssociative();
            $realm = database::$auth->executeQuery('SELECT flag FROM realmlist WHERE id = :realm', ['realm' => $realmId])->fetchAssociative();
            $reportedAt = $heartbeat ? (int) $heartbeat['starttime'] + (int) $heartbeat['uptime'] : 0;
            $grace = max(120, min(3600, (int) (get_config('realm_heartbeat_grace') ?: 1200)));
            $state = !$heartbeat || !$realm ? 'unknown' : (((int) $realm['flag'] & 2) || time() - $reportedAt > $grace ? 'offline' : 'online');
            return ['state' => $state, 'reported_at' => $reportedAt, 'uptime' => (int) ($heartbeat['uptime'] ?? 0),
                'peak' => (int) ($heartbeat['maxplayers'] ?? 0), 'characters' => (int) $counts['characters'],
                'online' => (int) $counts['online'], 'alliance' => (int) $counts['alliance'], 'horde' => (int) $counts['horde'], 'guilds' => $guilds];
        });
    }

    public static function online_players($realmId)
    {
        if (get_config('disable_online_players')) {
            return ['available' => false, 'disabled' => true];
        }
        return self::cached($realmId, 'online', static function () use ($realmId) {
            return ['rows' => self::connection($realmId)->executeQuery("SELECT name,race,class,level FROM characters WHERE online = 1 AND name <> '' ORDER BY level DESC,name ASC LIMIT 100")->fetchAllAssociative()];
        });
    }

    public static function arena($realmId, $bracket = 2)
    {
        if (get_config('disable_top_players')) {
            return ['available' => false, 'disabled' => true];
        }
        $bracket = in_array($bracket, [2, 3, 5], true) ? $bracket : 2;
        return self::cached($realmId, 'arena-' . $bracket, static function () use ($realmId, $bracket) {
            return ['rows' => self::connection($realmId)->executeQuery('SELECT a.arenaTeamId,a.name,a.rating,a.seasonGames,a.seasonWins,a.weekGames,a.weekWins,c.name AS captain,c.race,
                (SELECT COUNT(*) FROM arena_team_member m WHERE m.arenaTeamId = a.arenaTeamId) AS members
                FROM arena_team a LEFT JOIN characters c ON c.guid = a.captainGuid
                WHERE a.type = :bracket ORDER BY a.rating DESC,a.seasonWins DESC,a.name ASC LIMIT 100', ['bracket' => $bracket])->fetchAllAssociative()];
        });
    }

    public static function pvp($realmId, $faction = 'all', $class = 0, $search = '', $page = 1)
    {
        if (get_config('disable_top_players')) {
            return ['available' => false, 'disabled' => true];
        }
        $class = in_array($class, [1,2,3,4,5,6,7,8,9,11], true) ? $class : 0;
        $faction = in_array($faction, ['all','alliance','horde'], true) ? $faction : 'all';
        $search = substr(trim($search), 0, 48);
        $page = max(1, min(200, $page));
        return self::cached($realmId, 'pvp-' . json_encode([$faction,$class,$search,$page]), static function () use ($realmId, $faction, $class, $search, $page) {
            $where = "c.totalKills > 0 AND c.name <> '' AND (c.deleteDate IS NULL OR c.deleteDate = 0)";
            $params = [];
            if ($faction !== 'all') {
                $where .= $faction === 'alliance' ? ' AND c.race IN (1,3,4,7,11)' : ' AND c.race IN (2,5,6,8,10)';
            }
            if ($class) {
                $where .= ' AND c.class = :class';
                $params['class'] = $class;
            }
            if ($search !== '') {
                $where .= ' AND c.name LIKE :search';
                $params['search'] = '%' . $search . '%';
            }
            $db = self::connection($realmId);
            $total = (int) $db->executeQuery('SELECT COUNT(*) FROM characters c WHERE ' . $where, $params)->fetchOne();
            $rows = $db->executeQuery('SELECT c.name,c.race,c.class,c.level,c.totalKills,c.todayKills,c.yesterdayKills,g.name AS guild
                FROM characters c LEFT JOIN guild_member gm ON gm.guid = c.guid LEFT JOIN guild g ON g.guildid = gm.guildid
                WHERE ' . $where . ' ORDER BY c.totalKills DESC,c.name ASC LIMIT 50 OFFSET ' . (($page - 1) * 50), $params)->fetchAllAssociative();
            return ['rows' => $rows, 'total' => $total, 'page' => $page];
        });
    }

    public static function raid_catalog($expansion = null)
    {
        $catalog = require __DIR__ . '/raid-catalog.php';
        if ($expansion !== null) {
            foreach ($catalog as $map => &$raid) {
                $raid['modes'] = array_filter($raid['modes'], static function ($mode) use ($expansion) {
                    return $mode['expansion'] === $expansion;
                });
                if (!$raid['modes']) {
                    unset($catalog[$map]);
                    continue;
                }
                $raid['difficulties'] = array_keys($raid['modes']);
                $raid['tier'] = reset($raid['modes'])['tier'];
            }
            unset($raid);
        }
        return $catalog;
    }

    /** Consume sensitive core logs privately; return only explicitly selected public statistics. */
    public static function summarize_encounters($records, $guildNames)
    {
        $catalog = self::raid_catalog();
        $result = ['kills' => 0, 'clears' => 0, 'raids' => [], 'guilds' => [], 'recent' => [], 'recent_by_raid' => [], 'truncated' => false];
        $processed = 0;
        foreach ($records as $row) {
            if ($processed++ >= self::ENCOUNTER_LIMIT) {
                $result['truncated'] = true;
                break;
            }
            $map = (int) $row['map'];
            if (!isset($catalog[$map])) {
                continue;
            }
            $difficulty = (int) $row['difficulty'];
            $bossKey = (int) $row['creditType'] . ':' . (int) $row['creditEntry'];
            // The core also logs non-canonical credits for multi-creature encounters.
            // Only cataloged encounters and supported modes contribute to rankings.
            if (!isset($catalog[$map]['modes'][$difficulty], $catalog[$map]['bosses'][$bossKey])) {
                continue;
            }
            $boss = $catalog[$map]['bosses'][$bossKey];
            $result['kills']++;
            $result['clears'] += !empty($boss['final']) ? 1 : 0;
            $bucket = $map . ':' . $difficulty;
            if (!isset($result['raids'][$bucket])) {
                $result['raids'][$bucket] = ['kills' => 0, 'clears' => 0, 'bosses' => []];
            }
            $raid = &$result['raids'][$bucket];
            $raid['kills']++;
            $raid['clears'] += !empty($boss['final']) ? 1 : 0;
            $raid['bosses'][$bossKey] = ($raid['bosses'][$bossKey] ?? 0) + 1;
            unset($raid);
            $guildCounts = [];
            $participants = 0;
            foreach (explode("\n", (string) ($row['playersInfo'] ?? '')) as $line) {
                if (preg_match('/, guild: ([0-9]+)\),/', $line, $match)) {
                    $participants++;
                    $id = (int) $match[1];
                    if ($id > 0) {
                        $guildCounts[$id] = ($guildCounts[$id] ?? 0) + 1;
                    }
                }
            }
            arsort($guildCounts);
            $guildId = $guildCounts && reset($guildCounts) >= (int) ceil($participants * .8) ? (int) array_key_first($guildCounts) : 0;
            if ($guildId && isset($guildNames[$guildId])) {
                if (!isset($result['guilds'][$guildId])) {
                    $result['guilds'][$guildId] = ['kills' => 0, 'clears' => 0, 'unique_bosses' => []];
                }
                $result['guilds'][$guildId]['kills']++;
                $result['guilds'][$guildId]['clears'] += !empty($boss['final']) ? 1 : 0;
                $result['guilds'][$guildId]['unique_bosses'][$bucket . ':' . $bossKey] = true;
            }
            $publicRecord = ['time' => (int) $row['occurred_at'], 'map' => $map, 'difficulty' => $difficulty,
                'boss' => $boss['name'], 'clear' => !empty($boss['final']), 'participants' => $participants,
                'guild' => $guildId ? ($guildNames[$guildId] ?? 'Former guild') : 'Mixed group'];
            if (count($result['recent']) < 50) {
                $result['recent'][] = $publicRecord;
            }
            if (count($result['recent_by_raid'][$bucket] ?? []) < 20) {
                $result['recent_by_raid'][$bucket][] = $publicRecord;
            }
        }
        foreach ($result['guilds'] as &$guild) {
            $guild['progression'] = count($guild['unique_bosses']);
            unset($guild['unique_bosses']);
        }
        unset($guild);
        return $result;
    }

    public static function encounters($realmId, $days = 30)
    {
        if (get_config('disable_top_players')) {
            return ['available' => false, 'disabled' => true];
        }
        $days = in_array($days, [7,30,90], true) ? $days : 30;
        return self::cached($realmId, 'encounters-' . $days, static function () use ($realmId, $days) {
            $db = self::connection($realmId);
            $guildNames = [];
            foreach ($db->executeQuery('SELECT guildid,name FROM guild')->fetchAllAssociative() as $guild) {
                $guildNames[(int) $guild['guildid']] = $guild['name'];
            }
            $maps = implode(',', array_keys(self::raid_catalog()));
            $records = $db->executeQuery('SELECT UNIX_TIMESTAMP(time) AS occurred_at,map,difficulty,creditType,creditEntry,playersInfo
                FROM log_encounter WHERE time >= FROM_UNIXTIME(:since) AND map IN (' . $maps . ')
                ORDER BY time DESC,map ASC,difficulty ASC,creditType ASC,creditEntry ASC LIMIT ' . (self::ENCOUNTER_LIMIT + 1), ['since' => time() - $days * 86400])->iterateAssociative();
            return self::summarize_encounters($records, $guildNames);
        });
    }

    public static function guilds($realmId, $sort = 'progression', $days = 30)
    {
        if (get_config('disable_top_players')) {
            return ['available' => false, 'disabled' => true];
        }
        $sort = in_array($sort, ['progression','kills','members'], true) ? $sort : 'progression';
        $days = in_array($days, [7,30,90], true) ? $days : 30;
        return self::cached($realmId, 'guilds-' . $sort . '-' . $days, static function () use ($realmId, $sort, $days) {
            $rows = self::connection($realmId)->executeQuery('SELECT g.guildid,g.name,c.name AS leader,c.race,COUNT(m.guid) AS members,
                COALESCE(SUM(p.level = 80),0) AS max_level,COALESCE(SUM(p.totalKills),0) AS pvp_kills
                FROM guild g LEFT JOIN characters c ON c.guid = g.leaderguid LEFT JOIN guild_member m ON m.guildid = g.guildid
                LEFT JOIN characters p ON p.guid = m.guid GROUP BY g.guildid,g.name,c.name,c.race')->fetchAllAssociative();
            $encounters = self::encounters($realmId, $days);
            foreach ($rows as &$row) {
                $progress = $encounters['guilds'][(int) $row['guildid']] ?? [];
                $row['boss_kills'] = (int) ($progress['kills'] ?? 0);
                $row['clears'] = (int) ($progress['clears'] ?? 0);
                $row['progression'] = (int) ($progress['progression'] ?? 0);
            }
            unset($row);
            $metric = ['progression' => 'progression', 'kills' => 'pvp_kills', 'members' => 'members'][$sort];
            usort($rows, static function ($a, $b) use ($metric) {
                return (int) $b[$metric] <=> (int) $a[$metric] ?: ((int) $b['members'] <=> (int) $a['members']) ?: strcmp($a['name'], $b['name']);
            });
            return ['rows' => $rows, 'encounters_available' => !empty($encounters['available']), 'truncated' => !empty($encounters['truncated'])];
        });
    }
}
