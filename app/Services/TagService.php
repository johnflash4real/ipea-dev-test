<?php
/**
 * Created by PhpStorm.
 * User: JFlash
 * Date: 3/14/19
 * Time: 12:35 AM
 */

namespace App\Services;

use  App\Http\Helpers\InfusionsoftHelper;
use \App\Module;
use App\Tag;

class TagService
{

    /**
     * Get proper reminder tag for specific module or when no module
     * @param Module $module
     * @return Tag
     */

    public function getTagForModule(Module $module=null){
        //build proper tag slug
        if($module) $tagSlug = "start-{$module->course_key}-module-{$module->position}-reminders";
        else $tagSlug = "module-reminders-completed";

        $this->preloadTags(); //ensure we have tags on our db
        $theTag = Tag::where('slug',$tagSlug)->first();
        return $theTag;
    }


    /**
     * Check db for tags or fetch from api then save in db
     */

    private function preloadTags(){
        if(Tag::all()->count()==0){
            //if no tag in db, load into db from infusionsoft

            $infusionsoftHelper = new InfusionsoftHelper();
            $allTags = $infusionsoftHelper->getAllTags()->all();
            $tagsArray = [];

            foreach ($allTags as $tag)
                $tagsArray[] = [
                    'tag_id'=>$tag->id,
                    'name'=>$tag->name,
                    'slug'=>str_slug($tag->name) //give each tag a slug for easily finding it later
                ];

            Tag::insert($tagsArray);
        }
    }

}
