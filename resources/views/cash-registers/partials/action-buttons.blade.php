<div class="action-buttons">
    <a href="{{ route('cash-registers.show', $register->id) }}" 
       class="btn modern-btn-sm modern-btn-info" 
       title="View Details">
        <i class="fas fa-eye"></i>
    </a>
    
    @if($register->status === 'open')
        <a href="{{ route('cash-registers.close', $register->id) }}" 
           class="btn modern-btn-sm modern-btn-warning" 
           title="Close Register">
            <i class="fas fa-lock"></i>
        </a>
        <button onclick="suspendRegister({{ $register->id }})" 
                class="btn modern-btn-sm modern-btn-secondary" 
                title="Suspend">
            <i class="fas fa-pause"></i>
        </button>
    @elseif($register->status === 'suspended')
        <button onclick="resumeRegister({{ $register->id }})" 
                class="btn modern-btn-sm modern-btn-success" 
                title="Resume">
            <i class="fas fa-play"></i>
        </button>
    @elseif($register->status === 'closed')
        @can('cash-register-delete')
            <button onclick="deleteRegister({{ $register->id }})" 
                    class="btn modern-btn-sm modern-btn-danger" 
                    title="Delete Register">
                <i class="fas fa-trash"></i>
            </button>
        @endcan
    @endif
    
    <button onclick="generateRegisterReport({{ $register->id }})" 
            class="btn modern-btn-sm modern-btn-success" 
            title="Generate Report">
        <i class="fas fa-file-alt"></i>
    </button>
</div>