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
    $this->province = Province::factory()->create();
    $this->city = City::factory()->create(['province_id' => $this->province->id]);
    
    $this->user = User::factory()->create();
    $this->farm = Farm::factory()->create([
        'user_id' => $this->user->id,
        'city_id' => $this->city->id,
    ]);
    $this->user->update(['current_farm_id' => $this->farm->id]);
    
    $this->species = Species::factory()->create();
    $this->breed = Breed::factory()->create([
        'species_id' => $this->species->id,
    ]);
});

it('validates breed_id exists in breeds table', function () {
    $livestockData = [
        'name' => 'Test Livestock',
        'breed_id' => '00000000-0000-0000-0000-000000000000', // Non-existent UUID
        'sex' => 'F',
        'origin' => 1,
        'status' => 1,
    ];

    $response = $this->actingAs($this->user)
        ->post(route('livestocks.store'), $livestockData);

    $response->assertSessionHasErrors(['breed_id']);
});

it('validates male_parent_id exists in livestocks table when provided', function () {
    $livestockData = [
        'name' => 'Test Livestock',
        'breed_id' => $this->breed->id,
        'sex' => 'F',
        'origin' => 1,
        'status' => 1,
        'male_parent_id' => '00000000-0000-0000-0000-000000000000', // Non-existent UUID
    ];

    $response = $this->actingAs($this->user)
        ->post(route('livestocks.store'), $livestockData);

    $response->assertSessionHasErrors(['male_parent_id']);
});

it('validates female_parent_id exists in livestocks table when provided', function () {
    $livestockData = [
        'name' => 'Test Livestock',
        'breed_id' => $this->breed->id,
        'sex' => 'F',
        'origin' => 1,
        'status' => 1,
        'female_parent_id' => '00000000-0000-0000-0000-000000000000', // Non-existent UUID
    ];

    $response = $this->actingAs($this->user)
        ->post(route('livestocks.store'), $livestockData);

    $response->assertSessionHasErrors(['female_parent_id']);
});

it('accepts null values for optional parent fields', function () {
    $livestockData = [
        'name' => 'Test Livestock',
        'breed_id' => $this->breed->id,
        'sex' => 'F',
        'origin' => 1,
        'status' => 1,
        'male_parent_id' => null,
        'female_parent_id' => null,
    ];

    $response = $this->actingAs($this->user)
        ->post(route('livestocks.store'), $livestockData);

    $response->assertRedirect();
    
    $this->assertDatabaseHas('livestocks', [
        'name' => 'Test Livestock',
        'male_parent_id' => null,
        'female_parent_id' => null,
    ]);
});

it('validates numeric fields are properly formatted', function () {
    $livestockData = [
        'name' => 'Test Livestock',
        'breed_id' => $this->breed->id,
        'sex' => 'F',
        'origin' => 'invalid', // Should be numeric
        'status' => 'invalid', // Should be numeric
        'birth_weight' => 'not_a_number',
        'weight' => 'not_a_number',
        'purchase_price' => 'not_a_number',
    ];

    $response = $this->actingAs($this->user)
        ->post(route('livestocks.store'), $livestockData);

    $response->assertSessionHasErrors([
        'origin',
        'status', 
        'birth_weight',
        'weight',
        'purchase_price'
    ]);
});

it('validates date fields are properly formatted', function () {
    $livestockData = [
        'name' => 'Test Livestock',
        'breed_id' => $this->breed->id,
        'sex' => 'F',
        'origin' => 1,
        'status' => 1,
        'birthdate' => 'invalid-date',
        'purchase_date' => 'not-a-date',
        'barter_date' => '2023-13-40', // Invalid date
        'grant_date' => 'invalid',
        'borrowed_date' => 'invalid',
        'entry_date' => 'invalid',
    ];

    $response = $this->actingAs($this->user)
        ->post(route('livestocks.store'), $livestockData);

    $response->assertSessionHasErrors([
        'birthdate',
        'purchase_date',
        'barter_date',
        'grant_date',
        'borrowed_date',
        'entry_date'
    ]);
});

it('validates string field max lengths', function () {
    $longString = str_repeat('a', 300); // Exceeds 255 character limit

    $livestockData = [
        'name' => $longString,
        'breed_id' => $this->breed->id,
        'sex' => 'F',
        'origin' => 1,
        'status' => 1,
        'tag_type' => $longString,
        'tag_id' => $longString,
        'barter_livestock_id' => $longString,
        'barter_from' => $longString,
        'purchase_from' => $longString,
        'grant_from' => $longString,
        'borrowed_from' => $longString,
    ];

    $response = $this->actingAs($this->user)
        ->post(route('livestocks.store'), $livestockData);

    $response->assertSessionHasErrors([
        'name',
        'tag_type',
        'tag_id',
        'barter_livestock_id',
        'barter_from',
        'purchase_from',
        'grant_from',
        'borrowed_from'
    ]);
});

it('validates sex field only accepts M or F', function () {
    $livestockData = [
        'name' => 'Test Livestock',
        'breed_id' => $this->breed->id,
        'sex' => 'X', // Invalid value
        'origin' => 1,
        'status' => 1,
    ];

    $response = $this->actingAs($this->user)
        ->post(route('livestocks.store'), $livestockData);

    $response->assertSessionHasErrors(['sex']);
});

it('accepts valid sex values M and F', function () {
    $maleData = [
        'name' => 'Male Livestock',
        'breed_id' => $this->breed->id,
        'sex' => 'M',
        'origin' => 1,
        'status' => 1,
    ];

    $femaleData = [
        'name' => 'Female Livestock',
        'breed_id' => $this->breed->id,
        'sex' => 'F',
        'origin' => 1,
        'status' => 1,
    ];

    $maleResponse = $this->actingAs($this->user)
        ->post(route('livestocks.store'), $maleData);
    
    $femaleResponse = $this->actingAs($this->user)
        ->post(route('livestocks.store'), $femaleData);

    $maleResponse->assertRedirect();
    $femaleResponse->assertRedirect();
    
    $this->assertDatabaseHas('livestocks', ['name' => 'Male Livestock', 'sex' => 'M']);
    $this->assertDatabaseHas('livestocks', ['name' => 'Female Livestock', 'sex' => 'F']);
});

it('validates image array and individual image files', function () {
    $invalidData = [
        'name' => 'Test Livestock',
        'breed_id' => $this->breed->id,
        'sex' => 'F',
        'origin' => 1,
        'status' => 1,
        'photo' => 'not-an-array', // Should be array
    ];

    $response = $this->actingAs($this->user)
        ->post(route('livestocks.store'), $invalidData);

    $response->assertSessionHasErrors(['photo']);
});

it('handles empty photo arrays gracefully', function () {
    $livestockData = [
        'name' => 'No Photo Livestock',
        'breed_id' => $this->breed->id,
        'sex' => 'F',
        'origin' => 1,
        'status' => 1,
        'photo' => [], // Empty array
    ];

    $response = $this->actingAs($this->user)
        ->post(route('livestocks.store'), $livestockData);

    $response->assertRedirect();
    
    $livestock = Livestock::where('name', 'No Photo Livestock')->first();
    expect($livestock)->not->toBeNull();
    expect($livestock->photo)->toBeArray();
    expect($livestock->photo)->toBeEmpty();
});
