<?php
/**
 * Created by PhpStorm.
 * User: zhaominghai
 * Date: 2018/11/27
 * Time: 4:15 PM
 */

namespace App\Models\Traits;


use Carbon\Carbon;

trait LastActivedAtHelper
{
    protected $has_prefix = 'larabbs_last_actived_at_';
    protected $field_prefix = 'user_';


    public function recordLastActivedAt()
    {
        $hash = $this->getHashFromDateString(Carbon::now()->toDateString());

        $field = $this->getHashField();

        $now = Carbon::now()->toDateTimeString();

        \Redis::hSet($hash, $field, $now);
    }


    public function syncUserActivedAt()
    {
        $hash = $this->getHashFromDateString(Carbon::yesterday()->toDateString());

        $dates = \Redis::hGetAll($hash);

        foreach ($dates as $user_id => $actived_at) {
            $user_id = str_replace($this->field_prefix, '', $user_id);

            if ($user = $this->find($user_id)) {
                $user->last_actived_at = $actived_at;
                $user->save();
            }
        }

        \Redis::del($hash);
    }

    public function getHashFromDateString($date)
    {
        return $this->has_prefix . $date;
    }

    public function getHashField()
    {
        return $this->field_prefix . $this->id;
    }

    public function getLastActivedAtAttribute($value)
    {
        // 获取今日对应的哈希表名称
        $hash = $this->getHashFromDateString(Carbon::now()->toDateString());

        // 字段名称，如：user_1
        $field = $this->getHashField();

        $datetime = \Redis::hGet($hash, $field) ?: $value;

        if ($datetime) {
            return new Carbon($datetime);
        } else {
            return $this->created_at;
        }

    }
}