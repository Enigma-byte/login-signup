<?php
require_once 'configs/database.php';
require_once 'includes/helpers.php';

// Start session and check if valid
// Get return URL if set
$return_url = isset($_GET['return_url']) ? $_GET['return_url'] : null;

if (isLoggedIn()) {
    redirect(getUserRole(), $return_url);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitizeInput($_POST['username']);
    $password = $_POST['password'];

    try {
        $db = Database::getInstance();
        $result = $db->query(
            "SELECT UserId, Username, Password, Role FROM Users WHERE Username = ? AND AccountStatus = 'Active'",
            [$username]
        );

        if ($result && password_verify($password, $result[0]['Password'])) {
            $_SESSION['user_id'] = $result[0]['UserId'];
            $_SESSION['username'] = $result[0]['Username'];
            $_SESSION['user_role'] = $result[0]['Role'];

            // Create session record
            $sessionId = generateSessionId();
            $_SESSION['session_id'] = $sessionId; // Store session ID in PHP session
            
            $db->execute(
                "INSERT INTO Sessions (SessionId, UserId, ExpiresAt, IPAddress, UserAgent)
                VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 24 HOUR), ?, ?)",
                [$sessionId, $_SESSION['user_id'], $_SERVER['REMOTE_ADDR'], $_SERVER['HTTP_USER_AGENT']]
            );

            // Log successful login
            $db->execute(
                "INSERT INTO LoginAttempts (UserId, Status, IPAddress, UserAgent)
                VALUES (?, 'Success', ?, ?)",
                [$_SESSION['user_id'], $_SERVER['REMOTE_ADDR'], $_SERVER['HTTP_USER_AGENT']]
            );

            // Update last login
            $db->execute(
                "UPDATE Users SET LastLogin = NOW() WHERE UserId = ?",
                [$_SESSION['user_id']]
            );

            // Get return URL from POST if available, fallback to GET
            $return_url = isset($_POST['return_url']) ? $_POST['return_url'] : 
                         (isset($_GET['return_url']) ? $_GET['return_url'] : null);
            
            redirect($_SESSION['user_role'], $return_url);
        } else {
            // Log failed attempt if user exists
            if ($result) {
                $db->execute(
                    "INSERT INTO LoginAttempts (UserId, Status, IPAddress, UserAgent)
                    VALUES (?, 'Failure', ?, ?)",
                    [$result[0]['UserId'], $_SERVER['REMOTE_ADDR'], $_SERVER['HTTP_USER_AGENT']]
                );
            }
            setFlashMessage('error', 'Invalid username or password');
        }
    } catch (Exception $e) {
        setFlashMessage('error', 'An error occurred. Please try again later.');
    }
}

$pageTitle = 'Login';
require_once 'includes/header.php';
?>

<div class="min-h-screen bg-[radial-gradient(ellipse_at_top,_var(--tw-gradient-stops))] from-blue-950 via-gray-950 to-gray-950 flex items-center justify-center p-6">
    <div class="relative w-full max-w-md">
        <!-- Decorative elements -->
        <div class="absolute -top-12 -left-12 w-64 h-64 bg-blue-500 rounded-full mix-blend-multiply filter blur-2xl opacity-10 animate-blob"></div>
        <div class="absolute -top-12 -right-12 w-64 h-64 bg-cyan-500 rounded-full mix-blend-multiply filter blur-2xl opacity-10 animate-blob animation-delay-2000"></div>
        <div class="absolute -bottom-16 left-8 w-64 h-64 bg-purple-500 rounded-full mix-blend-multiply filter blur-2xl opacity-10 animate-blob animation-delay-4000"></div>

        <div class="relative space-y-8">
        <div class="bg-gradient-to-br from-blue-900 to-[#070c1b] border border-blue-500/20 rounded-2xl p-8 shadow-lg shadow-blue-900/20 hover:shadow-blue-400/30 hover:border-blue-400/30 transition-all duration-300">
            <!-- Logo/Brand -->
            <div class="flex items-center justify-center space-x-2 mb-8">
                <div class="w-12 h-12 bg-blue-800 bg-opacity-50 border border-blue-400/30 rounded-xl flex items-center justify-center shadow-lg transform hover:rotate-12 transition-transform duration-300">
                    <i class="fas fa-shield-alt text-white text-xl"></i>
                </div>
                <span class="text-2xl font-bold bg-gradient-to-r from-blue-400 to-purple-500 text-transparent bg-clip-text">Enigma</span>
            </div>

            <div class="text-center space-y-2 mb-8">
                <h1 class="text-3xl font-bold tracking-tight text-white">Welcome Back</h1>
                <p class="text-blue-300/80">Sign in to access your account</p>
            </div>

            <form method="POST" action="" class="space-y-5">
                <?php if (isset($_GET['return_url'])): ?>
                <input type="hidden" name="return_url" value="<?php echo htmlspecialchars($_GET['return_url']); ?>">
                <?php endif; ?>
            <div class="space-y-1.5">
                <div class="relative group">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="fas fa-user text-blue-400/50 group-focus-within:text-blue-400 transition-colors duration-200"></i>
                    </div>
                    <input type="text" id="username" name="username" required
                        class="w-full pl-10 pr-4 py-3 rounded-xl bg-slate-800 border border-blue-500/20 text-[#0f0f17] placeholder-blue-300/50 focus:border-blue-400 focus:bg-slate-700 focus:ring-2 focus:ring-blue-500/50 focus:outline-none transition-all duration-200 hover:bg-slate-700"
                        placeholder="Username">
                </div>
            </div>

            <div class="space-y-1.5">
                <div class="relative group">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="fas fa-lock text-blue-400/50 group-focus-within:text-blue-400 transition-colors duration-200"></i>
                    </div>
                    <input type="password" id="password" name="password" required
                        class="w-full pl-10 pr-4 py-3 rounded-xl bg-slate-800 border border-blue-500/20 text-[#0f0f17] placeholder-blue-300/50 focus:border-blue-400 focus:bg-slate-700 focus:ring-2 focus:ring-blue-500/50 focus:outline-none transition-all duration-200 hover:bg-slate-700"
                        placeholder="Password">
                </div>
            </div>

            <div class="pt-3">
                <button type="submit" class="group w-full bg-gradient-to-r from-blue-500 to-purple-600 hover:from-blue-600 hover:to-purple-700 text-white font-semibold py-3 px-4 rounded-xl transition-all duration-200 flex items-center justify-center space-x-2 shadow-lg shadow-blue-900/30 hover:shadow-purple-900/30 focus:ring-2 focus:ring-purple-500/20 focus:outline-none">
                    <i class="fas fa-sign-in-alt group-hover:translate-x-0.5 transition-transform duration-200"></i>
                    <span>Sign In</span>
                </button>
            </div>
            </form>

            <div class="mt-6 text-center">
                <p class="text-sm text-blue-400/80">
                    Don't have an account? <a href="/signup.php" class="text-transparent bg-clip-text bg-gradient-to-r from-blue-400 to-purple-400 hover:from-blue-300 hover:to-purple-300 font-medium transition-colors duration-200">Create one now →</a>
                </p>
            </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
