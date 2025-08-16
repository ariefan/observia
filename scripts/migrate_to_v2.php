<?php
/**
 * SAFE Migration Script: MySQL to PostgreSQL v2 with Automatic Rollback + Image Processing
 */

require_once __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use Intervention\Image\ImageManager;
use Illuminate\Support\Facades\Storage;

// --- Configuration ---
$tenantDatabaseName = $argv[1] ?? 'mulia_farm';

$mysqlConfig = [
    'host' => '54.251.33.126',
    'username' => 'aifarm',
    'password' => 'Bismillah!23',
    'charset' => 'utf8mb4'
];

$databases = [
    'client' => $tenantDatabaseName,
    'main' => 'aifarm'
];

echo "🚀 Starting SAFE Migration for tenant '{$tenantDatabaseName}'\n";
echo "🔒 All changes will be rolled back automatically if ANY error occurs\n";
echo str_repeat("=", 70) . "\n";

$stats = [
    'farms' => 0, 'users' => 0, 'breeds' => 0, 'herds' => 0, 'livestocks' => 0,
];

try {
    DB::beginTransaction();
    echo "🔒 Database transaction started.\n";

    $connections = connectToMySQL($mysqlConfig, $databases);
    DB::connection()->getPdo();
    echo "✅ Connected to PostgreSQL target database.\n\n";

    list($tenantId, $tenantInfo) = identifyTenant($connections['main'], $databases['client']);
    list($userMapping, $ownerId, $allUsers) = migrateUsers($connections['main'], $tenantId, $stats);
    $farmUuid = migrateFarm($tenantInfo, $ownerId, $stats);
    associateUsersToFarm($farmUuid, $userMapping, $allUsers, $stats);
    $breedMapping = migrateBreeds($connections['client'], $stats);
    $herdMapping = migrateHerds($connections['client'], $farmUuid, $stats);
    migrateLivestocks($connections['client'], $farmUuid, $breedMapping, $herdMapping, $stats);
    updateUserPasswords($stats);

    DB::commit();
    echo "\n🔓 Transaction committed successfully!\n";
    printSuccessSummary($stats);

} catch (Exception $e) {
    DB::rollBack();
    printFailureSummary($e, $stats);
    exit(1);
}

echo "🎉 Migration script finished.\n";

// ------------------
// Helper Functions
// ------------------

function connectToMySQL(array $config, array $databases): array {
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

function identifyTenant(PDO $connection, string $tenantDbName): array {
    echo "\n---\n🔍 Step 1: Identifying Tenant...\n";
    $stmt = $connection->prepare("SELECT * FROM tenant WHERE `database` = ? AND active = 1");
    $stmt->execute([$tenantDbName]);
    $tenant = $stmt->fetch();
    if (!$tenant) throw new Exception("Active tenant with database '{$tenantDbName}' not found.");
    echo "✅ Found tenant '{$tenant['name']}' (ID: {$tenant['id']}).\n";
    return [$tenant['id'], $tenant];
}

function migrateUsers(PDO $connection, int $tenantId, array &$stats): array {
    echo "\n---\n👤 Step 2: Migrating Users...\n";
    $stmt = $connection->prepare("SELECT * FROM user WHERE tenant_id = ? AND status = 'verified' ORDER BY id");
    $stmt->execute([$tenantId]);
    $users = $stmt->fetchAll();
    if (empty($users)) return [[], null, []];

    $userMapping = [];
    $usersToInsert = [];
    $firstUserUuid = null;
    foreach ($users as $user) {
        $uuid = generateUuid();
        $userMapping[$user['id']] = $uuid;
        if (is_null($firstUserUuid)) $firstUserUuid = $uuid;

        $usersToInsert[] = [
            'id' => $uuid,
            'name' => $user['name'] ?? 'Unknown User',
            'email' => $user['email'] ?? 'user'.$user['id'].'@aifarm.local',
            'phone' => $user['phone'] ?? null,
            'password' => $user['password'] ?? null,
            'is_super_user' => ($user['role'] ?? '') === 'superadmin',
            'profile_photo_path' => $user['photo'] ?? null,
            'created_at' => $user['created_at'] ?? now(),
            'updated_at' => now(),
        ];
    }

    DB::table('users')->insert($usersToInsert);
    $stats['users'] = count($usersToInsert);
    echo "✅ Migrated {$stats['users']} users.\n";
    return [$userMapping, $firstUserUuid, $users];
}

function migrateFarm(array $tenant, ?string $ownerId, array &$stats): string {
    echo "\n---\n🏢 Step 3: Migrating Tenant to Farm...\n";
    $farmUuid = generateUuid();
    $cityId = $tenant['city_id'] ?? 1;
    if ($cityId && !DB::table('cities')->where('id', $cityId)->exists()) $cityId = 1;

    DB::table('farms')->insert([
        'id' => $farmUuid,
        'name' => $tenant['name'] ?? 'Unknown Farm',
        'address' => $tenant['address'] ?? null,
        'picture' => $tenant['photo'] ?? null,
        'owner' => $tenant['name'] ?? null,
        'city_id' => $cityId,
        'user_id' => $ownerId,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $stats['farms']++;
    echo "✅ Farm '{$tenant['name']}' created with UUID: {$farmUuid}.\n";
    if ($ownerId) echo "✅ Farm owner assigned with user UUID: {$ownerId}.\n";
    return $farmUuid;
}

function associateUsersToFarm(string $farmUuid, array $userMapping, array $originalUsers, array &$stats): void {
    echo "\n---\n🔗 Step 4: Associating Users with Farm...\n";
    if (empty($userMapping)) return;

    $farmUserInserts = [];
    foreach ($originalUsers as $user) {
        if (isset($userMapping[$user['id']])) {
            $farmUserInserts[] = [
                'farm_id' => $farmUuid,
                'user_id' => $userMapping[$user['id']],
                'role' => $user['role'] ?? null,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
    }

    if (!empty($farmUserInserts)) {
        DB::table('farm_user')->insert($farmUserInserts);
        $stats['farm_users'] = count($farmUserInserts);
        echo "✅ Associated {$stats['farm_users']} users with the farm.\n";
    }
}

function migrateBreeds(PDO $connection, array &$stats): array {
    echo "\n---\n🐐 Step 5: Migrating Goat Types to Breeds...\n";
    $capraSpeciesId = DB::table('species')->where('code', 'CAPRA')->value('id');
    if (!$capraSpeciesId) throw new Exception("Species 'CAPRA' not found.");

    $stmt = $connection->query("SELECT DISTINCT type FROM goat WHERE type IS NOT NULL AND type != '' ORDER BY type");
    $types = $stmt->fetchAll(PDO::FETCH_COLUMN);

    $breedMapping = [];
    $breedsToInsert = [];

    foreach ($types as $breedName) {
        $breedName = trim($breedName);
        if (empty($breedName)) continue;

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

function migrateHerds(PDO $connection, string $farmUuid, array &$stats): array {
    echo "\n---\n🏠 Step 6: Migrating Cages to Herds...\n";
    $cages = $connection->query("SELECT * FROM cage ORDER BY id")->fetchAll();
    if (empty($cages)) return [];

    $herdMapping = [];
    $herdsToInsert = [];
    foreach ($cages as $cage) {
        $uuid = generateUuid();
        $herdMapping[$cage['id']] = $uuid;
        $herdsToInsert[] = [
            'id' => $uuid,
            'farm_id' => $farmUuid,
            'name' => $cage['name'] ?? 'Unnamed Herd',
            'description' => ($cage['status'] ?? '') . ' - ' . ($cage['category'] ?? ''),
            'capacity' => $cage['capacity'] ?? 0,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    DB::table('herds')->insert($herdsToInsert);
    $stats['herds'] = count($herdsToInsert);
    echo "✅ Migrated {$stats['herds']} herds.\n";
    return $herdMapping;
}

function migrateLivestocks(PDO $connection, string $farmUuid, array $breedMapping, array $herdMapping, array &$stats): void {
    echo "\n---\n🐑 Step 7: Migrating Goats to Livestocks...\n";
    $goats = $connection->query("SELECT * FROM goat WHERE is_active = 1 ORDER BY id")->fetchAll();
    if (empty($goats)) return;

    $manager = new ImageManager(new \Intervention\Image\Drivers\Gd\Driver());
    $livestocksToInsert = [];

    foreach ($goats as $goat) {
        $photos = [];
        for ($i = 1; $i <= 3; $i++) {
            $key = "image{$i}_url";
            if (!empty($goat[$key])) {
                $processed = processLivestockImage($goat[$key], $manager);
                if ($processed) $photos[] = $processed;
            }
        }

        // Store the filenames in JSON array format
        $photoJson = !empty($photos) ? json_encode('livestocks/'.$photos) : null;

        $originMap = ['Beli'=>2,'Lahir di kandang'=>1];
        $origin = $originMap[$goat['origin'] ?? ''] ?? 4;

        $statusMap = [9=>2, 8=>3];
        $status = $statusMap[$goat['status'] ?? 0] ?? 1;

        $livestocksToInsert[] = [
            'id' => generateUuid(),
            'farm_id' => $farmUuid,
            'breed_id' => $breedMapping[$goat['type']] ?? null,
            'herd_id' => $herdMapping[$goat['cage_id']] ?? null,
            'aifarm_id' => $goat['ear_tag'] ?? null,
            'name' => $goat['name'] ?? null,
            'birthdate' => $goat['birth_date'] ?? null,
            'sex' => ($goat['gender'] ?? 'f') === 'm' ? 'M' : 'F',
            'origin' => $origin,
            'status' => $status,
            'tag_id' => $goat['ear_tag'] ?? '',
            'birth_weight' => $goat['birth_weight'] ?? null,
            'weight' => $goat['weight'] ?? null,
            'photo' => $photoJson,
            'purchase_date' => !empty($goat['entry_date']) ? date('Y-m-d H:i:s', strtotime($goat['entry_date'])) : null,
            'purchase_price' => $goat['purchase_price'] ?? null,
            'entry_date' => !empty($goat['entry_date']) ? date('Y-m-d', strtotime($goat['entry_date'])) : null,
            'created_at' => $goat['created_at'] ?? now(),
            'updated_at' => $goat['updated_at'] ?? now(),
        ];
    }

    DB::table('livestocks')->insert($livestocksToInsert);
    $stats['livestocks'] = count($livestocksToInsert);
    echo "✅ Migrated {$stats['livestocks']} livestocks with photos stored as JSON.\n";
}

function processLivestockImage(string $url, ImageManager $manager): ?string {
    try {
        echo "⬇️ Downloading image from '{$url}'...\n";

        $contents = file_get_contents($url);
        if (!$contents) {
            echo "⚠️ Failed to download image.\n";
            return null;
        }

        $image = $manager->read($contents);
        $width = $image->width();
        $height = $image->height();

        echo "ℹ️ Original image size: {$width}x{$height}\n";

        if ($width >= $height) {
            $image->resize(1024, null, fn($c) => $c->aspectRatio());
            echo "🔧 Resized landscape image to width 1024px.\n";
        } else {
            $cropHeight = $height * 0.8;
            $image->crop($width, $cropHeight, 0, $height * 0.1);
            $image->resize(null, 768, fn($c) => $c->aspectRatio());
            echo "🔧 Cropped and resized portrait image to height 768px.\n";
        }

        $filename = generateUuid() . '.jpg';
        $publicPath = __DIR__ . '/../public/storage/livestocks/';
        if (!file_exists($publicPath)) mkdir($publicPath, 0777, true);

        $fullPath = $publicPath . $filename;
        $image->toJpg(85)->save($fullPath);

        echo "💾 Image saved to '{$fullPath}' successfully.\n";

        // Return **filename only**, will be stored as JSON in photo column
        return $filename;

    } catch (\Exception $e) {
        echo "⚠️ Failed to process image '{$url}': {$e->getMessage()}\n";
        return null;
    }
}

function updateUserPasswords(array &$stats): void {
    echo "\n---\n🔑 Step 8: Securing User Passwords...\n";
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

function generateUuid(): string {
    return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
        mt_rand(0, 0xffff), mt_rand(0, 0xffff),
        mt_rand(0, 0xffff),
        mt_rand(0, 0x0fff) | 0x4000,
        mt_rand(0, 0x3fff) | 0x8000,
        mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
    );
}

function now(): string { return date('Y-m-d H:i:s'); }

function printSuccessSummary(array $stats): void {
    echo "\n" . str_repeat("=", 70) . "\n";
    echo "🎉 MIGRATION COMPLETED SUCCESSFULLY!\n";
    echo str_repeat("-", 40) . "\n";
    echo "✅ Farms migrated:     {$stats['farms']}\n";
    echo "✅ Users migrated:     {$stats['users']}\n";
    if (isset($stats['farm_users'])) echo "✅ Farm Users created: {$stats['farm_users']}\n";
    echo "✅ Breeds migrated:    {$stats['breeds']}\n";
    echo "✅ Herds migrated:     {$stats['herds']}\n";
    echo "✅ Livestocks migrated:{$stats['livestocks']}\n";
    if (isset($stats['passwords_updated'])) echo "✅ Passwords updated:  {$stats['passwords_updated']}\n";
    echo str_repeat("=", 70) . "\n";
}

function printFailureSummary(Exception $e, array $stats): void {
    echo "\n" . str_repeat("!", 70) . "\n";
    echo "💥 MIGRATION FAILED - ROLLING BACK ALL CHANGES!\n";
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "📍 File: " . $e->getFile() . " (Line: " . $e->getLine() . ")\n";
    echo "✅ All database changes have been rolled back.\n";
    echo str_repeat("!", 70) . "\n";
}
