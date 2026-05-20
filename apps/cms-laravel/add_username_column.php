<?php
$db = new PDO('sqlite:database/database.sqlite');

$result = $db->query("PRAGMA table_info(users)");
$columns = [];
while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
    $columns[] = $row['name'];
}

if (!in_array('username', $columns)) {
    echo "Adding 'username' column...\n";
    $db->exec("ALTER TABLE users ADD COLUMN username TEXT UNIQUE");
    echo "✓ Username column added successfully!\n";
} else {
    echo "✓ Username column already exists\n";
}

echo "Database schema updated successfully!\n";
?>
