{{-- Shared report switcher. --}}
<ul class="nav nav-pills mb-4">
    @foreach ([
        'summary' => ['label' => __('Stock summary'), 'route' => 'inventory.reports.summary'],
        'ledger' => ['label' => __('Stock ledger'), 'route' => 'inventory.reports.ledger'],
        'valuation' => ['label' => __('Valuation'), 'route' => 'inventory.reports.valuation'],
    ] as $key => $report)
        <li class="nav-item">
            <a class="nav-link {{ $active === $key ? 'active' : '' }}" href="{{ route($report['route']) }}">
                {{ $report['label'] }}
            </a>
        </li>
    @endforeach
</ul>
