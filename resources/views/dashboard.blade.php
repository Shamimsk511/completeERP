@extends('layouts.modern-admin')

@section('title', 'ERP Dashboard')
@section('page_title', 'Dashboard Command Center')

@section('header_actions')
<div class="dash-head-actions">
    <form action="{{ route('dashboard') }}" method="GET" class="form-inline dash-filter-form">
        <select name="time_filter" class="form-control modern-select mr-2" onchange="toggleCustomDateInputs(this.value)">
            <option value="month" {{ request('time_filter', 'month') == 'month' ? 'selected' : '' }}>This Month</option>
            <option value="year" {{ request('time_filter') == 'year' ? 'selected' : '' }}>This Year</option>
            <option value="custom" {{ request('time_filter') == 'custom' ? 'selected' : '' }}>Custom Range</option>
        </select>
        <div id="custom-date-inputs" style="{{ request('time_filter') == 'custom' ? '' : 'display:none;' }}">
            <input type="date" name="start_date" class="form-control modern-input mr-2" value="{{ request('start_date', now()->startOfMonth()->format('Y-m-d')) }}">
            <input type="date" name="end_date" class="form-control modern-input mr-2" value="{{ request('end_date', now()->format('Y-m-d')) }}">
        </div>
        <button type="submit" class="btn modern-btn modern-btn-primary"><i class="fas fa-filter"></i> Apply</button>
    </form>
    <button type="button" class="btn modern-btn modern-btn-primary dash-focus-btn" id="dashboardFocusToggle" title="Focus mode (hide top and side bars)">
        <i class="fas fa-expand-arrows-alt"></i> Focus
    </button>
    <div class="dash-live-badge"><span class="pulse-dot"></span> Live <small id="live-last-sync">{{ now()->format('h:i:s A') }}</small></div>
</div>
@stop

@section('page_content')
<div class="dash-shell">
    <button type="button" class="btn modern-btn modern-btn-danger dash-focus-exit-btn" id="dashboardFocusFloatingExit">
        <i class="fas fa-compress-arrows-alt"></i> Exit Focus
    </button>
    <div class="row">
        <div class="col-xl-4 mb-3">
            <div class="dash-card clock-card">
                <div class="dash-title"><h3><i class="fas fa-clock"></i> Analog Clock</h3><span class="dash-pill">{{ now()->format('l') }}</span></div>
                <div class="clock-wrap">
                    <div class="analog-clock">
                        <div class="hand hour" id="clockHourHand"></div>
                        <div class="hand minute" id="clockMinuteHand"></div>
                        <div class="hand second" id="clockSecondHand"></div>
                        <div class="center-dot"></div>
                        @for($i=1;$i<=12;$i++)<span class="num n{{ $i }}">{{ $i }}</span>@endfor
                    </div>
                </div>
                <div class="clock-txt"><strong id="clockDigital">{{ now()->format('h:i:s A') }}</strong><span id="clockDate">{{ now()->format('d M Y') }}</span></div>
                <div class="clock-cal-head">
                    <h4><i class="fas fa-calendar-alt"></i> Mini Calendar</h4>
                    <div class="calendar-nav-wrap">
                        <button type="button" class="calendar-nav-btn" id="calendarPrevBtn" aria-label="Previous month">
                            <i class="fas fa-chevron-left" aria-hidden="true"></i>
                        </button>
                        <span class="dash-pill" id="calendarMonthLabel">{{ now()->format('F Y') }}</span>
                        <button type="button" class="calendar-nav-btn" id="calendarNextBtn" aria-label="Next month">
                            <i class="fas fa-chevron-right" aria-hidden="true"></i>
                        </button>
                    </div>
                </div>
                <div class="mini-cal mini-cal-under-clock">
                    <div class="wds"><span>Sun</span><span>Mon</span><span>Tue</span><span>Wed</span><span>Thu</span><span>Fri</span><span>Sat</span></div>
                    <div id="miniCalendarDays" class="days"></div>
                </div>
            </div>

            <div class="dash-card mb-3">
                <div class="dash-title"><h3><i class="fas fa-user-check"></i> Online Team</h3><span id="live-online-count" class="dash-pill ok">{{ $onlineSnapshot['count'] }}</span></div>
                <ul class="simple-list" id="online-users-list">
                    @forelse($onlineSnapshot['users'] as $onlineUser)
                    <li><span class="dot"></span><div><strong>{{ $onlineUser['name'] }}</strong><small>{{ $onlineUser['last_seen'] }}</small></div></li>
                    @empty
                    <li class="empty">No active users in the last 5 minutes.</li>
                    @endforelse
                </ul>
            </div>

            <div class="dash-card chatbox-card">
                <div class="dash-title">
                    <h3><i class="fas fa-comments"></i> Team Chat</h3>
                    @if(auth()->user() && auth()->user()->can('chat-access'))
                    <a href="{{ route('chat.index') }}">Open full chat</a>
                    @else
                    <span class="dash-pill">Preview</span>
                    @endif
                </div>
                <div class="chatbox-toolbar">
                    <select id="dashChatTarget" class="form-control modern-select">
                        <option value="all">Broadcast channel</option>
                        @foreach($chatUsers as $chatUser)
                        <option value="{{ $chatUser['id'] }}">{{ $chatUser['name'] }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="dash-chat-messages" id="dashChatMessages">
                    <div class="empty">Loading conversation...</div>
                </div>
                <form id="dashChatForm" class="dash-chat-form">
                    @csrf
                    <input type="text" id="dashChatInput" class="form-control modern-input" placeholder="Type message and press Enter">
                    <button type="submit" class="btn modern-btn modern-btn-primary">Send</button>
                </form>
            </div>
        </div>

        <div class="col-xl-8 mb-3">
            <div class="kpi-grid">
                <div class="kpi inv"><i class="fas fa-file-invoice"></i><div><h4 id="live-invoice-count">{{ $todayStats['invoice_count'] }}</h4><p>Invoices Today</p><small id="live-invoice-amount">Tk {{ number_format($todayStats['invoice_amount'],2) }}</small></div></div>
                <div class="kpi pur"><i class="fas fa-shopping-basket"></i><div><h4 id="live-purchase-count">{{ $todayStats['purchase_count'] }}</h4><p>Purchases Today</p><small id="live-purchase-amount">Tk {{ number_format($todayStats['purchase_amount'],2) }}</small></div></div>
                <div class="kpi del"><i class="fas fa-truck-loading"></i><div><h4 id="live-other-delivery-count">{{ $todayStats['other_delivery_count'] }}</h4><p>Other Deliveries</p><small>Auto update</small></div></div>
                <div class="kpi emp"><i class="fas fa-users"></i><div><h4 id="live-present-count">{{ $employeeSummary['present'] }}</h4><p>Employees Present</p><small id="employeePresenceRatio">{{ $employeeSummary['present'] }}/{{ $employeeSummary['active'] }}</small></div></div>
            </div>

            <div class="chip-grid">
                <div class="chip"><span>Selected Invoices</span><strong>{{ number_format($totalInvoices) }}</strong></div>
                <div class="chip"><span>Selected Sales</span><strong>Tk {{ number_format($totalInvoiceAmount,2) }}</strong></div>
                <div class="chip"><span>Total Due</span><strong>Tk {{ number_format($totalBalanceDue,2) }}</strong></div>
                <div class="chip"><span>Low Stock</span><strong>{{ $insights['low_stock_count'] }}</strong></div>
            </div>

            <div class="row">
                <div class="col-lg-6 mb-3">
                    <div class="dash-card">
                        <div class="dash-title"><h3><i class="fas fa-wave-square"></i> Hourly Sales Curve</h3><span class="dash-pill">Today</span></div>
                        <div class="curve-fixed-height">
                            <canvas id="hourlySalesChart"></canvas>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6 mb-3">
                    <div class="dash-card">
                        <div class="dash-title"><h3><i class="fas fa-chart-line"></i> Monthly Sales Curve</h3><span class="dash-pill {{ $monthlyTrend['is_up'] ? 'ok' : 'danger' }}">{{ $monthlyTrend['growth_percent'] === null ? 'New cycle' : number_format(abs($monthlyTrend['growth_percent']),1).'%' }}</span></div>
                        <div class="curve-fixed-height">
                            <canvas id="monthlySalesChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-6 mb-3">
                    <div class="dash-card">
                        <div class="dash-title"><h3><i class="fas fa-tasks"></i> My To-Do</h3><span class="dash-pill">Persistent</span></div>
                        <div class="todo-add"><input type="text" id="todoInput" class="form-control modern-input" placeholder="Add a task..."><button type="button" id="todoAddBtn" class="btn modern-btn modern-btn-primary">Add</button></div>
                        <ul id="todoList" class="todo-list"></ul>
                    </div>
                </div>
                <div class="col-lg-6 mb-3">
                    <div class="dash-card">
                        <div class="dash-title"><h3><i class="fas fa-user-tie"></i> Present Employees</h3><span class="dash-pill">{{ $employeeSummary['present'] }}/{{ $employeeSummary['active'] }}</span></div>
                        <ul class="simple-list" id="present-employees-list">
                            @forelse($presentEmployees as $employee)
                            <li><span class="dot blue"></span><div><strong>{{ $employee['name'] }}</strong><small>Checked in today</small></div></li>
                            @empty
                            <li class="empty">No present employee entries for today.</li>
                            @endforelse
                        </ul>
                        <div class="mini-links"><a href="{{ route('hr.attendance.index') }}"><i class="fas fa-calendar-check"></i> Attendance</a><a href="{{ route('hr.employees.index') }}"><i class="fas fa-users-cog"></i> Employees</a></div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-7 mb-3">
                    <div class="dash-card">
                        <div class="dash-title"><h3><i class="fas fa-file-invoice-dollar"></i> Latest Invoices</h3><a href="{{ route('invoices.index') }}">View all</a></div>
                        <div class="table-responsive">
                            <table class="table table-sm mb-0 dash-table">
                                <thead><tr><th>#</th><th>Customer</th><th>Date</th><th>Status</th><th class="text-right">Total</th></tr></thead>
                                <tbody>
                                    @forelse($latestInvoices as $invoice)
                                    <tr>
                                        <td>{{ $invoice->invoice_number }}</td>
                                        <td>{{ optional($invoice->customer)->name ?? 'Walk-in' }}</td>
                                        <td>{{ optional($invoice->invoice_date)->format('d M, Y') }}</td>
                                        <td><span class="tag {{ $invoice->payment_status === 'paid' ? 'ok' : ($invoice->payment_status === 'partial' ? 'warn' : 'danger') }}">{{ ucfirst($invoice->payment_status) }}</span></td>
                                        <td class="text-right">{{ number_format($invoice->total,2) }}</td>
                                    </tr>
                                    @empty
                                    <tr><td colspan="5" class="text-center text-muted py-3">No invoices available.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="col-lg-5 mb-3">
                    <div class="dash-card">
                        <div class="dash-title"><h3><i class="fas fa-lightbulb"></i> Useful Info</h3><span class="dash-pill">Actionable</span></div>
                        <div class="insight"><label>Due Customers</label><strong>{{ number_format($insights['due_customer_count']) }}</strong></div>
                        <div class="insight"><label>Unpaid / Partial</label><strong>{{ number_format($insights['unpaid_invoice_count']) }}</strong></div>
                        <div class="insight"><label>Total Stock</label><strong>{{ number_format($totalProductQuantity,2) }}</strong></div>
                        <div class="mini-links"><a href="{{ route('invoices.create') }}"><i class="fas fa-plus-circle"></i> New Invoice</a><a href="{{ route('purchases.create') }}"><i class="fas fa-dolly"></i> New Purchase</a><a href="{{ route('other-deliveries.create') }}"><i class="fas fa-truck"></i> Other Delivery</a></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@stop

@section('additional_css')
<style>
    .dash-head-actions{display:flex;align-items:center;gap:8px;flex-wrap:wrap}
    .dash-filter-form{display:flex;align-items:center;gap:6px;flex-wrap:wrap}
    .dash-focus-btn{padding:6px 10px;font-size:11px;font-weight:800}
    .dash-live-badge{display:inline-flex;align-items:center;gap:7px;background:rgba(255,255,255,.78);border:1px solid rgba(15,23,42,.1);border-radius:999px;padding:5px 10px;font-size:11px;font-weight:800}
    .dash-live-badge small{font-size:10px;color:#475569}
    .pulse-dot{width:7px;height:7px;border-radius:50%;background:#22c55e;animation:pulse 1.8s infinite}
    @keyframes pulse{0%{box-shadow:0 0 0 0 rgba(34,197,94,.7)}70%{box-shadow:0 0 0 9px rgba(34,197,94,0)}100%{box-shadow:0 0 0 0 rgba(34,197,94,0)}}
    .dash-shell{position:relative;padding:10px;border-radius:14px;overflow:hidden;min-height:calc(100vh - 145px);border:1px solid rgba(255,255,255,.5);background:radial-gradient(circle at 12% 10%,rgba(255,255,255,.58),transparent 38%),radial-gradient(circle at 90% 12%,rgba(255,255,255,.35),transparent 26%),linear-gradient(135deg,var(--app-bg,#eef2ff),var(--app-surface,#f8fafc))}
    .dash-card{background:linear-gradient(155deg,rgba(255,255,255,.9),rgba(248,250,252,.86));border:1px solid rgba(255,255,255,.58);border-radius:13px;padding:9px;backdrop-filter:blur(8px);box-shadow:0 7px 16px rgba(15,23,42,.1);height:auto}
    .dash-title{display:flex;align-items:center;justify-content:space-between;gap:8px;margin-bottom:8px}
    .dash-title h3{margin:0;font-size:13px;font-weight:800;color:#0f172a}
    .dash-title h3 i{margin-right:6px;color:#2563eb}
    .dash-title a{font-size:11px;font-weight:700;color:#1d4ed8;text-decoration:none}
    .dash-pill{display:inline-flex;align-items:center;border-radius:999px;font-size:10px;font-weight:800;padding:2px 7px;border:1px solid rgba(37,99,235,.22);color:#1e40af;background:rgba(191,219,254,.45)}
    .dash-pill.ok{border-color:rgba(16,185,129,.28);color:#047857;background:rgba(167,243,208,.44)}
    .dash-pill.danger{border-color:rgba(244,63,94,.25);color:#be123c;background:rgba(251,207,232,.45)}
    .kpi-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:7px;margin-bottom:9px}
    .kpi{position:relative;border-radius:12px;padding:9px 9px 7px 42px;min-height:78px;border:1px solid rgba(255,255,255,.4);background:linear-gradient(160deg,rgba(255,255,255,.86),rgba(248,250,252,.72));box-shadow:0 6px 12px rgba(2,6,23,.09)}
    .kpi>i{position:absolute;left:10px;top:10px;width:26px;height:26px;border-radius:8px;display:inline-flex;align-items:center;justify-content:center;color:#fff;font-size:11px}
    .kpi.inv>i{background:linear-gradient(135deg,#2563eb,#3b82f6)}
    .kpi.pur>i{background:linear-gradient(135deg,#059669,#10b981)}
    .kpi.del>i{background:linear-gradient(135deg,#ea580c,#f97316)}
    .kpi.emp>i{background:linear-gradient(135deg,#6d28d9,#8b5cf6)}
    .kpi h4{margin:0;font-size:19px;font-weight:800;color:#0f172a;line-height:1.1}
    .kpi p{margin:1px 0;color:#334155;font-size:11px;font-weight:700}
    .kpi small{color:#64748b;font-size:10px;font-weight:700}
    .chip-grid{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:7px;margin-bottom:9px}
    .chip{border-radius:10px;padding:7px 8px;background:rgba(255,255,255,.82);border:1px solid rgba(255,255,255,.55)}
    .chip span{display:block;font-size:10px;color:#475569;font-weight:700;margin-bottom:1px}
    .chip strong{color:#0f172a;font-size:13px;font-weight:800}
    .curve-fixed-height{height:205px;position:relative}
    .curve-fixed-height canvas{width:100%!important;height:100%!important}
    .clock-wrap{display:flex;justify-content:center;align-items:center}
    .analog-clock{width:195px;height:195px;max-width:100%;position:relative;border-radius:50%;border:8px solid rgba(255,255,255,.5);background:radial-gradient(circle at 35% 30%,rgba(255,255,255,.74),rgba(255,255,255,.08) 58%),conic-gradient(from 130deg,rgba(37,99,235,.26),rgba(16,185,129,.24),rgba(14,116,144,.26),rgba(37,99,235,.26));box-shadow:inset 0 8px 20px rgba(15,23,42,.22),0 10px 20px rgba(2,6,23,.24)}
    .analog-clock:before{content:"";position:absolute;inset:12px;border-radius:50%;background:radial-gradient(circle at 55% 40%,rgba(255,255,255,.92),rgba(226,232,240,.6));border:1px solid rgba(15,23,42,.08)}
    .num{position:absolute;left:50%;top:50%;font-size:11px;font-weight:800;color:#0f172a;z-index:3}
    .n1{transform:translate(45px,-77px)}.n2{transform:translate(74px,-50px)}.n3{transform:translate(82px,-4px)}.n4{transform:translate(72px,40px)}.n5{transform:translate(45px,70px)}.n6{transform:translate(-4px,81px)}.n7{transform:translate(-53px,70px)}.n8{transform:translate(-80px,39px)}.n9{transform:translate(-88px,-4px)}.n10{transform:translate(-79px,-50px)}.n11{transform:translate(-52px,-78px)}.n12{transform:translate(-5px,-88px)}
    .hand{position:absolute;left:50%;bottom:50%;transform-origin:bottom center;z-index:4;border-radius:999px}
    .hand.hour{width:5px;height:43px;margin-left:-2.5px;background:linear-gradient(180deg,#0f172a,#334155)}
    .hand.minute{width:3px;height:60px;margin-left:-1.5px;background:linear-gradient(180deg,#1d4ed8,#2563eb)}
    .hand.second{width:1.5px;height:68px;margin-left:-.75px;background:linear-gradient(180deg,#ef4444,#be123c)}
    .center-dot{position:absolute;left:50%;top:50%;width:10px;height:10px;margin-left:-5px;margin-top:-5px;border-radius:50%;background:linear-gradient(135deg,#0f172a,#1e293b);border:1px solid #f8fafc;z-index:6}
    .clock-txt{margin-top:6px;text-align:center}
    .clock-txt strong{display:block;font-size:14px;font-weight:800;color:#0f172a}
    .clock-txt span{display:block;font-size:10px;font-weight:700;color:#475569}
    .clock-cal-head{display:flex;align-items:center;justify-content:space-between;gap:6px;margin-top:8px;margin-bottom:7px;padding-top:7px;border-top:1px solid rgba(15,23,42,.12)}
    .clock-cal-head h4{margin:0;font-size:12px;font-weight:800;color:#0f172a}
    .clock-cal-head h4 i{margin-right:6px;color:#2563eb}
    .calendar-nav-wrap{display:flex;align-items:center;gap:6px}
    .calendar-nav-btn{width:24px;height:24px;display:inline-flex;align-items:center;justify-content:center;border-radius:999px;border:1px solid rgba(37,99,235,.25);background:rgba(191,219,254,.38);color:#1e40af;padding:0;cursor:pointer;line-height:1}
    .calendar-nav-btn:hover{background:rgba(147,197,253,.45)}
    .calendar-nav-btn:focus{outline:none}
    .calendar-nav-btn:focus-visible{box-shadow:0 0 0 2px rgba(37,99,235,.25)}
    .mini-cal{width:280px;max-width:100%;min-height:280px;margin:0 auto;border-radius:10px;padding:6px 5px;background:linear-gradient(160deg,rgba(248,250,252,.9),rgba(241,245,249,.74));border:1px solid rgba(148,163,184,.22)}
    .wds,.days{display:grid;grid-template-columns:repeat(7,minmax(0,1fr));gap:2px}
    .wds span{text-align:center;font-size:8px;font-weight:800;color:#64748b}
    .mini-cal-under-clock{margin-top:0}
    .days{margin-top:5px}
    .d{height:23px;border-radius:6px;display:flex;align-items:center;justify-content:center;font-size:10px;font-weight:700;color:#1e293b;background:rgba(255,255,255,.62)}
    .d.m{color:#94a3b8;background:rgba(241,245,249,.72)}
    .d.t{color:#fff;background:linear-gradient(135deg,#2563eb,#0ea5e9);box-shadow:0 4px 8px rgba(37,99,235,.36)}
    .simple-list,.todo-list{list-style:none;margin:0;padding:0}
    .simple-list li{display:flex;align-items:center;gap:8px;padding:7px 2px;border-top:1px solid rgba(15,23,42,.08)}
    .simple-list li:first-child{border-top:0}
    .simple-list strong{display:block;font-size:12px;font-weight:800;color:#0f172a}
    .simple-list small{display:block;font-size:10px;color:#64748b;font-weight:600}
    #online-users-list{height:auto;max-height:none;overflow:visible}
    .dot{width:8px;height:8px;border-radius:50%;background:#22c55e;box-shadow:0 0 0 3px rgba(34,197,94,.14);flex:0 0 auto}
    .dot.blue{background:#3b82f6;box-shadow:0 0 0 3px rgba(59,130,246,.14)}
    .empty{color:#64748b;font-size:11px;font-weight:700;padding:10px 2px}
    .todo-add{display:flex;gap:6px;margin-bottom:8px}
    .todo-list li{display:flex;align-items:center;justify-content:space-between;gap:8px;border-top:1px solid rgba(15,23,42,.08);padding:7px 2px}
    .todo-list li:first-child{border-top:0}
    .todo-txt{font-size:12px;font-weight:700;color:#0f172a;line-height:1.35}
    .todo-done{border:1px solid rgba(16,185,129,.26);background:rgba(16,185,129,.16);color:#047857;border-radius:8px;font-size:10px;font-weight:800;padding:4px 7px;cursor:pointer}
    .mini-links{display:flex;flex-wrap:wrap;gap:6px;margin-top:8px}
    .mini-links a{display:inline-flex;align-items:center;gap:5px;font-size:11px;font-weight:700;color:#1e40af;text-decoration:none;padding:4px 7px;border-radius:8px;background:rgba(191,219,254,.4);border:1px solid rgba(59,130,246,.2)}
    .dash-table th{border-top:0;border-bottom:1px solid rgba(15,23,42,.12);color:#475569;font-size:10px;font-weight:800;text-transform:uppercase}
    .dash-table td{vertical-align:middle;border-top:1px solid rgba(15,23,42,.08);color:#0f172a;font-size:11px;font-weight:700}
    .tag{display:inline-flex;align-items:center;border-radius:999px;padding:3px 7px;font-size:10px;font-weight:800}
    .tag.ok{color:#047857;background:rgba(16,185,129,.18);border:1px solid rgba(16,185,129,.3)}
    .tag.warn{color:#b45309;background:rgba(245,158,11,.2);border:1px solid rgba(245,158,11,.33)}
    .tag.danger{color:#b91c1c;background:rgba(239,68,68,.2);border:1px solid rgba(239,68,68,.32)}
    .insight{border-top:1px solid rgba(15,23,42,.08);padding:8px 2px;display:flex;justify-content:space-between;align-items:center}
    .insight:first-of-type{border-top:0}
    .insight label{margin:0;color:#475569;font-size:11px;font-weight:700}
    .insight strong{font-size:13px;font-weight:900;color:#0f172a}
    .chatbox-toolbar{margin-bottom:8px}
    .dash-chat-messages{height:198px;overflow-y:auto;border:1px solid rgba(148,163,184,.25);background:linear-gradient(180deg,rgba(255,255,255,.88),rgba(248,250,252,.86));border-radius:10px;padding:8px}
    .dash-chat-row{display:flex;flex-direction:column;max-width:88%;margin-bottom:7px}
    .dash-chat-row.mine{margin-left:auto;align-items:flex-end}
    .dash-chat-row.their{margin-right:auto;align-items:flex-start}
    .dash-chat-bubble{padding:6px 9px;border-radius:10px;background:#e2e8f0;color:#0f172a;font-size:11px;font-weight:600;line-height:1.35}
    .dash-chat-row.mine .dash-chat-bubble{background:#bfdbfe}
    .dash-chat-meta{font-size:9px;color:#64748b;margin-top:2px}
    .dash-chat-form{display:flex;gap:6px;margin-top:8px}
    .dash-chat-form .modern-input{font-size:12px;padding:6px 8px}
    .dash-chat-form .btn{padding:6px 10px;font-size:11px;font-weight:700}
    .dash-focus-exit-btn{display:none;position:fixed;top:12px;right:12px;z-index:1110;padding:8px 12px;font-size:11px;font-weight:800}
    body.dashboard-focus-mode .main-header,
    body.dashboard-focus-mode .main-sidebar,
    body.dashboard-focus-mode .main-footer,
    body.dashboard-focus-mode .sidebar-edge-toggle{display:none !important}
    body.dashboard-focus-mode .content-wrapper{margin-left:0 !important;min-height:100vh !important}
    body.dashboard-focus-mode .content-header{display:none !important}
    body.dashboard-focus-mode .content{padding-top:10px !important}
    body.dashboard-focus-mode .dash-focus-exit-btn{display:inline-flex !important;align-items:center;gap:6px}
    @media (max-width:1399.98px){.chip-grid{grid-template-columns:repeat(2,minmax(0,1fr))}}
    @media (max-width:1199.98px){.kpi-grid{grid-template-columns:repeat(1,minmax(0,1fr))}}
    @media (max-width:991.98px){.dash-shell{padding:9px;min-height:auto}.curve-fixed-height{height:190px}.dash-chat-messages{height:170px}.analog-clock{width:180px;height:180px}.mini-cal{width:240px;min-height:240px}}
</style>
@stop

@section('js')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
(function(){
    const seed={liveUrl:@json(route('dashboard.live')),userId:@json(auth()->id()),todayStats:@json($todayStats),onlineSnapshot:@json($onlineSnapshot),presentEmployees:@json($presentEmployees),employeeSummary:@json($employeeSummary),hourlySales:@json($hourlySales),monthlySales:@json($monthlySales),insights:@json($insights),chat:{access:@json(auth()->user() ? auth()->user()->can('chat-access') : false),canSend:@json(auth()->user() ? auth()->user()->can('chat-message-send') : false),messagesUrlBase:@json(url('chat/messages')),sendUrl:@json(route('chat.send')),csrf:@json(csrf_token())}};
    window.toggleCustomDateInputs=function(v){const el=document.getElementById('custom-date-inputs');if(el){el.style.display=v==='custom'?'inline-flex':'none';}};
    const n=v=>Number(v||0).toLocaleString(),m=v=>'Tk '+Number(v||0).toLocaleString(undefined,{minimumFractionDigits:2,maximumFractionDigits:2});
    const esc=v=>String(v??'').replace(/[&<>"']/g,ch=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[ch]));
    let calendarCursor=new Date(new Date().getFullYear(),new Date().getMonth(),1);
    function clock(){const now=new Date(),s=now.getSeconds(),mi=now.getMinutes(),h=now.getHours();const sh=document.getElementById('clockSecondHand'),mh=document.getElementById('clockMinuteHand'),hh=document.getElementById('clockHourHand');if(sh)sh.style.transform='rotate('+(s*6)+'deg)';if(mh)mh.style.transform='rotate('+(mi*6+s*.1)+'deg)';if(hh)hh.style.transform='rotate('+((h%12)*30+mi*.5)+'deg)';const d=document.getElementById('clockDigital'),dt=document.getElementById('clockDate');if(d)d.textContent=now.toLocaleTimeString([], {hour:'2-digit',minute:'2-digit',second:'2-digit',hour12:true});if(dt)dt.textContent=now.toLocaleDateString([], {weekday:'short',day:'2-digit',month:'short',year:'numeric'});}
    function calendar(){const now=new Date(),y=calendarCursor.getFullYear(),mo=calendarCursor.getMonth(),t=now.getDate(),isCurrent=(y===now.getFullYear()&&mo===now.getMonth()),fd=new Date(y,mo,1).getDay(),dim=new Date(y,mo+1,0).getDate(),dpm=new Date(y,mo,0).getDate();const label=document.getElementById('calendarMonthLabel'),days=document.getElementById('miniCalendarDays');if(label)label.textContent=new Date(y,mo,1).toLocaleDateString([], {month:'long',year:'numeric'});if(!days)return;let html='';for(let i=0;i<fd;i++)html+='<span class="d m">'+(dpm-fd+i+1)+'</span>';for(let d=1;d<=dim;d++)html+='<span class="d '+((isCurrent&&d===t)?'t':'')+'">'+d+'</span>';for(let i=1;i<=42-(fd+dim);i++)html+='<span class="d m">'+i+'</span>';days.innerHTML=html;}
    function initCalendarNav(){const prev=document.getElementById('calendarPrevBtn'),next=document.getElementById('calendarNextBtn');if(prev)prev.addEventListener('click',()=>{calendarCursor=new Date(calendarCursor.getFullYear(),calendarCursor.getMonth()-1,1);calendar();});if(next)next.addEventListener('click',()=>{calendarCursor=new Date(calendarCursor.getFullYear(),calendarCursor.getMonth()+1,1);calendar();});}
    function online(s){const list=document.getElementById('online-users-list'),cnt=document.getElementById('live-online-count');if(cnt)cnt.textContent=n(s.count||0);if(!list)return;const users=Array.isArray(s.users)?s.users:[];if(!users.length){list.innerHTML='<li class="empty">No active users in the last 5 minutes.</li>';return;}list.innerHTML=users.map(u=>'<li><span class="dot"></span><div><strong>'+(u.name||'Unknown')+'</strong><small>'+(u.last_seen||'Online')+'</small></div></li>').join('');}
    function present(p){const list=document.getElementById('present-employees-list'),cnt=document.getElementById('live-present-count'),ratio=document.getElementById('employeePresenceRatio');if(cnt)cnt.textContent=n((p.employeeSummary||{}).present||0);if(ratio)ratio.textContent=n((p.employeeSummary||{}).present||0)+'/'+n((p.employeeSummary||{}).active||0);if(!list)return;const rows=Array.isArray(p.presentEmployees)?p.presentEmployees:[];if(!rows.length){list.innerHTML='<li class="empty">No present employee entries for today.</li>';return;}list.innerHTML=rows.map(r=>'<li><span class="dot blue"></span><div><strong>'+(r.name||'Unknown')+'</strong><small>Checked in today</small></div></li>').join('');}
    function stats(s){if(document.getElementById('live-invoice-count'))document.getElementById('live-invoice-count').textContent=n(s.invoice_count);if(document.getElementById('live-purchase-count'))document.getElementById('live-purchase-count').textContent=n(s.purchase_count);if(document.getElementById('live-other-delivery-count'))document.getElementById('live-other-delivery-count').textContent=n(s.other_delivery_count);if(document.getElementById('live-invoice-amount'))document.getElementById('live-invoice-amount').textContent=m(s.invoice_amount);if(document.getElementById('live-purchase-amount'))document.getElementById('live-purchase-amount').textContent=m(s.purchase_amount);}
    let hChart=null,mChart=null;function charts(){if(!window.Chart)return;const h=document.getElementById('hourlySalesChart'),mo=document.getElementById('monthlySalesChart');if(!h||!mo)return;hChart=new Chart(h.getContext('2d'),{type:'line',data:{labels:seed.hourlySales.labels,datasets:[{data:seed.hourlySales.values,borderColor:'#2563eb',backgroundColor:'rgba(37,99,235,.14)',fill:true,tension:.34,pointRadius:0,borderWidth:2}]},options:{responsive:true,maintainAspectRatio:false,plugins:{legend:{display:false}},scales:{y:{beginAtZero:true,grid:{color:'rgba(15,23,42,.08)'},ticks:{color:'#64748b'}},x:{grid:{display:false},ticks:{color:'#64748b',maxTicksLimit:8}}}}});
    mChart=new Chart(mo.getContext('2d'),{type:'line',data:{labels:seed.monthlySales.labels,datasets:[{data:seed.monthlySales.values,borderColor:'#059669',backgroundColor:'rgba(5,150,105,.15)',fill:true,tension:.38,pointRadius:2,pointBackgroundColor:'#059669',borderWidth:2}]},options:{responsive:true,maintainAspectRatio:false,plugins:{legend:{display:false}},scales:{y:{beginAtZero:true,grid:{color:'rgba(15,23,42,.08)'},ticks:{color:'#64748b'}},x:{grid:{display:false},ticks:{color:'#64748b',maxTicksLimit:6}}}}});}
    function refreshHour(hs){if(!hChart||!hs)return;hChart.data.labels=hs.labels||[];hChart.data.datasets[0].data=hs.values||[];hChart.update();}
    function live(){fetch(seed.liveUrl,{headers:{'X-Requested-With':'XMLHttpRequest','Accept':'application/json'}}).then(r=>r.ok?r.json():Promise.reject()).then(p=>{stats(p.todayStats||{});online(p.onlineSnapshot||{});present(p);refreshHour(p.hourlySales||{});const el=document.getElementById('live-last-sync');if(el)el.textContent=new Date().toLocaleTimeString([], {hour:'2-digit',minute:'2-digit',second:'2-digit',hour12:true});}).catch(()=>{});}
    function todo(){const uid=seed.userId||0,key='dashboard.todos.user.'+uid,init=key+'.initialized',list=document.getElementById('todoList'),input=document.getElementById('todoInput'),btn=document.getElementById('todoAddBtn');if(!list||!input||!btn)return;const read=()=>{try{const p=JSON.parse(localStorage.getItem(key)||'[]');return Array.isArray(p)?p:[];}catch(e){return[];}},write=t=>localStorage.setItem(key,JSON.stringify(t));let tasks=read();if(!localStorage.getItem(init)&&!tasks.length){tasks=[{id:Date.now()+1,text:'Follow up '+(seed.insights.due_customer_count||0)+' due customers'},{id:Date.now()+2,text:'Review low stock items ('+(seed.insights.low_stock_count||0)+')'},{id:Date.now()+3,text:"Verify today's invoices and purchases"}];write(tasks);localStorage.setItem(init,'1');}
    const render=()=>{tasks=read();if(!tasks.length){list.innerHTML='<li class="empty">No open tasks. Add a new one above.</li>';return;}list.innerHTML=tasks.map(t=>'<li><span class="todo-txt">'+t.text+'</span><button type="button" class="todo-done" data-d="'+t.id+'">Done</button></li>').join('');};const add=()=>{const text=input.value.trim();if(!text)return;tasks=read();tasks.push({id:Date.now()+Math.floor(Math.random()*1000),text:text});write(tasks);input.value='';render();};btn.addEventListener('click',add);input.addEventListener('keydown',e=>{if(e.key==='Enter'){e.preventDefault();add();}});list.addEventListener('click',e=>{const b=e.target.closest('[data-d]');if(!b)return;const id=Number(b.getAttribute('data-d'));tasks=read().filter(t=>Number(t.id)!==id);write(tasks);render();});render();}
    function chatbox(){const box=document.getElementById('dashChatMessages'),target=document.getElementById('dashChatTarget'),form=document.getElementById('dashChatForm'),input=document.getElementById('dashChatInput'),sendBtn=form?form.querySelector('button[type="submit"]'):null;if(!box||!target||!form)return;let lastId=0;const showEmpty=t=>{box.innerHTML='<div class="empty">'+esc(t)+'</div>';};if(!seed.chat||!seed.chat.access){showEmpty('Chat access is not enabled for your account.');target.disabled=true;if(input)input.disabled=true;if(sendBtn)sendBtn.disabled=true;return;}const fmt=t=>{if(!t)return'Now';const d=new Date(t);if(Number.isNaN(d.getTime()))return'Now';return d.toLocaleTimeString([], {hour:'2-digit',minute:'2-digit',hour12:true});};const appendRow=row=>{const wrap=document.createElement('div');wrap.className='dash-chat-row '+(row.is_mine?'mine':'their');wrap.innerHTML='<div class="dash-chat-bubble">'+esc(row.message||'')+'</div><div class="dash-chat-meta">'+esc((row.is_broadcast?'Broadcast':'Direct')+' · '+fmt(row.created_at))+'</div>';box.appendChild(wrap);};const load=reset=>{let url=seed.chat.messagesUrlBase+'/'+encodeURIComponent(target.value||'all');if(!reset&&lastId>0)url+='?after_id='+lastId;fetch(url,{headers:{'X-Requested-With':'XMLHttpRequest','Accept':'application/json'}}).then(r=>r.ok?r.json():Promise.reject()).then(p=>{if(reset){box.innerHTML='';lastId=0;}const rows=Array.isArray(p.messages)?p.messages:[];if(!rows.length){if(reset)showEmpty('No messages yet. Start the conversation.');return;}if(box.querySelector('.empty'))box.innerHTML='';rows.forEach(row=>{appendRow(row);if(Number(row.id)>lastId)lastId=Number(row.id);});box.scrollTop=box.scrollHeight;}).catch(()=>{if(reset)showEmpty('Unable to load chat right now.');});};const send=()=>{if(!seed.chat.canSend||!input)return;const txt=input.value.trim();if(!txt)return;const recipient=(target.value||'all').toString();const body=new URLSearchParams();body.set('_token',seed.chat.csrf);body.set('recipient_id',recipient);body.set('message',txt);if(recipient==='all')body.set('broadcast_all','1');fetch(seed.chat.sendUrl,{method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded; charset=UTF-8','X-Requested-With':'XMLHttpRequest','Accept':'application/json'},body:body.toString()}).then(r=>r.ok?r.json():Promise.reject()).then(row=>{if(box.querySelector('.empty'))box.innerHTML='';appendRow(row);if(Number(row.id)>lastId)lastId=Number(row.id);input.value='';box.scrollTop=box.scrollHeight;}).catch(()=>{});};target.addEventListener('change',()=>load(true));form.addEventListener('submit',e=>{e.preventDefault();send();});if(input&&!seed.chat.canSend){input.disabled=true;if(sendBtn)sendBtn.disabled=true;input.placeholder='Chat sending is disabled for your account';}load(true);setInterval(()=>load(false),10000);}
    function focusMode(){const btn=document.getElementById('dashboardFocusToggle'),exitBtn=document.getElementById('dashboardFocusFloatingExit');if(!btn)return;const key='dashboard.focus.mode';const apply=on=>{document.body.classList.toggle('dashboard-focus-mode',on);btn.innerHTML=on?'<i class="fas fa-compress-arrows-alt"></i> Exit Focus':'<i class="fas fa-expand-arrows-alt"></i> Focus';if(exitBtn)exitBtn.style.display=on?'inline-flex':'none';};const toggle=()=>{const on=!document.body.classList.contains('dashboard-focus-mode');apply(on);localStorage.setItem(key,on?'1':'0');};apply(localStorage.getItem(key)==='1');btn.addEventListener('click',toggle);if(exitBtn)exitBtn.addEventListener('click',()=>{apply(false);localStorage.setItem(key,'0');});document.addEventListener('keydown',e=>{if(e.key==='Escape'&&document.body.classList.contains('dashboard-focus-mode')){apply(false);localStorage.setItem(key,'0');}});}
    clock();initCalendarNav();calendar();online(seed.onlineSnapshot);present({presentEmployees:seed.presentEmployees,employeeSummary:seed.employeeSummary});stats(seed.todayStats);charts();todo();chatbox();focusMode();setInterval(clock,1000);setInterval(live,30000);
})();
</script>
@stop
