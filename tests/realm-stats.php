<?php
require __DIR__ . '/fixtures/realm-stats.php';

$checks=0;
function stats_check($condition,$message) {
    global $checks;
    if (!$condition) { throw new RuntimeException($message); }
    $checks++;
}
function stats_fresh() { (new ReflectionProperty(realm_stats::class,'memo'))->setValue(null,[]); }
$summary=realm_stats::summary(1);
stats_check($summary['available'] && $summary['state']==='online','Fresh worldserver heartbeat is online');
stats_check($summary['online']===2 && $summary['alliance']===1 && $summary['horde']===1,'Online faction counts use character races');
stats_check($summary['characters']===3 && $summary['guilds']===2,'Deleted characters are excluded from realm totals');
$pdo->exec('UPDATE realmlist SET flag=2');stats_fresh();
stats_check(realm_stats::summary(1)['state']==='offline','Offline realm flag overrides a fresh heartbeat');
$pdo->exec('UPDATE realmlist SET flag=0; UPDATE uptime SET starttime=1');stats_fresh();
stats_check(realm_stats::summary(1)['state']==='offline','Stale heartbeat does not claim the realm is online');
$pdo->exec('DELETE FROM uptime');stats_fresh();
stats_check(realm_stats::summary(1)['state']==='unknown','Missing heartbeat stays unknown');
stats_check(count(realm_stats::online_players(1)['rows'])===2,'Online roster contains only online characters');
$arena=realm_stats::arena(1,2);
stats_check(count($arena['rows'])===2 && $arena['rows'][0]['name']==='Gold','Arena brackets and rating ordering are applied');
stats_check((int)$arena['rows'][1]['members']===2,'Arena roster sizes are joined correctly');
stats_check(realm_stats::arena(1,3)['rows'][0]['name']==='Trio' && realm_stats::arena(1,5)['rows'][0]['name']==='Five','3v3 and 5v5 have separate ladders');
stats_check(realm_stats::arena(1,999)['rows'][0]['name']==='Gold','Invalid bracket falls back to a bounded bracket');
$pvp=realm_stats::pvp(1);
stats_check($pvp['total']===3 && $pvp['rows'][0]['name']==='Bram','PvP uses lifetime kills and excludes deleted characters');
stats_check(count(realm_stats::pvp(1,'alliance',1)['rows'])===2,'Faction and class filters compose');
stats_check(realm_stats::pvp(1,'all',0,'Aster')['rows'][0]['guild']==='Wayfarers','Character search retains guild information');
stats_check(realm_stats::pvp(1,'all',0,"' OR 1=1 --")['total']===0,'Search is bound as data, not SQL');
stats_check(realm_stats::pvp(1,'all',0,'',2)['rows']===[],'Pagination does not repeat the first page');

$classic=realm_stats::raid_catalog('classic');
$tbc=realm_stats::raid_catalog('tbc');
$wrath=realm_stats::raid_catalog('wrath');
stats_check(count($classic)===7 && count($tbc)===9 && count($wrath)===9,'All Classic, TBC, and Wrath raid views are cataloged');
stats_check($classic[409]['modes'][0]['label']==='40-player normal' && $classic[509]['modes'][0]['label']==='20-player normal','Classic raids use their actual raid sizes');
stats_check($tbc[532]['modes'][0]['label']==='10-player normal' && $tbc[564]['modes'][0]['label']==='25-player normal','TBC raids use their actual raid sizes');
stats_check($classic[249]['difficulties']===[2] && $classic[533]['difficulties']===[2],'Restored Classic raids use distinct recorded modes');
stats_check($wrath[249]['difficulties']===[0,1] && $wrath[533]['difficulties']===[0,1],'Classic modes do not appear as Wrath heroic difficulties');
stats_check($wrath[631]['difficulties']===[0,1,2,3],'Wrath heroic raids retain all supported difficulties');
stats_check(!isset($classic[631]) && !isset($tbc[409]) && !isset($wrath[564]),'Expansion views exclude raids from other expansions');

function roster($guilds) {
    $lines=[];
    foreach ($guilds as $i=>$guild) { $lines[]="Fixture$i (Player-0-".($i+1).", acc: 987654, ip: 203.0.113.77, guild: $guild), xyz: (1, 2, 3), auras: PRIVATE_AURAS"; }
    return implode("\n",$lines);
}
$now=time();
$legacyRecords=[];
foreach ([409=>11502,469=>11583,309=>14834,509=>15339,531=>15727,532=>15690,565=>19044,544=>17257,548=>21212,550=>19622,534=>17968,564=>22917,568=>23863,580=>25315] as $map=>$finalBoss) {
    $legacyRecords[]=['occurred_at'=>$now,'map'=>$map,'difficulty'=>0,'creditType'=>0,'creditEntry'=>$finalBoss,'playersInfo'=>roster([1,1,1,1,2])];
}
$legacy=realm_stats::summarize_encounters($legacyRecords,[1=>'Wayfarers',2=>'Nightwatch']);
stats_check($legacy['kills']===14 && $legacy['clears']===14,'Classic and TBC final bosses count as kills and clears');
stats_check($legacy['guilds'][1]['progression']===14 && $legacy['guilds'][1]['clears']===14,'Legacy raids contribute to guild progression and clears');
stats_check($legacy['recent_by_raid']['580:0'][0]['boss']==="Kil'jaeden",'Sunwell records resolve canonical encounter credits');
$shared=[];
foreach ([249=>10184,533=>15990] as $map=>$boss) {
    foreach ([0,1,2] as $difficulty) {
        $shared[]=['occurred_at'=>$now,'map'=>$map,'difficulty'=>$difficulty,'creditType'=>0,'creditEntry'=>$boss,'playersInfo'=>roster([1])];
    }
}
$versions=realm_stats::summarize_encounters($shared,[1=>'Wayfarers']);
stats_check(count($versions['raids'])===6 && $versions['guilds'][1]['progression']===6,'Classic and Wrath versions retain separate boss counts and guild credit');
stats_check($versions['raids']['249:2']['clears']===1 && $versions['raids']['533:2']['clears']===1,'Restored Classic raid clears are recorded independently');
$ignored=[
    ['occurred_at'=>$now,'map'=>409,'difficulty'=>0,'creditType'=>0,'creditEntry'=>999999,'playersInfo'=>roster([1])],
    ['occurred_at'=>$now,'map'=>564,'difficulty'=>3,'creditType'=>0,'creditEntry'=>22917,'playersInfo'=>roster([1])],
    ['occurred_at'=>$now,'map'=>1,'difficulty'=>0,'creditType'=>0,'creditEntry'=>11502,'playersInfo'=>roster([1])],
];
stats_check(realm_stats::summarize_encounters($ignored,[1=>'Wayfarers'])['kills']===0,'Unknown credits, invalid difficulties, and non-raid maps cannot inflate rankings');
$ignoredOverflow=realm_stats::summarize_encounters(array_fill(0,realm_stats::ENCOUNTER_LIMIT+1,$ignored[0]),[]);
stats_check($ignoredOverflow['truncated'] && $ignoredOverflow['kills']===0,'Non-catalog records still obey the input history limit');
$records=[
    ['occurred_at'=>$now,'map'=>631,'difficulty'=>0,'creditType'=>0,'creditEntry'=>36597,'playersInfo'=>roster([1,1,1,1,2])],
    ['occurred_at'=>$now-1,'map'=>631,'difficulty'=>0,'creditType'=>0,'creditEntry'=>36612,'playersInfo'=>roster([1,1,1,2,2])],
    ['occurred_at'=>$now-2,'map'=>631,'difficulty'=>1,'creditType'=>1,'creditEntry'=>72959,'playersInfo'=>roster([1,1,1,1,0])],
];
$public=realm_stats::summarize_encounters($records,[1=>'Wayfarers',2=>'Nightwatch']);
stats_check($public['kills']===3 && $public['clears']===1,'Final-boss clears are distinct from other encounter victories');
stats_check($public['raids']['631:0']['kills']===2 && $public['raids']['631:1']['kills']===1,'Raid difficulties remain separate');
stats_check($public['guilds'][1]['kills']===2 && $public['guilds'][1]['progression']===2,'Guild raid credit requires an 80 percent roster majority');
stats_check($public['recent'][1]['guild']==='Mixed group','Mixed groups do not become invented guild kills');
stats_check($public['recent_by_raid']['631:1'][0]['boss']==='Icecrown Gunship Battle','Scripted encounter credit entries resolve correctly');
$json=json_encode($public);
foreach (['playersInfo','987654','203.0.113.77','PRIVATE_AURAS','Fixture0','xyz:','acc:'] as $private) {
    stats_check(!str_contains($json,$private),'Sensitive encounter content is removed: '.$private);
}
$overflow=array_fill(0,realm_stats::ENCOUNTER_LIMIT+1,$records[0]);
$bounded=realm_stats::summarize_encounters($overflow,[1=>'Wayfarers',2=>'Nightwatch']);
stats_check($bounded['truncated'] && $bounded['kills']===realm_stats::ENCOUNTER_LIMIT,'Large histories have an explicit bounded window');
stats_check(count($bounded['recent'])===50 && count($bounded['recent_by_raid']['631:0'])===20,'Recent records are bounded globally and separately per raid');
$insert=$pdo->prepare('INSERT INTO log_encounter VALUES (?,?,?,?,?,?)');
foreach ($records as $row) { $insert->execute([gmdate('Y-m-d H:i:s',$row['occurred_at']),$row['map'],$row['difficulty'],$row['creditType'],$row['creditEntry'],$row['playersInfo']]); }
stats_fresh();
stats_check(realm_stats::encounters(1,30)['kills']===3,'Encounter query runs against the verified core schema');
$config['realm_stats_cache_ttl']=30;
$config['realm_stats_cache_path']=sys_get_temp_dir().'/grim-stats-test-'.bin2hex(random_bytes(6));
stats_fresh();realm_stats::encounters(1,30);
$cacheFiles=glob($config['realm_stats_cache_path'].'/*.json');
stats_check(count($cacheFiles)===1,'Public encounter cache is written');
$cache=file_get_contents($cacheFiles[0]);
stats_check(!str_contains($cache,'203.0.113.77') && !str_contains($cache,'playersInfo') && !str_contains($cache,'987654'),'Cached snapshots contain no private roster details');
stats_fresh();realm_stats::pvp(1,'all',0,'uncached-search');
stats_check(count(glob($config['realm_stats_cache_path'].'/*.json'))===1,'Search strings cannot create unbounded disk cache files');
foreach($cacheFiles as $cacheFile){unlink($cacheFile);}rmdir($config['realm_stats_cache_path']);
$config['realm_stats_cache_ttl']=0;unset($config['realm_stats_cache_path']);stats_fresh();

$guilds=realm_stats::guilds(1,'progression');
stats_check($guilds['rows'][0]['name']==='Wayfarers' && $guilds['rows'][0]['progression']===2,'Guilds rank by encounter progression');
foreach ($legacyRecords as $row) { $insert->execute([gmdate('Y-m-d H:i:s',$row['occurred_at']),$row['map'],$row['difficulty'],$row['creditType'],$row['creditEntry'],$row['playersInfo']]); }
stats_fresh();
stats_check(realm_stats::encounters(1,30)['kills']===17,'Database map filtering includes Classic and TBC encounter histories');
stats_check(realm_stats::guilds(1,'progression')['rows'][0]['progression']===16,'Guild query combines progression across all three expansions');
stats_check(realm_stats::guilds(1,'kills')['rows'][0]['name']==='Nightwatch','Guild PvP sorting uses current member totals');
stats_check(realm_stats::guilds(1,'members')['rows'][0]['name']==='Wayfarers','Guild roster sorting works independently of raid history');
$pdo->exec('DELETE FROM log_encounter');stats_fresh();
stats_check(realm_stats::encounters(1)['available'] && realm_stats::encounters(1)['kills']===0,'Empty encounter history is available and empty');
$pdo->exec('DROP TABLE log_encounter');stats_fresh();
stats_check(!realm_stats::encounters(1)['available'],'Missing encounter data stays distinct from zero kills');
stats_check(realm_stats::guilds(1)['available'] && !realm_stats::guilds(1)['encounters_available'],'Guild rosters survive an unavailable encounter source');
$config['disable_top_players']=true;$config['disable_online_players']=true;stats_fresh();
stats_check(realm_stats::arena(1)['disabled'] && realm_stats::pvp(1)['disabled'] && realm_stats::guilds(1)['disabled'] && realm_stats::encounters(1)['disabled'],'Public ranking toggles are enforced');
stats_check(realm_stats::online_players(1)['disabled'],'Online player visibility toggle is enforced');
$config['disable_top_players']=false;stats_fresh();
stats_check(!realm_stats::arena(999)['available'],'Unknown realms cannot reach unconfigured database connections');
echo "$checks realm statistics checks passed.\n";
