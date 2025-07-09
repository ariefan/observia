<?php

use App\Models\User;
use App\Models\Farm;
use App\Models\Breed;
use App\Models\Species;
use App\Models\Livestock;
use App\Models\City;
use App\Models\Province;

beforeEach(function () {
    // Create required dependencies
    $province1 = Province::factory()->create();
    $city1 = City::factory()->create(['province_id' => $province1->id]);
    
    $province2 = Province::factory()->create();
    $city2 = City::factory()->create(['province_id' => $province2->id]);
    
    // Create first user with farm
    $this->user1 = User::factory()->create();
    $this->farm1 = Farm::factory()->create([
        'user_id' => $this->user1->id,
        'city_id' => $city1->id,
    ]);
    $this->user1->update(['current_farm_id' => $this->farm1->id]);
    
    // Create second user with different farm
    $this->user2 = User::factory()->create();
    $this->farm2 = Farm::factory()->create([
        'user_id' => $this->user2->id,
        'city_id' => $city2->id,
    ]);
    $this->user2->update(['current_farm_id' => $this->farm2->id]);
    
    $this->species = Species::factory()->create();
    $this->breed = Breed::factory()->create([
        'species_id' => $this->species->id,
    ]);
});

it('allows user to create livestock for their own farm', function () {
    $livestockData = [
        'name' => 'My Farm Livestock',
        'breed_id' => $this->breed->id,
        'sex' => 'F',
        'origin' => 1,
        'status' => 1,
    ];

    $response = $this->actingAs($this->user1)
        ->post(route('livestocks.store'), $livestockData);

    $response->assertRedirect();
    
    $this->assertDatabaseHas('livestocks', [
        'name' => 'My Farm Livestock',
        'farm_id' => $this->farm1->id,
    ]);
});

it('prevents user from viewing livestock from other farms on index', function () {
    // Create livestock for each farm
    $livestock1 = Livestock::factory()->create([
        'farm_id' => $this->farm1->id,
        'breed_id' => $this->breed->id,
        'name' => 'Farm 1 Livestock',
    ]);
    
    $livestock2 = Livestock::factory()->create([
        'farm_id' => $this->farm2->id,
        'breed_id' => $this->breed->id,
        'name' => 'Farm 2 Livestock',
    ]);

    // User 1 should only see their farm's livestock
    $response = $this->actingAs($this->user1)
        ->get(route('livestocks.index'));

    $response->assertStatus(200);
    $response->assertInertia(fn ($assert) => 
        $assert->has('livestocks', 1)
               ->where('livestocks.0.name', 'Farm 1 Livestock')
    );
});

it('allows user to view their own livestock details', function () {
    $livestock = Livestock::factory()->create([
        'farm_id' => $this->farm1->id,
        'breed_id' => $this->breed->id,
        'name' => 'My Livestock',
    ]);

    $response = $this->actingAs($this->user1)
        ->get(route('livestocks.show', $livestock));

    $response->assertStatus(200);
    $response->assertInertia(fn ($assert) => 
        $assert->where('livestock.name', 'My Livestock')
    );
});

it('prevents user from viewing other farms livestock details', function () {
    $livestock = Livestock::factory()->create([
        'farm_id' => $this->farm2->id,
        'breed_id' => $this->breed->id,
        'name' => 'Other Farm Livestock',
    ]);

    $response = $this->actingAs($this->user1)
        ->get(route('livestocks.show', $livestock));

    $response->assertStatus(403);
});

it('allows user to edit their own livestock', function () {
    $livestock = Livestock::factory()->create([
        'farm_id' => $this->farm1->id,
        'breed_id' => $this->breed->id,
        'name' => 'Original Name',
    ]);

    $response = $this->actingAs($this->user1)
        ->get(route('livestocks.edit', $livestock));

    $response->assertStatus(200);
    $response->assertInertia(fn ($assert) => 
        $assert->where('livestock.name', 'Original Name')
    );
});

it('prevents user from editing other farms livestock', function () {
    $livestock = Livestock::factory()->create([
        'farm_id' => $this->farm2->id,
        'breed_id' => $this->breed->id,
        'name' => 'Other Farm Livestock',
    ]);

    $response = $this->actingAs($this->user1)
        ->get(route('livestocks.edit', $livestock));

    $response->assertStatus(403);
});

it('allows user to update their own livestock', function () {
    $livestock = Livestock::factory()->create([
        'farm_id' => $this->farm1->id,
        'breed_id' => $this->breed->id,
        'name' => 'Original Name',
    ]);

    $updateData = [
        'name' => 'Updated Name',
        'breed_id' => $this->breed->id,
        'sex' => $livestock->sex,
        'origin' => $livestock->origin,
        'status' => $livestock->status,
    ];

    $response = $this->actingAs($this->user1)
        ->put(route('livestocks.update', $livestock), $updateData);

    $response->assertRedirect();
    
    $this->assertDatabaseHas('livestocks', [
        'id' => $livestock->id,
        'name' => 'Updated Name',
    ]);
});

it('prevents user from updating other farms livestock', function () {
    $livestock = Livestock::factory()->create([
        'farm_id' => $this->farm2->id,
        'breed_id' => $this->breed->id,
        'name' => 'Original Name',
    ]);

    $updateData = [
        'name' => 'Hacked Name',
        'breed_id' => $this->breed->id,
        'sex' => $livestock->sex,
        'origin' => $livestock->origin,
        'status' => $livestock->status,
    ];

    $response = $this->actingAs($this->user1)
        ->put(route('livestocks.update', $livestock), $updateData);

    $response->assertStatus(403);
    
    $this->assertDatabaseHas('livestocks', [
        'id' => $livestock->id,
        'name' => 'Original Name', // Should not be changed
    ]);
});

it('allows user to delete their own livestock', function () {
    $livestock = Livestock::factory()->create([
        'farm_id' => $this->farm1->id,
        'breed_id' => $this->breed->id,
        'name' => 'To Be Deleted',
    ]);

    $response = $this->actingAs($this->user1)
        ->delete(route('livestocks.destroy', $livestock));

    $response->assertRedirect();
    
    $this->assertSoftDeleted('livestocks', [
        'id' => $livestock->id,
    ]);
});

it('prevents user from deleting other farms livestock', function () {
    $livestock = Livestock::factory()->create([
        'farm_id' => $this->farm2->id,
        'breed_id' => $this->breed->id,
        'name' => 'Should Not Be Deleted',
    ]);

    $response = $this->actingAs($this->user1)
        ->delete(route('livestocks.destroy', $livestock));

    $response->assertStatus(403);
    
    $this->assertDatabaseHas('livestocks', [
        'id' => $livestock->id,
        'name' => 'Should Not Be Deleted',
        'deleted_at' => null,
    ]);
});

it('search returns only livestock from current user farm', function () {
    // Create livestock for each farm
    $livestock1 = Livestock::factory()->create([
        'farm_id' => $this->farm1->id,
        'breed_id' => $this->breed->id,
        'name' => 'Searchable 1',
        'aifarm_id' => 'FARM1001',
    ]);
    
    $livestock2 = Livestock::factory()->create([
        'farm_id' => $this->farm2->id,
        'breed_id' => $this->breed->id,
        'name' => 'Searchable 2',
        'aifarm_id' => 'FARM2001',
    ]);

    // User 1 searches for livestock
    $response = $this->actingAs($this->user1)
        ->get(route('livestocks.search', ['q' => 'Searchable']));

    $response->assertStatus(200);
    $data = $response->json();
    
    expect($data)->toHaveCount(1);
    expect($data[0]['name'])->toBe('Searchable 1');
    expect($data[0]['aifarm_id'])->toBe('FARM1001');
});

it('requires authentication for all livestock routes', function () {
    $livestock = Livestock::factory()->create([
        'farm_id' => $this->farm1->id,
        'breed_id' => $this->breed->id,
    ]);

    // Test all routes without authentication
    $this->get(route('livestocks.index'))->assertRedirect();
    $this->get(route('livestocks.create'))->assertRedirect();
    $this->post(route('livestocks.store'))->assertRedirect();
    $this->get(route('livestocks.show', $livestock))->assertRedirect();
    $this->get(route('livestocks.edit', $livestock))->assertRedirect();
    $this->put(route('livestocks.update', $livestock))->assertRedirect();
    $this->delete(route('livestocks.destroy', $livestock))->assertRedirect();
    $this->get(route('livestocks.search'))->assertRedirect();
});

it('user without current_farm_id cannot access livestock features', function () {
    $userWithoutFarm = User::factory()->create(['current_farm_id' => null]);

    $livestockData = [
        'name' => 'Should Not Be Created',
        'breed_id' => $this->breed->id,
        'sex' => 'F',
        'origin' => 1,
        'status' => 1,
    ];

    // This might fail or create livestock with null farm_id, depending on implementation
    $response = $this->actingAs($userWithoutFarm)
        ->post(route('livestocks.store'), $livestockData);

    // The livestock should not be created with a valid farm_id
    $this->assertDatabaseMissing('livestocks', [
        'name' => 'Should Not Be Created',
    ]);
});

it('validates parent livestock belongs to same farm', function () {
    // Create parent livestock in different farm
    $parentFromOtherFarm = Livestock::factory()->create([
        'farm_id' => $this->farm2->id,
        'breed_id' => $this->breed->id,
        'sex' => 'M',
    ]);

    $livestockData = [
        'name' => 'Child Livestock',
        'breed_id' => $this->breed->id,
        'sex' => 'F',
        'origin' => 1,
        'status' => 1,
        'male_parent_id' => $parentFromOtherFarm->id,
    ];

    $response = $this->actingAs($this->user1)
        ->post(route('livestocks.store'), $livestockData);

    // This should either fail validation or create with null parent
    // The exact behavior depends on how cross-farm validation is implemented
    $livestock = Livestock::where('name', 'Child Livestock')->first();
    if ($livestock) {
        expect($livestock->male_parent_id)->toBeNull();
    }
});
