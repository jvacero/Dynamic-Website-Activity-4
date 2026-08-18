<?php
// seed.php - one-time database seeding script for DynamicWebDemoAct4.
// Passwords/emails must be hashed in PHP (bcrypt / sha256), so seeding is done
// here rather than with raw SQL INSERTs.
//
// HOW TO USE:
//   1. Import DynamicWebDemoAct4.sql first.
//   2. Visit this file once in your browser.
//   3. Delete or rename this file afterwards so it can't be re-run by accident.

require_once __DIR__ . '/../config/mysqli_connect.php';

function hash_email(string $email): string {
    return hash('sha256', strtolower(trim($email)));
}

// Inserts one user + their profile row, hashing email and password first.
function insert_account(mysqli $conn, string $username, string $email, string $password, string $role, string $displayName, string $bio): int {
    $emailHash = hash_email($email);
    $passwordHash = password_hash($password, PASSWORD_BCRYPT);

    $stmt = $conn->prepare("INSERT INTO users (username, email_hash, password_hash, role) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $username, $emailHash, $passwordHash, $role);
    $stmt->execute();
    $userId = $stmt->insert_id;
    $stmt->close();

    $stmt = $conn->prepare("INSERT INTO profiles (user_id, display_name, bio) VALUES (?, ?, ?)");
    $stmt->bind_param("iss", $userId, $displayName, $bio);
    $stmt->execute();
    $stmt->close();

    return $userId;
}

function insert_post(mysqli $conn, int $userId, string $content): void {
    $stmt = $conn->prepare("INSERT INTO posts (user_id, content) VALUES (?, ?)");
    $stmt->bind_param("is", $userId, $content);
    $stmt->execute();
    $stmt->close();
}

// -----------------------------------------------------------------------
// Accounts: 2 admins + 10 regular users.
// NOTE: these are demo passwords only — change/remove before deploying anywhere real!
// -----------------------------------------------------------------------
$accounts = [
    // key            username           email                             password       role     display name     bio
    'admin1' => ['AdminRex',       'admin@dynamicweb.demo',       'Admin123!',   'admin', 'Admin Rex',    'running this place since day one lol.'],
    'admin2' => ['AdminZee',       'zee.admin@dynamicweb.demo',   'Admin456!',   'admin', 'Zee',          'co-admin, mostly here to keep the peace.'],
    'u1'     => ['xXBuilderXx',    'builder@dynamicweb.demo',     'Builder123',  'user',  'Jake B.',      'obsessed with pixel builds!! :)'],
    'u2'     => ['pixel_princess', 'princess@dynamicweb.demo',    'Princess123', 'user',  'Mia',          'blue is life ~'],
    'u3'     => ['CoolKidz99',     'coolkidz@dynamicweb.demo',    'Coolkid123',  'user',  'Tyler',        'add me!! always online after school'],
    'u4'     => ['StudMaster2010', 'studmaster@dynamicweb.demo',  'Stud12345',   'user',  'StudMaster',   'making the best stud maps rn'],
    'u5'     => ['NoobSlayerXO',   'noobslayer@dynamicweb.demo',  'Noob12345',   'user',  'Alex',         'lol just messing around here'],
    'u6'     => ['GlitchWizard',   'glitchwizard@dynamicweb.demo','Glitch123',   'user',  'Sam',          'finding bugs so you dont have to'],
    'u7'     => ['SkyBuilder21',   'skybuilder@dynamicweb.demo',  'SkyBuild21',  'user',  'Riley',        'building in the clouds, literally'],
    'u8'     => ['RetroGamerX',    'retrogamer@dynamicweb.demo',  'Retro1234',   'user',  'Devon',        '2010s internet enjoyer forever'],
    'u9'     => ['PixelNova',      'pixelnova@dynamicweb.demo',   'Nova12345',   'user',  'Nova',         'sparkly pixel art & stuff'],
    'u10'    => ['BlockQueen88',   'blockqueen@dynamicweb.demo',  'Queen8888',   'user',  'Queenie',      'ruling the stud kingdom since 88'],
];

$ids = [];
foreach ($accounts as $key => $a) {
    [$username, $email, $password, $role, $displayName, $bio] = $a;
    $ids[$key] = insert_account($conn, $username, $email, $password, $role, $displayName, $bio);
}

// -----------------------------------------------------------------------
// Posts - every one of the 12 accounts already has at least one post.
// -----------------------------------------------------------------------
$posts = [
    ['admin1', "Welcome everyone to the new site!! still fixing some bugs so bear with me lol"],
    ['u1',     "just finished my new pixel castle build, screenshots coming soon!!!"],
    ['u2',     "does anyone know how to change my profile song? plz help lol"],
    ['u3',     "ugh homework again... someone save me D:"],
    ['u4',     "new stud texture pack dropping this weekend, stay tuned guys"],
    ['u5',     "who wants to play later? add me on here first"],
    ['admin2', "hey all, im the new co-admin! here to help keep things running smooth :)"],
    ['u6',     "found a weird glitch by the spawn point, reporting it to the admins lol"],
    ['u7',     "sky base is finally done, took me like 2 weeks!!"],
    ['u8',     "does anyone else miss old internet or is it just me"],
    ['admin1', "reminder: be nice to each other in the comments or i will delete your posts >:("],
    ['u1',     "my internet keeps lagging its so annoying rn"],
    ['u2',     "got a new layout for my profile, tell me what u think!!"],
    ['u9',     "posted some new pixel art in my gallery, check it out"],
    ['u10',    "the block kingdom is recruiting builders, message me!!"],
    ['u3',     "just beat my highscore!!! so hyped rn"],
    ['u4',     "trading pixel bricks, message me if interested"],
    ['u5',     "why is everyone posting so much today lol lurking rn"],
    ['admin2', "psa: use the report button (coming soon) instead of arguing in the comments lol"],
    ['u6',     "patched my own game save after that glitch, all good now"],
    ['admin1', "site update: added music player to the sidebar, enjoy!"],
    ['u1',     "shoutout to everyone who helped me build my base this week you guys rock"],
    ['u7',     "clouds looking extra pixelated today, great day for screenshots"],
    ['u8',     "digging out my old playlists, feeling nostalgic"],
    ['u2',     "changed my display pic, do you like it??"],
    ['u9',     "does anyone want a custom pixel avatar? taking requests this week"],
    ['u3',     "can't believe summer is almost over :(("],
    ['u10',    "queenie's block kingdom hit 50 members today, thank u all!!"],
    ['u4',     "working on a huge secret project, cant say much yet ;)"],
    ['u5',     "anyone else's game crashing today? or is it just me lol"],
    ['u6',     "found ANOTHER glitch lol this place is held together with tape"],
    ['u1',     "friday finally!! whos online tonight"],
    ['u7',     "sky builder tip: always save before you jump lol learned the hard way"],
    ['u8',     "throwback to when this whole site was just me and 5 people lol"],
    ['u9',     "art trade anyone? dm me your ideas"],
    ['u10',    "block kingdom movie night this weekend, bring snacks"],
    ['admin1', "thanks for 100 members everyone!! couldnt have done it without you guys <3"],
    ['admin2', "also thank you all, this community is honestly the best part of my week"],
];

foreach ($posts as [$key, $content]) {
    insert_post($conn, $ids[$key], $content);
}

echo "Seeding complete! 2 admins + 10 users + " . count($posts) . " posts inserted into DynamicWebDemoAct4.\n";
echo "Every account already has at least one post.\n\n";
echo "Demo logins (username | email | password):\n";
foreach ($accounts as $a) {
    echo "  {$a[0]} | {$a[1]} | {$a[2]} (" . strtoupper($a[3]) . ")\n";
}
echo "\nPlease delete this file (seed.php) now that seeding is done.";
