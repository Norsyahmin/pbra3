<?php
require_once __DIR__ . '/vendor/autoload.php'; // Ensure this path is correct

// Correct Dotenv instantiation
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

$servername = $_ENV['DB_SERVER'];
$username = $_ENV['DB_USERNAME'];
$password = $_ENV['DB_PASSWORD'];
$database = $_ENV['DB_DATABASE'];

// Optional port (defaults to 3306)
$port = isset($_ENV['DB_PORT']) && is_numeric($_ENV['DB_PORT']) ? (int) $_ENV['DB_PORT'] : 3306;

// Make mysqli throw exceptions so we can catch and retry when the DB container is still starting
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$maxAttempts = 8; // number of retries (total wait up to ~8 seconds)
$conn = null;
for ($attempt = 1; $attempt <= $maxAttempts; $attempt++) {
	try {
		$conn = new mysqli($servername, $username, $password, $database, $port);
		// connected
		break;
	} catch (mysqli_sql_exception $e) {
		// If last attempt, rethrow / show helpful message
		if ($attempt === $maxAttempts) {
			// Mask password when printing
			$maskedPass = $password ? str_repeat('*', min(8, strlen($password))) : '(empty)';
			$msg = sprintf(
				"Database connection failed after %d attempts. host=%s port=%d user=%s db=%s. Error: %s",
				$maxAttempts,
				$servername,
				$port,
				$username,
				$database,
				$e->getMessage()
			);
			error_log($msg);
			// Friendly output for browser (avoid leaking sensitive info)
			die("Database connection failed. Please check database container/service is running and environment variables. (See server logs for details)");
		}
		// wait a bit and retry (use sleep in seconds)
		sleep(1);
	}
}

// Final sanity checks: ensure $conn is a valid mysqli instance and connected.
if (!isset($conn) || !is_object($conn) || !($conn instanceof mysqli)) {
	error_log('Database connection failed: $conn is not a mysqli instance (type=' . gettype($conn) . ')');
	// Friendly message for browser; avoid leaking sensitive info
	die('Database connection not available. Please check server configuration and logs.');
}

if ($conn->connect_error) {
	error_log('Database connection error: ' . $conn->connect_error);
	die('Database connection failed. Please check server logs for details.');
}

// Ensure a sensible default character set and fail gracefully if cannot be set
try {
	if (!@$conn->set_charset('utf8mb4')) {
		// set_charset returns true on success, false on failure
		error_log('Warning: Failed to set DB charset to utf8mb4: ' . ($conn->error ?? 'unknown'));
	}
} catch (Throwable $e) {
	error_log('Exception while setting DB charset: ' . $e->getMessage());
}
