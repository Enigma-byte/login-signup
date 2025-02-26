<?php
require_once '../configs/database.php';
require_once '../includes/helpers.php';
require_once '../includes/ua_helper.php';

requireLogin();

try {
    $db = Database::getInstance();
    $userId = $_SESSION['user_id'];

    // Get user data
    $userData = $db->query(
        "SELECT Username, Email, LastLogin, CreatedAt FROM Users WHERE UserId = ?",
        [$userId]
    )[0];

    // Get recent login history
    $loginHistory = $db->query(
        "SELECT AttemptTime, Status, IPAddress, UserAgent
        FROM LoginAttempts
        WHERE UserId = ?
        ORDER BY AttemptTime DESC
        LIMIT 3",
        [$userId]
    );

    // Get active sessions
    $activeSessions = $db->query(
        "SELECT CreatedAt, ExpiresAt, IPAddress, UserAgent
        FROM Sessions
        WHERE UserId = ? AND ExpiresAt > NOW()
        ORDER BY CreatedAt DESC",
        [$userId]
    );
} catch (Exception $e) {
    setFlashMessage('error', 'Error loading user data');
}

$pageTitle = 'Home';
require_once '../includes/header.php';
?>

<div class="min-h-screen bg-gray-950 space-y-8 p-6 sm:p-8">
    <!-- Welcome Section -->
    <div class="bg-gradient-to-br from-blue-900 to-[#070c1b] border border-blue-500/20 rounded-xl p-8 shadow-lg shadow-blue-900/20 hover:shadow-blue-400/30 hover:border-blue-400/30 transition-all duration-300">
        <div class="flex items-center space-x-4">
            <div class="bg-blue-800 bg-opacity-50 border border-blue-400/30 rounded-full p-4 shadow-lg shadow-blue-500/30">
                <i class="fas fa-user text-3xl text-fuchsia-400"></i>
            </div>
            <div>
                <h1 class="text-3xl font-bold tracking-tight text-fuchsia-100">Welcome, <?php echo htmlspecialchars($userData['Username']); ?>! 👋</h1>
                <p class="text-fuchsia-300">
                    Member since <?php echo date('F j, Y', strtotime($userData['CreatedAt'])); ?>
                </p>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 sm:gap-6">
        <!-- Account Information -->
        <div class="bg-gradient-to-br from-blue-900 to-[#070c1b] border border-blue-500/20 rounded-xl p-6 shadow-lg shadow-blue-900/20 hover:shadow-blue-400/30 hover:border-blue-400/30 transition-all duration-300">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-xl font-semibold text-fuchsia-100">Account Information</h2>
                <span class="text-xs text-blue-400 bg-blue-950/50 px-3 py-1 rounded-full border border-blue-800/20">Personal Details</span>
            </div>
            <div class="space-y-4">
                <div>
                    <label class="text-fuchsia-300 font-medium">Email</label>
                    <p><?php echo htmlspecialchars($userData['Email']); ?></p>
                </div>
                <div>
                    <label class="text-fuchsia-300 font-medium">Last Login</label>
                    <p><?php echo $userData['LastLogin'] ? date('Y-m-d H:i:s', strtotime($userData['LastLogin'])) : 'Never'; ?></p>
                </div>
            </div>
        </div>

        <!-- Active Sessions -->
        <div class="bg-gradient-to-br from-blue-900 to-[#070c1b] border border-blue-500/20 rounded-xl p-6 shadow-lg shadow-blue-900/20 hover:shadow-blue-400/30 hover:border-blue-400/30 transition-all duration-300">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-xl font-semibold text-fuchsia-100">Active Sessions</h2>
                <span class="text-xs text-blue-400 bg-blue-950/50 px-3 py-1 rounded-full border border-blue-800/20">Current Devices</span>
            </div>
            <div class="space-y-4">
                <?php foreach ($activeSessions as $session): ?>
                    <div class="flex items-center justify-between p-3 bg-blue-800 bg-opacity-50 border border-blue-400/30 rounded-lg shadow-lg shadow-blue-500/30 hover:bg-blue-800 hover:bg-opacity-70 transition-all duration-200">
                        <div>
                            <?php $ua = getFriendlyUserAgent($session['UserAgent']); ?>
                            <div class="text-fuchsia-200"><?php echo htmlspecialchars($ua['full']); ?></div>
                            <div class="text-xs text-[var(--text-secondary)]">
                                <?php echo htmlspecialchars($session['IPAddress']); ?>
                            </div>
                        </div>
                        <div class="text-xs text-[var(--text-secondary)]">
                            Expires: <?php echo date('Y-m-d H:i:s', strtotime($session['ExpiresAt'])); ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Recent Login Activity -->
        <div class="bg-gradient-to-br from-blue-900 to-[#070c1b] border border-blue-500/20 rounded-xl p-6 shadow-lg shadow-blue-900/20 hover:shadow-blue-400/30 hover:border-blue-400/30 transition-all duration-300 lg:col-span-2">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-xl font-semibold text-blue-100">Recent Login Activity</h2>
                <span class="text-xs text-blue-400 bg-blue-950/50 px-3 py-1 rounded-full border border-blue-800/20">Last 24 hours</span>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="text-blue-300 text-left">
                            <th class="pb-4">Time</th>
                            <th class="pb-4">Status</th>
                            <th class="pb-4">IP Address</th>
                            <th class="pb-4">Device</th>
                        </tr>
                    </thead>
                    <tbody class="text-sm">
                        <?php foreach ($loginHistory as $login): ?>
                            <tr class="border-t border-blue-800/30">
                                <td class="py-4"><?php echo date('Y-m-d H:i:s', strtotime($login['AttemptTime'])); ?></td>
                                <td class="py-4">
                                    <span class="px-2 py-1 rounded-full text-xs <?php echo $login['Status'] === 'Success' ? 'bg-green-900/50 text-green-300' : 'bg-red-900/50 text-red-300'; ?>">
                                        <?php echo $login['Status']; ?>
                                    </span>
                                </td>
                                <td class="py-4"><?php echo htmlspecialchars($login['IPAddress']); ?></td>
                                <?php $ua = getFriendlyUserAgent($login['UserAgent']); ?>
                                <td class="py-4">
                                    <div class="text-blue-200"><?php echo htmlspecialchars($ua['full']); ?></div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
