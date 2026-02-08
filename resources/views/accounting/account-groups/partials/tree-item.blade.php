@if($items && $items->count() > 0)
    @foreach($items as $item)
        @php
            $hasChildren = ($item->children && $item->children->count() > 0) || ($item->accounts && $item->accounts->count() > 0);
            $branchId = 'branch-' . $item->id;
        @endphp
        <div class="tree-item" style="margin-left: {{ $level * 15 }}px;">
            <div class="tree-item-header d-flex align-items-center">
                @if($hasChildren)
                    <span class="branch-toggle mr-1" data-target="{{ $branchId }}" style="cursor: pointer; width: 20px;">
                        <i class="fas fa-chevron-down toggle-icon"></i>
                    </span>
                @else
                    <span style="width: 20px;"></span>
                @endif
                <i class="fas fa-folder text-warning mr-1"></i>
                <strong>{{ $item->name }}</strong>
                <small class="text-muted ml-1">({{ $item->code }})</small>
                @if(!$item->is_system)
                    <a href="{{ route('accounting.account-groups.edit', $item) }}" class="btn btn-xs btn-link p-0 ml-2">
                        <i class="fas fa-edit text-warning"></i>
                    </a>
                @endif
            </div>

            <!-- Collapsible Content -->
            <div class="branch-content" id="{{ $branchId }}">
                <!-- Child Groups -->
                @if($item->children && $item->children->count() > 0)
                    @include('accounting.account-groups.partials.tree-item', ['items' => $item->children, 'level' => $level + 1])
                @endif

                <!-- Accounts under this group -->
                @if($item->accounts && $item->accounts->count() > 0)
                    @foreach($item->accounts as $account)
                        <div class="account-item" style="margin-left: {{ ($level + 1) * 15 }}px;">
                            <i class="fas fa-file-alt text-muted"></i>
                            <a href="{{ route('accounting.accounts.show', $account) }}">
                                {{ $account->code }} - {{ $account->name }}
                            </a>
                            @if(!$account->is_active)
                                <span class="badge badge-secondary">Inactive</span>
                            @endif
                        </div>
                    @endforeach
                @endif
            </div>
        </div>
    @endforeach
@endif
