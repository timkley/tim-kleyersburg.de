<?php

declare(strict_types=1);

use Modules\Holocron\Gear\Models\Category;

it('can be created using factory', function () {
    $category = Category::factory()->create();

    expect($category)->toBeInstanceOf(Category::class)
        ->and($category->name)->toBeString();
});

it('uses gear_categories table', function () {
    $category = new Category;

    expect($category->getTable())->toBe('gear_categories');
});

it('has fillable name attribute', function () {
    $category = Category::factory()->create(['name' => 'Electronics']);

    expect($category->name)->toBe('Electronics');
});
