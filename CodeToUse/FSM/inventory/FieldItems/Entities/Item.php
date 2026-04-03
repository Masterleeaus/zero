<?php

namespace Modules\FieldItems\Entities;

use App\Models\ModuleSetting;
use App\Models\BaseModel;
use App\Traits\HasCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Traits\CustomFieldsTrait;
use App\Models\Tax;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\UnitType;

class Item extends BaseModel
{
    use HasCompany;
    use HasFactory, CustomFieldsTrait;

    protected $guarded = ['id'];
    const MODULE_NAME = 'items';

    const FILE_PATH = 'items';

    protected $fillable = ['name', 'price', 'description', 'taxes'];

    protected $appends = ['total_amount', 'image_url', 'download_file_url'];

    protected $with = ['tax'];

    const CUSTOM_FIELD_MODEL = 'Modules\FieldItems\Entities\Item';
    
    protected static function newFactory()
    {
        return \Modules\FieldItems\Database\factories\ItemFactory::new();
    }

    public static function addModuleSetting($company)
    {
        // create admin, employee and client module settings
        $roles = ['admin', 'employee'];

        ModuleSetting::createRoleSettingEntry(self::MODULE_NAME, $roles, $company);

    }

    public function getImageUrlAttribute()
    {
        if (app()->environment(['development','demo']) && str_contains($this->default_image, 'http')) {
            return $this->default_image;
        }

        return ($this->default_image) ? asset_url_local_s3(Item::FILE_PATH . '/' . $this->default_image) : '';
    }

    public function getDownloadFileUrlAttribute()
    {
        return ($this->downloadable_file) ? asset_url_local_s3(Item::FILE_PATH . '/' . $this->downloadable_file) : null;
    }

    public function tax(): BelongsTo
    {
        return $this->belongsTo(Tax::class)->withTrashed();
    }

    public function leads(): BelongsToMany
    {
        return $this->belongsToMany(Lead::class, 'lead_items');
    }

    public static function taxbyid($id)
    {
        return Tax::where('id', $id)->withTrashed();
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ItemCategory::class, 'category_id');
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(UnitType::class, 'unit_id');
    }

    public function subCategory(): BelongsTo
    {
        return $this->belongsTo(ItemSubCategory::class, 'sub_category_id');
    }

    public function getTotalAmountAttribute()
    {

        if (!is_null($this->price) && !is_null($this->tax)) {
            return (int)$this->price + ((int)$this->price * ((int)$this->tax->rate_percent / 100));
        }

        return '';
    }

    public function files(): HasMany
    {
        return $this->hasMany(ItemFiles::class, 'item_id')->orderBy('id', 'desc');
    }

    public function getTaxListAttribute()
    {
        $itemItem = Item::findOrFail($this->id);
        $taxes = '';

        if ($itemItem && $itemItem->taxes) {
            $numItems = count(json_decode($itemItem->taxes));

            if (!is_null($itemItem->taxes)) {
                foreach (json_decode($itemItem->taxes) as $index => $tax) {
                    $tax = $this->taxbyid($tax)->first();
                    $taxes .= $tax->tax_name . ': ' . $tax->rate_percent . '%';

                    $taxes = ($index + 1 != $numItems) ? $taxes . ', ' : $taxes;
                }
            }
        }

        return $taxes;
    }
}