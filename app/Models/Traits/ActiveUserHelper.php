<?php
/**
 * Created by PhpStorm.
 * User: zhaominghai
 * Date: 2018/11/26
 * Time: 3:16 PM
 */

namespace App\Models\Traits;


use App\Models\Reply;
use App\Models\Topic;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

trait ActiveUserHelper
{
    protected $users = [];

    protected $topic_weight = 4;
    protected $reply_wieght = 1;
    protected $pass_day     = 50;
    protected $user_number  = 6;

    protected $cache_key = 'larabbs_active_users';
    protected $cache_expire_in_minutes = 65;

    public function getActiveUsers()
    {
        return Cache::remember($this->cache_key, $this->cache_expire_in_minutes, function () {
            return $this->calculateActiveUsers();
        });
    }

    public function calculateAndCacheActiveUsers()
    {
        $active_users = $this->calculateActiveUsers();

        $this->cacheActiveUsers($active_users);

    }

    private function calculateActiveUsers()
    {
        $this->calculateTopicScore();
        $this->calculateReplyScore();

        $users = array_sort($this->users, function ($user) {
            return $user['score'];
        });

        $users = array_reverse($users, true);

        $users = array_slice($users, 0, $this->user_number, true);

        $active_users = collect();
        foreach ($users as $user_id => $user) {
            $user = $this->find($user_id);

            if ($users) {
                $active_users->push($user);
            }

        }
        return $active_users;
    }

    private function calculateTopicScore()
    {
        $topic_users = Topic::query()->select(\DB::raw('user_id, count(*) as topic_count'))
                                     ->where('created_at', '>=', Carbon::now()->subDays($this->pass_day))
                                     ->groupBy('user_id')
                                     ->get();

        foreach ($topic_users as $value) {
            $this->users[$value->user_id]['score'] = $value->topic_count * $this->topic_weight;
        }
    }
    private function calculateReplyScore()
    {
        // 从回复数据表里取出限定时间范围（$pass_days）内，有发表过回复的用户
        // 并且同时取出用户此段时间内发布回复的数量
        $reply_users = Reply::query()->select(\DB::raw('user_id, count(*) as reply_count'))
            ->where('created_at', '>=', Carbon::now()->subDays($this->pass_day))
            ->groupBy('user_id')
            ->get();
        // 根据回复数量计算得分
        foreach ($reply_users as $value) {
            $reply_score = $value->reply_count * $this->reply_wieght;
            if (isset($this->users[$value->user_id])) {
                $this->users[$value->user_id]['score'] += $reply_score;
            } else {
                $this->users[$value->user_id]['score'] = $reply_score;
            }
        }
    }


    private function cacheActiveUsers($active_users)
    {
        Cache::put($this->cache_key, $active_users, $this->cache_expire_in_minutes);
    }

}