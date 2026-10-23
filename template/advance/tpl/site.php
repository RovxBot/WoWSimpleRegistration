<?php

function gg_url($page = 'home', $query = [])
{
    $base = rtrim((string) get_config('baseurl'), '/') . '/index.php';
    if ($page !== 'home') {
        $query = ['page' => $page] + $query;
    }
    return $base . ($query ? '?' . http_build_query($query, '', '&', PHP_QUERY_RFC3986) : '');
}

function gg_faction($race)
{
    if (in_array((int) $race, [1,3,4,7,11], true)) {
        return 'Alliance';
    }
    return in_array((int) $race, [2,5,6,8,10], true) ? 'Horde' : '—';
}

function gg_classes()
{
    return [1=>'Warrior',2=>'Paladin',3=>'Hunter',4=>'Rogue',5=>'Priest',6=>'Death Knight',7=>'Shaman',8=>'Mage',9=>'Warlock',11=>'Druid'];
}

function gg_duration($seconds)
{
    $seconds = max(0, (int) $seconds);
    $days = intdiv($seconds, 86400);
    $hours = intdiv($seconds % 86400, 3600);
    $minutes = intdiv($seconds % 3600, 60);
    return ($days ? $days . 'd ' : '') . ($hours ? $hours . 'h ' : '') . $minutes . 'm';
}

function gg_empty($data, $message = 'No records yet.', $description = 'Check back after the next adventure.')
{
    $unavailable = empty($data['available']);
    $disabled = !empty($data['disabled']);
    echo '<div class="empty-state" role="status"><span class="empty-rune" aria-hidden="true">◇</span><h3>' . htmlspecialchars($unavailable ? ($disabled ? 'This view is currently hidden.' : 'Realm data is temporarily unavailable.') : $message, ENT_QUOTES, 'UTF-8') . '</h3><p>' . htmlspecialchars($unavailable ? ($disabled ? 'This realm has disabled this public view.' : 'Please try again shortly. Your account and game connection are unaffected.') : $description, ENT_QUOTES, 'UTF-8') . '</p></div>';
}

function gg_realm_filter($page, $realms, $selected, $query = [])
{
    if (count($realms) < 2) {
        return;
    }
    echo '<nav class="realm-choices" aria-label="Choose realm">';
    foreach ($realms as $id => $realm) {
        echo '<a class="' . ($id === $selected ? 'is-active' : '') . '" href="' . htmlspecialchars(gg_url($page, ['realm'=>$id] + $query), ENT_QUOTES, 'UTF-8') . '">' . htmlspecialchars($realm['realmname'], ENT_QUOTES, 'UTF-8') . '</a>';
    }
    echo '</nav>';
}
