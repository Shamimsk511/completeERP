@extends('layouts.emp')

@section('title', $employee->name . ' - ' . $employee->position . ' | Rahman Tiles and Sanitary')

@section('meta')
<meta name="description" content="{{ $employee->name }}, {{ $employee->position }} at Rahman Tiles and Sanitary, Shariatpur. Professional with {{ $employee->experience_years }} years experience in finance and accounting.">
<meta name="keywords" content="Rahman Tiles and Sanitary, {{ $employee->name }}, Accounts Manager, Finance Manager, Shariatpur, Bangladesh, Employee Profile">
<meta name="author" content="Rahman Tiles and Sanitary">
<meta property="og:title" content="{{ $employee->name }} - {{ $employee->position }} | Rahman Tiles and Sanitary">
<meta property="og:description" content="Professional profile of {{ $employee->name }}, {{ $employee->position }} at Rahman Tiles and Sanitary, Shariatpur, Bangladesh.">
<meta property="og:url" content="{{ url()->current() }}">
<meta property="og:type" content="profile">
<meta name="twitter:card" content="summary">
<meta name="twitter:title" content="{{ $employee->name }} - {{ $employee->position }}">
<meta name="twitter:description" content="Professional profile of {{ $employee->name }} at Rahman Tiles and Sanitary">
<link rel="canonical" href="{{ url()->current() }}">
@endsection

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Breadcrumb -->
    <nav class="mb-8 text-sm text-gray-600">
        <a href="{{ url('/') }}" class="hover:text-blue-600">Home</a> 
        <span class="mx-2">></span>
        <span class="mx-2">></span>
        <span class="text-gray-900">{{ $employee->name }}</span>
    </nav>

    <!-- Employee Header -->
     <div class="bg-white rounded-lg shadow-lg p-8 mb-8">
        <div class="flex flex-col lg:flex-row items-start lg:items-center gap-8">
            <div class="w-32 h-32 bg-gray-200 rounded-full flex items-center justify-center overflow-hidden">
                @if(isset($employee->profile_picture) && $employee->profile_picture)
                    <img src="{{ asset($employee->profile_picture) }}" 
                         alt="{{ $employee->name }}" 
                         class="w-full h-full object-cover rounded-full"
                         onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                    <div class="w-full h-full flex items-center justify-center" style="display: none;">
                        <svg class="w-16 h-16 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"></path>
                        </svg>
                    </div>
                @else
                    <svg class="w-16 h-16 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"></path>
                    </svg>
                @endif
            </div>
            
            <div class="flex-1">
                <h1 class="text-4xl font-bold text-gray-900 mb-2">{{ $employee->name }}</h1>
                <h2 class="text-2xl text-blue-600 font-semibold mb-4">{{ $employee->position }}</h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-gray-700">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 mr-2 text-gray-500" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"></path>
                        </svg>
                        <span>{{ $employee->location }}</span>
                    </div>
                    
                    <div class="flex items-center">
                        <svg class="w-5 h-5 mr-2 text-gray-500" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z"></path>
                            <path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z"></path>
                        </svg>
                        <a href="mailto:{{ $employee->email }}" class="text-blue-600 hover:underline">{{ $employee->email }}</a>
                    </div>
                    
                    <div class="flex items-center">
                        <svg class="w-5 h-5 mr-2 text-gray-500" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M2 3a1 1 0 011-1h2.153a1 1 0 01.986.836l.74 4.435a1 1 0 01-.54 1.06l-1.548.773a11.037 11.037 0 006.105 6.105l.774-1.548a1 1 0 011.059-.54l4.435.74a1 1 0 01.836.986V17a1 1 0 01-1 1h-2C7.82 18 2 12.18 2 5V3z"></path>
                        </svg>
                        <span>{{ $employee->phone }}</span>
                    </div>
                    
                    <div class="flex items-center">
                        <svg class="w-5 h-5 mr-2 text-gray-500" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"></path>
                        </svg>
                        <span>{{ $employee->experience_years }} years experience</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Main Content -->
        <div class="lg:col-span-2 space-y-8">
            <!-- About Section -->
            <div class="bg-white rounded-lg shadow-lg p-6">
                <h3 class="text-2xl font-bold text-gray-900 mb-4">About</h3>
                <p class="text-gray-700 leading-relaxed">{{ $employee->about }}</p>
            </div>

            <!-- Responsibilities -->
            <div class="bg-white rounded-lg shadow-lg p-6">
                <h3 class="text-2xl font-bold text-gray-900 mb-4">Key Responsibilities</h3>
                <ul class="space-y-3">
                    @foreach($employee->responsibilities as $responsibility)
                    <li class="flex items-start">
                        <svg class="w-5 h-5 text-green-500 mr-3 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                        </svg>
                        <span class="text-gray-700">{{ $responsibility }}</span>
                    </li>
                    @endforeach
                </ul>
            </div>

            <!-- Skills -->
            <div class="bg-white rounded-lg shadow-lg p-6">
                <h3 class="text-2xl font-bold text-gray-900 mb-4">Skills & Expertise</h3>
                <div class="flex flex-wrap gap-2">
                    @foreach($employee->skills as $skill)
                    <span class="px-3 py-1 bg-blue-100 text-blue-800 rounded-full text-sm">{{ $skill }}</span>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="space-y-8">
            <!-- Education -->
            <div class="bg-white rounded-lg shadow-lg p-6">
                <h3 class="text-xl font-bold text-gray-900 mb-4">Education</h3>
                <div class="space-y-2">
                    <h4 class="font-semibold text-gray-900">{{ $employee->education['degree'] }}</h4>
                    <p class="text-gray-700">{{ $employee->education['major'] }}</p>
                    <p class="text-gray-600">{{ $employee->education['university'] }}</p>
                    <p class="text-gray-600">CGPA: {{ $employee->education['cgpa'] }}</p>
                    <p class="text-gray-600">Graduated: {{ $employee->education['graduation_year'] }}</p>
                </div>
            </div>

            <!-- Languages -->
            <div class="bg-white rounded-lg shadow-lg p-6">
                <h3 class="text-xl font-bold text-gray-900 mb-4">Languages</h3>
                <ul class="space-y-2">
                    @foreach($employee->languages as $language)
                    <li class="text-gray-700">{{ $language }}</li>
                    @endforeach
                </ul>
            </div>

            <!-- Achievements -->
            <div class="bg-white rounded-lg shadow-lg p-6">
                <h3 class="text-xl font-bold text-gray-900 mb-4">Achievements & Activities</h3>
                <ul class="space-y-2">
                    @foreach($employee->achievements as $achievement)
                    <li class="flex items-start">
                        <svg class="w-4 h-4 text-yellow-500 mr-2 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                        </svg>
                        <span class="text-gray-700 text-sm">{{ $achievement }}</span>
                    </li>
                    @endforeach
                </ul>
            </div>

            <!-- Company Info -->
            <div class="bg-blue-50 rounded-lg p-6">
                <h3 class="text-xl font-bold text-gray-900 mb-4">Company Information</h3>
                <div class="space-y-2">
                    <p class="font-semibold text-gray-900">{{ $employee->company }}</p>
                    <p class="text-gray-700">{{ $employee->location }}</p>
                    <p class="text-gray-600">Department: {{ $employee->department }}</p>
                    <p class="text-gray-600">Since: {{ \Carbon\Carbon::parse($employee->start_date)->format('F Y') }}</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- JSON-LD Structured Data -->
<script type="application/ld+json">
{
    "@context": "https://schema.org",
    "@type": "Person",
    "name": "{{ $employee->name }}",
    "jobTitle": "{{ $employee->position }}",
    "worksFor": {
        "@type": "Organization",
        "name": "{{ $employee->company }}",
        "address": {
            "@type": "PostalAddress",
            "addressLocality": "Shariatpur",
            "addressRegion": "Dhaka",
            "addressCountry": "Bangladesh"
        }
    },
    "email": "{{ $employee->email }}",
    "telephone": "***********",
    "address": {
        "@type": "PostalAddress",
        "streetAddress": "{{ $employee->address }}",
        "addressCountry": "Bangladesh"
    },
    "alumniOf": {
        "@type": "CollegeOrUniversity",
        "name": "{{ $employee->education['university'] }}"
    },
    "knowsAbout": [
        @foreach($employee->skills as $index => $skill)
        "{{ $skill }}"{{ $index < count($employee->skills) - 1 ? ',' : '' }}
        @endforeach
    ]
}
</script>
@endsection