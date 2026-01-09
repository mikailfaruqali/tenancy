<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Workspaces</title>
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">

    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        html,
        body {
            height: 100%;
            width: 100%;
        }

        body {
            min-height: 100vh;
            background: #f1f5f9;
            font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            padding: 1.5rem;
        }

        .background {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background:
                radial-gradient(circle at 30% 20%, rgba(34, 197, 94, 0.06) 0%, transparent 50%),
                radial-gradient(circle at 70% 80%, rgba(59, 130, 246, 0.05) 0%, transparent 50%);
            pointer-events: none;
            z-index: 0;
        }

        .container {
            position: relative;
            z-index: 1;
            max-width: 700px;
            margin: 0 auto;
            padding-bottom: 4rem;
        }

        .header {
            display: flex;
            align-items: baseline;
            gap: 0.625rem;
            margin-bottom: 1.5rem;
        }

        h1 {
            font-size: 1.375rem;
            font-weight: 700;
            color: #0f172a;
            letter-spacing: -0.03em;
        }

        .tenant-count {
            font-size: 0.8125rem;
            font-weight: 500;
            color: #94a3b8;
        }

        .toolbar {
            display: flex;
            flex-direction: column;
            gap: 0.625rem;
            margin-bottom: 1.5rem;
        }

        @media (min-width: 480px) {
            .toolbar {
                flex-direction: row;
            }
        }

        .search-input {
            flex: 1;
            padding: 0.75rem 1rem;
            font-size: 1rem;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            background: white;
            color: #1e293b;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
        }

        .search-input:focus {
            outline: none;
            border-color: #22c55e;
            box-shadow: 0 0 0 3px rgba(34, 197, 94, 0.1);
        }

        .search-input::placeholder {
            color: #94a3b8;
        }

        .sort-select {
            padding: 0.75rem 1rem;
            font-size: 1rem;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            background: white;
            color: #1e293b;
            cursor: pointer;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
        }

        .sort-select:focus {
            outline: none;
            border-color: #22c55e;
        }

        .list {
            display: flex;
            flex-direction: column;
            gap: 0.625rem;
        }

        .list-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            background: white;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 1rem 1.25rem;
            transition: all 0.2s ease;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
        }

        .list-item:hover {
            border-color: #cbd5e1;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        }

        .list-item-left {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
            min-width: 0;
        }

        .tenant-name {
            font-size: 1.0625rem;
            font-weight: 700;
            color: #0f172a;
            letter-spacing: -0.02em;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .health-badges {
            display: flex;
            flex-wrap: wrap;
            gap: 0.375rem;
        }

        .badge {
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
            font-size: 0.6875rem;
            font-weight: 500;
            color: #64748b;
            background: #f8fafc;
            padding: 0.25rem 0.5rem;
            border-radius: 6px;
            border: 1px solid #e2e8f0;
        }

        .badge-value {
            font-weight: 700;
            color: #0f172a;
        }

        .open-button {
            font-size: 0.8125rem;
            font-weight: 600;
            color: white;
            background: linear-gradient(135deg, #22c55e 0%, #16a34a 100%);
            text-decoration: none;
            padding: 0.5rem 1rem;
            border-radius: 8px;
            transition: all 0.15s ease;
            flex-shrink: 0;
            box-shadow: 0 2px 6px rgba(34, 197, 94, 0.25);
        }

        .open-button:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 10px rgba(34, 197, 94, 0.3);
        }

        .pagination {
            display: flex;
            align-items: center;
            justify-content: center;
            flex-wrap: wrap;
            gap: 0.375rem;
            margin-top: 1.5rem;
        }

        .pagination-link {
            font-size: 0.8125rem;
            font-weight: 500;
            color: #475569;
            text-decoration: none;
            padding: 0.5rem 0.75rem;
            border-radius: 8px;
            background: white;
            border: 1px solid #e2e8f0;
            transition: all 0.15s ease;
        }

        .pagination-link:hover {
            background: #f8fafc;
            border-color: #cbd5e1;
        }

        .pagination-link.active {
            background: #0f172a;
            border-color: #0f172a;
            color: white;
        }

        .pagination-link.disabled {
            color: #cbd5e1;
            pointer-events: none;
        }

        .pagination-dots {
            color: #94a3b8;
            padding: 0.5rem 0.25rem;
            font-size: 0.875rem;
        }

        .empty-state {
            color: #64748b;
            font-size: 0.9375rem;
            text-align: center;
            padding: 3rem 1.5rem;
            background: white;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
        }
    </style>
</head>

<body>
    <div class="background"></div>

    <div class="container">
        <div class="header">
            <h1>Workspaces</h1>
            <span class="tenant-count">({{ $tenants->total() }})</span>
        </div>

        <form class="toolbar" method="GET" action="{{ route('tenancy.list.view') }}">
            <input type="text" name="search" class="search-input" placeholder="Search workspaces..."
                value="{{ request('search') }}">

            <select name="sort" class="sort-select" onchange="this.form.submit()">
                @foreach (config()->array('snawbar-tenancy.health_sort_options') as $key => $label)
                    <option value="{{ $key }}" {{ when(request('sort') === $key, 'selected') }}>
                        {{ $label }}</option>
                @endforeach
            </select>
        </form>

        @if ($tenants->isNotEmpty())
            <div class="list">
                @foreach ($tenants as $tenant)
                    <div class="list-item">
                        <div class="list-item-left">
                            <span class="tenant-name">{{ $tenant->subdomain }}</span>

                            @if (filled($tenant->health))
                                <div class="health-badges">
                                    @foreach ($tenant->health as $key => $value)
                                        <span class="badge">
                                            {{ $key }}
                                            <span class="badge-value">{{ formatHealthValue($value) }}</span>
                                        </span>
                                    @endforeach
                                </div>
                            @endif
                        </div>

                        <a href="//{{ $tenant->subdomain }}" target="_blank" class="open-button">Open</a>
                    </div>
                @endforeach
            </div>

            @if ($tenants->hasPages())
                <div class="pagination">
                    @if ($tenants->onFirstPage())
                        <span class="pagination-link disabled">Prev</span>
                    @else
                        <a href="{{ $tenants->appends(request()->query())->previousPageUrl() }}"
                            class="pagination-link">Prev</a>
                    @endif

                    @if ($tenants->lastPage() <= 7)
                        @foreach ($tenants->getUrlRange(1, $tenants->lastPage()) as $page => $url)
                            <a href="{{ $tenants->appends(request()->query())->url($page) }}"
                                class="pagination-link {{ when($page == $tenants->currentPage(), 'active') }}">{{ $page }}</a>
                        @endforeach
                    @else
                        @if ($tenants->currentPage() > 3)
                            <a href="{{ $tenants->appends(request()->query())->url(1) }}" class="pagination-link">1</a>
                            <span class="pagination-dots">...</span>
                        @endif

                        @foreach (range(max(1, $tenants->currentPage() - 1), min($tenants->lastPage(), $tenants->currentPage() + 1)) as $page)
                            <a href="{{ $tenants->appends(request()->query())->url($page) }}"
                                class="pagination-link {{ when($page == $tenants->currentPage(), 'active') }}">{{ $page }}</a>
                        @endforeach

                        @if ($tenants->currentPage() < $tenants->lastPage() - 2)
                            <span class="pagination-dots">...</span>
                            <a href="{{ $tenants->appends(request()->query())->url($tenants->lastPage()) }}"
                                class="pagination-link">{{ $tenants->lastPage() }}</a>
                        @endif
                    @endif

                    @if ($tenants->hasMorePages())
                        <a href="{{ $tenants->appends(request()->query())->nextPageUrl() }}"
                            class="pagination-link">Next</a>
                    @else
                        <span class="pagination-link disabled">Next</span>
                    @endif
                </div>
            @endif
        @else
            <div class="empty-state">No workspaces found</div>
        @endif
    </div>
</body>

</html>
