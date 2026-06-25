<?php
require_once __DIR__ . '/config/database.php';

$db = new Database();
$his_conn = $db->getHisConnection();

if (!$his_conn) {
    die("Cannot connect to HIS DB\n");
}

$tables_to_check = ['hos.insclasses', 'hos.itemlist'];
$schema_output = "";

foreach ($tables_to_check as $table) {
    $schema_output .= "=== Structure of table '$table' ===\n";
    list($db_name, $tbl_name) = explode('.', $table);
    try {
        $stmt = $his_conn->query("DESCRIBE `$db_name`.`$tbl_name`");
        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($columns as $col) {
            $schema_output .= str_pad($col['Field'], 20) . " | " . 
                              str_pad($col['Type'], 15) . " | " . 
                              str_pad($col['Null'], 4) . " | " . 
                              str_pad($col['Key'], 4) . " | " . 
                              str_pad($col['Default'] ?? 'NULL', 10) . " | " . 
                              $col['Extra'] . "\n";
        }
    } catch (PDOException $e) {
        // อาจจะเป็น database name แทนที่จะเป็น table name
        $schema_output .= "Error getting table '$table' in database 'hos': " . $e->getMessage() . "\n";
        
        // ลองเช็คว่ามันเป็น Database หรือเปล่า
        try {
            $stmt2 = $his_conn->query("SHOW TABLES FROM `$table`");
            $tables = $stmt2->fetchAll(PDO::FETCH_COLUMN);
            $schema_output .= "Wait, '$table' is a Database! It contains " . count($tables) . " tables.\n";
            if (count($tables) > 0) {
                $schema_output .= "First 10 tables: " . implode(", ", array_slice($tables, 0, 10)) . "\n";
            }
        } catch (PDOException $e2) {
            $schema_output .= "Also not a database.\n";
        }
    }
    $schema_output .= "\n";
}

file_put_contents(__DIR__ . '/schema_dump.txt', $schema_output);
echo "Schema dumped to schema_dump.txt\n";
?>
