<?php
declare(strict_types=1);

// Optional: include system auth or header scripts if integrated into the main app
// require_once __DIR__ . '/config/auth.php';

// Developer dataset - can be migrated to MySQL query if needed
$developers = [
    [
        "id"       => "dev_01",
        "name"     => "John Victor Acero",
        "role"     => "Lead Systems Architect",
        "bio"      => "Handles design elements, file environment, system architecture, core backend PHP logic, MySQL schemas.",
        "level"    => "LVL 99",
        "status"   => "ONLINE",
        "color_class" => "accent-cyan",
        "avatar"   => "../Cryptic/devsphotos/1000010946.png" // Optional image URL path
    ],
    [
        "id"       => "dev_02",
        "name"     => "Richsander Orduña",
        "role"     => "UI / UX Engineer",
        "bio"      => "Crafted the retro scanline visuals, pixel borders, and responsive grid system.",
        "level"    => "LVL 85",
        "status"   => "ONLINE",
        "color_class" => "accent-green",
        "avatar"   => "../Cryptic/devsphotos/1000010945.png"
    ],
    [
        "id"       => "dev_03",
        "name"     => "Mark Denniel Urqueza",
        "role"     => "UI / UX Engineer, Security Analyst",
        "bio"      => "Maintains cryptography modules, user authentication handlers, and session validation.",
        "level"    => "LVL 85",
        "status"   => "AWAY",
        "color_class" => "accent-orange",
        "avatar"   => "../Cryptic/devsphotos/1000010943.png"
    ],
    [
        "id"       => "dev_04",
        "name"     => "Andrei Mulato",
        "role"     => "UI / UX Engineer",
        "bio"      => "Crafted the retro scanline visuals, pixel borders, and responsive grid system.",
        "level"    => "LVL 85",
        "status"   => "AWAY",
        "color_class" => "accent-orange",
        "avatar"   => "../Cryptic/devsphotos/1000010942.png"
    ]
];

/**
 * Safely escape string values for HTML output.
 */
function escape_html(?string $value): string {
    return htmlspecialchars($value ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Developer Credits - Retro Arcade</title>
    
    <!-- Retro Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Press+Start+2P&display=swap" rel="stylesheet">
    
    <!-- System Stylesheet -->
    <link rel="stylesheet" href="assets/devs.css">
</head>
<body>

    <main class="container">
        <!-- HEADER -->
        <header class="pixel-header">
            <h1>DEVELOPER CREDITS</h1>
            <p>-- MEET THE CREW BEHIND THE SYSTEM --</p>
        </header>

        <!-- ACTION BAR -->
        <nav class="nav-bar">
            <a href="index.php" class="pixel-btn nav-btn">&lt; MAIN MENU</a>
        </nav>

        <!-- DEVELOPER CARDS GRID -->
        <section class="devs-grid" aria-label="Development Team List">
            <?php foreach ($developers as $dev): ?>
                <article class="dev-card <?php echo escape_html($dev['color_class']); ?>">
                    
                    <div class="dev-avatar">
                        <?php if (!empty($dev['avatar'])): ?>
                            <img src="<?php echo escape_html($dev['avatar']); ?>" alt="<?php echo escape_html($dev['name']); ?>'s Avatar">
                        <?php else: ?>
                            <span class="avatar-placeholder" aria-hidden="true">👾</span>
                        <?php endif; ?>
                    </div>
                    
                    <h2 class="dev-name"><?php echo escape_html($dev['name']); ?></h2>
                    <div class="dev-role"><?php echo escape_html($dev['role']); ?></div>
                    
                    <div class="stat-row">
                        <span>STATUS:</span> 
                        <span class="stat-val status-<?php echo strtolower($dev['status']); ?>">
                            <?php echo escape_html($dev['status']); ?>
                        </span>
                    </div>
                    <div class="stat-row">
                        <span>RANK:</span> 
                        <span class="stat-val"><?php echo escape_html($dev['level']); ?></span>
                    </div>

                    <div class="dev-bio">
                        &gt; <?php echo escape_html($dev['bio']); ?>
                    </div>

                </article>
            <?php endforeach; ?>
        </section>
    </main>

    <!-- FOOTER -->
    <footer class="pixel-footer">
        MADE WITH <span>&lt;3</span> BY THE SYSTEM DEVS &copy; <?php echo date("Y"); ?>
    </footer>

</body>
</html>