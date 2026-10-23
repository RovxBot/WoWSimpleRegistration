<?php
$bracket = in_array((int) ($_GET['bracket'] ?? 2),[2,3,5],true) ? (int) ($_GET['bracket'] ?? 2) : 2;
$arena = $selectedRealm ? realm_stats::arena($realmId,$bracket) : ['available'=>false];
?>
<div class="container page-content">
    <?php gg_realm_filter('arena',$realms,$realmId,['bracket'=>$bracket]); ?>
    <div class="page-intro"><div><h2>Enter the arena.</h2><p>Team ratings, season records, and the contenders climbing the ladder.</p></div><span class="scope-badge">Players & playerbots</span></div>
    <nav class="ranking-tabs" aria-label="Arena bracket"><?php foreach ([2,3,5] as $size) { ?><a href="<?php echo $escape(gg_url('arena',['realm'=>$realmId,'bracket'=>$size])); ?>" <?php echo $size===$bracket ? 'aria-current="page"' : ''; ?>><?php echo $size . 'v' . $size; ?></a><?php } ?></nav>
    <section class="tavern-panel ranking-panel"><div class="panel-title"><h3><?php echo $bracket . 'v' . $bracket; ?> ladder</h3><span class="table-caption">Top 100 · ordered by team rating</span></div>
    <?php if (empty($arena['rows'])) { gg_empty($arena,'The ladder is waiting.','No teams are recorded in this bracket yet.'); } else { ?>
    <div class="table-responsive"><table class="table leaderboard"><thead><tr><th>Rank</th><th>Team</th><th>Captain</th><th>Rating</th><th>Season wins</th><th>Played</th><th>Win rate</th><th>Members</th></tr></thead><tbody>
    <?php foreach ($arena['rows'] as $rank => $team) { ?><tr><td><span class="rank-number rank-<?php echo $rank+1; ?>"><?php echo $rank+1; ?></span></td><th scope="row"><?php echo $escape($team['name']); ?></th><td><?php echo $escape($team['captain'] ?: '—'); ?></td><td class="score-cell"><?php echo number_format((int)$team['rating']); ?></td><td><?php echo number_format((int)$team['seasonWins']); ?></td><td><?php echo number_format((int)$team['seasonGames']); ?></td><td><?php echo (int)$team['seasonGames'] > 0 ? round(100*(int)$team['seasonWins']/(int)$team['seasonGames']) . '%' : '—'; ?></td><td><?php echo (int)$team['members']; ?></td></tr><?php } ?>
    </tbody></table></div><?php } ?></section>
    <p class="data-caption">Rankings use the realm’s recorded team ratings and season results. Teams with no matches have no win percentage.</p>
</div>
