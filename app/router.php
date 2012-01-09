<?php
// definovani stylu
NRoute::addStyle('title', NULL);
// article/10-a-asd-a-sd/
NRoute::setStyleProperty('title', NRoute::PATTERN, '[a-zA-Z0-9\-]*');


// Vsechny cesty vedou do indexu....
$router[] = new NRoute('index.php', 'Front:Dashboard:default', NRoute::ONE_WAY);

// Admin modul
$router[] = new NRoute('admin/<presenter>/<action>[/<id>]', Array(
	'module' => 'Admin',
	'presenter' => 'Homepage',
	'action' => 'default'
));

// Settings modul
$router[] = new NRoute('settings/<presenter>/<action>[/<id>]', Array(
	'module' => 'Settings',
	'presenter' => 'Homepage',
	'action' => 'default'
));

// Obecne schema (nejzakladnejsi)
// MUSI BYT POSLEDNI!
$router[] = new NRoute('<presenter>/<action>[/<id>]', Array(
	'module' => 'Front',
	'presenter' => 'Dashboard',
	'action' => 'default'
));

