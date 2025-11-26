<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PriceController extends Controller
{
    public function convert()
    {
        $amount = (float) request('amount', 1);
        $rate = 3000;
        return ['amount'=>$amount, 'currency'=>'ETH', 'rate'=>$rate, 'gbp'=>$amount*$rate];
    }
}
