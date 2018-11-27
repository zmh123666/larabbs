<?php

namespace App\Observers;

//creating, created, updating, updated, saving,
// saved,  deleting, deleted, restoring, restored

use App\Models\Link;

class LinkObserver
{
   public function saved(Link $link)
   {
       \Cache::forget($link->cache_key);
   }
}