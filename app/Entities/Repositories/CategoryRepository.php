<?php

namespace LaravelItalia\Entities\Repositories;


use LaravelItalia\Entities\Category;

/**
 * Class CategoryRepository
 * @package LaravelItalia\Entities\Repositories
 */
class CategoryRepository
{
    public function save(Category $category)
    {
        $category->save();
    }

    public function findBySlug($slug)
    {
        return Category::where('slug', '=', $slug)->first();
    }

    public function getAll()
    {
        return Category::all();
    }

    public function delete(Category $category)
    {
        $category->delete();
    }
}