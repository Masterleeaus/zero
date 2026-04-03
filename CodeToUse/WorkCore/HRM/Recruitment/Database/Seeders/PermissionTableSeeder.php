<?php

namespace Modules\Recruitment\Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

class PermissionTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        if(module_is_active('Hrm'))
       {
            Model::unguard();
            $permission  = [
                'recruitment manage',
                'job manage',
                'job create',
                'job edit',
                'job delete',
                'job show',
                'jobcategory manage',
                'jobcategory create',
                'jobcategory edit',
                'jobcategory delete',
                'jobstage manage',
                'jobstage create',
                'jobstage edit',
                'jobstage delete',
                'jobapplication manage',
                'jobapplication create',
                'jobapplication show',
                'jobapplication delete',
                'jobapplication move',
                'jobapplication add skill',
                'jobapplication add note',
                'jobapplication delete note',
                'jobapplication candidate manage',
                'jobonboard manage',
                'jobonboard create',
                'jobonboard edit',
                'jobonboard delete',
                'jobonboard convert',
                'custom question manage',
                'custom question create',
                'custom question edit',
                'custom question delete',
                'interview schedule manage',
                'interview schedule create',
                'interview schedule edit',
                'interview schedule delete',
                'interview schedule show',
                'career manage',
                'letter offer manage',
            ];
            $company_role = Role::where('name','company')->first();
            foreach ($permission as $key => $value)
            {
                $table = Permission::where('name',$value)->where('module','Recruitment')->exists();
                if(!$table)
                {
                    $permission = Permission::create(
                        [
                            'name' => $value,
                            'guard_name' => 'web',
                            'module' => 'Recruitment',
                            'created_by' => 0,
                            "created_at" => date('Y-m-d H:i:s'),
                            "updated_at" => date('Y-m-d H:i:s')
                        ]
                    );
                    if (!$company_role->hasPermission($value)) {
                        $company_role->givePermission($permission);
                    }
                }
            }
       }
    }
}
