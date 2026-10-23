<?php
$sort = in_array($_GET['sort'] ?? 'progression',['progression','kills','members'],true) ? ($_GET['sort'] ?? 'progression') : 'progression';
$days = in_array((int)($_GET['days'] ?? 30),[7,30,90],true) ? (int)($_GET['days'] ?? 30) : 30;
$guilds = $selectedRealm ? realm_stats::guilds($realmId,$sort,$days) : ['available'=>false];
?>
<div class="container page-content">
    <?php gg_realm_filter('guilds',$realms,$realmId,['sort'=>$sort,'days'=>$days]); ?>
    <div class="page-intro"><div><h2>A guild is more than a name.</h2><p>Explore the realm’s communities, raid progression, and combined PvP achievements.</p></div><span class="scope-badge">Players & playerbots</span></div>
    <form class="ranking-filters" method="get" action="<?php echo $escape(gg_url()); ?>"><input type="hidden" name="page" value="guilds"><input type="hidden" name="realm" value="<?php echo $realmId; ?>">
        <div><label for="guild-sort">Rank guilds by</label><select class="form-control" id="guild-sort" name="sort"><?php foreach (['progression'=>'Raid progression','kills'=>'Lifetime PvP kills','members'=>'Guild members'] as $value=>$label) { ?><option value="<?php echo $value; ?>" <?php echo $sort===$value ? 'selected' : ''; ?>><?php echo $label; ?></option><?php } ?></select></div>
        <div><label for="guild-days">Raid history</label><select class="form-control" id="guild-days" name="days"><?php foreach ([7,30,90] as $period) { ?><option value="<?php echo $period; ?>" <?php echo $days===$period ? 'selected' : ''; ?>>Last <?php echo $period; ?> days</option><?php } ?></select></div><button type="submit" class="btn btn-primary">Apply filters</button>
    </form>
    <?php if (!empty($guilds['available']) && empty($guilds['encounters_available'])) { ?><p class="data-notice" role="status">Raid history is temporarily unavailable. Guild rosters and lifetime PvP totals are still shown.</p><?php } ?>
    <section class="tavern-panel ranking-panel"><div class="panel-title"><h3>The guild hall</h3><span class="table-caption"><?php echo !empty($guilds['rows']) ? count($guilds['rows']) . ' guilds' : 'Realm communities'; ?></span></div>
    <?php if (empty($guilds['rows'])) { gg_empty($guilds,'The guild hall is waiting.','Guilds will appear here after they are founded in game.'); } else { ?>
    <div class="table-responsive"><table class="table leaderboard"><thead><tr><th>Rank</th><th>Guild</th><th>Guild master</th><th>Members</th><th>Level 80</th><th>Raid progression</th><th>Boss kills</th><th>PvP kills</th></tr></thead><tbody>
    <?php foreach ($guilds['rows'] as $i=>$guild) { ?><tr><td><span class="rank-number rank-<?php echo $i+1; ?>"><?php echo $sort==='progression' && !(int)$guild['progression'] ? '—' : $i+1; ?></span></td><th scope="row"><?php echo $escape($guild['name']); ?></th><td><?php echo $escape($guild['leader'] ?: '—'); ?></td><td><?php echo (int)$guild['members']; ?></td><td><?php echo (int)$guild['max_level']; ?></td><td class="score-cell"><?php echo !empty($guilds['encounters_available']) ? (int)$guild['progression'] : '—'; ?></td><td><?php echo !empty($guilds['encounters_available']) ? number_format($guild['boss_kills']) : '—'; ?></td><td><?php echo number_format((int)$guild['pvp_kills']); ?></td></tr><?php } ?>
    </tbody></table></div><?php } ?></section>
    <p class="data-caption">Raid progression counts distinct boss victories per raid difficulty over the last <?php echo $days; ?> days. A guild receives raid credit when at least 80% of recorded participants belong to it. PvP totals use current guild members’ lifetime honorable kills.</p>
    <?php if (!empty($guilds['truncated'])) { ?><p class="data-notice">Raid rankings use the latest <?php echo number_format(realm_stats::ENCOUNTER_LIMIT); ?> encounters within this period.</p><?php } ?>
</div>
