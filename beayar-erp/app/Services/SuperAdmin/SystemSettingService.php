<?php

namespace App\Services\SuperAdmin;

use App\Models\SystemSetting;

class SystemSettingService
{
    public function get(string $key, $default = null)
    {
        $setting = SystemSetting::where('key', $key)->first();

        return $setting ? $setting->value : $default;
    }

    public function set(string $key, $value, string $group = 'general')
    {
        return SystemSetting::updateOrCreate(
            ['key' => $key],
            ['value' => $value, 'group' => $group]
        );
    }
}
