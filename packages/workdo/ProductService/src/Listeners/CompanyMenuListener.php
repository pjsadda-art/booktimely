<?php

namespace Workdo\ProductService\Listeners;

use App\Events\CompanyMenuEvent;

class CompanyMenuListener
{
    /**
     * Handle the event.
     */
    public function handle(CompanyMenuEvent $event): void
    {
        // This legacy module's "Inventory" entry is superseded by Inventory
        // V2 (app/Listeners/CompanyMenuListener.php), which registers its own
        // "Inventory" nav item pointing at the maintained system. Registering
        // both produced two identically-named "Inventory" entries — one of
        // them (this one) leading to broken/legacy pages — so this one is
        // intentionally unlinked rather than shown alongside it.
    }
}
