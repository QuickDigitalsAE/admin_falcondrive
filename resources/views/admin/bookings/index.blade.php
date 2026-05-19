@extends('admin.layouts.app')

@section('title', 'Bookings')
@section('page_title', 'Bookings')

@section('breadcrumbs')
    <nav class="flex flex-wrap items-center gap-2 text-[12px] text-slate-500">
        <a href="{{ route('admin.dashboard') }}" class="transition hover:text-[#9b7a28]">
            <i class="fas fa-house text-[11px]"></i>
            <span class="ml-1">Dashboard</span>
        </a>
        <i class="fas fa-chevron-right text-[10px] text-slate-400"></i>
        <span class="font-medium text-slate-700">Bookings</span>
    </nav>
@endsection

@section('content')
    <div class="flex h-full flex-col gap-5">
        <div class="grid grid-cols-1 gap-3 xl:grid-cols-[1fr_auto]">
            <div class="rounded-2xl border border-[#eee4ca] bg-white/95 p-3 shadow-sm">
                <form id="bookingSearchForm" method="GET" action="{{ route('admin.bookings') }}" class="flex flex-col gap-2 sm:flex-row sm:items-center">
                    <div class="min-w-0 flex-1">
                        <div class="relative">
                            <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-[#b49543]">
                                <i class="fa-solid fa-magnifying-glass text-sm"></i>
                            </span>
                            <input
                                type="text"
                                id="searchInput"
                                name="search"
                                value="{{ $search ?? '' }}"
                                placeholder="Search by name, number, email, coupon or transaction id"
                                class="w-full rounded-xl border border-[#eadfbe] bg-[#fffdf8] py-2.5 pl-11 pr-4 text-sm outline-none transition focus:border-[#d8bf79] focus:bg-white"
                            >
                        </div>
                    </div>

                    <div class="flex shrink-0 items-center gap-2 sm:self-stretch">
                        <button
                            type="submit"
                            id="searchBtn"
                            class="inline-flex items-center justify-center rounded-xl bg-[#d6ab3d] px-4 py-2.5 text-sm font-medium text-white transition hover:bg-[#c59626] sm:min-w-[110px]"
                        >
                            <i class="fa-solid fa-magnifying-glass mr-2 text-[12px]"></i>
                            Search
                        </button>

                        <a
                            href="{{ route('admin.bookings') }}"
                            id="resetBtn"
                            class="inline-flex h-[42px] w-[42px] items-center justify-center rounded-xl border border-[#eadfbe] bg-white text-[#87671c] transition hover:bg-[#fff8e7]"
                            title="Reset"
                        >
                            <i class="fa-solid fa-rotate-right text-[13px]"></i>
                        </a>
                    </div>
                </form>
            </div>

            <div class="rounded-2xl border border-[#eee4ca] bg-white/95 p-3 shadow-sm">
                <div class="flex flex-wrap items-center justify-start gap-2 xl:justify-end">
                    @can('Booking_Add')
                        <a
                            href="{{ route('admin.bookings.create') }}"
                            class="inline-flex items-center rounded-xl bg-[#c79a2b] px-4 py-2.5 text-sm font-medium text-white shadow-sm transition hover:bg-[#b4871d]"
                        >
                            <i class="fa-solid fa-plus mr-2 text-[13px]"></i>
                            Add Booking
                        </a>
                    @endcan

                    @can('Booking_Delete')
                        <button
                            type="button"
                            id="trashToggleBtn"
                            class="inline-flex h-[42px] w-[42px] items-center justify-center rounded-xl border border-red-300 bg-red-50 text-red-700 shadow-sm transition hover:bg-red-100"
                            title="View Trash"
                        >
                            <i class="fa-solid fa-recycle text-[14px]"></i>
                        </button>
                    @endcan

                    @can('Booking_ViewAll')
                        <a
                            id="exportCsvBtn"
                            href="{{ route('admin.bookings', ['is_export' => 1, 'search' => $search ?? null]) }}"
                            class="inline-flex h-[42px] w-[42px] items-center justify-center rounded-xl border border-[#eadfbe] bg-white text-[#87671c] shadow-sm transition hover:bg-[#fff8e7]"
                            title="Export CSV"
                        >
                            <i class="fa-solid fa-file-csv text-[14px]"></i>
                        </a>
                    @endcan
                </div>
            </div>
        </div>

        <div class="flex min-h-0 flex-1 flex-col overflow-hidden rounded-2xl border border-[#eee4ca] bg-white shadow-sm">
            <div class="theme-table-scroll min-h-0 flex-1 overflow-auto">
                <table class="min-w-full divide-y divide-[#f2ead4]">
                    <thead class="sticky top-0 z-10 bg-[#fffaf0]">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-semibold uppercase text-[#9d8750]">Actions</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold uppercase text-[#9d8750]">Customer</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold uppercase text-[#9d8750]">Rental</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold uppercase text-[#9d8750]">Payment</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold uppercase text-[#9d8750]">Created</th>
                        </tr>
                    </thead>
                    <tbody id="recordsTableBody" class="divide-y divide-[#f6f0df] bg-white">
                        @forelse ($records as $booking)
                            <tr class="hover:bg-[#fffaf0]">
                                <td class="px-6 py-4">
                                    <div class="flex flex-wrap items-center gap-2">
                                        @can('Booking_ViewAll|Booking_View')
                                            <a
                                                href="{{ route('admin.bookings.show', $booking->id) }}"
                                                class="inline-flex items-center rounded-xl border border-[#eadfbe] bg-white px-3 py-2 text-xs font-semibold text-[#7d6220] transition hover:bg-[#fff8e8]"
                                            >
                                                <i class="fa-solid fa-eye mr-2 text-[12px]"></i>
                                            </a>
                                        @endcan
                                        <!-- @can('Booking_Edit')
                                            <a
                                                href="{{ route('admin.bookings.edit', $booking->id) }}"
                                                class="inline-flex items-center rounded-xl border border-[#eadfbe] bg-white px-3 py-2 text-xs font-semibold text-[#7d6220] transition hover:bg-[#fff8e8]"
                                            >
                                                <i class="fa-solid fa-pen-to-square mr-2 text-[12px]"></i>
                                                Edit
                                            </a>
                                        @endcan -->
                                        @can('Booking_Delete')
                                            <form method="POST" action="{{ route('admin.bookings.delete', $booking->id) }}" onsubmit="return confirm('Delete this booking?');">
                                                @csrf
                                                @method('DELETE')
                                                <button
                                                    type="submit"
                                                    class="inline-flex items-center rounded-xl border border-red-200 bg-white px-3 py-2 text-xs font-semibold text-red-600 transition hover:bg-red-50"
                                                >
                                                    <i class="fa-solid fa-trash mr-2 text-[12px]"></i>
                                                </button>
                                            </form>
                                        @endcan
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="space-y-1">
                                        <div class="text-sm font-semibold text-slate-800">{{ $booking->name }}</div>
                                        <div class="text-xs text-slate-500">{{ $booking->number }}</div>
                                        <div class="text-xs text-slate-500">{{ $booking->email ?? '-' }}</div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="space-y-1">
                                        <div class="text-xs text-slate-700">
                                            {{ optional($booking->start_date)->format('Y-m-d') ?? '-' }}
                                            @if(!empty($booking->start_time)) {{ $booking->start_time }} @endif
                                        </div>
                                        <div class="text-xs text-slate-700">
                                            {{ optional($booking->end_date)->format('Y-m-d') ?? '-' }}
                                            @if(!empty($booking->end_time)) {{ $booking->end_time }} @endif
                                        </div>
                                        <div class="text-xs text-slate-500">
                                            {{ $booking->rental_type ?? '-' }} / {{ $booking->resident_tourist ?? '-' }}
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="space-y-1">
                                        <div class="text-xs text-slate-700">Flow: {{ $booking->payment_flow == 'now' ? 'Pay Now' : 'Pay Later' }}</div>
                                        <div class="text-xs text-slate-500">
                                            Amount:
                                            <span class="inline-flex items-center gap-1 font-semibold text-slate-700">
                                                <img src="{{ asset('images/durham.png') }}" alt="AED" class="inline-block h-[1em] w-[1em] object-contain align-[-0.12em]">
                                                {{ number_format((float) $booking->total_amount, 2) }}
                                            </span>
                                        </div>
                                        <div class="text-xs text-slate-500">Status: {{ $booking->paid_status ?? '-' }}</div>
                                        <div class="text-xs text-slate-500">Via: {{ $booking->paid_via ?? '-' }}</div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-sm text-slate-600">
                                    {{ optional($booking->created_at)->format('d M Y, h:i A') }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-12 text-center text-slate-500">No bookings found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="shrink-0 border-t border-[#f2ead4] bg-[#fffdf9] px-6 py-4">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <p id="tableMeta" class="text-sm text-slate-500">
                        Showing {{ $records->firstItem() ?? 0 }} to {{ $records->lastItem() ?? 0 }} of {{ $records->total() }} results
                    </p>
                    <div id="paginationWrapper" class="flex flex-wrap items-center gap-2">
                        {{ $records->links() }}
                    </div>
                </div>
            </div>
        </div>

        <div id="sendModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/60 backdrop-blur-sm overflow-y-auto p-4">
            <div class="bg-white rounded-3xl w-full max-w-6xl flex flex-col max-h-[90vh] shadow-2xl relative">
                <div class="flex justify-between items-center p-6 border-b border-gray-100 flex-shrink-0">
                    <div class="flex items-center gap-3">
                        <div>
                            <h2 class="text-xl font-bold text-gray-800">Send Booking To Speed</h2>
                            <p class="text-xs text-gray-500">Dispatch booking data to the Speed System</p>
                        </div>
                    </div>
                    <button type="button" onclick="closeSendModal()" class="h-10 w-10 flex items-center justify-center rounded-full bg-gray-100 hover:bg-red-50 hover:text-red-600 transition-all text-gray-500">
                        <i class="fas fa-times text-lg"></i>
                    </button>
                </div>

                <div class="overflow-y-auto flex-1 p-6 space-y-8">
                    <form id="sendForm">
                        @csrf
                        <input type="hidden" name="inquiry_id" id="inquiry_id">
                        <input type="hidden" name="customerId" id="customerId">

                        <div class="bg-gray-50/50 border border-gray-100 rounded-2xl p-5">
                            <div class="flex items-center gap-2 mb-4">
                                <h3 class="text-lg font-bold text-gray-800">Customer Details</h3>
                            </div>

                            <div class="mb-4">
                                <label class="block text-sm font-semibold text-gray-700 mb-1.5">Search by Email</label>
                                <div class="relative">
                                    <input type="email" name="customerEmail" id="customerEmail" class="w-full border border-gray-300 rounded-xl p-3 pl-4 focus:ring-4 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none" placeholder="customer@example.com" oninput="searchCustomer(this.value)">
                                </div>
                                <span id="emailError" class="text-red-500 text-xs mt-1 block hidden"></span>
                            </div>

                            <div id="customerFields" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 hidden">
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">First Name</label>
                                    <input type="text" name="firstName" id="firstName" placeholder="First Name" class="w-full border border-gray-300 rounded-xl p-3 bg-white transition-all outline-none" readonly>
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">Last Name</label>
                                    <input type="text" name="lastName" id="lastName" placeholder="Last Name" class="w-full border border-gray-300 rounded-xl p-3 bg-white transition-all outline-none" readonly>
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">Mobile No</label>
                                    <input type="text" name="mobileNo" id="mobileNo" placeholder="Mobile" class="w-full border border-gray-300 rounded-xl p-3 bg-white transition-all outline-none" readonly>
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">City</label>
                                    <input type="text" name="city" id="city" placeholder="City" class="w-full border border-gray-300 rounded-xl p-3 bg-white transition-all outline-none" readonly>
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">Country</label>
                                    <input type="text" name="country" id="country" placeholder="Country" class="w-full border border-gray-300 rounded-xl p-3 bg-white transition-all outline-none" readonly>
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">Street</label>
                                    <input type="text" name="street" id="street" placeholder="Street" class="w-full border border-gray-300 rounded-xl p-3 bg-white transition-all outline-none" readonly>
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">State</label>
                                    <input type="text" name="state" id="state" placeholder="State" class="w-full border border-gray-300 rounded-xl p-3 bg-white transition-all outline-none">
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">Postal Code</label>
                                    <input type="text" name="postalCode" id="postalCode" placeholder="Postal Code" class="w-full border border-gray-300 rounded-xl p-3 bg-white transition-all outline-none">
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5 mt-6">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-1.5">Vehicle</label>
                                <select name="vehicle" id="vehicleSelect" required class="w-full border border-gray-300 rounded-xl p-3 outline-none">
                                    <option value="">Loading vehicles...</option>
                                </select>
                            </div>

                            <input type="hidden" name="vehicle" id="vehicleId">
                            <input type="hidden" name="tariffGroupId" id="tariffGroupId">
                            <input type="hidden" name="plateNo" id="plateNo">
                            <input type="hidden" name="vehicleTitle" id="vehicleTitle">

                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-1.5">Vehicle Group</label>
                                <select id="vehicleGroupSelect" name="vehicleGroupId" required class="w-full border border-gray-300 rounded-xl p-3 outline-none">
                                    <option value="">Loading...</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-1.5">Booking Status</label>
                                <select id="bookingStatus" name="bookingStatus" required class="w-full border border-gray-300 rounded-xl p-3 outline-none">
                                    <option value="0">Select Status</option>
                                    <option value="1">New</option>
                                    <option value="2">Confirmed</option>
                                    <option value="3">Cancelled</option>
                                    <option value="4">Closed</option>
                                    <option value="5">NoShow</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-1.5">Booking Type</label>
                                <select id="bookingType" name="bookingType" required class="w-full border border-gray-300 rounded-xl p-3 outline-none">
                                    <option value="0">Select Type</option>
                                    <option value="1">TradeLicense</option>
                                    <option value="2">Passport</option>
                                    <option value="3">NationalId</option>
                                    <option value="4">DrivingLicense</option>
                                    <option value="5">Other</option>
                                    <option value="6">StaffDocument1</option>
                                    <option value="7">HealthCard</option>
                                    <option value="8">Visa</option>
                                    <option value="9">CreditApplication</option>
                                    <option value="10">CreditCard</option>
                                    <option value="15">OtherDocument2</option>
                                    <option value="16">OtherDocument3</option>
                                    <option value="17">OtherDocument4</option>
                                    <option value="18">Signature</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-1.5">Location</label>
                                <select id="locationSelect" name="locationId" required class="w-full border border-gray-300 rounded-xl p-3 outline-none">
                                    <option value="">Loading...</option>
                                </select>
                            </div>

                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">Advance</label>
                                    <input type="number" name="advance" placeholder="0.00" class="w-full border border-gray-300 rounded-xl p-3 outline-none">
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">Tax %</label>
                                    <input type="number" name="taxPercent" placeholder="5" class="w-full border border-gray-300 rounded-xl p-3 outline-none">
                                </div>
                            </div>
                        </div>

                        <div class="mt-8">
                            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 mb-4">
                                <div>
                                    <h3 class="font-bold text-lg text-gray-800">Additional Charges</h3>
                                </div>
                                <button type="button" id="addCharge" class="flex items-center gap-2 bg-emerald-600 hover:bg-emerald-700 text-white font-medium px-4 py-2.5 rounded-xl shadow-sm transition-all text-sm">
                                    <i class="fas fa-plus"></i> Add Charge
                                </button>
                            </div>

                            <div id="chargesWrapper" class="space-y-4"></div>
                        </div>

                        <div class="mt-8 bg-gray-50 border border-gray-200 rounded-2xl p-5 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 items-end">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-1.5">Discount</label>
                                <input type="number" name="discount" placeholder="0.00" class="w-full border border-gray-300 rounded-xl p-3 bg-white outline-none">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-1.5">Flat Tax</label>
                                <input type="number" name="chargesTax" placeholder="0.00" class="w-full border border-gray-300 rounded-xl p-3 bg-white outline-none">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-1.5">Total Extra Charges</label>
                                <input type="number" name="totalCharges" readonly class="w-full bg-gray-100 border border-gray-200 font-semibold text-gray-800 rounded-xl p-3 cursor-not-allowed outline-none">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-1.5">Grand Total Amount</label>
                                <input type="number" name="amount" readonly class="w-full bg-blue-50 font-bold text-blue-700 border border-blue-200 rounded-xl p-3 cursor-not-allowed outline-none">
                            </div>
                        </div>

                        <div class="mt-8 bg-gray-50/70 border border-gray-200 rounded-2xl p-5">
                            <div class="flex items-center justify-between mb-5">
                                <h3 class="text-lg font-bold text-gray-800">Billing Details</h3>
                            </div>

                            <div class="mb-5">
                                <label class="block text-sm font-semibold text-gray-700 mb-1.5">Billing Notes</label>
                                <textarea name="billingNotes" rows="2" placeholder="Enter billing notes..." class="w-full border border-gray-300 rounded-xl p-3 outline-none"></textarea>
                            </div>

                            <div class="bg-white border border-gray-200 rounded-xl p-4 mb-5">
                                <h4 class="text-sm font-semibold text-gray-700 mb-3">Credit Card Info</h4>
                                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                                    <div>
                                        <label class="text-xs font-medium text-gray-600">Card Last Digits</label>
                                        <input type="text" name="cardLastDigits" required placeholder="1234" class="w-full border border-gray-300 rounded-lg p-2.5 outline-none">
                                    </div>
                                    <div>
                                        <label class="text-xs font-medium text-gray-600">Transaction No</label>
                                        <input type="text" name="transactionNo" placeholder="TXN12345" class="w-full border border-gray-300 rounded-lg p-2.5 outline-none">
                                    </div>
                                    <div>
                                        <label class="text-xs font-medium text-gray-600">Expiry Date</label>
                                        <input type="date" name="expiryDate" required class="w-full border border-gray-300 rounded-lg p-2.5 outline-none">
                                    </div>
                                    <div>
                                        <label class="text-xs font-medium text-gray-600">Commission %</label>
                                        <input type="number" name="commissionPercentage" placeholder="0" class="w-full border border-gray-300 rounded-lg p-2.5 outline-none">
                                    </div>
                                </div>
                            </div>

                            <div class="bg-white border border-gray-200 rounded-xl p-4">
                                <h4 class="text-sm font-semibold text-gray-700 mb-3">Card Details</h4>
                                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                                    <div>
                                        <label class="text-xs font-medium text-gray-600">Card Number</label>
                                        <input type="text" name="cardNumber" required placeholder="1234 5678 9012 3456" class="w-full border border-gray-300 rounded-lg p-2.5 outline-none">
                                    </div>
                                    <div>
                                        <label class="text-xs font-medium text-gray-600">Last 4 Digits</label>
                                        <input type="text" name="cardLastFourDigits" required placeholder="1234" class="w-full border border-gray-300 rounded-lg p-2.5 outline-none">
                                    </div>
                                    <div>
                                        <label class="text-xs font-medium text-gray-600">CVV</label>
                                        <input type="password" name="cvv" required placeholder="***" class="w-full border border-gray-300 rounded-lg p-2.5 outline-none">
                                    </div>
                                    <div>
                                        <label class="text-xs font-medium text-gray-600">Name on Card</label>
                                        <input type="text" name="nameOnCard" required placeholder="Enter name as on card" class="w-full border border-gray-300 rounded-lg p-2.5 outline-none">
                                    </div>
                                    <div>
                                        <label class="text-xs font-medium text-gray-600">Bank Name</label>
                                        <input type="text" name="bankName" required placeholder="ABC Bank" class="w-full border border-gray-300 rounded-lg p-2.5 outline-none">
                                    </div>
                                    <div>
                                        <label class="text-xs font-medium text-gray-600">Expiry</label>
                                        <input type="date" name="cardExpiry" required class="w-full border border-gray-300 rounded-lg p-2.5 outline-none">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mt-6">
                            <label class="block text-sm font-semibold text-gray-700 mb-1.5">Notes & Terms</label>
                            <textarea name="notes" rows="3" placeholder="Add custom terms or booking adjustments here..." class="w-full border border-gray-300 rounded-xl p-3 outline-none"></textarea>
                        </div>
                    </form>
                </div>

                <div class="flex flex-col-reverse sm:flex-row justify-end p-6 border-t border-gray-100 bg-white rounded-b-3xl gap-3 flex-shrink-0">
                    <button type="button" onclick="closeSendModal()" class="px-6 py-3 bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold rounded-xl text-sm transition-all">
                        Cancel
                    </button>
                    <button type="submit" form="sendForm" id="sendBookingSubmitBtn" class="flex items-center justify-center gap-2 px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-xl text-sm shadow-lg shadow-blue-500/30 transition-all">
                        <i class="fas fa-paper-plane"></i>
                        Send Booking Data
                    </button>
                </div>
            </div>
        </div>

        <div id="speedViewModal" class="fixed inset-0 bg-black/50 hidden items-center justify-center z-50 p-4">
            <div class="bg-white w-full max-w-5xl mx-4 rounded-xl shadow-lg flex flex-col">
                <div class="flex justify-between items-center p-4 border-b">
                    <h2 class="text-lg font-semibold text-purple-600">Speed Booking Details</h2>
                    <button type="button" onclick="closeSpeedViewModal()">✕</button>
                </div>

                <div class="p-4 overflow-y-auto space-y-4" style="max-height: 75vh;">
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <h3 class="font-semibold mb-2">Booking Info</h3>
                        <div class="grid grid-cols-2 gap-3 text-sm" id="bookingInfo"></div>
                    </div>
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <h3 class="font-semibold mb-2">Vehicle</h3>
                        <div class="grid grid-cols-2 gap-3 text-sm" id="vehicleInfo"></div>
                    </div>
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <h3 class="font-semibold mb-2">Customer</h3>
                        <div class="grid grid-cols-2 gap-3 text-sm" id="customerInfo"></div>
                    </div>
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <h3 class="font-semibold mb-2">Charges</h3>
                        <div id="chargesList" class="space-y-2 text-sm"></div>
                    </div>
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <h3 class="font-semibold mb-2">Billing</h3>
                        <div class="grid grid-cols-2 gap-3 text-sm" id="billingInfo"></div>
                    </div>
                </div>

                <div class="p-4 border-t text-right">
                    <button type="button" onclick="closeSpeedViewModal()" class="px-4 py-2 bg-gray-500 text-white rounded-lg">
                        Close
                    </button>
                </div>
            </div>
        </div>
    </div>

    <form id="actionForm" class="hidden">
        @csrf
        <input type="hidden" name="_method" id="actionFormMethod">
    </form>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const endpoint = @json(route('admin.bookings'));
            const searchForm = document.getElementById('bookingSearchForm');
            const searchInput = document.getElementById('searchInput');
            const resetBtn = document.getElementById('resetBtn');
            const trashToggleBtn = document.getElementById('trashToggleBtn');
            const exportCsvBtn = document.getElementById('exportCsvBtn');
            const recordsTableBody = document.getElementById('recordsTableBody');
            const paginationWrapper = document.getElementById('paginationWrapper');
            const tableMeta = document.getElementById('tableMeta');

            let state = {
                search: new URLSearchParams(window.location.search).get('search') || '',
                is_deleted: new URLSearchParams(window.location.search).get('is_deleted') === '1' ? 1 : 0,
                page: parseInt(new URLSearchParams(window.location.search).get('page') || '1', 10),
                loading: false,
                requestId: 0,
            };

            if (searchInput) {
                searchInput.value = state.search;
            }

            function escapeHtml(value) {
                if (value === null || value === undefined) return '';

                return String(value)
                    .replaceAll('&', '&amp;')
                    .replaceAll('<', '&lt;')
                    .replaceAll('>', '&gt;')
                    .replaceAll('"', '&quot;')
                    .replaceAll("'", '&#039;');
            }


            function currencyHtml(value) {
                const amount = value === null || value === undefined || value === '' ? '0.00' : value;

                return `
                    <span class="inline-flex items-center gap-1 font-semibold text-slate-700">
                        <img src="{{ asset('images/durham.png') }}" alt="AED" class="inline-block h-[1em] w-[1em] object-contain align-[-0.12em]">
                        <span>${escapeHtml(amount)}</span>
                    </span>
                `;
            }

            function csrfToken() {
                return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || @json(csrf_token());
            }

            function renderMeta(pagination) {
                if (!tableMeta) return;

                const from = pagination?.from ?? 0;
                const to = pagination?.to ?? 0;
                const total = pagination?.total ?? 0;

                tableMeta.textContent = `Showing ${from} to ${to} of ${total} results`;
            }

            function setLoading() {
                recordsTableBody.innerHTML = `
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center text-slate-500">
                            <div class="inline-flex items-center gap-2">
                                <i class="fa-solid fa-spinner fa-spin text-[#b49543]"></i>
                                <span>Loading bookings...</span>
                            </div>
                        </td>
                    </tr>
                `;
            }

            function updateTrashUI() {
                if (!trashToggleBtn) return;

                const icon = trashToggleBtn.querySelector('i');

                if (Number(state.is_deleted) === 1) {
                    trashToggleBtn.classList.remove('border-red-300', 'bg-red-50', 'text-red-700', 'hover:bg-red-100');
                    trashToggleBtn.classList.add('border-green-300', 'bg-green-100', 'text-green-800', 'hover:bg-green-200');
                    trashToggleBtn.setAttribute('title', 'Back to Active Bookings');

                    if (icon) {
                        icon.className = 'fa-solid fa-arrow-rotate-left text-[14px]';
                    }

                    return;
                }

                trashToggleBtn.classList.add('border-red-300', 'bg-red-50', 'text-red-700', 'hover:bg-red-100');
                trashToggleBtn.classList.remove('border-green-300', 'bg-green-100', 'text-green-800', 'hover:bg-green-200');
                trashToggleBtn.setAttribute('title', 'View Trash');

                if (icon) {
                    icon.className = 'fa-solid fa-recycle text-[14px]';
                }
            }

            function updateExportUrl() {
                if (!exportCsvBtn) return;

                const params = new URLSearchParams();

                if (state.search) {
                    params.set('search', state.search);
                }

                if (Number(state.is_deleted) === 1) {
                    params.set('is_deleted', '1');
                }

                params.set('is_export', '1');
                exportCsvBtn.href = `${endpoint}?${params.toString()}`;
            }

            function syncUrl() {
                const url = new URL(window.location.href);

                state.search ? url.searchParams.set('search', state.search) : url.searchParams.delete('search');
                Number(state.is_deleted) === 1 ? url.searchParams.set('is_deleted', '1') : url.searchParams.delete('is_deleted');
                state.page > 1 ? url.searchParams.set('page', state.page) : url.searchParams.delete('page');

                window.history.replaceState({}, '', url.toString());
            }

            function renderPagination(pagination) {
                if (!paginationWrapper) return;

                paginationWrapper.innerHTML = '';

                if (!pagination || pagination.last_page <= 1) {
                    return;
                }

                const buttons = [];

                buttons.push(`
                    <button
                        type="button"
                        class="page-btn rounded-lg border px-3 py-2 text-sm ${pagination.current_page === 1 ? 'cursor-not-allowed border-slate-200 bg-slate-100 text-slate-400' : 'border-[#eadfbe] bg-white text-[#87671c] hover:bg-[#fff8e7]'}"
                        data-page="${pagination.current_page - 1}"
                        ${pagination.current_page === 1 ? 'disabled' : ''}>
                        Prev
                    </button>
                `);

                for (let page = 1; page <= pagination.last_page; page++) {
                    if (
                        page === 1 ||
                        page === pagination.last_page ||
                        (page >= pagination.current_page - 1 && page <= pagination.current_page + 1)
                    ) {
                        buttons.push(`
                            <button
                                type="button"
                                class="page-btn rounded-lg border px-3 py-2 text-sm ${page === pagination.current_page ? 'border-[#c79a2b] bg-[#c79a2b] text-white' : 'border-[#eadfbe] bg-white text-[#87671c] hover:bg-[#fff8e7]'}"
                                data-page="${page}">
                                ${page}
                            </button>
                        `);
                    } else if (page === pagination.current_page - 2 || page === pagination.current_page + 2) {
                        buttons.push('<span class="px-1 text-slate-400">...</span>');
                    }
                }

                buttons.push(`
                    <button
                        type="button"
                        class="page-btn rounded-lg border px-3 py-2 text-sm ${pagination.current_page === pagination.last_page ? 'cursor-not-allowed border-slate-200 bg-slate-100 text-slate-400' : 'border-[#eadfbe] bg-white text-[#87671c] hover:bg-[#fff8e7]'}"
                        data-page="${pagination.current_page + 1}"
                        ${pagination.current_page === pagination.last_page ? 'disabled' : ''}>
                        Next
                    </button>
                `);

                paginationWrapper.innerHTML = buttons.join('');
            }

            function actionsHtml(record) {
                const permissions = record.permissions || {};
                const buttons = [];

                if (permissions.can_view) {
                    buttons.push(`
                        <a
                            href="${record.show_url}"
                            class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-[#eadfbe] bg-white text-[#87671c] transition hover:bg-[#fff8e7]"
                            title="View">
                            <i class="fa-solid fa-eye text-[13px]"></i>
                        </a>
                    `);
                }

                if (!record.deleted_at && permissions.can_send_booking) {
                    if (record.send_booking_id) {
                        buttons.push(`
                            <button
                                type="button"
                                id="speedBtn-${record.id}"
                                class="speed-view-btn inline-flex h-9 w-9 items-center justify-center rounded-lg border border-purple-200 bg-purple-50 text-purple-600 transition hover:bg-purple-100"
                                data-id="${record.id}"
                                title="Speed">
                                <span class="icon-box flex items-center justify-center">
                                    <i class="fa-solid fa-bolt text-[13px]"></i>
                                </span>
                            </button>
                        `);
                    } else {
                        buttons.push(`
                            <button
                                type="button"
                                id="sendBtn-${record.id}"
                                class="send-booking-btn inline-flex h-9 w-9 items-center justify-center rounded-lg border border-green-200 bg-green-50 text-green-600 transition hover:bg-green-100"
                                data-id="${record.id}"
                                data-email="${escapeHtml(record.email || '')}"
                                title="Send Booking">
                                <span class="icon-box flex items-center justify-center">
                                    <i class="fa-solid fa-paper-plane text-[13px]"></i>
                                </span>
                            </button>
                        `);
                    }
                }

                if (!record.deleted_at && permissions.can_delete) {
                    buttons.push(`
                        <button
                            type="button"
                            class="delete-booking-btn inline-flex h-9 w-9 items-center justify-center rounded-lg border border-red-200 bg-red-50 text-red-600 transition hover:bg-red-100"
                            data-url="${record.delete_url}"
                            title="Delete">
                            <i class="fa-solid fa-trash-can text-[13px]"></i>
                        </button>
                    `);
                }

                if (record.deleted_at && permissions.can_restore) {
                    buttons.push(`
                        <button
                            type="button"
                            class="restore-booking-btn inline-flex h-9 w-9 items-center justify-center rounded-lg border border-emerald-200 bg-emerald-50 text-emerald-600 transition hover:bg-emerald-100"
                            data-url="${record.restore_url}"
                            title="Restore">
                            <i class="fa-solid fa-recycle text-[13px]"></i>
                        </button>
                    `);
                }

                if (!buttons.length) {
                    return '<span class="text-xs text-slate-400">No Actions</span>';
                }

                return `<div class="flex flex-wrap items-center gap-2">${buttons.join('')}</div>`;
            }

            function renderRows(items) {
                if (!items.length) {
                    recordsTableBody.innerHTML = `
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center text-slate-500">
                                No bookings found.
                            </td>
                        </tr>
                    `;
                    return;
                }

                recordsTableBody.innerHTML = items.map((record) => `
                    <tr class="transition hover:bg-[#fffdf7]">
                        <td class="px-6 py-4">${actionsHtml(record)}</td>

                        <td class="px-6 py-4">
                            <div class="space-y-1">
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="text-sm font-semibold text-slate-800">${escapeHtml(record.name || '-')}</span>
                                    ${record.deleted_at ? '<span class="rounded-full bg-red-50 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wide text-red-600">Trashed</span>' : ''}
                                </div>
                                <div class="text-xs text-slate-500">${escapeHtml(record.number || '-')}</div>
                                <div class="text-xs text-slate-500">${escapeHtml(record.email || '-')}</div>
                            </div>
                        </td>

                        <td class="px-6 py-4">
                            <div class="space-y-1">
                                <div class="text-xs text-slate-700">${escapeHtml(record.start_date || '-')} ${escapeHtml(record.start_time || '')}</div>
                                <div class="text-xs text-slate-700">${escapeHtml(record.end_date || '-')} ${escapeHtml(record.end_time || '')}</div>
                                <div class="text-xs text-slate-500">${escapeHtml(record.rental_type || '-')} / ${escapeHtml(record.resident_tourist || '-')}</div>
                            </div>
                        </td>

                        <td class="px-6 py-4">
                            <div class="space-y-1">
                                <div class="text-xs text-slate-700">Flow: ${escapeHtml(record.payment_flow || '-')}</div>
                                <div class="text-xs text-slate-500">Amount: ${currencyHtml(record.total_amount || '0.00')}</div>
                                <div class="text-xs text-slate-500">Status: ${escapeHtml(record.paid_status || '-')}</div>
                                <div class="text-xs text-slate-500">Via: ${escapeHtml(record.paid_via || '-')}</div>
                            </div>
                        </td>

                        <td class="px-6 py-4 text-sm text-slate-600 whitespace-nowrap">
                            ${escapeHtml(record.created_at_human || '-')}
                        </td>
                    </tr>
                `).join('');
            }

            async function fetchRecords() {
                if (state.loading) return;

                state.loading = true;
                state.requestId += 1;

                const currentRequestId = state.requestId;
                setLoading();
                updateTrashUI();
                updateExportUrl();

                const params = new URLSearchParams();

                if (state.search) {
                    params.set('search', state.search);
                }

                if (Number(state.is_deleted) === 1) {
                    params.set('is_deleted', '1');
                }

                if (state.page > 1) {
                    params.set('page', state.page);
                }

                try {
                    const response = await fetch(`${endpoint}?${params.toString()}`, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json',
                        }
                    });

                    const result = await response.json();

                    if (currentRequestId !== state.requestId) return;

                    if (!response.ok || !result.status) {
                        throw new Error(result.message || 'Failed to fetch bookings.');
                    }

                    renderRows(result.data.items || []);
                    renderPagination(result.data.pagination || {});
                    renderMeta(result.data.pagination || {});
                    syncUrl();
                } catch (error) {
                    recordsTableBody.innerHTML = `
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center text-red-500">
                                ${escapeHtml(error.message || 'Something went wrong.')}
                            </td>
                        </tr>
                    `;
                    if (paginationWrapper) paginationWrapper.innerHTML = '';
                    renderMeta({ from: 0, to: 0, total: 0 });
                } finally {
                    state.loading = false;
                }
            }

            async function deleteBooking(url) {
                if (!url) return;

                const result = await Swal.fire({
                    title: 'Delete Booking?',
                    text: 'This booking will be deleted.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, Delete',
                    cancelButtonText: 'Cancel',
                    reverseButtons: true,
                    focusCancel: true,
                    confirmButtonColor: '#dc2626',
                    cancelButtonColor: '#94a3b8',
                    customClass: {
                        popup: 'rounded-3xl',
                        confirmButton: 'rounded-xl px-5 py-2',
                        cancelButton: 'rounded-xl px-5 py-2',
                    }
                });

                if (!result.isConfirmed) return;

                try {
                    const response = await fetch(url, {
                        method: 'POST',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrfToken(),
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({ _method: 'DELETE' })
                    });

                    const data = await response.json();

                    if (!response.ok || !data.status) {
                        throw new Error(data.message || 'Booking delete failed.');
                    }

                    Swal.fire({
                        title: 'Deleted',
                        text: data.message || 'Booking deleted successfully.',
                        icon: 'success',
                        timer: 1200,
                        showConfirmButton: false,
                    });

                    fetchRecords();
                } catch (error) {
                    Swal.fire({
                        title: 'Error',
                        text: error.message || 'Something went wrong.',
                        icon: 'error',
                        confirmButtonColor: '#c79a2b',
                    });
                }
            }


            async function restoreBooking(url) {
                if (!url) return;

                const result = await Swal.fire({
                    title: 'Restore Booking?',
                    text: 'This booking will be moved back to active bookings.',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, Restore',
                    cancelButtonText: 'Cancel',
                    reverseButtons: true,
                    focusCancel: true,
                    confirmButtonColor: '#16a34a',
                    cancelButtonColor: '#94a3b8',
                    customClass: {
                        popup: 'rounded-3xl',
                        confirmButton: 'rounded-xl px-5 py-2',
                        cancelButton: 'rounded-xl px-5 py-2',
                    }
                });

                if (!result.isConfirmed) return;

                try {
                    const response = await fetch(url, {
                        method: 'POST',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrfToken(),
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({ _method: 'PUT' })
                    });

                    const data = await response.json();

                    if (!response.ok || !data.status) {
                        throw new Error(data.message || 'Booking restore failed.');
                    }

                    Swal.fire({
                        title: 'Restored',
                        text: data.message || 'Booking restored successfully.',
                        icon: 'success',
                        timer: 1200,
                        showConfirmButton: false,
                    });

                    fetchRecords();
                } catch (error) {
                    Swal.fire({
                        title: 'Error',
                        text: error.message || 'Something went wrong.',
                        icon: 'error',
                        confirmButtonColor: '#c79a2b',
                    });
                }
            }

            if (searchForm) {
                searchForm.addEventListener('submit', function (event) {
                    event.preventDefault();
                    state.search = searchInput.value.trim();
                    state.page = 1;
                    fetchRecords();
                });
            }

            if (resetBtn) {
                resetBtn.addEventListener('click', function (event) {
                    event.preventDefault();
                    state.search = '';
                    state.is_deleted = 0;
                    state.page = 1;
                    if (searchInput) searchInput.value = '';
                    fetchRecords();
                });
            }


            if (trashToggleBtn) {
                trashToggleBtn.addEventListener('click', function () {
                    state.is_deleted = Number(state.is_deleted) === 1 ? 0 : 1;
                    state.page = 1;
                    fetchRecords();
                });
            }

            if (paginationWrapper) {
                paginationWrapper.addEventListener('click', function (event) {
                    const btn = event.target.closest('.page-btn');

                    if (!btn || btn.disabled) return;

                    const page = parseInt(btn.dataset.page || '1', 10);

                    if (!page || page < 1 || page === state.page) return;

                    state.page = page;
                    fetchRecords();
                });
            }

            recordsTableBody.addEventListener('click', function (event) {
                const sendBookingBtn = event.target.closest('.send-booking-btn');

                if (sendBookingBtn) {
                    if (typeof window.prepareSendModal === 'function') {
                        window.prepareSendModal(
                            sendBookingBtn,
                            Number(sendBookingBtn.dataset.id),
                            sendBookingBtn.dataset.email || ''
                        );
                    }
                    return;
                }

                const speedViewBtn = event.target.closest('.speed-view-btn');

                if (speedViewBtn) {
                    if (typeof window.openSpeedViewModal === 'function') {
                        window.openSpeedViewModal(Number(speedViewBtn.dataset.id));
                    }
                    return;
                }

                const deleteBtn = event.target.closest('.delete-booking-btn');

                if (deleteBtn) {
                    deleteBooking(deleteBtn.dataset.url);
                    return;
                }

                const restoreBtn = event.target.closest('.restore-booking-btn');

                if (restoreBtn) {
                    restoreBooking(restoreBtn.dataset.url);
                }
            });

            window.refreshBookingsTable = fetchRecords;
            fetchRecords();
        });
    </script>
@endpush

@push('styles')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
    <style>
        #sendModal > div {
            border: 1px solid #eadfbe;
            background: radial-gradient(circle at top, #fffdf7 0%, #fffaf0 36%, #f8f5ea 100%);
            box-shadow: 0 28px 90px rgba(15, 23, 42, 0.28);
        }

        #sendModal > div > .flex:first-child {
            position: relative;
            border-bottom-color: #efe5cb;
            background: linear-gradient(135deg, rgba(214, 171, 61, 0.16), rgba(255, 255, 255, 0));
        }

        #sendModal > div > .flex:first-child h2 {
            font-size: 1.65rem;
            line-height: 1.15;
            color: #0f172a;
            letter-spacing: -0.02em;
        }

        #sendModal > div > .flex:first-child p {
            margin-top: 0.25rem;
            font-size: 0.84rem;
            color: #64748b;
        }

        #sendModal > div > .flex:first-child button {
            border: 1px solid #ebe2cb;
            background: rgba(255, 255, 255, 0.9);
            border-radius: 1rem;
            box-shadow: 0 4px 16px rgba(15, 23, 42, 0.06);
        }

        #sendModal form > div,
        #sendModal form > .mt-8,
        #sendModal form > .mt-6 {
            border: 1px solid #eadfbe;
            border-radius: 26px;
            background: linear-gradient(180deg, rgba(255, 253, 247, 0.96) 0%, rgba(255, 255, 255, 0.98) 100%);
            box-shadow: 0 10px 28px rgba(15, 23, 42, 0.05);
        }

        #sendModal form > .grid.grid-cols-1.sm\:grid-cols-2.lg\:grid-cols-3.gap-5.mt-6,
        #sendModal form > .mt-8,
        #sendModal form > .mt-6 {
            padding: 1.25rem;
        }

        #sendModal form > .mt-8.bg-gray-50.border.border-gray-200.rounded-2xl.p-5.grid {
            background: linear-gradient(135deg, #fffefb, #f7f1df);
            border-color: #e8dec1;
        }

        #sendModal form > .bg-gray-50\/50.border.border-gray-100.rounded-2xl.p-5,
        #sendModal form > .mt-8.bg-gray-50\/70.border.border-gray-200.rounded-2xl.p-5 {
            padding: 1.25rem;
        }

        #sendModal form h3 {
            color: #0f172a;
            letter-spacing: -0.01em;
        }

        #sendModal label {
            color: #334155;
            font-weight: 700;
        }

        #sendModal input:not([type="hidden"]),
        #sendModal textarea,
        #sendModal select {
            border-color: #d8cda9 !important;
            background: #fffefb;
            color: #0f172a;
            border-radius: 1rem;
            transition: border-color .2s ease, box-shadow .2s ease, background-color .2s ease;
        }

        #sendModal input:not([type="hidden"]):focus,
        #sendModal textarea:focus,
        #sendModal select:focus {
            border-color: #c79a2b !important;
            box-shadow: 0 0 0 4px rgba(214, 171, 61, 0.16);
            background: #ffffff;
        }

        #sendModal input[readonly] {
            background: #f8fafc;
            color: #475569;
        }

        #sendModal textarea {
            min-height: 96px;
        }

        #sendModal .bg-white.border.border-gray-200.rounded-xl.p-4,
        #sendModal .bg-white.border.border-gray-200.rounded-xl.p-4.mb-5 {
            border-radius: 1.1rem;
            border-color: #e5e7eb;
            box-shadow: 0 8px 24px rgba(15, 23, 42, 0.05);
        }

        #sendModal .bg-white.border.border-gray-200.rounded-xl.p-4 h4,
        #sendModal .bg-white.border.border-gray-200.rounded-xl.p-4.mb-5 h4 {
            color: #64748b;
            font-size: 0.78rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.12em;
        }

        #sendModal #addCharge {
            border-radius: 1rem;
            font-weight: 700;
            box-shadow: 0 10px 24px rgba(5, 150, 105, 0.2);
        }

        #sendModal > div > .flex.flex-col-reverse,
        #sendModal > div > div:last-child {
            border-top-color: #efe5cb;
            background: rgba(255, 255, 255, 0.92);
        }

        #sendBookingSubmitBtn {
            background: #c89d2d !important;
            border-radius: 1rem;
            box-shadow: 0 16px 30px rgba(200, 157, 45, 0.28) !important;
        }

        #sendBookingSubmitBtn:hover {
            background: #b98d1e !important;
        }

        #speedViewModal > div {
            border: 1px solid #eadfbe;
            border-radius: 28px;
            background: linear-gradient(180deg, #fffdf7 0%, #faf7ef 100%);
            box-shadow: 0 24px 80px rgba(15, 23, 42, 0.24);
        }

        #speedViewModal > div > .flex:first-child {
            border-bottom-color: #ede3ca;
        }

        #speedViewModal > div > .flex:first-child h2 {
            color: #0f172a;
            font-weight: 800;
            letter-spacing: -0.01em;
        }

        #speedViewModal .bg-gray-50 {
            border: 1px solid #e8ddbf;
            background: linear-gradient(180deg, #fffefb 0%, #ffffff 100%);
            border-radius: 1.35rem;
            box-shadow: 0 8px 24px rgba(15, 23, 42, 0.05);
        }

        #speedViewModal .bg-gray-50 h3 {
            margin-bottom: 0.75rem;
            color: #8b6a1c;
            font-size: 0.8rem;
            font-weight: 800;
            letter-spacing: 0.12em;
            text-transform: uppercase;
        }

        #speedViewModal > div > .p-4.border-t {
            border-top-color: #ede3ca;
            background: rgba(255, 255, 255, 0.92);
        }

        #speedViewModal > div > .p-4.border-t button {
            border-radius: 1rem;
            font-weight: 700;
        }

        .select2-container .select2-selection--single {
            min-height: 48px;
            border-radius: 1rem;
            border-color: #d8cda9;
            display: flex;
            align-items: center;
            background: #fffefb;
        }

        .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 46px;
            padding-left: 12px;
            padding-right: 32px;
            color: #0f172a;
        }

        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 46px;
        }

        .select2-dropdown {
            border-radius: 1rem;
            border-color: #d8cda9;
            overflow: hidden;
            box-shadow: 0 18px 36px rgba(15, 23, 42, 0.12);
        }

        .select2-container--default.select2-container--focus .select2-selection--single {
            border-color: #c79a2b;
            box-shadow: 0 0 0 4px rgba(214, 171, 61, 0.16);
        }

        @media (max-width: 768px) {
            #sendModal form > div,
            #sendModal form > .mt-8,
            #sendModal form > .mt-6 {
                border-radius: 22px;
                padding: 1rem;
            }

            #speedViewModal .bg-gray-50 {
                border-radius: 1.1rem;
            }
        }
    </style>
@endpush

@push('scripts')
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <script>
        let debounceTimer;
        let vehiclesList = [];
        let chargesSettings = [];
        window.latestPayload = window.latestPayload || {};

        document.addEventListener('DOMContentLoaded', function () {
            loadVehicles();
            loadVehicleGroups();
            loadLocations();
            loadChargesSettings();

            $('#addCharge').on('click', function () {
                addChargeRow();
            });

            $(document).on('input', 'input[name="discount"], input[name="chargesTax"]', function () {
                calculateFinalAmount();
            });

            $('#sendForm').on('submit', function (event) {
                event.preventDefault();

                const formData = new FormData(this);
                const inquiryId = $('#inquiry_id').val(); // booking id is passed in this field for booking page
                const email = $('#customerEmail').val();
                const submitBtn = $('#sendBookingSubmitBtn');

                submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Processing...');

                $.ajax({
                    url: @json(route('get.customer.by.email')),
                    method: 'POST',
                    data: {
                        _token: @json(csrf_token()),
                        email: email
                    },
                    success: function (response) {
                        if (response.success && response.result) {
                            formData.set('customerId', response.result.customerId || response.result.id || '');
                            proceedBooking(formData, inquiryId);
                            return;
                        }

                        createCustomerAndProceed(formData, inquiryId);
                    },
                    error: function (xhr) {
                        const message = xhr.responseJSON?.error || xhr.responseJSON?.message || 'Customer check failed';
                        showToast(message, 'error');
                        resetSubmitButton();
                    }
                });
            });

            const speedViewModal = document.getElementById('speedViewModal');
            if (speedViewModal) {
                speedViewModal.addEventListener('click', function (event) {
                    if (event.target === speedViewModal) {
                        closeSpeedViewModal();
                    }
                });
            }
        });

        $('#vehicleSelect').on('change', function () {
            const selectedOption = this.options[this.selectedIndex];

            if (!selectedOption.value) {
                resetVehicleFields();
                return;
            }

            const vehicle = JSON.parse(selectedOption.dataset.vehicle || '{}');
            $('#vehicleId').val(vehicle.id || '');
            $('#tariffGroupId').val(vehicle.tariffGroupId || '');
            $('#plateNo').val(vehicle.plateNo || '');
            $('#vehicleTitle').val(vehicle.makeModelVariant || '');
        });

        $(document).on('change', '.chargeType', function () {
            const card = $(this).closest('.charge-card');
            const chargeTypeId = $(this).val();
            const filtered = chargesSettings.filter(item => String(item.chargesTypeId) === String(chargeTypeId));

            let rateOptions = '<option value="">Rate Type</option>';
            filtered.forEach(item => {
                if (item.rateType) {
                    rateOptions += `<option value="${item.rateType.id}">${item.rateType.name}</option>`;
                }
            });

            card.find('.rateType').html(rateOptions).trigger('change');

            if (filtered.length > 0) {
                const item = filtered[0];
                card.find('.taxCodeId').val(item.taxCodeId ?? '');
                card.find('.module').val(item.module ?? '');
                card.find('.order').val(item.order ?? '');
                card.find('.applyOnClosing').val(item.applyOnClosing ?? false);
                card.find('.isMandatory').val(item.isMandatory ?? false);
                card.find('.isStatic').val(item.isStatic ?? false);
                card.find('.taxable').val(item.taxable ?? false);
            }
        });

        $(document).on('input', '.rate, .units', function () {
            const card = $(this).closest('.charge-card');
            const rate = parseFloat(card.find('.rate').val()) || 0;
            const units = parseFloat(card.find('.units').val()) || 0;
            card.find('.total').val(rate * units);
            calculateTotalCharges();
        });

        $(document).on('click', '.removeCharge', function () {
            $(this).closest('.charge-card').remove();
            calculateTotalCharges();
        });

        function resetVehicleFields() {
            $('#vehicleId').val('');
            $('#tariffGroupId').val('');
            $('#plateNo').val('');
            $('#vehicleTitle').val('');
        }

        function loadChargesSettings() {
            fetch(@json(url('/api/speed/getChargesSettings')))
                .then(res => res.json())
                .then(data => {
                    chargesSettings = data.items || [];
                    if (!$('#chargesWrapper').children().length) {
                        addChargeRow();
                    }
                });
        }

        function addChargeRow() {
            const html = `
                <div class="charge-card border border-gray-200 rounded-2xl p-5 mb-4 bg-white shadow-sm relative transition-all hover:shadow-md">
                    <button type="button" class="removeCharge absolute top-3 right-3 h-8 w-8 flex items-center justify-center rounded-full bg-gray-50 hover:bg-red-50 text-gray-400 hover:text-red-500 transition-all">&times;</button>
                    <div class="grid grid-cols-1 md:grid-cols-5 gap-4 mt-2">
                        <div>
                            <label class="block text-xs font-bold text-gray-600 mb-1.5">Charge Type</label>
                            <select class="chargeType border border-gray-300 p-2.5 rounded-xl w-full text-sm">
                                <option value="">Select Charge Type</option>
                                ${getChargeTypeOptions()}
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-600 mb-1.5">Rate Type</label>
                            <select class="rateType border border-gray-300 p-2.5 rounded-xl w-full text-sm">
                                <option value="">Select Rate Type</option>
                                ${getRateTypeOptions()}
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-600 mb-1.5">Rate</label>
                            <input type="number" class="rate border border-gray-300 p-2.5 rounded-xl w-full text-sm outline-none" placeholder="Rate">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-600 mb-1.5">Units</label>
                            <input type="number" class="units border border-gray-300 p-2.5 rounded-xl w-full text-sm outline-none" placeholder="Qty">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-600 mb-1.5">Total</label>
                            <input type="number" class="total border border-gray-200 p-2.5 rounded-xl bg-gray-100 text-gray-700 font-semibold w-full text-sm cursor-not-allowed outline-none" readonly placeholder="Calculated total">
                        </div>
                    </div>
                    <div class="mt-4">
                        <label class="block text-xs font-bold text-gray-600 mb-1.5">Notes</label>
                        <input type="text" class="notes border border-gray-300 p-2.5 rounded-xl w-full text-sm outline-none" placeholder="Internal remarks for this specific surcharge...">
                    </div>
                    <input type="hidden" class="taxCodeId">
                    <input type="hidden" class="module">
                    <input type="hidden" class="order">
                    <input type="hidden" class="applyOnClosing">
                    <input type="hidden" class="isMandatory">
                    <input type="hidden" class="isStatic">
                    <input type="hidden" class="taxable">
                </div>
            `;

            $('#chargesWrapper').append(html);
            applySelect2();
        }

        function applySelect2() {
            $('.chargeType, .rateType, #vehicleSelect, #vehicleGroupSelect, #locationSelect, #bookingStatus, #bookingType').select2({
                placeholder: 'Select option...',
                width: '100%',
                dropdownParent: $('#sendModal')
            });
        }

        function getChargeTypeOptions() {
            const unique = {};
            chargesSettings.forEach(item => {
                if (item.chargesType) {
                    unique[item.chargesType.id] = item.chargesType.name;
                }
            });

            return Object.entries(unique)
                .map(([id, name]) => `<option value="${id}">${name}</option>`)
                .join('');
        }

        function getRateTypeOptions() {
            const unique = {};
            chargesSettings.forEach(item => {
                if (item.rateType) {
                    unique[item.rateType.id] = item.rateType.name;
                }
            });

            return Object.entries(unique)
                .map(([id, name]) => `<option value="${id}">${name}</option>`)
                .join('');
        }

        function calculateTotalCharges() {
            let total = 0;
            $('.charge-card').each(function () {
                total += parseFloat($(this).find('.total').val()) || 0;
            });
            $('input[name="totalCharges"]').val(total);
            calculateFinalAmount();
        }

        function calculateFinalAmount() {
            const total = parseFloat($('input[name="totalCharges"]').val()) || 0;
            const discount = parseFloat($('input[name="discount"]').val()) || 0;
            const taxAmount = parseFloat($('input[name="chargesTax"]').val()) || 0;
            const finalAmount = Math.round(((total - discount) + taxAmount) * 100) / 100;
            $('input[name="amount"]').val(finalAmount);
        }

        function loadVehicles() {
            const select = $('#vehicleSelect');
            select.prop('disabled', true).html('<option value="">Loading vehicles...</option>');

            fetch(@json(url('/api/speed/getVehicles')))
                .then(res => res.json())
                .then(res => {
                    select.empty().append('<option value="">Select Vehicle</option>');
                    vehiclesList = res.items || [];

                    vehiclesList.forEach(vehicle => {
                        const option = new Option(`${vehicle.makeModelVariant}`, vehicle.id, false, false);
                        option.dataset.vehicle = JSON.stringify(vehicle);
                        select.append(option);
                    });

                    select.prop('disabled', false).trigger('change');
                    applySelect2();
                })
                .catch(() => {
                    select.html('<option value="">Failed to load</option>').prop('disabled', false).trigger('change');
                });
        }

        function loadVehicleGroups() {
            const select = $('#vehicleGroupSelect');
            select.prop('disabled', true).html('<option>Loading vehicle groups...</option>');

            fetch(@json(url('/api/speed/getVehicleGroups')))
                .then(res => res.json())
                .then(res => {
                    select.empty().append('<option value="">Select Vehicle Group</option>');
                    (res.items || []).forEach(item => {
                        select.append(new Option(item.title || item.name, item.id, false, false));
                    });

                    select.prop('disabled', false).trigger('change');
                    applySelect2();
                })
                .catch(() => {
                    select.html('<option>Error loading</option>').prop('disabled', false).trigger('change');
                });
        }

        function loadLocations() {
            const select = $('#locationSelect');
            select.prop('disabled', true).html('<option>Loading locations...</option>');

            fetch(@json(url('/api/speed/getLocations')))
                .then(res => res.json())
                .then(res => {
                    select.empty().append('<option value="">Select Location</option>');
                    (res.items || []).forEach(item => {
                        select.append(new Option(item.name || item.locationName, item.id, false, false));
                    });

                    select.prop('disabled', false).trigger('change');
                    applySelect2();
                })
                .catch(() => {
                    select.html('<option>Error loading</option>').prop('disabled', false).trigger('change');
                });
        }

        function prepareSendModal(el, inquiryId, email) {
            startBtnLoader(el);
            const emailField = document.getElementById('customerEmail');
            emailField.value = email || '';
            emailField.setAttribute('readonly', true);

            searchCustomer(email, function () {
                stopBtnLoader(el);
                openSendModal(inquiryId);
            });
        }

        function openSendModal(id) {
            document.getElementById('inquiry_id').value = id;
            const modal = document.getElementById('sendModal');
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            document.body.classList.add('overflow-hidden');
        }

        function closeSendModal() {
            const modal = document.getElementById('sendModal');
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            document.body.classList.remove('overflow-hidden');

            const form = document.getElementById('sendForm');
            if (form) {
                form.reset();
            }

            $('#chargesWrapper').html('');
            $('#customerId, #vehicleId, #tariffGroupId, #plateNo, #vehicleTitle').val('');
            $('#customerFields').addClass('hidden');
            $('#emailError').addClass('hidden').text('');
            $('#sendForm select').each(function () {
                $(this).val('').trigger('change');
            });
            $('#bookingStatus').val('0').trigger('change');
            $('#bookingType').val('0').trigger('change');
            $('#customerEmail').removeAttr('readonly');
            addChargeRow();
            resetSubmitButton();
        }

        function startBtnLoader(el) {
            if (!el) {
                return;
            }

            el.classList.add('pointer-events-none', 'opacity-70');
            const iconBox = el.querySelector('.icon-box');
            if (iconBox) {
                iconBox.innerHTML = '<i class="fas fa-spinner fa-spin text-sm"></i>';
            }

            const text = el.querySelector('.btn-text');
            if (text) {
                text.innerText = 'Processing...';
            }
        }

        function stopBtnLoader(el) {
            if (!el) {
                return;
            }

            el.classList.remove('pointer-events-none', 'opacity-70');
            const iconBox = el.querySelector('.icon-box');
            if (iconBox) {
                iconBox.innerHTML = '<i class="fas fa-paper-plane text-sm"></i>';
            }

            const text = el.querySelector('.btn-text');
            if (text) {
                text.innerText = 'Send';
            }
        }

        function searchCustomer(value, callback) {
            clearTimeout(debounceTimer);

            const emailErrorEl = document.getElementById('emailError');
            const customerFieldsEl = document.getElementById('customerFields');

            if (!value || value.length < 3) {
                customerFieldsEl.classList.add('hidden');
                emailErrorEl.textContent = '';
                emailErrorEl.classList.add('hidden');
                if (callback) callback();
                return;
            }

            debounceTimer = setTimeout(() => {
                fetch(@json(route('get.customer.by.email')), {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ email: value })
                })
                    .then(res => res.json())
                    .then(res => {
                        if (res.success && res.result) {
                            const customer = res.result;
                            const address = customerAddress(customer);

                            document.getElementById('customerId').value = pickCustomerValue(customer, ['customerId', 'CustomerId', 'id', 'Id']);
                            document.getElementById('firstName').value = pickCustomerValue(customer, ['firstName', 'FirstName']) || pickCustomerValue(customer.customer, ['firstName', 'FirstName']);
                            document.getElementById('lastName').value = pickCustomerValue(customer, ['lastName', 'LastName']) || pickCustomerValue(customer.customer, ['lastName', 'LastName']);
                            document.getElementById('mobileNo').value = pickCustomerValue(customer, ['mobileNo', 'MobileNo', 'phone', 'Phone']) || pickCustomerValue(customer.customer, ['mobileNo', 'MobileNo', 'phone', 'Phone']);
                            document.getElementById('city').value = pickCustomerValue(address, ['city', 'City']);
                            document.getElementById('country').value = pickCustomerValue(address, ['country', 'Country']);
                            document.getElementById('street').value = pickCustomerValue(address, ['street', 'Street', 'addressLine1', 'AddressLine1']);
                            document.getElementById('state').value = pickCustomerValue(address, ['state', 'State']);
                            document.getElementById('postalCode').value = pickCustomerValue(address, ['postalCode', 'PostalCode', 'zipCode', 'ZipCode']);

                            showCustomerFieldsReadonly();
                            emailErrorEl.textContent = '';
                            emailErrorEl.classList.add('hidden');
                        } else {
                            makeCustomerFieldsEditable();
                            emailErrorEl.textContent = res.error || '';
                            if (res.error) {
                                emailErrorEl.classList.remove('hidden');
                            } else {
                                emailErrorEl.classList.add('hidden');
                            }
                        }

                        if (callback) callback();
                    })
                    .catch(() => {
                        customerFieldsEl.classList.add('hidden');
                        emailErrorEl.textContent = 'Something went wrong';
                        emailErrorEl.classList.remove('hidden');
                        if (callback) callback();
                    });
            }, 500);
        }

        function showCustomerFieldsReadonly() {
            const fields = document.querySelectorAll('#customerFields input');
            document.getElementById('customerFields').classList.remove('hidden');
            fields.forEach(el => {
                el.setAttribute('readonly', true);
                el.classList.add('bg-gray-100');
            });
            document.getElementById('state').removeAttribute('readonly');
            document.getElementById('postalCode').removeAttribute('readonly');
            document.getElementById('state').classList.remove('bg-gray-100');
            document.getElementById('postalCode').classList.remove('bg-gray-100');
        }

        function makeCustomerFieldsEditable() {
            const fields = document.querySelectorAll('#customerFields input');
            document.getElementById('customerFields').classList.remove('hidden');
            document.getElementById('customerId').value = '';
            fields.forEach(el => {
                el.removeAttribute('readonly');
                el.classList.remove('bg-gray-100');
                if (el.id !== 'customerEmail') {
                    el.value = '';
                }
            });
        }

        function showToast(message, type = 'success') {
            const bg = type === 'success' ? 'bg-green-600' : 'bg-red-600';
            const toast = $(`<div class="fixed top-5 right-5 z-[9999] ${bg} text-white px-5 py-3 rounded-xl shadow-lg">${message}</div>`);
            $('body').append(toast);
            setTimeout(() => {
                toast.fadeOut(300, function () {
                    $(this).remove();
                });
            }, 3000);
        }

        function resetSubmitButton() {
            $('#sendBookingSubmitBtn').prop('disabled', false).html('<i class="fas fa-paper-plane"></i> Send Booking Data');
        }

        function pickCustomerValue(source, keys = []) {
            if (!source || typeof source !== 'object') {
                return '';
            }

            for (const key of keys) {
                const value = source[key];
                if (value !== undefined && value !== null && String(value).trim() !== '') {
                    return value;
                }
            }

            return '';
        }

        function customerAddress(customer) {
            return customer?.address || customer?.Address || customer?.customer?.address || customer?.customer?.Address || {};
        }

        function syncCustomerFieldsToFormData(formData) {
            ['customerId', 'customerEmail', 'firstName', 'lastName', 'mobileNo', 'street', 'city', 'state', 'postalCode', 'country']
                .forEach((field) => {
                    const element = document.getElementById(field);
                    if (element) {
                        formData.set(field, element.value || '');
                    }
                });
        }

        function proceedBooking(formData, inquiryId) {
            const charges = [];

            $('.charge-card').each(function () {
                charges.push({
                    order: parseInt($(this).find('.order').val(), 10) || 0,
                    chargesTypeId: parseInt($(this).find('.chargeType').val(), 10) || 0,
                    rateTypeId: parseInt($(this).find('.rateType').val(), 10) || 0,
                    rate: parseFloat($(this).find('.rate').val()) || 0,
                    units: parseFloat($(this).find('.units').val()) || 0,
                    charges: parseFloat($(this).find('.total').val()) || 0,
                    applyOnClosing: String($(this).find('.applyOnClosing').val()) === 'true',
                    isMandatory: String($(this).find('.isMandatory').val()) === 'true',
                    taxable: String($(this).find('.taxable').val()) === 'true',
                    accepted: true,
                    included: false,
                    excessRate: 0,
                    limit: 0,
                    discountPercent: 0,
                    notes: $(this).find('.notes').val(),
                    tax: 0
                });
            });

            formData.set('charges_json', JSON.stringify(charges));
            formData.set('inquiry_id', inquiryId);
            syncCustomerFieldsToFormData(formData);

            $.ajax({
                url: @json(route('send.booking')),
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function (res) {
                    resetSubmitButton();

                    if (!res.success) {
                        showToast(res.error || 'Booking failed', 'error');
                        return;
                    }

                    if (res.result) {
                        window.latestPayload[inquiryId] = {
                            booking: res.result,
                            send_booking_id: res.result.id || null
                        };
                    }

                    showToast('Booking sent successfully.', 'success');
                    closeSendModal();

                    const sendBtn = document.getElementById(`sendBtn-${inquiryId}`);
                    if (sendBtn) {
                        sendBtn.outerHTML = `
                            <button
                                type="button"
                                id="speedBtn-${inquiryId}"
                                class="speed-view-btn inline-flex h-9 w-9 items-center justify-center rounded-lg border border-purple-200 bg-purple-50 text-purple-600 transition hover:bg-purple-100"
                                data-id="${inquiryId}"
                                title="Speed">
                                <span class="icon-box flex items-center justify-center">
                                    <i class="fa-solid fa-bolt text-[13px]"></i>
                                </span>
                            </button>
                        `;
                    }

                    if (typeof window.refreshBookingsTable === 'function') {
                        window.refreshBookingsTable();
                    }
                },
                error: function (xhr) {
                    resetSubmitButton();
                    const message = xhr.responseJSON?.error || xhr.responseJSON?.message || 'Something went wrong';
                    showToast(message, 'error');
                }
            });
        }

        function createCustomerAndProceed(formData, inquiryId) {
            $.ajax({
                url: @json(route('create.customer')),
                method: 'POST',
                data: {
                    _token: @json(csrf_token()),
                    firstName: $('#firstName').val(),
                    lastName: $('#lastName').val(),
                    email: $('#customerEmail').val(),
                    mobileNo: $('#mobileNo').val(),
                    locationId: 1,
                    street: $('#street').val(),
                    city: $('#city').val(),
                    state: $('#state').val(),
                    postalCode: $('#postalCode').val(),
                    country: $('#country').val()
                },
                success: function (res) {
                    if (res.success && res.result) {
                        formData.set('customerId', res.result.id);
                        proceedBooking(formData, inquiryId);
                        return;
                    }

                    showToast(res.error || 'Customer create failed', 'error');
                    resetSubmitButton();
                },
                error: function (xhr) {
                    const message = xhr.responseJSON?.error || xhr.responseJSON?.message || 'Customer create failed';
                    showToast(message, 'error');
                    resetSubmitButton();
                }
            });
        }

        function detailRow(label, value) {
            return `<div><b>${label}:</b> ${value ?? '-'}</div>`;
        }

        function renderSpeedView(data) {
            if (!data || !data.booking) {
                return;
            }

            const booking = data.booking;
            const vehicle = booking.vehicle || {};
            const tariff = vehicle.tariffGroup || vehicle.tariffgroup || {};
            const customer = booking.customer || {};
            const address = customer.address || {};
            const billing = booking.billingDetail || booking.BillingDetail || {};
            const creditCard = billing.creditCard || billing.CreditCard || {};
            const contactCard = creditCard.contactCard || creditCard.ContactCard || {};

            document.getElementById('bookingInfo').innerHTML = [
                detailRow('Booking Id', booking.id),
                detailRow('Agreement No', booking.agreementNo),
                detailRow('Status', booking.bookingStatus),
                detailRow('Start', booking.startDate ? new Date(booking.startDate).toLocaleString() : '-'),
                detailRow('End', booking.endDate ? new Date(booking.endDate).toLocaleString() : '-'),
                detailRow('Location', booking.locationId),
                detailRow('Advance', booking.advance || 0),
                detailRow('Tax Percent', booking.taxPercent || 0),
                detailRow('Discount', booking.discount || 0),
                detailRow('Tax', booking.tax || 0),
                detailRow('Total Charges', booking.totalCharges || 0),
                detailRow('Booking Type', booking.bookingType || '-'),
                detailRow('Notes', booking.notes || '-')
            ].join('');

            document.getElementById('vehicleInfo').innerHTML = [
                detailRow('Plate No', vehicle.plateNo),
                detailRow('Model', tariff.title || tariff.Title),
                detailRow('Sub Title', tariff.subTitle || tariff.SubTitle),
                detailRow('Seats', tariff.passengerCapacity || tariff.PassengerCapacity),
                detailRow('Large Bags', tariff.largeBagsCapacity || tariff.LargeBagsCapacity),
                detailRow('Small Bags', tariff.smallBagsCapacity || tariff.SmallBagsCapacity)
            ].join('');

            document.getElementById('customerInfo').innerHTML = [
                detailRow('Name', `${customer.firstName || ''} ${customer.lastName || ''}`.trim() || '-'),
                detailRow('Email', customer.email),
                detailRow('Phone', customer.mobileNo),
                detailRow('City', address.city),
                detailRow('Street', address.street || address.addressLine1),
                detailRow('Zip Code', address.zipCode || address.postalCode),
                detailRow('Country', address.country)
            ].join('');

            if (Array.isArray(booking.charges) && booking.charges.length) {
                document.getElementById('chargesList').innerHTML = booking.charges.map(charge => `
                    <div class="border p-2 rounded-lg bg-white mb-2">
                        <b>Rate:</b> ${charge.rate} × ${charge.units} = ${charge.charges}<br>
                        <small>Type ID: ${charge.chargesTypeId} | Rate Type: ${charge.rateTypeId} | Notes: ${charge.notes || '-'}</small>
                    </div>
                `).join('');
            } else {
                document.getElementById('chargesList').innerHTML = '<div>No charges available</div>';
            }

            document.getElementById('billingInfo').innerHTML = `
                <div class="bg-gray-100 p-2 rounded mb-2">
                    <h4 class="font-semibold mb-1">Credit Card Info</h4>
                    ${detailRow('Transaction No', creditCard.transactionNo || creditCard.TransactionNo)}
                    ${detailRow('Card Last Digits', creditCard.cardNoLastDigits || creditCard.CardNoLastDigits)}
                    ${detailRow('Card Holder', creditCard.cardHolderName || creditCard.CardHolderName)}
                    ${detailRow('Expiry Date', creditCard.expiryDate || creditCard.ExpiryDate)}
                    ${detailRow('Commission %', creditCard.commissionPercentage || creditCard.CommissionPercentage || 0)}
                </div>
                <div class="bg-gray-100 p-2 rounded mb-2">
                    <h4 class="font-semibold mb-1">Contact Card Info</h4>
                    ${detailRow('Type', contactCard.type || contactCard.Type)}
                    ${detailRow('Card No', contactCard.cardNo || contactCard.CardNo)}
                    ${detailRow('Last 4 Digits', contactCard.cardNoLastFourDigits || contactCard.CardNoLastFourDigits)}
                    ${detailRow('Expiry', contactCard.expiry || contactCard.Expiry)}
                    ${detailRow('CVV', contactCard.cvv || contactCard.Cvv)}
                    ${detailRow('Name On Card', contactCard.nameOnCard || contactCard.NameOnCard)}
                    ${detailRow('Bank', contactCard.bankName || contactCard.BankName)}
                    ${detailRow('Default', (contactCard.isDefault || contactCard.IsDefault) ? 'Yes' : 'No')}
                    ${detailRow('Contact ID', contactCard.contactId || contactCard.ContactId)}
                    ${detailRow('External Source', contactCard.externalSource || contactCard.ExternalSource)}
                </div>
                <div class="mb-2"><b>Billing Notes:</b> ${billing.notes || billing.Notes || '-'}</div>
            `;
        }

        function openSpeedViewModal(inquiryId) {
            if (window.latestPayload[inquiryId]?.booking) {
                renderSpeedView(window.latestPayload[inquiryId]);
            } else {
                fetch(@json(route('admin.bookings.payload', ['id' => '__ID__'])).replace('__ID__', inquiryId), {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                })
                    .then(res => res.json())
                    .then(res => {
                        if (!res.status || !res.payload) {
                            showToast('Booking data not available', 'error');
                            return;
                        }

                        window.latestPayload[inquiryId] = {
                            booking: res.payload,
                            send_booking_id: res.send_booking_id || null
                        };

                        renderSpeedView(window.latestPayload[inquiryId]);
                    })
                    .catch(() => {
                        showToast('Failed to load booking data', 'error');
                    });
            }

            const modal = document.getElementById('speedViewModal');
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            document.body.classList.add('overflow-hidden');
        }

        function closeSpeedViewModal() {
            const modal = document.getElementById('speedViewModal');
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            document.body.classList.remove('overflow-hidden');
        }

        window.prepareSendModal = prepareSendModal;
        window.closeSendModal = closeSendModal;
        window.openSpeedViewModal = openSpeedViewModal;
        window.closeSpeedViewModal = closeSpeedViewModal;
        window.searchCustomer = searchCustomer;
    </script>
@endpush

