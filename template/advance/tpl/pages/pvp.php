<?php
$faction = in_array($_GET['faction'] ?? 'all',['all','alliance','horde'],true) ? ($_GET['faction'] ?? 'all') : 'all';
$class = in_array((int) ($_GET['class'] ?? 0),array_keys(gg_classes()),true) ? (int)$_GET['class'] : 0;
$search = substr(trim((string)($_GET['q'] ?? '')),0,48);
$rankPage = max(1,min(200,(int)($_GET['p'] ?? 1)));
$rankings = $selectedRealm ? realm_stats::pvp($realmId,$faction,$class,$search,$rankPage) : ['available'=>false];
$filters = ['realm'=>$realmId,'faction'=>$faction,'class'=>$class,'q'=>$search];
?>
<div class="container page-content">
    <?php gg_realm_filter('pvp',$realms,$realmId,['faction'=>$faction,'class'=>$class,'q'=>$search]); ?>
    <div class="page-intro"><div><h2>Leave your mark on the battlefield.</h2><p>Lifetime honorable kills, ranked across the realm.</p></div><span class="scope-badge">Players & playerbots</span></div>
    <form class="ranking-filters" method="get" action="<?php echo $escape(gg_url()); ?>">
        <input type="hidden" name="page" value="pvp"><input type="hidden" name="realm" value="<?php echo $realmId; ?>">
        <div><label for="pvp-faction">Faction</label><select class="form-control" id="pvp-faction" name="faction"><?php foreach (['all'=>'Both factions','alliance'=>'Alliance','horde'=>'Horde'] as $value=>$label) { ?><option value="<?php echo $value; ?>" <?php echo $faction===$value ? 'selected' : ''; ?>><?php echo $label; ?></option><?php } ?></select></div>
        <div><label for="pvp-class">Class</label><select class="form-control" id="pvp-class" name="class"><option value="0">All classes</option><?php foreach (gg_classes() as $value=>$label) { ?><option value="<?php echo $value; ?>" <?php echo $class===$value ? 'selected' : ''; ?>><?php echo $label; ?></option><?php } ?></select></div>
        <div class="filter-search"><label for="pvp-search">Character name</label><input class="form-control" id="pvp-search" name="q" value="<?php echo $escape($search); ?>" maxlength="48" placeholder="Find an adventurer…" type="search"></div>
        <button class="btn btn-primary" type="submit">Apply filters</button><a class="filter-reset" href="<?php echo $escape(gg_url('pvp',['realm'=>$realmId])); ?>">Reset</a>
    </form>
    <section class="tavern-panel ranking-panel"><div class="panel-title"><h3>Hall of honor</h3><span class="table-caption"><?php echo !empty($rankings['available']) ? number_format($rankings['total']) . ' characters' : 'Lifetime honorable kills'; ?></span></div>
    <?php if (empty($rankings['rows'])) { gg_empty($rankings,'No adventurers found.','Try another name, faction, or class. Characters appear after their first honorable kill.'); } else { ?>
    <div class="table-responsive"><table class="table leaderboard"><thead><tr><th>Rank</th><th>Character</th><th>Guild</th><th>Faction</th><th>Class</th><th>Level</th><th>Honorable kills</th><th>Today</th></tr></thead><tbody>
    <?php foreach ($rankings['rows'] as $i=>$character) { $rank=($rankPage-1)*50+$i+1; ?><tr><td><span class="rank-number rank-<?php echo $rank; ?>"><?php echo $rank; ?></span></td><th scope="row"><?php echo $escape($character['name']); ?></th><td><?php echo $escape($character['guild'] ?: '—'); ?></td><td class="<?php echo strtolower(gg_faction($character['race'])); ?>-text"><?php echo gg_faction($character['race']); ?></td><td><?php echo $escape(gg_classes()[(int)$character['class']] ?? 'Unknown'); ?></td><td><?php echo (int)$character['level']; ?></td><td class="score-cell"><?php echo number_format((int)$character['totalKills']); ?></td><td><?php echo number_format((int)$character['todayKills']); ?></td></tr><?php } ?>
    </tbody></table></div><?php } ?></section>
    <?php if (!empty($rankings['available']) && $rankings['total']>50) { ?><nav class="rank-pagination" aria-label="PvP ranking pages"><?php if ($rankPage>1) { ?><a class="btn btn-ghost" href="<?php echo $escape(gg_url('pvp',$filters+['p'=>$rankPage-1])); ?>">← Previous</a><?php } ?><span>Page <?php echo $rankPage; ?> of <?php echo (int)ceil($rankings['total']/50); ?></span><?php if ($rankPage*50<$rankings['total'] && $rankPage<200) { ?><a class="btn btn-ghost" href="<?php echo $escape(gg_url('pvp',$filters+['p'=>$rankPage+1])); ?>">Next →</a><?php } ?></nav><?php } ?>
    <p class="data-caption">Ranks reflect the selected filters. “Today” follows the game server’s daily honor reset.</p>
</div>
