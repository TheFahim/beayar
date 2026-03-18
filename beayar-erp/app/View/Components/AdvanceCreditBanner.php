<?php

namespace App\View\Components;

use Illuminate\View\Component;
use Illuminate\View\View;

class AdvanceCreditBanner extends Component
{
    public int $quotationId;
    public bool $showApplyButton;

    /**
     * Create a new component instance.
     */
    public function __construct(int $quotationId, bool $showApplyButton = false)
    {
        $this->quotationId = $quotationId;
        $this->showApplyButton = $showApplyButton;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View
    {
        return view('tenant.bills.partials.advance-credit-banner');
    }
}
