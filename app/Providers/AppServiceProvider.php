<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;
use App\Models\permission_settings;
use App\Models\permission_settings_list;
use App\Models\module;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // Register @hasPermission Blade directive
        Blade::if('hasPermission', function ($moduleName) {
            // Get authenticated user or student
            $user = auth('web')->user();
            $student = auth('students')->user();
            
            if (!$user && !$student) {
                return false;
            }
            
            // Get module by name
            $module = module::where('module', $moduleName)
                ->where('status', 'active')
                ->first();
            
            if (!$module) {
                return false;
            }
            
            // Get permission setting for user or student
            $permissionSetting = null;
            if ($user) {
                $permissionSetting = permission_settings::where('users_id', $user->id)
                    ->whereNull('students_id')
                    ->where('status', 'active')
                    ->first();
            } else if ($student) {
                $permissionSetting = permission_settings::where('students_id', $student->id)
                    ->whereNull('users_id')
                    ->where('status', 'active')
                    ->first();
            }
            
            if (!$permissionSetting) {
                return false;
            }
            
            // Check if module is assigned to this permission setting
            $hasModule = permission_settings_list::where('permission_settings_id', $permissionSetting->id)
                ->where('module_id', $module->id)
                ->where('status', 'active')
                ->exists();
            
            return $hasModule;
        });
    }
}
