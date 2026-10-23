<?php
$catalog = realm_stats::raid_catalog();
$expansions = ['classic' => 'Classic', 'tbc' => 'The Burning Crusade', 'wrath' => 'Wrath of the Lich King'];
$requestedMap = (int)($_GET['raid'] ?? 631);
$requestedDifficulty = (int)($_GET['difficulty'] ?? 0);
$inferredExpansion = $catalog[$requestedMap]['modes'][$requestedDifficulty]['expansion'] ?? $catalog[$requestedMap]['expansion'] ?? 'wrath';
$requestedExpansion = is_string($_GET['expansion'] ?? null) ? $_GET['expansion'] : '';
$expansion = isset($expansions[$requestedExpansion]) ? $requestedExpansion : $inferredExpansion;
$catalog = realm_stats::raid_catalog($expansion);
$defaultMap = ['classic' => 409, 'tbc' => 532, 'wrath' => 631][$expansion];
$map = isset($catalog[$requestedMap]) ? $requestedMap : $defaultMap;
$difficulty = in_array($requestedDifficulty,$catalog[$map]['difficulties'],true) ? $requestedDifficulty : $catalog[$map]['difficulties'][0];
$difficultyLabel = $catalog[$map]['modes'][$difficulty]['label'];
$days = in_array((int)($_GET['days'] ?? 30),[7,30,90],true) ? (int)($_GET['days'] ?? 30) : 30;
$encounters = $selectedRealm ? realm_stats::encounters($realmId,$days) : ['available'=>false];
$raidData = $encounters['raids'][$map . ':' . $difficulty] ?? ['kills'=>0,'clears'=>0,'bosses'=>[]];
$recent = $encounters['recent_by_raid'][$map . ':' . $difficulty] ?? [];
?>
<div class="container page-content">
    <?php gg_realm_filter('raids',$realms,$realmId,['expansion'=>$expansion,'raid'=>$map,'difficulty'=>$difficulty,'days'=>$days]); ?>
    <div class="page-intro"><div><h2>Every victory has a story.</h2><p>Follow boss kills and final-boss clears from Classic through Wrath.</p></div><span class="scope-badge">Players & playerbots</span></div>
    <nav class="ranking-tabs" aria-label="Raid expansion"><?php foreach ($expansions as $value=>$label) { ?><a href="<?php echo $escape(gg_url('raids',['realm'=>$realmId,'expansion'=>$value,'days'=>$days])); ?>" <?php echo $value===$expansion ? 'aria-current="page"' : ''; ?>><?php echo $label; ?></a><?php } ?></nav>
    <form class="ranking-filters" method="get" action="<?php echo $escape(gg_url()); ?>"><input type="hidden" name="page" value="raids"><input type="hidden" name="realm" value="<?php echo $realmId; ?>"><input type="hidden" name="expansion" value="<?php echo $expansion; ?>">
        <div class="filter-search"><label for="raid-map">Raid</label><select class="form-control" id="raid-map" name="raid"><?php foreach ($catalog as $id=>$raid) { ?><option value="<?php echo $id; ?>" <?php echo $id===$map ? 'selected' : ''; ?>><?php echo $escape($raid['name']); ?></option><?php } ?></select></div>
        <div><label for="raid-difficulty">Raid size & difficulty</label><select class="form-control" id="raid-difficulty" name="difficulty"><?php foreach ($catalog[$map]['modes'] as $value=>$mode) { ?><option value="<?php echo $value; ?>" <?php echo $difficulty===$value ? 'selected' : ''; ?>><?php echo $escape($mode['label']); ?></option><?php } ?></select></div>
        <div><label for="raid-days">History</label><select class="form-control" id="raid-days" name="days"><?php foreach ([7,30,90] as $period) { ?><option value="<?php echo $period; ?>" <?php echo $period===$days ? 'selected' : ''; ?>>Last <?php echo $period; ?> days</option><?php } ?></select></div><button type="submit" class="btn btn-primary">View raid</button>
    </form>
    <?php if (empty($encounters['available'])) { gg_empty($encounters); } else { ?>
    <div class="raid-heading tavern-panel"><div><span class="eyebrow"><?php echo $escape($catalog[$map]['tier']); ?> · <?php echo $escape($difficultyLabel); ?></span><h2><?php echo $escape($catalog[$map]['name']); ?></h2><p><?php echo $escape($expansions[$expansion]); ?> · Last <?php echo $days; ?> days</p></div><div class="raid-metrics"><div><strong><?php echo number_format($raidData['kills']); ?></strong><span>Boss victories</span></div><div><strong><?php echo number_format($raidData['clears']); ?></strong><span>Final-boss clears</span></div></div></div>
    <div class="raid-layout"><section class="tavern-panel ranking-panel"><div class="panel-title"><h3>Encounter record</h3><span class="table-caption"><?php echo count($catalog[$map]['bosses']); ?> encounters</span></div><ol class="boss-records"><?php foreach ($catalog[$map]['bosses'] as $key=>$boss) { $kills=$raidData['bosses'][$key] ?? 0; ?><li><span class="boss-marker <?php echo $kills ? 'is-defeated' : ''; ?>" aria-hidden="true"><?php echo $kills ? '✓' : '◇'; ?></span><div><strong><?php echo $escape($boss['name']); ?></strong><?php if ($boss['final']) { ?><small>Final boss</small><?php } ?></div><span class="boss-kill-count"><?php echo number_format($kills); ?> <small><?php echo $kills===1 ? 'victory' : 'victories'; ?></small></span></li><?php } ?></ol></section>
    <section class="tavern-panel ranking-panel"><div class="panel-title"><h3>Recent victories</h3></div><?php if (!$recent) { gg_empty($encounters,'Your story is still being written.','No boss victories have been recorded for this raid, difficulty, and period yet.'); } else { ?><div class="victory-list"><?php foreach ($recent as $kill) { ?><article class="raid-victory"><span class="eyebrow"><?php echo gmdate('d M Y · H:i',$kill['time']); ?> UTC</span><h4><?php echo $escape($kill['boss']); ?></h4><p><?php echo $escape($kill['guild']); ?> · <?php echo $kill['participants']; ?> adventurers<?php echo $kill['clear'] ? ' · Final-boss clear' : ''; ?></p></article><?php } ?></div><?php } ?></section></div>
    <p class="data-caption">A clear is a recorded final-boss victory. Boss counts reflect recorded encounters; a zero does not establish whether a boss has ever been defeated. Vault of Archavon tracks its independent boss encounters without inferring a full clear.</p>
    <?php if ($map === 533 && $expansion === 'classic') { ?><p class="data-caption">Classic Naxxramas records require the restored 40-player version on the realm.</p><?php } ?>
    <?php if (!empty($encounters['truncated'])) { ?><p class="data-notice">Showing the latest <?php echo number_format(realm_stats::ENCOUNTER_LIMIT); ?> encounters in this period.</p><?php } ?>
    <?php } ?>
</div>
