<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../configs/database.php';
require_once '../configs/config.php';
require_once '../includes/helpers.php';

// Define constants for roles and statuses
const VALID_ROLES = ['Admin', 'User'];
const VALID_STATUSES = ['Active', 'Inactive', 'Suspended'];

// Check admin access
requireAdmin();

require_once '../includes/header.php';

try {
    $db = Database::getInstance();

    // Handle user actions (status changes, role changes, etc)
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (isset($_POST['action'])) {
            $userId = filter_input(INPUT_POST, 'userId', FILTER_SANITIZE_NUMBER_INT);

            // Prevent any modifications to the super admin account (ID 1)
            if ($userId == 1) {
                setFlashMessage('error', 'The super admin account cannot be modified');
                header('Location: ' . $_SERVER['PHP_SELF']);
                exit;
            }

            switch ($_POST['action']) {
                case 'updateStatus':
                    $newStatus = htmlspecialchars(trim($_POST['status']));
                    if (in_array($newStatus, VALID_STATUSES)) {
                        $db->query("UPDATE Users SET AccountStatus = ? WHERE UserId = ?", [$newStatus, $userId]);

                        // Invalidate sessions if status is not Active
                        if ($newStatus !== 'Active') {
                            $db->query(
                                "UPDATE Sessions SET IsValid = FALSE
                                WHERE UserId = ? AND ExpiresAt > NOW()",
                                [$userId]
                            );
                        }
                    }
                    break;

                case 'updateRole':
                    $newRole = htmlspecialchars(trim($_POST['role']));
                    if (in_array($newRole, VALID_ROLES)) {
                        $db->query("UPDATE Users SET Role = ? WHERE UserId = ?", [$newRole, $userId]);
                    }
                    break;

                case 'deleteUser':
                    // First invalidate all sessions
                    $db->query(
                        "UPDATE Sessions SET IsValid = FALSE
                        WHERE UserId = ? AND ExpiresAt > NOW()",
                        [$userId]
                    );
                    // Then delete the user (Sessions and LoginAttempts will be cascade deleted)
                    $db->query("DELETE FROM Users WHERE UserId = ? AND Role != 'Admin'", [$userId]);
                    break;
            }

            // Set success message
            setFlashMessage('success', 'User updated successfully');
        }
    }

    // Fetch all users with their session count (excluding super admin with ID 1)
    $users = $db->query(
        "SELECT
            u.UserId,
            u.Username,
            u.Email,
            u.Role,
            u.CreatedAt,
            u.LastLogin,
            u.AccountStatus,
            COUNT(DISTINCT s.SessionId) as ActiveSessions
        FROM Users u
        LEFT JOIN Sessions s ON u.UserId = s.UserId
            AND s.ExpiresAt > NOW()
            AND s.IsValid = TRUE
        WHERE u.UserId != 1
        GROUP BY u.UserId
        ORDER BY u.CreatedAt DESC"
    );

} catch (Exception $e) {
    error_log(sprintf("Error in users.php [%s]: %s", get_class($e), $e->getMessage()));
    setFlashMessage('error', 'An error occurred while processing your request');
}

// Get flash message if any
$flash = getFlashMessage();
?>

<div class="space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="text-3xl font-bold">
            <i class="fas fa-users-cog"></i> User Management
        </h1>
        <a href="index.php" class="inline-flex items-center px-4 py-2 bg-blue-800 hover:bg-blue-700 text-blue-100 rounded-lg transition-colors">
            <i class="fas fa-arrow-left mr-2"></i>
            Back to Dashboard
        </a>
    </div>

    <?php if ($flash): ?>
    <div class="rounded-lg p-4 <?php echo $flash['type'] === 'error' ? 'bg-red-900/50 border border-red-700/50 text-red-300' : 'bg-green-900/50 border border-green-700/50 text-green-300'; ?>">
        <?php echo htmlspecialchars($flash['message']); ?>
    </div>
    <?php endif; ?>

    <div class="bg-gradient-to-br from-blue-900 to-[#070c1b] border border-blue-500/20 rounded-xl p-6 shadow-lg shadow-blue-900/20">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="text-blue-300 text-left">
                        <th class="pb-4 font-medium">Username</th>
                        <th class="pb-4 font-medium">Email</th>
                        <th class="pb-4 font-medium">Role</th>
                        <th class="pb-4 font-medium">Status</th>
                        <th class="pb-4 font-medium">Created</th>
                        <th class="pb-4 font-medium">Last Login</th>
                        <th class="pb-4 font-medium">Active Sessions</th>
                        <th class="pb-4 font-medium">Actions</th>
                    </tr>
                </thead>
                <tbody class="text-sm">
                    <?php foreach ($users as $user): ?>
                    <tr class="border-t border-blue-900/30">
                        <td class="py-4 text-blue-200"><?php echo htmlspecialchars($user['Username']); ?></td>
                        <td class="py-4 text-blue-200"><?php echo htmlspecialchars($user['Email']); ?></td>
                        <td class="py-4">
                            <form method="POST" class="inline-block">
                                <input type="hidden" name="action" value="updateRole">
                                <input type="hidden" name="userId" value="<?php echo $user['UserId']; ?>">
                                <select name="role"
                                    class="appearance-none cursor-pointer min-w-[100px] bg-gradient-to-r from-blue-800 to-blue-700 hover:from-blue-700 hover:to-blue-600 text-white font-medium rounded-lg px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all"
                                    onchange="confirmChange(this.form, 'Are you sure you want to change this user\'s role?')"
                                    <?php echo $user['Role'] === 'Admin' ? 'disabled' : ''; ?>>
                                    <?php foreach (VALID_ROLES as $role): ?>
                                    <option value="<?php echo $role; ?>" <?php echo $user['Role'] === $role ? 'selected' : ''; ?>><?php echo $role; ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-white">
                                    <i class="fas fa-chevron-down text-xs"></i>
                                </div>
                            </form>
                        </td>
                        <td class="py-4">
                            <form method="POST" class="inline-block relative">
                                <input type="hidden" name="action" value="updateStatus">
                                <input type="hidden" name="userId" value="<?php echo $user['UserId']; ?>">
                                <select name="status"
                                    class="appearance-none cursor-pointer min-w-[120px] font-medium rounded-lg px-4 py-2 text-sm focus:outline-none focus:ring-2 transition-all
                                    <?php
                                    echo match($user['AccountStatus']) {
                                        'Active' => 'bg-gradient-to-r from-green-800 to-green-700 hover:from-green-700 hover:to-green-600 text-green-100 focus:ring-green-500',
                                        'Inactive' => 'bg-gradient-to-r from-yellow-800 to-yellow-700 hover:from-yellow-700 hover:to-yellow-600 text-yellow-100 focus:ring-yellow-500',
                                        'Suspended' => 'bg-gradient-to-r from-red-800 to-red-700 hover:from-red-700 hover:to-red-600 text-red-100 focus:ring-red-500',
                                        default => 'bg-gradient-to-r from-gray-800 to-gray-700 hover:from-gray-700 hover:to-gray-600 text-gray-100 focus:ring-gray-500'
                                    };
                                    ?>"
                                    onchange="confirmChange(this.form, 'Are you sure you want to change this user\'s status? This will invalidate all their active sessions.')"
                                    <?php echo $user['Role'] === 'Admin' ? 'disabled' : ''; ?>>
                                    <?php foreach (VALID_STATUSES as $status): ?>
                                    <option value="<?php echo $status; ?>" <?php echo $user['AccountStatus'] === $status ? 'selected' : ''; ?>><?php echo $status; ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-white">
                                    <i class="fas fa-chevron-down text-xs"></i>
                                </div>
                            </form>
                        </td>
                        <td class="py-4 text-blue-200"><?php echo date('Y-m-d', strtotime($user['CreatedAt'])); ?></td>
                        <td class="py-4 text-blue-200"><?php echo $user['LastLogin'] ? date('Y-m-d', strtotime($user['LastLogin'])) : 'Never'; ?></td>
                        <td class="py-4 text-blue-200"><?php echo $user['ActiveSessions']; ?></td>
                        <td class="py-4">
                            <?php if ($user['Role'] !== 'Admin'): ?>
                            <form method="POST" class="inline-block" onsubmit="return confirm('Are you sure you want to delete this user? This action cannot be undone and will invalidate all their sessions.');">
                                <input type="hidden" name="action" value="deleteUser">
                                <input type="hidden" name="userId" value="<?php echo $user['UserId']; ?>">
                                <button type="submit" class="bg-red-900/50 hover:bg-red-800 text-red-300 border border-red-700/50 rounded-lg px-3 py-1 text-sm transition-colors">
                                    <i class="fas fa-trash-alt mr-1"></i> Delete
                                </button>
                            </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
function confirmChange(form, message) {
    const select = form.querySelector('select');
    const confirmed = confirm(message);
    if (confirmed) {
        form.submit();
    } else {
        // Restore the original value and trigger change event for any dependent styling
        select.value = select.getAttribute('data-original');
        select.dispatchEvent(new Event('change'));
    }
}

document.addEventListener('DOMContentLoaded', function() {
    const selects = document.querySelectorAll('select');
    selects.forEach(select => {
        select.setAttribute('data-original', select.value);
    });
});
</script>

<?php require_once '../includes/footer.php'; ?>
