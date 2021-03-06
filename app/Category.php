<?php

namespace App;

use Carbon\Carbon;
// use EloquentFilter\Filterable;
// use Yajra\Auditable\AuditableTrait;
use Illuminate\Database\Eloquent\Model;
// use App\Api\V1\Traits\SearchableByDateRange;

class Category extends Model
{
    // use AuditableTrait, Filterable, SearchableByDateRange;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'description',
        'meta_keyword',
        'meta_description',
        'parent_id',
        'picture_id',
        'display_order',
        'cat_slug',
        'cat_image',
        'favicon_image',
        'created_by',
        'updated_by'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [];

    protected $appends = ['parent_category', 'picture', 'date_created', 'creator', 'date_updated', 'updater'];


    public function products()
    {
        return $this->belongsToMany('App\Product', 'category_product', 'category_id', 'product_id');
    }


    public function parent()
    {
        return $this->belongsTo('App\Category', 'parent_id');
    }
    public function categories()
    {
        return $this->hasMany(Category::class,'parent_id');
    }

    public function childrenCategories()
    {
        return $this->hasMany(Category::class,'parent_id')->with('categories');
    }

    public function children()
    {
        return $this->hasMany('App\Category', 'parent_id','id');
    }

    public function grandChildren()
    {
        return $this->belongsTo('App\Category', 'parent_id','id');
    }

    public function getGrandChildrenCategoryAttribute()
    {
        if ($this->parent_id == null || $this->id == $this->parent_id) {
            return "";
        }
        $item = Category::where('parent_id', $this->parent_id)->first();
        if (!$item) {
            return "";
        }
        return $item->name;
    }
    public function getParentCategoryAttribute()
    {
        if ($this->parent_id == null || $this->id == $this->parent_id) {
            return "";
        }
        $item = Category::where('id', $this->parent_id)->first();
        if (!$item) {
            return "";
        }
        return $item->name;
    }

    public function getPictureAttribute()
    {
        if ($this->picture_id == null) {
            return null;
        }
        return Picture::where('id', $this->picture_id)->first();
    }

    public function getCreatorAttribute()
    {
        $user = User::where('id', $this->created_by)->first();
        if (!$user) {
            return "";
        }
        return $user->username;
    }

    public function getUpdaterAttribute()
    {
        if ($this->updated_by == null) {
            return "";
        }
        $user = User::where('id', $this->updated_by)->first();
        if (!$user) {
            return "";
        }
        return $user->username;
    }

    public function getDateCreatedAttribute()
    {
        return Carbon::createFromFormat('Y-m-d H:i:s', $this->created_at)->diffForHumans(null, false, true);
    }

    public function getDateUpdatedAttribute()
    {
        if ($this->updated_at == null) {
            return "";
        }
        return Carbon::createFromFormat('Y-m-d H:i:s', $this->updated_at)->diffForHumans(null, false, true);
    }

    /**
     * check that the passed categories are available in db
     *
     * @return ids of valid categories|bool
     */
    public function areValidCategories(array $categories = [])
    {
        if ($categories === array_intersect($categories, $this->pluck('name')->toArray())) {
            return $this->where('name', $categories)->pluck('id');
        }
        return false;
    }
}
