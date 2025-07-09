<?php

use App\Models\User;
use App\Models\Farm;
use App\Models\Breed;
use App\Models\Species;
use App\Models\Livestock;
use App\Models\City;
use App\Models\Province;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    // Create required dependencies
    $this->province = Province::factory()->create();
    $this->city = City::factory()->create(['province_id' => $this->province->id]);
    
    // Create test user
    $this->user = User::factory()->create();
    
    // Create test farm with explicit city_id
    $this->farm = Farm::factory()->create([
        'user_id' => $this->user->id,
        'city_id' => $this->city->id,
    ]);
    $this->user->update(['current_farm_id' => $this->farm->id]);
    
    // Create test species and breed
    $this->species = Species::factory()->create();
    $this->breed = Breed::factory()->create([
        'species_id' => $this->species->id,
    ]);
    
    // Fake storage for image uploads
    Storage::fake('public');
});

it('dapat menyimpan ternak baru tanpa gambar', function () {
    $livestockData = [
        'name' => 'Test Livestock',
        'breed_id' => $this->breed->id,
        'sex' => 'F',
        'origin' => 1,
        'status' => 1,
        'birthdate' => '2023-01-01',
        'birth_weight' => 2.5,
        'weight' => 25.0,
        'tag_id' => 'TEST001',
        'tag_type' => 'eartag',
        'entry_date' => '2023-01-01',
    ];

    $response = $this->actingAs($this->user)
        ->post(route('livestocks.store'), $livestockData);

    $response->assertRedirect();
    
    $this->assertDatabaseHas('livestocks', [
        'name' => 'Test Livestock',
        'breed_id' => $this->breed->id,
        'sex' => 'F',
        'origin' => 1,
        'status' => 1,
        'farm_id' => $this->farm->id,
        'tag_id' => 'TEST001',
        'entry_date' => '2023-01-01',
    ]);
    
    $livestock = Livestock::where('name', 'Test Livestock')->first();
    expect($livestock)->not->toBeNull();
    expect($livestock->aifarm_id)->not->toBeNull();
});

it('dapat menyimpan ternak baru dengan gambar', function () {
    $image1 = UploadedFile::fake()->image('livestock1.jpg', 800, 600);
    $image2 = UploadedFile::fake()->image('livestock2.png', 800, 600);

    $livestockData = [
        'name' => 'Test Livestock with Images',
        'breed_id' => $this->breed->id,
        'sex' => 'M',
        'origin' => 2,
        'status' => 1,
        'birthdate' => '2023-02-01',
        'purchase_date' => '2023-02-15',
        'purchase_price' => 500000,
        'purchase_from' => 'Test Seller',
        'birth_weight' => 3.0,
        'weight' => 30.0,
        'tag_id' => 'TEST002',
        'entry_date' => '2023-02-15',
        'photo' => [$image1, $image2],
    ];

    $response = $this->actingAs($this->user)
        ->post(route('livestocks.store'), $livestockData);

    $response->assertRedirect();
    
    $livestock = Livestock::where('name', 'Test Livestock with Images')->first();
    expect($livestock)->not->toBeNull();
    expect($livestock->photo)->toBeArray();
    expect(count($livestock->photo))->toBe(2);
    
    // Check that images were stored
    foreach ($livestock->photo as $photoPath) {
        Storage::disk('public')->assertExists($photoPath);
    }
    
    $this->assertDatabaseHas('livestocks', [
        'name' => 'Test Livestock with Images',
        'purchase_from' => 'Test Seller',
        'purchase_price' => 500000,
        'entry_date' => '2023-02-15',
    ]);
});

it('dapat menyimpan ternak dengan hubungan induk', function () {
    // Create parent livestock
    $maleParent = Livestock::factory()->create([
        'farm_id' => $this->farm->id,
        'breed_id' => $this->breed->id,
        'sex' => 'M',
        'name' => 'Male Parent',
    ]);
    
    $femaleParent = Livestock::factory()->create([
        'farm_id' => $this->farm->id,
        'breed_id' => $this->breed->id,
        'sex' => 'F',
        'name' => 'Female Parent',
    ]);

    $livestockData = [
        'name' => 'Child Livestock',
        'breed_id' => $this->breed->id,
        'sex' => 'F',
        'origin' => 1,
        'status' => 1,
        'birthdate' => '2024-01-01',
        'male_parent_id' => $maleParent->id,
        'female_parent_id' => $femaleParent->id,
        'birth_weight' => 2.0,
        'weight' => 20.0,
        'tag_id' => 'CHILD001',
        'entry_date' => '2024-01-01',
    ];

    $response = $this->actingAs($this->user)
        ->post(route('livestocks.store'), $livestockData);

    $response->assertRedirect();
    
    $this->assertDatabaseHas('livestocks', [
        'name' => 'Child Livestock',
        'male_parent_id' => $maleParent->id,
        'female_parent_id' => $femaleParent->id,
    ]);
});

it('dapat menyimpan ternak dengan asal-usul berbeda dan bidang terkait', function () {
    // Test barter origin
    $barterData = [
        'name' => 'Barter Livestock',
        'breed_id' => $this->breed->id,
        'sex' => 'F',
        'origin' => 3,
        'status' => 1,
        'birthdate' => '2023-03-01',
        'barter_livestock_id' => 'BARTER001',
        'barter_from' => 'Barter Partner',
        'barter_date' => '2023-03-15',
        'entry_date' => '2023-03-15',
        'tag_id' => 'BARTER001',
    ];

    $response = $this->actingAs($this->user)
        ->post(route('livestocks.store'), $barterData);

    $response->assertRedirect();
    
    $this->assertDatabaseHas('livestocks', [
        'name' => 'Barter Livestock',
        'origin' => 3,
        'barter_livestock_id' => 'BARTER001',
        'barter_from' => 'Barter Partner',
        'barter_date' => '2023-03-15',
    ]);

    // Test grant origin
    $grantData = [
        'name' => 'Grant Livestock',
        'breed_id' => $this->breed->id,
        'sex' => 'M',
        'origin' => 4,
        'status' => 1,
        'birthdate' => '2023-04-01',
        'grant_from' => 'Grant Provider',
        'grant_date' => '2023-04-15',
        'entry_date' => '2023-04-15',
        'tag_id' => 'GRANT001',
    ];

    $response = $this->actingAs($this->user)
        ->post(route('livestocks.store'), $grantData);

    $response->assertRedirect();
    
    $this->assertDatabaseHas('livestocks', [
        'name' => 'Grant Livestock',
        'origin' => 4,
        'grant_from' => 'Grant Provider',
        'grant_date' => '2023-04-15',
    ]);

    // Test borrowed origin
    $borrowedData = [
        'name' => 'Borrowed Livestock',
        'breed_id' => $this->breed->id,
        'sex' => 'F',
        'origin' => 5,
        'status' => 1,
        'birthdate' => '2023-05-01',
        'borrowed_from' => 'Lender',
        'borrowed_date' => '2023-05-15',
        'entry_date' => '2023-05-15',
        'tag_id' => 'BORROW001',
    ];

    $response = $this->actingAs($this->user)
        ->post(route('livestocks.store'), $borrowedData);

    $response->assertRedirect();
    
    $this->assertDatabaseHas('livestocks', [
        'name' => 'Borrowed Livestock',
        'origin' => 5,
        'borrowed_from' => 'Lender',
        'borrowed_date' => '2023-05-15',
    ]);
});

it('memvalidasi bidang yang wajib diisi saat menyimpan ternak', function () {
    $invalidData = [
        // Missing required fields
        'sex' => 'F',
    ];

    $response = $this->actingAs($this->user)
        ->post(route('livestocks.store'), $invalidData);

    $response->assertSessionHasErrors(['name', 'breed_id', 'origin', 'status', 'tag_id']);
});

it('memvalidasi unggahan gambar', function () {
    $invalidFile = UploadedFile::fake()->create('document.pdf', 1000);

    $livestockData = [
        'name' => 'Test Livestock',
        'breed_id' => $this->breed->id,
        'sex' => 'F',
        'origin' => 1,
        'status' => 1,
        'photo' => [$invalidFile],
    ];

    $response = $this->actingAs($this->user)
        ->post(route('livestocks.store'), $livestockData);

    $response->assertSessionHasErrors(['photo.0']);
});

it('dapat memperbarui ternak yang ada tanpa gambar', function () {
    $livestock = Livestock::factory()->create([
        'farm_id' => $this->farm->id,
        'breed_id' => $this->breed->id,
        'name' => 'Original Name',
        'sex' => 'F',
    ]);

    $updateData = [
        'name' => 'Updated Name',
        'breed_id' => $this->breed->id,
        'sex' => 'M',
        'origin' => 2,
        'status' => 1,
        'birthdate' => '2023-06-01',
        'purchase_date' => '2023-06-15',
        'purchase_price' => 750000,
        'purchase_from' => 'Updated Seller',
        'weight' => 35.0,
        'tag_id' => 'UPDATED001',
        'entry_date' => '2023-06-15',
    ];

    $response = $this->actingAs($this->user)
        ->put(route('livestocks.update', $livestock), $updateData);

    $response->assertRedirect();
    
    $this->assertDatabaseHas('livestocks', [
        'id' => $livestock->id,
        'name' => 'Updated Name',
        'sex' => 'M',
        'purchase_from' => 'Updated Seller',
        'purchase_price' => 750000,
        'entry_date' => '2023-06-15',
    ]);
});

it('dapat memperbarui ternak yang ada dengan gambar baru', function () {
    $livestock = Livestock::factory()->create([
        'farm_id' => $this->farm->id,
        'breed_id' => $this->breed->id,
        'photo' => ['old_image.jpg'],
    ]);

    $newImage1 = UploadedFile::fake()->image('new_image1.jpg', 800, 600);
    $newImage2 = UploadedFile::fake()->image('new_image2.png', 800, 600);

    $updateData = [
        'name' => $livestock->name,
        'breed_id' => $this->breed->id,
        'sex' => $livestock->sex,
        'origin' => $livestock->origin->value, // Convert enum to scalar
        'status' => $livestock->status->value, // Convert enum to scalar
        'tag_id' => $livestock->tag_id,
        'photo' => ['old_image.jpg', $newImage1, $newImage2], // Include existing photo + new ones
    ];

    $response = $this->actingAs($this->user)
        ->put(route('livestocks.update', $livestock), $updateData);

    $response->assertRedirect();
    
    $livestock->refresh();
    expect($livestock->photo)->toBeArray();
    expect(count($livestock->photo))->toBe(3); // Old image + 2 new images
    
    // Check that new images were stored
    foreach ($livestock->photo as $photoPath) {
        if ($photoPath !== 'old_image.jpg') {
            Storage::disk('public')->assertExists($photoPath);
        }
    }
});

it('dapat memperbarui ternak dengan campuran gambar lama dan baru', function () {
    $livestock = Livestock::factory()->create([
        'farm_id' => $this->farm->id,
        'breed_id' => $this->breed->id,
        'photo' => ['existing_image.jpg'],
    ]);

    $newImage = UploadedFile::fake()->image('additional_image.jpg', 800, 600);

    $updateData = [
        'name' => $livestock->name,
        'breed_id' => $this->breed->id,
        'sex' => $livestock->sex,
        'origin' => $livestock->origin->value, // Convert enum to scalar
        'status' => $livestock->status->value, // Convert enum to scalar
        'tag_id' => $livestock->tag_id,
        'photo' => ['existing_image.jpg', $newImage], // Mix of string and file
    ];

    $response = $this->actingAs($this->user)
        ->put(route('livestocks.update', $livestock), $updateData);

    $response->assertRedirect();
    
    $livestock->refresh();
    expect($livestock->photo)->toBeArray();
    expect(count($livestock->photo))->toBe(2);
    expect($livestock->photo)->toContain('existing_image.jpg');
});

it('memvalidasi bidang yang wajib diisi saat memperbarui ternak', function () {
    $livestock = Livestock::factory()->create([
        'farm_id' => $this->farm->id,
        'breed_id' => $this->breed->id,
    ]);

    $invalidData = [
        'name' => '', // Required field empty
        'sex' => 'X', // Invalid value
    ];

    $response = $this->actingAs($this->user)
        ->put(route('livestocks.update', $livestock), $invalidData);

    $response->assertSessionHasErrors(['name', 'sex', 'breed_id', 'origin', 'status', 'tag_id']);
});

it('mencegah pengguna yang tidak sah menyimpan ternak', function () {
    $otherProvince = Province::factory()->create();
    $otherCity = City::factory()->create(['province_id' => $otherProvince->id]);
    $otherUser = User::factory()->create();
    $otherFarm = Farm::factory()->create([
        'user_id' => $otherUser->id,
        'city_id' => $otherCity->id,
    ]);
    
    $livestockData = [
        'name' => 'Unauthorized Livestock',
        'breed_id' => $this->breed->id,
        'sex' => 'F',
        'origin' => 1,
        'status' => 1,
    ];

    $response = $this->actingAs($otherUser)
        ->post(route('livestocks.store'), $livestockData);

    // Should fail because other user doesn't have current_farm_id set to our farm
    $this->assertDatabaseMissing('livestocks', [
        'name' => 'Unauthorized Livestock',
        'farm_id' => $this->farm->id,
    ]);
});

it('mencegah pengguna yang tidak sah memperbarui ternak', function () {
    $livestock = Livestock::factory()->create([
        'farm_id' => $this->farm->id,
        'breed_id' => $this->breed->id,
    ]);

    $otherUser = User::factory()->create();
    
    $updateData = [
        'name' => 'Hacked Name',
        'breed_id' => $this->breed->id,
        'sex' => 'F',
        'origin' => 1,
        'status' => 1,
        'tag_id' => 'HACK001',
    ];

    $response = $this->actingAs($otherUser)
        ->put(route('livestocks.update', $livestock), $updateData);

    // Should be forbidden
    $response->assertStatus(403);
});

it('menghasilkan aifarm_id unik saat menyimpan ternak', function () {
    $livestock1Data = [
        'name' => 'Livestock 1',
        'breed_id' => $this->breed->id,
        'sex' => 'F',
        'origin' => 1,
        'status' => 1,
        'tag_id' => 'TEST001',
    ];

    $livestock2Data = [
        'name' => 'Livestock 2',
        'breed_id' => $this->breed->id,
        'sex' => 'M',
        'origin' => 1,
        'status' => 1,
        'tag_id' => 'TEST002',
    ];

    $response1 = $this->actingAs($this->user)
        ->post(route('livestocks.store'), $livestock1Data);
    
    $response2 = $this->actingAs($this->user)
        ->post(route('livestocks.store'), $livestock2Data);

    $livestock1 = Livestock::where('name', 'Livestock 1')->first();
    $livestock2 = Livestock::where('name', 'Livestock 2')->first();

    expect($livestock1->aifarm_id)->not->toBeNull();
    expect($livestock2->aifarm_id)->not->toBeNull();
    expect($livestock1->aifarm_id)->not->toBe($livestock2->aifarm_id);
});

it('menangani unggahan gambar besar dalam batas', function () {
    // Create a 2MB image (at the limit)
    $largeImage = UploadedFile::fake()->image('large_image.jpg', 2000, 2000)->size(2048);

    $livestockData = [
        'name' => 'Large Image Livestock',
        'breed_id' => $this->breed->id,
        'sex' => 'F',
        'origin' => 1,
        'status' => 1,
        'tag_id' => 'LRG001',
        'photo' => [$largeImage],
    ];

    $response = $this->actingAs($this->user)
        ->post(route('livestocks.store'), $livestockData);

    $response->assertRedirect();
    
    $livestock = Livestock::where('name', 'Large Image Livestock')->first();
    expect($livestock)->not->toBeNull();
    expect($livestock->photo)->toBeArray();
    expect(count($livestock->photo))->toBe(1);
});

it('menolak unggahan gambar yang terlalu besar', function () {
    // Create a 3MB image (over the limit)
    $oversizedImage = UploadedFile::fake()->image('oversized_image.jpg', 3000, 3000)->size(3072);

    $livestockData = [
        'name' => 'Oversized Image Livestock',
        'breed_id' => $this->breed->id,
        'sex' => 'F',
        'origin' => 1,
        'status' => 1,
        'photo' => [$oversizedImage],
    ];

    $response = $this->actingAs($this->user)
        ->post(route('livestocks.store'), $livestockData);

    $response->assertSessionHasErrors(['photo.0']);
});
