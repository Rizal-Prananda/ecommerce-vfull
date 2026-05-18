<?php
// Quick script to add status column if it doesn't exist
$db = new PDO('sqlite:database/database.sqlite');

// Check if column exists
$result = $db->query("PRAGMA table_info(users)");
$columns = [];
while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
    $columns[] = $row['name'];
}

if (!in_array('status', $columns)) {
    echo "Adding 'status' column...\n";
    $db->exec("ALTER TABLE users ADD COLUMN status TEXT DEFAULT 'ACTIVE'");
    echo "✓ Status column added successfully!\n";
} else {
    echo "✓ Status column already exists\n";
}

if (!in_array('avatar_path', $columns)) {
    echo "Adding 'avatar_path' column...\n";
    $db->exec("ALTER TABLE users ADD COLUMN avatar_path TEXT");
    echo "✓ Avatar_path column added successfully!\n";
} else {
    echo "✓ Avatar_path column already exists\n";
}

if (!in_array('last_login_at', $columns)) {
    echo "Adding 'last_login_at' column...\n";
    $db->exec("ALTER TABLE users ADD COLUMN last_login_at DATETIME");
    echo "✓ Last_login_at column added successfully!\n";
} else {
    echo "✓ Last_login_at column already exists\n";
}

echo "\nDatabase schema updated successfully!\n";
?>
