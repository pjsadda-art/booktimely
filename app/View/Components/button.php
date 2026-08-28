<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;
use Laravel\Paddle\Checkout as PaddleCheckout;

class button extends Component
{
    /**
     * Create a new component instance.
     */
    public function __construct(public PaddleCheckout $checkout)
    {
        //
    }

    /**
     * Get the view / view contents that represent the component.
     *
     * @return \Illuminate\View\View|string
     */
    public function render()
    {
        return view('cashier::components.button');
    }
}
