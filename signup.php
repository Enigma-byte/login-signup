<?php
require_once 'configs/database.php';
require_once 'includes/helpers.php';

if (isLoggedIn()) {
    redirect(getUserRole());
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitizeInput($_POST['username']);
    $email = sanitizeInput($_POST['email']);
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirm_password'];

    $errors = [];

    // Validate input
    if (strlen($username) < 3) {
        $errors[] = "Username must be at least 3 characters long";
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format";
    }
    if (strlen($password) < 8) {
        $errors[] = "Password must be at least 8 characters long";
    }
    if ($password !== $confirmPassword) {
        $errors[] = "Passwords do not match";
    }

    if (empty($errors)) {
        try {
            $db = Database::getInstance();

            // Check if username or email already exists
            $existing = $db->query(
                "SELECT Username, Email FROM Users WHERE Username = ? OR Email = ?",
                [$username, $email]
            );

            if ($existing) {
                foreach ($existing as $user) {
                    if ($user['Username'] === $username) {
                        $errors[] = "Username already taken";
                    }
                    if ($user['Email'] === $email) {
                        $errors[] = "Email already registered";
                    }
                }
            }

            if (empty($errors)) {
                // Create user
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                $db->execute(
                    "INSERT INTO Users (Username, Email, Password, Role, AccountStatus)
                    VALUES (?, ?, ?, 'User', 'Active')",
                    [$username, $email, $hashedPassword]
                );

                setFlashMessage('success', 'Account created successfully! Please log in.');
                header('Location: /index.php');
                exit();
            }
        } catch (Exception $e) {
            $errors[] = "An error occurred. Please try again later.";
        }
    }

    if (!empty($errors)) {
        setFlashMessage('error', implode('<br>', $errors));
    }
}

$pageTitle = 'Sign Up';
require_once 'includes/header.php';
?>

<div class="flex items-center justify-center min-h-[calc(100vh-200px)]">
    <div class="bg-gradient-to-br from-blue-900 to-[#070c1b] border border-blue-500/20 rounded-xl p-8 shadow-lg shadow-blue-900/20 hover:shadow-blue-400/30 hover:border-blue-400/30 transition-all duration-300 w-full max-w-md">
        <h1 class="text-3xl font-bold text-center mb-8 text-fuchsia-100">
            <i class="fas fa-user-plus"></i> Sign Up
        </h1>

        <form method="POST" action="" class="space-y-6">
            <div>
                <label for="username" class="block text-sm font-medium text-[var(--text-secondary)]">Username</label>
                <input type="text" id="username" name="username" required
                    class="form-input mt-1 block w-full rounded-md px-4 py-2"
                    value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>">
            </div>

            <div>
                <label for="email" class="block text-sm font-medium text-[var(--text-secondary)]">Email</label>
                <input type="email" id="email" name="email" required
                    class="form-input mt-1 block w-full rounded-md px-4 py-2"
                    value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
            </div>

            <div>
                <label for="password" class="block text-sm font-medium text-[var(--text-secondary)]">Password</label>
                <input type="password" id="password" name="password" required
                    class="form-input mt-1 block w-full rounded-md px-4 py-2">
            </div>

            <div>
                <label for="confirm_password" class="block text-sm font-medium text-[var(--text-secondary)]">Confirm Password</label>
                <input type="password" id="confirm_password" name="confirm_password" required
                    class="form-input mt-1 block w-full rounded-md px-4 py-2">
            </div>

            <button type="submit"
                class="btn-primary w-full rounded-md px-4 py-2 text-center font-medium">
                Create Account
            </button>
        </form>

        <p class="mt-4 text-center text-sm text-[var(--text-secondary)]">
            Already have an account?
            <a href="/index.php" class="text-[var(--accent)] hover:underline">Sign in</a>
        </p>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
