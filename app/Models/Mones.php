<?php

namespace App\Models;

use App\Models\Customer;
use App\Models\KriotYomi;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Mones extends Model
{
    use HasFactory;
    protected $table = 'monim';
    // protected $appends = ['name', 'avg_qty'];

    // public function getNameAttribute($value)
    // {
    //     $str = '';
    //     $str .= (isset($this->hisCustomer->address) ? 'Address: ' . $this->hisCustomer->address : '');
    //     $str .= (isset($this->kriot->qty) ? 'qty: ' . $this->kriot->qty : '');
    //     $str .= (isset($this->kriot->real_qty) ? 'real_qty: ' . $this->kriot->real_qty : '');
    //     $str .= (isset($this->kriot->delta) ? 'delta: ' . $this->kriot->delta : '');
    //     $str .= (isset($this->kriot->delta) ? 'per_cent: ' . $this->kriot->per_cent : '');
    //     return $str;
    // }

    // public function getAvgQtyAttribute()
    // {
    //     if ( ! array_key_exists('avgQty', $this->relations)) {
    //         $this->load('avgQty');
    //     }

    //     $relation = $this->getRelation('avgQty')->first();

    //     return ($relation) ? $relation->aggregate : null;
    // }

    public function oneChildren()
    {
        return $this->hasMany(self::class, 'mone_av', 'mone')
                        ->whereRaw('mone <> mone_av');
    }

    public function _children()
    {
        return $this->oneChildren()->with('_children');
    }

    public function kriot()
    {
        return $this->hasMany(kriotYomi::class, 'mone', 'mone');
        // return $this->hasOne(kriotYomi::class, 'mone', 'mone')->ofMany('day_date', 'max');
    }

    public function hisCustomer()
    {
        return $this->hasOne(Customer::class, 'neches', 'neches');
    }

    // public function avgQty()
    // {
    //     return $this->kriot()
    //                 ->selectRaw('avg(qty) as avg_qty, avg(delta) as avg_delta, avg(delta) as avg_delta, avg(per_cent) as avg_per_cent, mone')
    //                 ->groupBy('mone');
    // }
}
