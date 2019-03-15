<?php

use Illuminate\Database\Seeder;
use App\Tag;

class TagsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        Tag::insert(
            json_decode(file_get_contents(__DIR__."/Tags.json"),true)
        );
    }
}
