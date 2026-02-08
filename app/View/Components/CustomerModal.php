<?php

namespace App\View\Components;

use Illuminate\View\Component;
use App\Models\Accounting\AccountGroup;

class CustomerModal extends Component
{
    public $customerGroups;

    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->customerGroups = $this->getCustomerAccountGroups();
    }

    /**
     * Get customer account groups (Sundry Debtors and its sub-groups)
     */
    protected function getCustomerAccountGroups()
    {
        // Get Sundry Debtors group
        $sundryDebtors = AccountGroup::where('code', 'SUNDRY-DEBTORS')->first();

        if (!$sundryDebtors) {
            return collect();
        }

        // Get Sundry Debtors and all its descendants
        $groups = collect([$sundryDebtors]);

        // Recursively get all child groups
        $this->getChildGroups($sundryDebtors, $groups);

        return $groups;
    }

    /**
     * Recursively get child groups
     */
    protected function getChildGroups(AccountGroup $parent, &$groups): void
    {
        foreach ($parent->children as $child) {
            $groups->push($child);
            $this->getChildGroups($child, $groups);
        }
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function render()
    {
        return view('components.customer-modal');
    }
}
