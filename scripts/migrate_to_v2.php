<?php
/**
 * SAFE Migration Script: MySQL to PostgreSQL v2 with Automatic Rollback
 *
 * This script migrates a single tenant's data from a legacy MySQL database to the new
 * PostgreSQL structure. It ensures data integrity through a specific migration order and
 * uses a full transaction to allow for automatic rollback on any failure.
 *
 * Migration Order:
 * 1. Tenant -> Farm
 * 2. Users -> Users
 * 3. Goat Types -> Breeds (linked to 'CAPRA' species)
 * 4. Cages -> Herds
 * 5. Goats -> Livestocks
 */

// Bootstrap Laravel application
require_once __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

// --- Configuration ---
// Get the tenant database name from the command-line argument, defaulting to 'mulia_farm'
$tenantDatabaseName = $argv[1] ?? 'mulia_farm';

$mysqlConfig = [
    'host' => '54.251.33.126',
    'username' => 'aifarm',
    'password' => 'Bismillah!23',
    'charset' => 'utf8mb4'
];

// Source databases
$databases = [
    'client' => $tenantDatabaseName, // The specific tenant database to migrate
    'main' => 'aifarm'             // The central database with user and tenant metadata
];

// --- Main Execution ---
echo "🚀 Starting SAFE Migration for tenant '{$tenantDatabaseName}'\n";
if (!isset($argv[1])) {
    echo "ℹ️  No tenant database name provided. Using default: 'mulia_farm'.\n";
}
echo "🔒 All changes will be rolled back automatically if ANY error occurs\n";
echo str_repeat("=", 70) . "\n";


$stats = [
    'farms' => 0, 'users' => 0, 'breeds' => 0, 'herds' => 0, 'livestocks' => 0,
];

try {
    // Start the master transaction
    DB::beginTransaction();
    echo "🔒 Database transaction started.\n";

    // Establish connections to source MySQL databases
    $connections = connectToMySQL($mysqlConfig, $databases);

    // Ensure target PostgreSQL connection is alive
    DB::connection()->getPdo();
    echo "✅ Connected to PostgreSQL target database.\n\n";

    // --- Step 1: Identify Tenant ---
    list($tenantId, $tenantInfo) = identifyTenant($connections['main'], $databases['client']);

    // --- Step 2: Migrate Users ---
    list($userMapping, $ownerId, $allUsers) = migrateUsers($connections['main'], $tenantId, $stats);

    // --- Step 3: Migrate Farm ---
    $farmUuid = migrateFarm($tenantInfo, $ownerId, $stats);

    // --- Step 4: Associate Users with Farm ---
    associateUsersToFarm($farmUuid, $userMapping, $allUsers, $stats);

    // --- Step 5: Migrate Breeds ---
    $breedMapping = migrateBreeds($connections['client'], $stats);

    // --- Step 6: Migrate Herds ---
    $herdMapping = migrateHerds($connections['client'], $farmUuid, $stats);

    // --- Step 7: Migrate Livestocks ---
    migrateLivestocks($connections['client'], $farmUuid, $breedMapping, $herdMapping, $stats);

    // --- Step 8: Update User Passwords ---
    updateUserPasswords($stats);

    // --- Finalize ---
    DB::commit();
    echo "\n🔓 Transaction committed successfully!\n";
    printSuccessSummary($stats);

} catch (Exception $e) {
    DB::rollBack();
    printFailureSummary($e, $stats);
    exit(1);
}

echo "🎉 Migration script finished.\n";


// --- Helper Functions ---

/**
 * Establishes and returns PDO connections to the source MySQL databases.
 */
function connectToMySQL(array $config, array $databases): array
{
    $connections = [];
    foreach ($databases as $key => $dbName) {
        $dsn = "mysql:host={$config['host']};dbname={$dbName};charset={$config['charset']}";
        $connections[$key] = new PDO($dsn, $config['username'], $config['password'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
        echo "✅ Connected to MySQL '{$dbName}' database ('{$key}').\n";
    }
    return $connections;
}

/**
 * Identifies and returns the tenant to be migrated.
 */
function identifyTenant(PDO $connection, string $tenantDbName): array
{
    echo "\n---\n🔍 Step 1: Identifying Tenant...\n";
    $stmt = $connection->prepare("SELECT * FROM tenant WHERE `database` = ? AND active = 1");
    $stmt->execute([$tenantDbName]);
    $tenant = $stmt->fetch();

    if (!$tenant) {
        throw new Exception("Active tenant with database '{$tenantDbName}' not found.");
    }
    echo "✅ Found tenant '{$tenant['name']}' (ID: {$tenant['id']}).\n";
    return [
        $tenant['id'],
        $tenant
    ];
}

/**
 * Migrates users for a given tenant and returns the user mapping, owner's UUID, and original user data.
 */
function migrateUsers(PDO $connection, int $tenantId, array &$stats): array
{
    echo "\n---\n👤 Step 2: Migrating Users...\n";
    $stmt = $connection->prepare("SELECT * FROM user WHERE tenant_id = ? AND status = 'verified' ORDER BY id");
    $stmt->execute([$tenantId]);
    $users = $stmt->fetchAll();

    if (empty($users)) {
        echo "⚠️ No verified users found for this tenant.\n";
        return [[], null, []];
    }

    $userMapping = [];
    $usersToInsert = [];
    $firstUserUuid = null;

    foreach ($users as $user) {
        $uuid = generateUuid();
        $userMapping[$user['id']] = $uuid;
        if (is_null($firstUserUuid)) {
            $firstUserUuid = $uuid;
        }

        $usersToInsert[] = [
            'id' => $uuid,
            'name' => getValue($user, 'name', 'Unknown User'),
            'email' => getValue($user, 'email') ?: 'user' . $user['id'] . '@aifarm.local',
            'phone' => getValue($user, 'phone'),
            'password' => getValue($user, 'password'), // Keep original password to check format later
            'is_super_user' => getValue($user, 'role') === 'superadmin',
            'profile_photo_path' => getValue($user, 'photo'),
            'created_at' => getValue($user, 'created_at', now()),
            'updated_at' => now(),
        ];
    }

    DB::table('users')->insert($usersToInsert);
    $stats['users'] = count($usersToInsert);

    echo "✅ Migrated {$stats['users']} users.\n";
    return [$userMapping, $firstUserUuid, $users];
}

/**
 * Migrates the specified tenant to a farm.
 */
function migrateFarm(array $tenant, ?string $ownerId, array &$stats): string
{
    echo "\n---\n🏢 Step 3: Migrating Tenant to Farm...\n";
    
    $farmUuid = generateUuid();
    $cityId = getValue($tenant, 'city_id', 1);
    if ($cityId && !DB::table('cities')->where('id', $cityId)->exists()) {
        echo "⚠️ Warning: City ID '{$cityId}' not found. Defaulting to 1.\n";
        $cityId = 1;
    }

    DB::table('farms')->insert([
        'id' => $farmUuid,
        'name' => getValue($tenant, 'name', 'Unknown Farm'),
        'address' => getValue($tenant, 'address'),
        'picture' => getValue($tenant, 'photo'),
        'owner' => getValue($tenant, 'name'),
        'city_id' => $cityId,
        'user_id' => $ownerId,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $stats['farms']++;
    echo "✅ Farm '{$tenant['name']}' created with UUID: {$farmUuid}.\n";
    if ($ownerId) {
        echo "✅ Farm owner assigned with user UUID: {$ownerId}.\n";
    }
    return $farmUuid;
}

/**
 * Associates the migrated users with the new farm.
 */
function associateUsersToFarm(string $farmUuid, array $userMapping, array $originalUsers, array &$stats): void
{
    echo "\n---\n🔗 Step 4: Associating Users with Farm...\n";
    if (empty($userMapping)) {
        echo "⚠️ No users to associate with the farm.\n";
        return;
    }

    $farmUserInserts = [];
    foreach ($originalUsers as $user) {
        if (isset($userMapping[$user['id']])) {
            $farmUserInserts[] = [
                'farm_id' => $farmUuid,
                'user_id' => $userMapping[$user['id']],
                'role' => getValue($user, 'role'),
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
    }

    if (!empty($farmUserInserts)) {
        DB::table('farm_user')->insert($farmUserInserts);
        $stats['farm_users'] = count($farmUserInserts);
        echo "✅ Associated {" . $stats['farm_users'] . "} users with the farm.\n";
    }
}

/**
 * Migrates goat types as breeds under the 'CAPRA' species.
 */
function migrateBreeds(PDO $connection, array &$stats): array
{
    echo "\n---\n";
    echo "🐐 Step 5: Migrating Goat Types to Breeds...\n";
    $capraSpeciesId = DB::table('species')->where('code', 'CAPRA')->value('id');
    if (!$capraSpeciesId) {
        throw new Exception("Species with code 'CAPRA' not found. Please seed the species table first.");
    }
    echo "✅ Found 'CAPRA' species with ID: {$capraSpeciesId}.\n";

    $stmt = $connection->query("SELECT DISTINCT type FROM goat WHERE type IS NOT NULL AND type != '' ORDER BY type");
    $types = $stmt->fetchAll(PDO::FETCH_COLUMN);

    $breedMapping = [];
    $breedsToInsert = [];

    foreach ($types as $breedName) {
        $breedName = trim($breedName);
        if (empty($breedName)) continue;

        // Check if breed already exists to prevent duplicates
        $existingBreed = DB::table('breeds')->where('name', $breedName)->where('species_id', $capraSpeciesId)->first();
        if ($existingBreed) {
            $breedMapping[$breedName] = $existingBreed->id;
            continue;
        }

        $uuid = generateUuid();
        $breedMapping[$breedName] = $uuid;
        $breedsToInsert[] = [
            'id' => $uuid,
            'species_id' => $capraSpeciesId,
            'name' => $breedName,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    if (!empty($breedsToInsert)) {
        DB::table('breeds')->insert($breedsToInsert);
        $stats['breeds'] = count($breedsToInsert);
    }

    echo "✅ Migrated {$stats['breeds']} new breeds.\n";
    return $breedMapping;
}

/**
 * Migrates cages to herds for the given farm.
 */
function migrateHerds(PDO $connection, string $farmUuid, array &$stats): array
{
    echo "\n---\n";
    echo "🏠 Step 6: Migrating Cages to Herds...\n";
    $cages = $connection->query("SELECT * FROM cage ORDER BY id")->fetchAll();

    if (empty($cages)) {
        echo "⚠️ No cages found to migrate.\n";
        return [];
    }

    $herdMapping = [];
    $herdsToInsert = [];

    foreach ($cages as $cage) {
        $uuid = generateUuid();
        $herdMapping[$cage['id']] = $uuid;
        $herdsToInsert[] = [
            'id' => $uuid,
            'farm_id' => $farmUuid,
            'name' => getValue($cage, 'name', 'Unnamed Herd'),
            'description' => getValue($cage, 'status') . ' - ' . getValue($cage, 'category'),
            'capacity' => getValue($cage, 'capacity', 0),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    DB::table('herds')->insert($herdsToInsert);
    $stats['herds'] = count($herdsToInsert);
    echo "✅ Migrated {$stats['herds']} herds.\n";
    return $herdMapping;
}

/**
 * Migrates goats to livestocks.
 */
function migrateLivestocks(PDO $connection, string $farmUuid, array $breedMapping, array $herdMapping, array &$stats): void
{
    echo "\n---\n";
    echo "🐑 Step 7: Migrating Goats to Livestocks...\n";
    $goats = $connection->query("SELECT * FROM goat WHERE is_active = 1 ORDER BY id")->fetchAll();

    if (empty($goats)) {
        echo "⚠️ No active goats found to migrate.\n";
        return;
    }

    $livestocksToInsert = [];
    foreach ($goats as $goat) {
        $photos = [];
        if (!empty($goat['image1_url'])) $photos[] = $goat['image1_url'];
        if (!empty($goat['image2_url'])) $photos[] = $goat['image2_url'];
        if (!empty($goat['image3_url'])) $photos[] = $goat['image3_url'];

        $livestocksToInsert[] = [
            'id' => generateUuid(),
            'farm_id' => $farmUuid,
            'breed_id' => getValue($breedMapping, $goat['type']),
            'herd_id' => getValue($herdMapping, $goat['cage_id']),
            'aifarm_id' => getValue($goat, 'ear_tag'),
            'name' => getValue($goat, 'name'),
            'birthdate' => getValue($goat, 'birth_date'),
            'sex' => getValue($goat, 'gender') === 'm' ? 'M' : 'F',
            'origin' => getValue($goat, 'origin', '1'),
            'status' => getValue($goat, 'status', '1'),
            'tag_id' => getValue($goat, 'ear_tag', ''),
            'birth_weight' => getValue($goat, 'birth_weight'),
            'weight' => getValue($goat, 'weight'),
            'photo' => !empty($photos) ? json_encode($photos) : null,
            'purchase_date' => getValue($goat, 'entry_date') ? date('Y-m-d H:i:s', strtotime($goat['entry_date'])) : null,
            'purchase_price' => getValue($goat, 'purchase_price'),
            'entry_date' => getValue($goat, 'entry_date') ? date('Y-m-d', strtotime($goat['entry_date'])) : null,
            'created_at' => getValue($goat, 'created_at', now()),
            'updated_at' => getValue($goat, 'updated_at', now()),
        ];
    }

    DB::table('livestocks')->insert($livestocksToInsert);
    $stats['livestocks'] = count($livestocksToInsert);
    echo "✅ Migrated {$stats['livestocks']} livestocks.\n";
}

/**
 * Updates passwords for users with non-bcrypt hashes.
 */
function updateUserPasswords(array &$stats): void
{
    echo "\n---\n🔑 Step 8: Securing User Passwords...\n";
    
    // Default password hash for '12345678'
    $newPasswordHash = '$2y$10$xIIrWcC5pbXHvQ2A6cWiX.gZc0z2eGXXWK/O4Stc/zDJrBkhs1HqO';

    $updatedCount = DB::table('users')
        ->where('password', 'not like', '$2y$%')
        ->update(['password' => $newPasswordHash]);

    if ($updatedCount > 0) {
        $stats['passwords_updated'] = $updatedCount;
        echo "✅ Secured passwords for {$updatedCount} users.\n";
    } else {
        echo "✅ All user passwords are secure. No updates needed.\n";
    }
}

/**
 * Generates a UUID v4.
 */
function generateUuid(): string
{
    return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
        mt_rand(0, 0xffff), mt_rand(0, 0xffff),
        mt_rand(0, 0xffff),
        mt_rand(0, 0x0fff) | 0x4000,
        mt_rand(0, 0x3fff) | 0x8000,
        mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
    );
}

/**
 * Safely gets a value from an array.
 */
function getValue(array $array, string $key, $default = null)
{
    return $array[$key] ?? $default;
}

/**
 * Prints a summary of the successful migration.
 */
function printSuccessSummary(array $stats): void
{
    echo "\n" . str_repeat("=", 70) . "\n";
    echo "🎉 MIGRATION COMPLETED SUCCESSFULLY!\n";
    echo str_repeat("-", 40) . "\n";
    echo "✅ Farms migrated:     {$stats['farms']}\n";
    echo "✅ Users migrated:     {$stats['users']}\n";
    if (isset($stats['farm_users']) && $stats['farm_users'] > 0) {
        echo "✅ Farm Users created: {$stats['farm_users']}\n";
    }
    echo "✅ Breeds migrated:    {$stats['breeds']}\n";
    echo "✅ Herds migrated:     {$stats['herds']}\n";
    echo "✅ Livestocks migrated:{$stats['livestocks']}\n";
    if (isset($stats['passwords_updated']) && $stats['passwords_updated'] > 0) {
        echo "✅ Passwords updated:  {$stats['passwords_updated']}\n";
    }
    echo str_repeat("=", 70) . "\n";
}

/**
 * Prints a summary of the failed migration.
 */
function printFailureSummary(Exception $e, array $stats): void
{
    echo "\n" . str_repeat("!", 70) . "\n";
    echo "💥 MIGRATION FAILED - ROLLING BACK ALL CHANGES!\n";
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "📍 File: " . $e->getFile() . " (Line: " . $e->getLine() . ")\n";
    echo "✅ All database changes have been rolled back.\n";
    echo str_repeat("!", 70) . "\n";
}

