<?php

namespace Modules\Recruitment\Entities;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Models\WorkSpace;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Job extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'requirement',
        'branch',
        'category',
        'skill',
        'position',
        'start_date',
        'end_date',
        'status',
        'applicant',
        'visibility',
        'code',
        'custom_question',
        'workspace',
        'created_by',
    ];

    protected static function newFactory()
    {
        return \Modules\Recruitment\Database\factories\JobFactory::new();
    }

    public static $status = [
        'active' => 'Active',
        'in_active' => 'In Active',
    ];

    public function branches()
    {
        return $this->hasOne(\Modules\Hrm\Entities\Branch::class, 'id', 'branch');
    }

    public function categories()
    {
        return $this->hasOne(JobCategory::class, 'id', 'category');
    }

    public function questions()
    {
        $ids = explode(',', $this->custom_question);

        return CustomQuestion::whereIn('id', $ids)->get();
    }

    public function createdBy()
    {
        return $this->hasOne(User::class, 'id', 'created_by');
    }

    public static function defaultdata($company_id = null,$workspace_id = null)
    {
        $job_stages = [
            "Applied",
            "Phone Screen",
            "Interview",
            "Hired",
            "Rejected",
        ];

        if($company_id == Null)
        {
            $companys = User::where('type','company')->get();
            foreach($companys as $company)
            {
                $WorkSpaces = WorkSpace::where('created_by',$company->id)->get();
                foreach($WorkSpaces as $WorkSpace)
                {
                    OfferLetter::defaultOfferLetter($company->id, $WorkSpace->id);

                    foreach($job_stages as $job_stage)
                        {
                            $jobstage = JobStage::where('title',$job_stage)->where('workspace',$WorkSpace->id)->where('created_by',$company->id)->first();
                            if($jobstage == null){
                            $jobstage = new JobStage();
                            $jobstage->title = $job_stage;
                            $jobstage->workspace =  !empty($WorkSpace->id) ? $WorkSpace->id : 0 ;
                            $jobstage->created_by = !empty($company->id) ? $company->id : 2;
                            $jobstage->save();
                            }
                        }
                }
            }
        }elseif($workspace_id == Null){
            $company = User::where('type','company')->where('id',$company_id)->first();
            $WorkSpaces = WorkSpace::where('created_by',$company->id)->get();
            foreach($WorkSpaces as $WorkSpace)
            {
                foreach($job_stages as $job_stage)
                {
                    OfferLetter::defaultOfferLetter($company->id, $WorkSpace->id);

                    $jobstage = JobStage::where('title',$job_stage)->where('workspace',$WorkSpace->id)->where('created_by',$company->id)->first();
                    if($jobstage == null){
                    $jobstage = new JobStage();
                    $jobstage->title = $job_stage;
                    $jobstage->workspace =  !empty($WorkSpace->id) ? $WorkSpace->id : 0 ;
                    $jobstage->created_by = !empty($company->id) ? $company->id : 2;
                    $jobstage->save();
                    }

                }
            }
        }else{
            $company = User::where('type','company')->where('id',$company_id)->first();
            $WorkSpace = WorkSpace::where('created_by',$company->id)->where('id',$workspace_id)->first();
            foreach($job_stages as $job_stage)
            {
                OfferLetter::defaultOfferLetter($company->id, $WorkSpace->id);

                $jobstage = JobStage::where('title',$job_stage)->where('workspace',$WorkSpace->id)->where('created_by',$company->id)->first();
                if($jobstage == null){
                $jobstage = new JobStage();
                $jobstage->title = $job_stage;
                $jobstage->workspace =  !empty($WorkSpace->id) ? $WorkSpace->id : 0 ;
                $jobstage->created_by = !empty($company->id) ? $company->id : 2;
                $jobstage->save();
                }
            }
        }
    }

    public static function GivePermissionToRoles($role_id = null,$rolename = null)
    {
        $staff_permission=[
            'career manage',
            'recruitment manage',
        ];

        if($role_id == Null)
        {
            // staff
            $roles_v = Role::where('name','staff')->get();

            foreach($roles_v as $role)
            {
                foreach($staff_permission as $permission_v){
                    $permission = Permission::where('name',$permission_v)->first();
                    if (!empty($permission)) {
                        if (!$roles_v->hasPermission($permission_v)) {
                            $roles_v->givePermission($permission);
                        }
                    }
                }
            }

        }
        else
        {
            if($rolename == 'staff')
            {
                $roles_v = Role::where('name','staff')->where('id',$role_id)->first();
                foreach($staff_permission as $permission_v){
                    $permission = Permission::where('name',$permission_v)->first();
                    if (!empty($permission)) {
                        if (!$roles_v->hasPermission($permission_v)) {
                            $roles_v->givePermission($permission);
                        }
                    }
                }
            }
        }
    }
}
