<?php

namespace App\Models;

use App\Traits\HasCompany;
use App\Traits\CustomFieldsTrait;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Purchase\Entities\PurchaseStockAdjustment;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * App\Models\Service / Extra
 *
 * @property int $id
 * @property string $name
 * @property string $price
 * @property string|null $taxes
 * @property int $allow_purchase
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string $description
 * @property int|null $unit_id
 * @property int|null $category_id
 * @property int|null $sub_category_id
 * @property int|null $added_by
 * @property int|null $last_updated_by
 * @property string|null $hsn_sac_code
 * @property string|null $sku
 * @property-read mixed $icon
 * @property-read mixed $total_amount
 * @property-read \App\Models\Tax $tax
 * @method static \Database\Factories\ProductFactory factory(...$parameters)
 * @method static \Illuminate\Database\Eloquent\Builder|Service / Extra newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Service / Extra newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Service / Extra query()
 * @method static \Illuminate\Database\Eloquent\Builder|Service / Extra whereAddedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Service / Extra whereAllowPurchase($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Service / Extra whereCategoryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Service / Extra whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Service / Extra whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Service / Extra whereHsnSacCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Service / Extra whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Service / Extra whereLastUpdatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Service / Extra whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Service / Extra wherePrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Service / Extra whereSubCategoryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Service / Extra whereTaxes($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Service / Extra whereUpdatedAt($value)
 * @property-read \App\Models\ProductCategory|null $category
 * @property string|null $image
 * @property-read mixed $image_url
 * @method static \Illuminate\Database\Eloquent\Builder|Service / Extra whereImage($value)
 * @property int $downloadable
 * @property string|null $downloadable_file
 * @property string|null $default_image
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\ProductFiles[] $files
 * @property-read int|null $files_count
 * @property-read mixed $download_file_url
 * @property-read mixed $extras
 * @property-read \App\Models\ProductSubCategory|null $subCategory
 * @method static \Illuminate\Database\Eloquent\Builder|Service / Extra whereDefaultImage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Service / Extra whereDownloadable($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Service / Extra whereDownloadableFile($value)
 * @property int|null $company_id
 * @property-read \App\Models\Company|null $company
 * @property-read mixed $tax_list
 * @method static \Illuminate\Database\Eloquent\Builder|Service / Extra whereCompanyId($value)
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Enquiry> $enquiries
 * @property-read int|null $leads_count
 * @property-read \App\Models\UnitType|null $unit
 * @method static \Illuminate\Database\Eloquent\Builder|Service / Extra whereUnitId($value)
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\OrderItems> $orderItem
 * @property-read int|null $order_item_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Enquiry> $enquiries
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\OrderItems> $orderItem
 * @property string|null $purchase_price
 * @property string $purchase_information
 * @property string $track_inventory
 * @property string|null $sales_description
 * @property string|null $purchase_description
 * @property int|null $opening_stock
 * @property float|null $rate_per_unit
 * @property string|null $sku
 * @property string|null $type
 * @property string $status
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Enquiry> $enquiries
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\OrderItems> $orderItem
 * @method static \Illuminate\Database\Eloquent\Builder|Service / Extra whereOpeningStock($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Service / Extra wherePurchaseDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Service / Extra wherePurchaseInformation($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Service / Extra wherePurchasePrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Service / Extra whereRatePerUnit($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Service / Extra whereSalesDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Service / Extra whereSku($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Service / Extra whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Service / Extra whereTrackInventory($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Service / Extra whereType($value)
 * @property-read \Illuminate\Database\Eloquent\Collection<int, PurchaseStockAdjustment> $inventory
 * @property-read int|null $inventory_count
 * @mixin \Eloquent
 */
class Service / Extra extends BaseModel
{

    use HasCompany;
    use HasFactory, CustomFieldsTrait;

    protected $table = 'services / extras';
    const FILE_PATH = 'services / extras';

    protected $fillable = ['name', 'price', 'description', 'taxes'];

    protected $appends = ['total_amount', 'image_url', 'download_file_url', 'image'];

    protected $with = ['tax'];

    const CUSTOM_FIELD_MODEL = 'App\Models\Service / Extra';

    public function getImageUrlAttribute()
    {
        if (app()->environment(['development','demo']) && str_contains($this->default_image, 'http')) {
            return $this->default_image;
        }

        return ($this->default_image) ? asset_url_local_s3(Service / Extra::FILE_PATH . '/' . $this->default_image) : '';
    }

    public function getImageAttribute()
    {
        if($this->default_image){
            return str($this->default_image)->contains('http') ? $this->default_image : (Service / Extra::FILE_PATH . '/' . $this->default_image);
        }

        return $this->default_image;
    }

    public function getDownloadFileUrlAttribute()
    {
        return ($this->downloadable_file) ? asset_url_local_s3(Service / Extra::FILE_PATH . '/' . $this->downloadable_file) : null;
    }

    public function tax(): BelongsTo
    {
        return $this->belongsTo(Tax::class)->withTrashed();
    }

    public function enquiries(): BelongsToMany
    {
        return $this->belongsToMany(Deal::class, 'lead_products');
    }

    public static function taxbyid($id)
    {
        return Tax::where('id', $id)->withTrashed();
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ProductCategory::class, 'category_id');
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(UnitType::class, 'unit_id');
    }

    public function subCategory(): BelongsTo
    {
        return $this->belongsTo(ProductSubCategory::class, 'sub_category_id');
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
        return $this->hasMany(ProductFiles::class, 'product_id')->orderByDesc('id');
    }

    public function getTaxListAttribute()
    {
        $productItem = Service / Extra::findOrFail($this->id);
        $taxes = '';

        if ($productItem && $productItem->taxes) {
            $numItems = count(json_decode($productItem->taxes));

            if (!is_null($productItem->taxes)) {
                foreach (json_decode($productItem->taxes) as $index => $tax) {
                    $tax = $this->taxbyid($tax)->first();
                    $taxes .= $tax->tax_name . ': ' . $tax->rate_percent . '%';

                    $taxes = ($index + 1 != $numItems) ? $taxes . ', ' : $taxes;
                }
            }
        }

        return $taxes;
    }

    public function orderItem(): HasMany
    {
        return $this->hasMany(OrderItems::class, 'product_id');

    }

    public function inventory()
    {
        /** @phpstan-ignore-next-line */
        return $this->hasMany(PurchaseStockAdjustment::class, 'product_id');
    }

}
