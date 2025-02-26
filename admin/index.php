<?php
require_once '../configs/database.php';
require_once '../includes/helpers.php';
require_once '../includes/ua_helper.php';

requireAdmin();

try {
    $db = Database::getInstance();
    $stats = [
        'total_users' => $db->query("SELECT COUNT(*) as count FROM Users")[0]['count'],
        'active_users' => $db->query("SELECT COUNT(*) as count FROM Users WHERE AccountStatus = 'Active'")[0]['count'],
        'login_attempts' => $db->query("SELECT COUNT(*) as count FROM LoginAttempts WHERE AttemptTime > DATE_SUB(NOW(), INTERVAL 24 HOUR)")[0]['count'],
        'active_sessions' => $db->query("SELECT COUNT(*) as count FROM Sessions WHERE ExpiresAt > NOW()")[0]['count']
    ];
} catch (Exception $e) {
    setFlashMessage('error', 'Error loading dashboard data');
}

$pageTitle = 'Admin Dashboard';
require_once '../includes/header.php';
?>

<div class="space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="text-3xl font-bold">
            <i class="fas fa-dashboard"></i> Admin Dashboard
        </h1>
        <div class="text-sm text-[var(--text-secondary)]">
            Last updated: <?php echo date('Y-m-d H:i:s'); ?>
        </div>
    </div>

    <!-- Admin Navigation -->
    <div class="flex gap-4 mb-6">
        <a href="users.php" class="inline-flex items-center px-4 py-2 bg-blue-800 hover:bg-blue-700 text-blue-100 rounded-lg transition-colors">
            <i class="fas fa-users-cog mr-2"></i>
            Manage Users
        </a>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6">
        <!-- Total Users Card -->
        <div class="bg-gradient-to-br from-blue-900 to-[#070c1b] border border-blue-500/20 rounded-xl p-6 shadow-lg shadow-blue-900/20 hover:shadow-blue-400/30 hover:border-blue-400/30 transition-all duration-300 hover:-translate-y-1">
            <div class="flex items-center justify-between">
                <div class="text-blue-300">Total Users</div>
                <div class="p-3 bg-blue-800 bg-opacity-50 border border-blue-400/30 rounded-lg shadow-lg shadow-blue-500/30">
                    <i class="fas fa-users text-2xl text-blue-400"></i>
                </div>
            </div>
            <div class="mt-4 text-3xl font-bold text-blue-100"><?php echo $stats['total_users']; ?></div>
        </div>

        <!-- Active Users Card -->
        <div class="bg-gradient-to-br from-blue-900 to-[#070c1b] border border-blue-500/20 rounded-xl p-6 shadow-lg shadow-blue-900/20 hover:shadow-blue-400/30 hover:border-blue-400/30 transition-all duration-300 hover:-translate-y-1">
            <div class="flex items-center justify-between">
                <div class="text-blue-300">Active Users</div>
                <div class="p-3 bg-blue-800 bg-opacity-50 border border-blue-400/30 rounded-lg shadow-lg shadow-blue-500/30">
                    <i class="fas fa-user-check text-2xl text-blue-400"></i>
                </div>
            </div>
            <div class="mt-4 text-3xl font-bold text-blue-100"><?php echo $stats['active_users']; ?></div>
        </div>

        <!-- Login Attempts Card -->
        <div class="bg-gradient-to-br from-blue-900 to-[#070c1b] border border-blue-500/20 rounded-xl p-6 shadow-lg shadow-blue-900/20 hover:shadow-blue-400/30 hover:border-blue-400/30 transition-all duration-300 hover:-translate-y-1">
            <div class="flex items-center justify-between">
                <div class="text-blue-300">24h Login Attempts</div>
                <div class="p-3 bg-blue-800 bg-opacity-50 border border-blue-400/30 rounded-lg shadow-lg shadow-blue-500/30">
                    <i class="fas fa-shield-alt text-2xl text-blue-400"></i>
                </div>
            </div>
            <div class="mt-4 text-3xl font-bold text-blue-100"><?php echo $stats['login_attempts']; ?></div>
        </div>

        <!-- Active Sessions Card -->
        <div class="bg-gradient-to-br from-blue-900 to-[#070c1b] border border-blue-500/20 rounded-xl p-6 shadow-lg shadow-blue-900/20 hover:shadow-blue-400/30 hover:border-blue-400/30 transition-all duration-300 hover:-translate-y-1">
            <div class="flex items-center justify-between">
                <div class="text-blue-300">Active Sessions</div>
                <div class="p-3 bg-blue-800 bg-opacity-50 border border-blue-400/30 rounded-lg shadow-lg shadow-blue-500/30">
                    <i class="fas fa-key text-2xl text-blue-400"></i>
                </div>
            </div>
            <div class="mt-4 text-3xl font-bold text-blue-100"><?php echo $stats['active_sessions']; ?></div>
        </div>
    </div>

    <!-- Recent Activity -->
    <div class="bg-gradient-to-br from-blue-900 to-[#070c1b] border border-blue-500/20 rounded-xl p-6 shadow-lg shadow-blue-900/20 hover:shadow-blue-400/30 hover:border-blue-400/30 transition-all duration-300">
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-xl font-semibold text-blue-100">Recent Activity</h2>
            <span class="text-xs text-blue-400 bg-blue-950/50 px-3 py-1 rounded-full border border-blue-800/20">Last 24 hours</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="text-blue-300 text-left">
                        <th class="pb-4 font-medium">Username</th>
                        <th class="pb-4 font-medium">Action</th>
                        <th class="pb-4 font-medium">Browser</th>
                        <th class="pb-4 font-medium">IP Address</th>
                        <th class="pb-4 font-medium">Time</th>
                    </tr>
                </thead>
                <tbody class="text-sm">
                    <?php
                    $activities = $db->query(
                        "SELECT u.Username, la.Status, la.IPAddress, la.AttemptTime, la.UserAgent
                        FROM LoginAttempts la
                        JOIN Users u ON la.UserId = u.UserId
                        ORDER BY la.AttemptTime DESC
                        LIMIT 5"
                    );

                    foreach ($activities as $activity): ?>
                        <tr class="border-t border-blue-900/30">
                            <td class="py-4 text-blue-200"><?php echo htmlspecialchars($activity['Username']); ?></td>
                            <td class="py-4">
                                <span class="px-3 py-1 rounded-full text-xs font-medium border <?php echo $activity['Status'] === 'Success' ? 'bg-green-900/30 border-green-700/30 text-green-400' : 'bg-red-900/30 border-red-700/30 text-red-400'; ?>">
                                    <?php echo $activity['Status']; ?> Login
                                </span>
                            </td>
                            <?php $ua = getFriendlyUserAgent($activity['UserAgent']); ?>
                            <td class="py-4">
                                <div class="text-fuchsia-200"><?php echo htmlspecialchars($ua['full']); ?></div>
                            </td>
                            <td class="py-4"><?php echo htmlspecialchars($activity['IPAddress']); ?></td>
                            <td class="py-4"><?php echo date('Y-m-d H:i:s', strtotime($activity['AttemptTime'])); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
