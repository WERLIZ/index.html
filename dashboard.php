<?php
require_once 'config.php';

if (!isset($_SESSION['player_id'])) {
    header("Location: login.php");
    exit;
}

$player_id = $_SESSION['player_id'];
$username = $_SESSION['username'];

// 1. Njibo ma3lomat l-dar (House)
// Smiyat l-columns kima dertihom f Pawn: ownerID b7al owner, hStashLocked, etc.
$stmt_house = $pdo->prepare("SELECT * FROM `houses` WHERE `owner` = ? OR `ownerID` = ? LIMIT 1");
$stmt_house->execute([$username, $player_id]);
$house = $stmt_house->fetch();

$has_house = $house ? true : false;
$house_x = $has_house ? (float)$house['hPosX'] : 0.0;
$house_y = $has_house ? (float)$house['hPosY'] : 0.0;

// Conversion d coordinates d GTA SA l Percent (%) bach t-pina exact f l-map
$map_left = (($house_x + 3000) / 6000) * 100;
$map_top = ((3000 - $house_y) / 6000) * 100;

// 2. Dynamic Inventory Loading mn jadwel `newinventory` kima 3ndek f Pawn!
// Slots 3ndna mn 0 tal 24 (25 slots f l-majmo3)
$inventory = array_fill(0, 25, null); // Khowwi dynamic slots f l-awal

$stmt_inv = $pdo->prepare("SELECT * FROM `newinventory` WHERE `ownerID` = ? ORDER BY `slot` ASC");
$stmt_inv->execute([$player_id]);
$inv_items = $stmt_inv->fetchAll();

foreach ($inv_items as $item) {
    $slot_id = (int)$item['slot'];
    if ($slot_id >= 0 && $slot_id < 25) {
        $inventory[$slot_id] = [
            'item' => $item['invItem'],
            'model' => $item['invModel'],
            'quantity' => $item['invQuantity']
        ];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SAMP - Player Dashboard</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Poppins', sans-serif; }
        body {
            background: radial-gradient(circle at center, #1b1c31 0%, #0a0b12 100%);
            color: #fff;
            min-height: 100vh;
            padding: 20px;
        }
        .dashboard-wrapper {
            max-width: 1100px;
            margin: 0 auto;
            background: rgba(255, 255, 255, 0.03);
            backdrop-filter: blur(15px);
            -webkit-backdrop-filter: blur(15px);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 16px;
            padding: 30px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.5);
        }
        .main-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        .logo-area { display: flex; align-items: center; gap: 12px; }
        .logo-small {
            width: 36px;
            height: 36px;
            background: #5d6cfc;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 18px;
        }
        .welcome-txt { font-size: 15px; color: #e2e4f0; }
        .welcome-txt strong { color: #5d6cfc; }
        .nav-menu { display: flex; gap: 10px; }
        .nav-item {
            color: #8c8fae;
            text-decoration: none;
            padding: 10px 18px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s;
        }
        .nav-item.active, .nav-item:hover { color: #fff; background: rgba(255, 255, 255, 0.05); }
        .nav-item.logout { color: #ff5252; }

        /* Tabs Panels */
        .tab-panel { display: none; }
        .tab-panel.active { display: block; }

        /* Stats Cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
        }
        .stat-card {
            background: rgba(255, 255, 255, 0.02);
            border: 1px solid rgba(255, 255, 255, 0.05);
            border-radius: 12px;
            padding: 22px;
        }
        .stat-val { font-size: 26px; font-weight: 700; margin-bottom: 6px; display: block; }
        .stat-lbl { font-size: 12px; color: #8c8fae; text-transform: uppercase; }
        .val-purple { color: #b388ff; }
        .val-green { color: #4caf50; }
        .val-orange { color: #ff9800; }

        /* Dynamic Inventory Grid (5x5 Layout) */
        .inv-grid {
            display: grid;
            grid-template-columns: repeat(5, 1fr);
            gap: 15px;
            max-width: 600px;
            margin: 0 auto;
        }
        .inv-slot {
            background: rgba(255, 255, 255, 0.02);
            border: 1px solid rgba(255, 255, 255, 0.06);
            border-radius: 12px;
            aspect-ratio: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            position: relative;
            padding: 10px;
            transition: 0.3s;
        }
        .inv-slot:hover {
            background: rgba(93, 108, 252, 0.08);
            border-color: #5d6cfc;
        }
        .inv-slot-empty { color: rgba(255, 255, 255, 0.15); font-size: 24px; font-weight: 300; }
        .inv-item-name { font-size: 11px; text-align: center; color: #e2e4f0; margin-top: 5px; font-weight: 500; }
        .inv-qty {
            position: absolute;
            top: 8px;
            left: 8px;
            background: rgba(93, 108, 252, 0.3);
            border: 1px solid #5d6cfc;
            color: #fff;
            font-size: 10px;
            padding: 2px 6px;
            border-radius: 6px;
            font-weight: 600;
        }

        /* Map styling */
        .map-wrapper {
            position: relative;
            width: 100%;
            height: 520px;
            border-radius: 12px;
            overflow: hidden;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        .gta-map-img { width: 100%; height: 100%; object-fit: cover; opacity: 0.85; }
        .map-marker {
            position: absolute;
            width: 16px;
            height: 16px;
            background: #00e676;
            border: 3px solid #ffffff;
            border-radius: 50%;
            transform: translate(-50%, -50%);
            cursor: pointer;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.5);
        }
        .map-marker .pulse {
            position: absolute;
            top: -3px; left: -3px; width: 16px; height: 16px;
            border-radius: 50%; border: 3px solid #00e676;
            animation: pulse-animation 1.8s infinite ease-out; opacity: 0;
        }
        @keyframes pulse-animation {
            0% { transform: scale(1); opacity: 1; }
            100% { transform: scale(3.5); opacity: 0; }
        }
        .marker-popover {
            position: absolute;
            bottom: 25px; left: 50%; transform: translateX(-50%);
            background: rgba(10, 11, 18, 0.95);
            border: 1px solid rgba(255, 255, 255, 0.15);
            padding: 10px 14px; border-radius: 8px;
            font-size: 11px; color: #fff; white-space: nowrap;
            box-shadow: 0 5px 15px rgba(0,0,0,0.5); pointer-events: none; opacity: 0; transition: opacity 0.3s ease;
        }
        .map-marker:hover .marker-popover { opacity: 1; }
        
        .section-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
        .badge-unlocked { background: rgba(76, 175, 80, 0.1); color: #4caf50; border: 1px solid rgba(76, 175, 80, 0.2); padding: 4px 14px; border-radius: 20px; font-size: 12px; font-weight: 600; }
    </style>
</head>
<body>

    <div class="dashboard-wrapper">
        <!-- Header -->
        <header class="main-header">
            <div class="logo-area">
                <div class="logo-small">K</div>
                <span class="welcome-txt">Welcome back, <strong><?php echo htmlspecialchars($username); ?></strong></span>
            </div>
            <nav class="nav-menu">
                <div onclick="switchTab('house')" id="tab-btn-house" class="nav-item active">My House</div>
                <div onclick="switchTab('inventory')" id="tab-btn-inventory" class="nav-item">My Inventory</div>
                <div onclick="switchTab('map')" id="tab-btn-map" class="nav-item">Map Location</div>
                <a href="logout.php" class="nav-item logout">Logout</a>
            </nav>
        </header>

        <!-- TAB 1: HOUSE INFO -->
        <div id="tab-house" class="tab-panel active">
            <div class="section-header">
                <h2>House Information</h2>
                <?php if($has_house): ?>
                    <span class="badge-unlocked">Owned</span>
                <?php else: ?>
                    <span class="badge-unlocked" style="background:rgba(244,67,54,0.1); color:#f44336; border-color:rgba(244,67,54,0.2);">No House</span>
                <?php endif; ?>
            </div>

            <div class="stats-grid">
                <div class="stat-card">
                    <span class="stat-val"><?php echo $has_house ? htmlspecialchars($house['hType']) : '0'; ?></span>
                    <span class="stat-lbl">House Type</span>
                </div>
                <div class="stat-card">
                    <span class="stat-val val-purple">Level <?php echo $has_house ? htmlspecialchars($house['hLevel']) : '0'; ?></span>
                    <span class="stat-lbl">House Level</span>
                </div>
                <div class="stat-card">
                    <span class="stat-val val-green">$<?php echo $has_house ? number_format($house['hCash']) : '0'; ?></span>
                    <span class="stat-lbl">House Cash</span>
                </div>
                <div class="stat-card">
                    <span class="stat-val val-orange">$<?php echo $has_house ? number_format($house['hPrice']) : '0'; ?></span>
                    <span class="stat-lbl">Property Value</span>
                </div>
            </div>
        </div>

        <!-- TAB 2: MY INVENTORY (5x5 Grid) -->
        <div id="tab-inventory" class="tab-panel">
            <div class="section-header">
                <h2>My Inventory (25 Slots)</h2>
            </div>
            
            <div class="inv-grid">
                <?php for ($i = 0; $i < 25; $i++): ?>
                    <?php if ($inventory[$i] !== null): ?>
                        <div class="inv-slot">
                            <span class="inv-qty">x<?php echo $inventory[$i]['quantity']; ?></span>
                            <!-- Icon/Placeholder representation -->
                            <span style="font-size:24px;">📦</span>
                            <span class="inv-item-name"><?php echo htmlspecialchars($inventory[$i]['item']); ?></span>
                        </div>
                    <?php else: ?>
                        <div class="inv-slot">
                            <span class="inv-slot-empty">+</span>
                        </div>
                    <?php endif; ?>
                <?php endfor; ?>
            </div>
        </div>

        <!-- TAB 3: MAP LOCATION -->
        <div id="tab-map" class="tab-panel">
            <div class="section-header">
                <h2>House Location on Map</h2>
            </div>

            <div class="map-wrapper">
                <!-- High Resolution SA-MP Map -->
                <img class="gta-map-img" src="https://i.imgur.com/kPLa2Y9.jpeg" alt="GTA San Andreas Map">
                
                <?php if($has_house): ?>
                    <!-- Marker converter dyal (X, Y) coordinates -->
                    <div class="map-marker" style="left: <?php echo $map_left; ?>%; top: <?php echo $map_top; ?>%;">
                        <div class="pulse"></div>
                        <div class="marker-popover">
                            <strong>House ID: #<?php echo $house['hID']; ?></strong><br>
                            X: <?php echo round($house_x, 1); ?><br>
                            Y: <?php echo round($house_y, 1); ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        function switchTab(tabName) {
            // Khbi kolchi l-tab panels
            document.querySelectorAll('.tab-panel').forEach(panel => {
                panel.classList.remove('active');
            });
            // 7eyyed l-active mn ga3 l-buttons
            document.querySelectorAll('.nav-item').forEach(btn => {
                btn.classList.remove('active');
            });

            // Warri l-tab li clikiti 3lih
            document.getElementById('tab-' + tabName).classList.add('active');
            document.getElementById('tab-btn-' + tabName).classList.add('active');
        }
    </script>
</body>
</html>