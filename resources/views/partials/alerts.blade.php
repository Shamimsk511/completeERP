@if(session('success'))
    <div class="alert modern-alert modern-alert-success alert-dismissible" role="alert">
        <div class="alert-content">
            <i class="fas fa-check-circle alert-icon"></i>
            <div class="alert-message">
                <strong>Success!</strong> {{ session('success') }}
            </div>
            <button type="button" class="alert-close" onclick="this.parentElement.parentElement.style.display='none'">
                <i class="fas fa-times"></i>
            </button>
        </div>
    </div>
@endif

@if(session('error'))
    <div class="alert modern-alert modern-alert-error alert-dismissible" role="alert">
        <div class="alert-content">
            <i class="fas fa-exclamation-circle alert-icon"></i>
            <div class="alert-message">
                <strong>Error!</strong> {{ session('error') }}
            </div>
            <button type="button" class="alert-close" onclick="this.parentElement.parentElement.style.display='none'">
                <i class="fas fa-times"></i>
            </button>
        </div>
    </div>
@endif