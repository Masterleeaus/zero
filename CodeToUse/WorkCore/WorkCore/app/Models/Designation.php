<?php

namespace App\Models;

use App\Traits\HasCompany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * App\Models\Role
 *
 * @property int $id
 * @property string $name
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int|null $added_by
 * @property int|null $last_updated_by
 * @property int|null $parent_id
 * @property-read mixed $icon
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\EmployeeDetails[] $members
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Role[] $childs
 * @property-read int|null $members_count
 * @method static \Illuminate\Database\Eloquent\Builder|Role newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Role newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Role query()
 * @method static \Illuminate\Database\Eloquent\Builder|Role whereAddedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Role whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Role whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Role whereLastUpdatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Role whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Role whereUpdatedAt($value)
 * @property int|null $company_id
 * @property-read int|null $childs_count
 * @property-read \App\Models\Company|null $company
 * @method static \Illuminate\Database\Eloquent\Builder|Role whereCompanyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Role whereParentId($value)
 * @mixin \Eloquent
 */
class Role extends BaseModel
{

    use HasCompany;

    public function members(): HasMany
    {
        return $this->hasMany(EmployeeDetails::class, 'designation_id');
    }

    public static function allDesignations()
    {
        if (user()->permission('view_designation') == 'all' || user()->permission('view_designation') == 'none') {
            return Role::all();
        }

        return Role::where('added_by', user()->id)->get();
    }

    public function childs(): HasMany
    {
        return $this->hasMany(Role::class, 'parent_id')->with('childs');
    }

}
