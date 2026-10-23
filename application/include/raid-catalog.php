<?php
// AzerothCore WotLK encounter credit entries, including scripted (type 1) victories.
// Source: AzerothCore data/sql/base/db_world/instance_encounters.sql.
$raids = [
    409 => ['name' => 'Molten Core', 'expansion' => 'classic', 'size' => 40, 'tier' => 'Tier 1', 'bosses' => [
        '0:12118' => 'Lucifron', '0:11982' => 'Magmadar', '0:12259' => 'Gehennas', '0:12057' => 'Garr',
        '0:12264' => 'Shazzrah', '0:12056' => 'Baron Geddon', '0:12098' => 'Sulfuron Harbinger',
        '0:11988' => 'Golemagg the Incinerator', '0:12018' => 'Majordomo Executus', '0:11502' => 'Ragnaros'], 'final' => '0:11502'],
    469 => ['name' => 'Blackwing Lair', 'expansion' => 'classic', 'size' => 40, 'tier' => 'Tier 2', 'bosses' => [
        '0:12435' => 'Razorgore the Untamed', '0:13020' => 'Vaelastrasz the Corrupt', '0:12017' => 'Broodlord Lashlayer',
        '0:11983' => 'Firemaw', '0:14601' => 'Ebonroc', '0:11981' => 'Flamegor', '0:14020' => 'Chromaggus', '0:11583' => 'Nefarian'], 'final' => '0:11583'],
    309 => ['name' => 'Zul\'Gurub', 'expansion' => 'classic', 'size' => 20, 'tier' => 'Zandalar', 'bosses' => [
        '0:14507' => 'High Priest Venoxis', '0:14517' => 'High Priestess Jeklik', '0:14510' => 'High Priestess Mar\'li',
        '0:11382' => 'Bloodlord Mandokir', '0:15083' => 'Edge of Madness', '0:14509' => 'High Priest Thekal',
        '0:15114' => 'Gahz\'ranka', '0:14515' => 'High Priestess Arlokk', '0:11380' => 'Jin\'do the Hexxer', '0:14834' => 'Hakkar'], 'final' => '0:14834'],
    509 => ['name' => 'Ruins of Ahn\'Qiraj', 'expansion' => 'classic', 'size' => 20, 'tier' => 'Ahn\'Qiraj', 'bosses' => [
        '0:15348' => 'Kurinnaxx', '0:15341' => 'General Rajaxx', '0:15340' => 'Moam',
        '0:15370' => 'Buru the Gorger', '0:15369' => 'Ayamiss the Hunter', '0:15339' => 'Ossirian the Unscarred'], 'final' => '0:15339'],
    531 => ['name' => 'Temple of Ahn\'Qiraj', 'expansion' => 'classic', 'size' => 40, 'tier' => 'Tier 2.5', 'bosses' => [
        '0:15263' => 'The Prophet Skeram', '0:15544' => 'Silithid Royalty', '0:15516' => 'Battleguard Sartura',
        '0:15510' => 'Fankriss the Unyielding', '0:15299' => 'Viscidus', '0:15509' => 'Princess Huhuran',
        '0:15275' => 'Twin Emperors', '0:15517' => 'Ouro', '0:15727' => 'C\'Thun'], 'final' => '0:15727'],
    532 => ['name' => 'Karazhan', 'expansion' => 'tbc', 'size' => 10, 'tier' => 'Tier 4', 'bosses' => [
        '0:15550' => 'Attumen the Huntsman', '0:15687' => 'Moroes', '0:16457' => 'Maiden of Virtue', '0:16812' => 'Opera Event',
        '0:15691' => 'The Curator', '0:15688' => 'Terestian Illhoof', '0:16524' => 'Shade of Aran',
        '0:15689' => 'Netherspite', '0:22520' => 'Chess Event', '0:15690' => 'Prince Malchezaar', '0:17225' => 'Nightbane'], 'final' => '0:15690'],
    565 => ['name' => 'Gruul\'s Lair', 'expansion' => 'tbc', 'size' => 25, 'tier' => 'Tier 4',
        'bosses' => ['0:18831' => 'High King Maulgar', '0:19044' => 'Gruul the Dragonkiller'], 'final' => '0:19044'],
    544 => ['name' => 'Magtheridon\'s Lair', 'expansion' => 'tbc', 'size' => 25, 'tier' => 'Tier 4',
        'bosses' => ['0:17257' => 'Magtheridon'], 'final' => '0:17257'],
    548 => ['name' => 'Serpentshrine Cavern', 'expansion' => 'tbc', 'size' => 25, 'tier' => 'Tier 5', 'bosses' => [
        '0:21216' => 'Hydross the Unstable', '0:21217' => 'The Lurker Below', '0:21215' => 'Leotheras the Blind',
        '0:21214' => 'Fathom-Lord Karathress', '0:21213' => 'Morogrim Tidewalker', '0:21212' => 'Lady Vashj'], 'final' => '0:21212'],
    550 => ['name' => 'Tempest Keep: The Eye', 'expansion' => 'tbc', 'size' => 25, 'tier' => 'Tier 5', 'bosses' => [
        '0:19514' => 'Al\'ar', '0:19516' => 'Void Reaver', '0:18805' => 'High Astromancer Solarian',
        '0:19622' => 'Kael\'thas Sunstrider'], 'final' => '0:19622'],
    534 => ['name' => 'The Battle for Mount Hyjal', 'expansion' => 'tbc', 'size' => 25, 'tier' => 'Tier 6', 'bosses' => [
        '0:17767' => 'Rage Winterchill', '0:17808' => 'Anetheron', '0:17888' => 'Kaz\'rogal',
        '0:17842' => 'Azgalor', '0:17968' => 'Archimonde'], 'final' => '0:17968'],
    564 => ['name' => 'Black Temple', 'expansion' => 'tbc', 'size' => 25, 'tier' => 'Tier 6', 'bosses' => [
        '0:22887' => 'High Warlord Naj\'entus', '0:22898' => 'Supremus', '0:22841' => 'Shade of Akama',
        '0:22871' => 'Teron Gorefiend', '0:22948' => 'Gurtogg Bloodboil', '0:23420' => 'Reliquary of Souls',
        '0:22947' => 'Mother Shahraz', '0:23426' => 'The Illidari Council', '0:22917' => 'Illidan Stormrage'], 'final' => '0:22917'],
    568 => ['name' => 'Zul\'Aman', 'expansion' => 'tbc', 'size' => 10, 'tier' => 'Zul\'Aman', 'bosses' => [
        '0:23574' => 'Akil\'zon', '0:23576' => 'Nalorakk', '0:23578' => 'Jan\'alai',
        '0:23577' => 'Halazzi', '0:24239' => 'Hex Lord Malacrass', '0:23863' => 'Zul\'jin'], 'final' => '0:23863'],
    580 => ['name' => 'Sunwell Plateau', 'expansion' => 'tbc', 'size' => 25, 'tier' => 'Sunwell', 'bosses' => [
        '0:24892' => 'Kalecgos', '0:24882' => 'Brutallus', '0:25038' => 'Felmyst',
        '0:25165' => 'Eredar Twins', '0:25840' => 'M\'uru', '0:25315' => 'Kil\'jaeden'], 'final' => '0:25315'],
    533 => ['name' => 'Naxxramas', 'tier' => 'Tier 7', 'bosses' => [
        '0:15956' => 'Anub\'Rekhan', '0:15953' => 'Grand Widow Faerlina', '0:15952' => 'Maexxna',
        '0:15954' => 'Noth the Plaguebringer', '0:15936' => 'Heigan the Unclean', '0:16011' => 'Loatheb',
        '0:16061' => 'Instructor Razuvious', '0:16060' => 'Gothik the Harvester', '1:59450' => 'The Four Horsemen',
        '0:16028' => 'Patchwerk', '0:15931' => 'Grobbulus', '0:15932' => 'Gluth', '0:15928' => 'Thaddius',
        '0:15989' => 'Sapphiron', '0:15990' => 'Kel\'Thuzad'], 'final' => '0:15990'],
    615 => ['name' => 'The Obsidian Sanctum', 'tier' => 'Tier 7', 'bosses' => [
        '0:30452' => 'Tenebron', '0:30451' => 'Shadron', '0:30449' => 'Vesperon', '0:28860' => 'Sartharion'], 'final' => '0:28860'],
    616 => ['name' => 'The Eye of Eternity', 'tier' => 'Tier 7', 'bosses' => ['0:28859' => 'Malygos'], 'final' => '0:28859'],
    603 => ['name' => 'Ulduar', 'tier' => 'Tier 8', 'bosses' => [
        '0:33113' => 'Flame Leviathan', '0:33118' => 'Ignis the Furnace Master', '0:33186' => 'Razorscale',
        '0:33293' => 'XT-002 Deconstructor', '1:65195' => 'The Assembly of Iron', '0:32930' => 'Kologarn',
        '0:33515' => 'Auriaya', '1:64899' => 'Hodir', '1:64985' => 'Thorim', '1:65074' => 'Freya',
        '0:33432' => 'Mimiron', '0:33271' => 'General Vezax', '0:33288' => 'Yogg-Saron', '1:65184' => 'Algalon the Observer'], 'final' => '0:33288'],
    649 => ['name' => 'Trial of the Crusader', 'tier' => 'Tier 9', 'bosses' => [
        '0:34797' => 'Northrend Beasts', '0:34780' => 'Lord Jaraxxus', '1:68184' => 'Faction Champions',
        '0:34496' => 'Val\'kyr Twins', '0:34564' => 'Anub\'arak'], 'final' => '0:34564'],
    249 => ['name' => 'Onyxia\'s Lair', 'tier' => 'Tier 9', 'bosses' => ['0:10184' => 'Onyxia'], 'final' => '0:10184'],
    631 => ['name' => 'Icecrown Citadel', 'tier' => 'Tier 10', 'bosses' => [
        '0:36612' => 'Lord Marrowgar', '0:36855' => 'Lady Deathwhisper', '1:72959' => 'Icecrown Gunship Battle',
        '0:37813' => 'Deathbringer Saurfang', '0:36626' => 'Festergut', '0:36627' => 'Rotface',
        '0:36678' => 'Professor Putricide', '0:37970' => 'Blood Prince Council', '0:37955' => 'Blood-Queen Lana\'thel',
        '1:72706' => 'Valithria Dreamwalker', '0:36853' => 'Sindragosa', '0:36597' => 'The Lich King'], 'final' => '0:36597'],
    724 => ['name' => 'The Ruby Sanctum', 'tier' => 'Tier 10', 'bosses' => [
        '0:39751' => 'Baltharus the Warborn', '0:39747' => 'Saviana Ragefire', '0:39746' => 'General Zarithrian', '0:39863' => 'Halion'], 'final' => '0:39863'],
    624 => ['name' => 'Vault of Archavon', 'tier' => 'Wintergrasp', 'bosses' => [
        '0:31125' => 'Archavon the Stone Watcher', '0:33993' => 'Emalon the Storm Watcher',
        '0:35013' => 'Koralon the Flame Watcher', '0:38433' => 'Toravon the Ice Watcher'], 'final' => null],
];
foreach ($raids as $mapId => &$raid) {
    $raid['expansion'] = $raid['expansion'] ?? 'wrath';
    $labels = isset($raid['size']) ? [0 => $raid['size'] . '-player normal'] :
        [0 => '10-player normal', 1 => '25-player normal'];
    if (in_array($mapId, [631,649,724], true)) {
        $labels += [2 => '10-player heroic', 3 => '25-player heroic'];
    }
    $raid['modes'] = [];
    foreach ($labels as $difficulty => $label) {
        $raid['modes'][$difficulty] = ['label' => $label, 'expansion' => $raid['expansion'], 'tier' => $raid['tier']];
    }
    // Reserved log mode for restored Classic raids sharing a Wrath map.
    // The collection patch tags progression-system Onyxia here; Naxx40 needs a server module providing that version.
    if ($mapId === 249 || $mapId === 533) {
        $raid['modes'][2] = ['label' => '40-player normal', 'expansion' => 'classic', 'tier' => $mapId === 533 ? 'Tier 3' : 'Classic'];
    }
    $raid['difficulties'] = array_keys($raid['modes']);
    foreach ($raid['bosses'] as $key => $name) {
        $raid['bosses'][$key] = ['name' => $name, 'final' => $key === $raid['final']];
    }
    unset($raid['final']);
}
unset($raid);
return $raids;
