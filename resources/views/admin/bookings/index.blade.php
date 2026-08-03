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
                            <th class="px-6 py-4 text-left text-xs font-semibold uppercase text-[#9d8750]">Self Location IDs</th>
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
                                        @can('Booking_Edit')
                                            @if(!empty($booking->send_booking_id))
                                                <a
                                                    href="{{ route('admin.bookings.edit', $booking->id) }}"
                                                    class="inline-flex items-center rounded-xl border border-[#eadfbe] bg-white px-3 py-2 text-xs font-semibold text-[#7d6220] transition hover:bg-[#fff8e8]"
                                                    title="Edit Booking">
                                                    <i class="fa-solid fa-pen-to-square mr-2 text-[12px]"></i>
                                                </a>
                                            @endif
                                        @endcan
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
                                        <div class="text-xs text-slate-700">Pickup Location ID: {{ $booking->pickup_location_id ?? '-' }}</div>
                                        <div class="text-xs text-slate-700">Self Pickup ID: {{ $booking->self_pickup_location_id ?? '-' }}</div>
                                        <div class="text-xs text-slate-700">Return ID: {{ $booking->self_return_location_id ?? '-' }}</div>
                                        <div class="text-xs text-slate-700">Vehicle Group ID: {{ $booking->vehicle_group_id ?? '-' }}</div>
                                        <div class="text-xs text-slate-700">Tariff Group ID: {{ $booking->tariff_group_id ?? '-' }}</div>
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
                                <td colspan="6" class="px-6 py-12 text-center text-slate-500">No bookings found.</td>
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

        <div id="sendModal" class="fixed inset-0 z-50 hidden items-start justify-center bg-slate-900/60 backdrop-blur-sm overflow-y-auto p-4 pt-6">
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
                        <input type="hidden" name="booking_id" id="booking_id">
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
                            </div>

                            @php
                                $speedCountries = [
                                    'Burundi', 'Cabo Verde', 'Cambodia', 'Cameroon', 'Canada', 'Central African Republic (CAR)',
                                    'Chad', 'Chile', 'China', 'Colombia', 'Comoros', 'Democratic Republic of the Congo',
                                    'Republic of the Congo', 'Costa Rica', "Cote d'Ivoire", 'Croatia', 'Cuba', 'Cyprus',
                                    'Czech Republic', 'Denmark', 'Djibouti', 'Dominica', 'Dominican Republic', 'Ecuador',
                                    'Egypt', 'El Salvador', 'Equatorial Guinea', 'Eritrea', 'Estonia', 'Ethiopia', 'Fiji',
                                    'Finland', 'France', 'Gabon', 'Gambia', 'Georgia', 'Germany', 'Ghana', 'Greece',
                                    'Grenada', 'Guatemala', 'Guinea', 'Guinea-Bissau', 'Guyana', 'Haiti', 'Honduras',
                                    'Hungary', 'Iceland', 'India', 'Indonesia', 'Iran', 'Iraq', 'Ireland', 'Israel',
                                    'Italy', 'Jamaica', 'Japan', 'Jordan', 'Kazakhstan', 'Kenya', 'Kiribati', 'Kosovo',
                                    'Kuwait', 'Kyrgyzstan', 'Laos', 'Latvia', 'Lebanon', 'Lesotho', 'Liberia', 'Libya',
                                    'Liechtenstein', 'Lithuania', 'Luxembourg', 'Macedonia', 'Madagascar', 'Malawi',
                                    'Malaysia', 'Maldives', 'Mali', 'Malta', 'Marshall Islands', 'Mauritania', 'Mauritius',
                                    'Mexico', 'Micronesia', 'Moldova', 'Monaco', 'Mongolia', 'Montenegro', 'Morocco',
                                    'Mozambique', 'Myanmar (Burma)', 'Namibia', 'Nauru', 'Nepal', 'Netherlands',
                                    'New Zealand', 'Nicaragua', 'Niger', 'Nigeria', 'North Korea', 'Norway', 'Oman',
                                    'Pakistan', 'Palau', 'Palestine', 'Panama', 'Papua New Guinea', 'Paraguay', 'Peru',
                                    'Philippines', 'Poland', 'Portugal', 'Qatar', 'Romania', 'Russia', 'Rwanda',
                                    'Saint Kitts and Nevis', 'Saint Lucia', 'Saint Vincent and the Grenadines', 'Samoa',
                                    'San Marino', 'Sao Tome and Principe', 'Saudi Arabia', 'Senegal', 'Serbia',
                                    'Seychelles', 'Sierra Leone', 'Singapore', 'Slovakia', 'Slovenia', 'Solomon Islands',
                                    'Somalia', 'South Africa', 'South Korea', 'South Sudan', 'Spain', 'Sri Lanka', 'Sudan',
                                    'Suriname', 'Swaziland', 'Sweden', 'Switzerland', 'Syria', 'Taiwan', 'Tajikistan',
                                    'Tanzania', 'Thailand', 'Timor-Leste', 'Togo', 'Tonga', 'Trinidad and Tobago',
                                    'Tunisia', 'Turkey', 'Turkmenistan', 'Tuvalu', 'Uganda', 'Ukraine',
                                    'United Arab Emirates (UAE)', 'United Kingdom (UK)', 'United States of America (USA)',
                                    'Uruguay', 'Uzbekistan', 'Vanuatu', 'Vatican City (Holy See)', 'Venezuela',
                                    'Vietnam', 'Yemen', 'Zambia', 'Zimbabwe', 'Afghanistan', 'Albania', 'Algeria',
                                    'Andorra', 'Angola', 'Antigua and Barbuda', 'Argentina', 'Armenia', 'Australia',
                                    'Austria', 'Azerbaijan', 'Bahamas', 'Bahrain', 'Bangladesh', 'Barbados', 'Belarus',
                                    'Belgium', 'Belize', 'Benin', 'Bhutan', 'Bolivia', 'Bosnia and Herzegovina',
                                    'Botswana', 'Brazil', 'Brunei', 'Bulgaria', 'Burkina Faso', 'burundi', 'Kambodscha',
                                    'Kamerun', 'Kanada', 'Zentralafrikanische Republik (CAR)', 'Tschad', 'Kolumbien',
                                    'komoren', 'Demokratische Republik Kongo', 'Republik Kongo', 'Elfenbeinküste',
                                    'Kroatien', 'Kuba', 'Zypern', 'Tschechien', 'Dänemark', 'Dschibuti',
                                    'Dominikanische Republik', 'Ägypten', 'Äquatorialguinea', 'Estland', 'Äthiopien',
                                    'Fidschi', 'Finnland', 'Frankreich', 'Gabun', 'Deutschland', 'Griechenland',
                                    'grenada', 'Ungarn', 'Island', 'Indien', 'Ich rannte', 'Irland', 'Italien',
                                    'Jamaika', 'Jordanien', 'Kasachstan', 'Kenia', 'Kirgisistan', 'Lettland',
                                    'Libanon', 'Libyen', 'Litauen', 'Luxemburg', 'Mazedonien', 'Madagaskar',
                                    'Malediven', 'mali', 'Marshallinseln', 'Mauretanien', 'Mexiko', 'Mikronesien',
                                    'Moldawien', 'Mongolei', 'Marokko', 'Mosambik', 'Niederlande', 'Neuseeland',
                                    'Nord Korea', 'Palästina', 'Papua-Neuguinea', 'Philippinen', 'Polen', 'Katar',
                                    'Rumänien', 'Russland', 'ruanda', 'St. Kitts und Nevis',
                                    'St. Vincent und die Grenadinen', 'Saudi Arabien', 'Serbien', 'Seychellen',
                                    'Singapur', 'Slowakei', 'Slowenien', 'Salomon-Inseln', 'Südafrika',
                                    'Südkorea', 'Südsudan', 'Spanien', 'sudan', 'Surinam', 'Swasiland', 'Schweden',
                                    'Schweiz', 'Syrien', 'Tadschikistan', 'Tansania', 'Gehen', 'tonga', 'Tunesien',
                                    'Truthahn', 'tuvalu', 'Vereinigte Arabische Emirate (UAE)',
                                    'Vereinigtes Königreich (UK)', 'Vereinigte Staaten von Amerika (USA)',
                                    'Usbekistan', 'Vatikanstadt (Heiliger Stuhl)', 'Jemen', 'Sambia', 'Simbabwe',
                                    'Albanien', 'Algerien', 'angola', 'Antigua und Barbuda', 'Argentinien',
                                    'Armenien', 'Australien', 'Österreich', 'Aserbaidschan', 'Bangladesch',
                                    'Weißrussland', 'Belgien', 'benin', 'Bolivien', 'Bosnien und Herzegowina',
                                    'Brasilien', 'Hong-Kong',
                                ];
                            @endphp

                            <div id="customerFields" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 hidden">
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">First Name</label>
                                    <input type="text" name="firstName" id="firstName" placeholder="First Name" class="w-full border border-gray-300 rounded-xl p-3 bg-white transition-all outline-none">
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">Last Name</label>
                                    <input type="text" name="lastName" id="lastName" placeholder="Last Name" class="w-full border border-gray-300 rounded-xl p-3 bg-white transition-all outline-none">
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">Mobile No</label>
                                    <input type="text" name="mobileNo" id="mobileNo" placeholder="Mobile" class="w-full border border-gray-300 rounded-xl p-3 bg-white transition-all outline-none">
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">Nationality</label>
                                    <select name="nationality" id="nationality" class="w-full border border-gray-300 rounded-xl p-3 bg-white transition-all outline-none">
                                        <option value="">Select Nationality</option>
                                        @foreach ($speedCountries as $speedCountry)
                                            <option value="{{ $speedCountry }}">{{ $speedCountry }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">Gender</label>
                                    <select name="gender" id="gender" class="w-full border border-gray-300 rounded-xl p-3 bg-white transition-all outline-none">
                                        <option value="">Select Gender</option>
                                        <option value="1">Male</option>
                                        <option value="2">Female</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">Date of Birth</label>
                                    <input type="date" name="dateOfBirth" id="dateOfBirth" class="w-full border border-gray-300 rounded-xl p-3 bg-white transition-all outline-none">
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">Country</label>
                                    <select name="country" id="country" class="w-full border border-gray-300 rounded-xl p-3 bg-white transition-all outline-none">
                                        <option value="">Select Country</option>
                                        @foreach ($speedCountries as $speedCountry)
                                            <option value="{{ $speedCountry }}">{{ $speedCountry }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">City</label>
                                    <input type="text" name="city" id="city" placeholder="City" class="w-full border border-gray-300 rounded-xl p-3 bg-white transition-all outline-none">
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">Street</label>
                                    <input type="text" name="street" id="street" placeholder="Street" class="w-full border border-gray-300 rounded-xl p-3 bg-white transition-all outline-none">
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
                                <select name="vehicle" id="vehicleSelect" class="w-full border border-gray-300 rounded-xl p-3 outline-none">
                                    <option value="">Loading vehicles...</option>
                                </select>
                            </div>

                            <input type="hidden" name="vehicle" id="vehicleId">
                            <input type="hidden" name="tariffGroupId" id="tariffGroupId">
                            <input type="hidden" name="plateNo" id="plateNo">
                            <input type="hidden" name="vehicleTitle" id="vehicleTitle">

                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-1.5">Vehicle Group</label>
                                <select id="vehicleGroupSelect" name="vehicleGroupId" class="w-full border border-gray-300 rounded-xl p-3 outline-none">
                                    <option value="">Loading...</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-1.5">Booking Status</label>
                                <select id="bookingStatus" name="bookingStatus" class="w-full border border-gray-300 rounded-xl p-3 outline-none">
                                    <option value="1">New</option>
                                    <option value="2">Confirmed</option>
                                    <option value="3">Cancelled</option>
                                    <option value="4">Closed</option>
                                    <option value="5">NoShow</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-1.5">Booking Type</label>
                                <select id="bookingType" name="bookingType" class="w-full border border-gray-300 rounded-xl p-3 outline-none">
                                    <option value="1">Daily</option>
                                    <option value="1">Weekly</option>
                                    <option value="3">Monthly</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-1.5">Location</label>
                                <select id="locationSelect" name="locationId" class="w-full border border-gray-300 rounded-xl p-3 outline-none">
                                    <option value="">Loading...</option>
                                </select>
                            </div>

                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">Advance</label>
                                    <input type="number" step="any" name="advance" placeholder="0.00" class="w-full border border-gray-300 rounded-xl p-3 outline-none">
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">Tax %</label>
                                    <input type="number" step="any" name="taxPercent" placeholder="5" class="w-full border border-gray-300 rounded-xl p-3 outline-none">
                                </div>
                            </div>
                        </div>

                        <div class="mt-8">
                            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 mb-4">
                                <div>
                                    <h3 class="font-bold text-lg text-gray-800">Charges</h3>
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
                                <input type="number" step="any" name="discount" placeholder="0.00" class="w-full border border-gray-300 rounded-xl p-3 bg-white outline-none">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-1.5">Flat Tax</label>
                                <input type="number" step="any" name="chargesTax" placeholder="0.00" class="w-full border border-gray-300 rounded-xl p-3 bg-white outline-none">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-1.5">Total Charges</label>
                                <input type="number" step="any" name="totalCharges" readonly class="w-full bg-gray-100 border border-gray-200 font-semibold text-gray-800 rounded-xl p-3 cursor-not-allowed outline-none">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-1.5">Grand Total Amount</label>
                                <input type="number" step="any" name="amount" readonly class="w-full bg-blue-50 font-bold text-blue-700 border border-blue-200 rounded-xl p-3 cursor-not-allowed outline-none">
                            </div>
                        </div>

                        <div class="mt-8 bg-gray-50/70 border border-gray-200 rounded-2xl p-5">
                            <div class="flex items-center justify-between mb-5">
                                <h3 class="text-lg font-bold text-gray-800">Card & Billing Details</h3>
                            </div>
                            <div class="bg-white border border-gray-200 rounded-xl p-4">
                                
                                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                                    <div>
                                        <label class="text-xs font-medium text-gray-600">Card Number</label>
                                        <input type="text" name="cardNumber" id="cardNumber" inputmode="numeric" autocomplete="cc-number" maxlength="19" placeholder="1234 5678 9012 3456" class="w-full border border-gray-300 rounded-lg p-2.5 outline-none">
                                    </div>
                                    
                                    <div>
                                        <label class="text-xs font-medium text-gray-600">Last 4 Digits</label>
                                        <input type="text" name="cardLastFourDigits" id="cardLastFourDigits" inputmode="numeric" maxlength="4" placeholder="1234" class="w-full border border-gray-300 rounded-lg p-2.5 outline-none">
                                    </div>
                                    <div>
                                        <label class="text-xs font-medium text-gray-600">CVV</label>
                                        <input type="password" name="cvv" placeholder="***" class="w-full border border-gray-300 rounded-lg p-2.5 outline-none">
                                    </div>
                                    <div>
                                        <label class="text-xs font-medium text-gray-600">Name on Card</label>
                                        <input type="text" name="nameOnCard" placeholder="Enter name as on card" class="w-full border border-gray-300 rounded-lg p-2.5 outline-none">
                                    </div>
                                    <div>
                                        <label class="text-xs font-medium text-gray-600">Bank Name</label>
                                        <input type="text" name="bankName" placeholder="ABC Bank" class="w-full border border-gray-300 rounded-lg p-2.5 outline-none">
                                    </div>
                                    <div>
                                        <label class="text-xs font-medium text-gray-600">Expiry</label>
                                        <input type="date" name="cardExpiry" class="w-full border border-gray-300 rounded-lg p-2.5 outline-none">
                                    </div>
                                    <div>
                                        <label class="text-xs font-medium text-gray-600">Transaction No</label>
                                        <input type="text" name="transactionNo" placeholder="TXN12345" class="w-full border border-gray-300 rounded-lg p-2.5 outline-none">
                                    </div>
                                    <div>
                                        <label class="text-xs font-medium text-gray-600">Commission %</label>
                                        <input type="number" step="any" name="commissionPercentage" placeholder="0" class="w-full border border-gray-300 rounded-lg p-2.5 outline-none">
                                    </div>
                                </div>
                                <div class="mt-5">
                                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">Billing Notes</label>
                                    <textarea name="billingNotes" rows="2" placeholder="Enter billing notes..." class="w-full border border-gray-300 rounded-xl p-3 outline-none"></textarea>
                                </div>
                            </div>
                            

                            
                        </div>

                        <div class="mt-6">
                            <label class="block text-sm font-semibold text-gray-700 mb-1.5">Notes & Terms</label>
                            <textarea name="notes" rows="6" placeholder="Booking notes will appear here..." class="w-full border border-gray-300 rounded-xl p-3 bg-gray-100 text-gray-700 outline-none cursor-not-allowed" readonly></textarea>
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
                        <h3 class="font-semibold mb-2">Customer</h3>
                        <div class="grid grid-cols-2 gap-3 text-sm" id="customerInfo"></div>
                    </div>
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <h3 class="font-semibold mb-2">Vehicle</h3>
                        <div class="grid grid-cols-2 gap-3 text-sm" id="vehicleInfo"></div>
                    </div>
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <h3 class="font-semibold mb-2">Charges</h3>
                        <div id="chargesList" class="space-y-2 text-sm"></div>
                    </div>
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <h3 class="font-semibold mb-2">Billing</h3>
                        <div class="space-y-3 text-sm" id="billingInfo"></div>
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
                        <td colspan="6" class="px-6 py-12 text-center text-slate-500">
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

                if (!record.deleted_at && !record.send_booking_id && permissions.can_edit) {
                    buttons.push(`
                        <a
                            href="${record.edit_url}"
                            class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-amber-200 bg-amber-50 text-amber-700 transition hover:bg-amber-100"
                            title="Edit Booking">
                            <i class="fa-solid fa-pen-to-square text-[13px]"></i>
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
                                data-name="${escapeHtml(record.name || '')}"
                                data-number="${escapeHtml(record.number || '')}"
                                data-notes="${escapeHtml(record.notes || '')}"
                                data-advance="${escapeHtml(record.advance || '')}"
                                data-tax-percent="${escapeHtml(record.tax_percent || '')}"
                                data-discount="${escapeHtml(record.discount || '')}"
                                data-charges-tax="${escapeHtml(record.charges_tax || '')}"
                                data-total-charges="${escapeHtml(record.total_charges || '')}"
                                data-amount="${escapeHtml(record.total_amount || '')}"
                                data-vehicle-group-id="${escapeHtml(record.vehicle_group_id || '')}"
                                data-tariff-group-id="${escapeHtml(record.tariff_group_id || '')}"
                                data-rental-type="${escapeHtml(record.rental_type || '')}"
                                data-pickup-location-id="${escapeHtml(record.pickup_location_id || '')}"
                                data-self-pickup-location-id="${escapeHtml(record.self_pickup_location_id || '')}"
                                data-self-return-location-id="${escapeHtml(record.self_return_location_id || '')}"
                                data-rental-price="${escapeHtml(record.rental_price || '')}"
                                data-rental-duration="${escapeHtml(record.rental_duration || '')}"
                                data-full-insurance="${record.full_insurance ? '1' : '0'}"
                                data-full-insurance-price="${escapeHtml(record.full_insurance_price || '')}"
                                data-baby-seat="${record.baby_seat ? '1' : '0'}"
                                data-baby-seat-price="${escapeHtml(record.baby_seat_price || '')}"
                                data-additional-driver="${record.additional_driver ? '1' : '0'}"
                                data-additional-driver-charges="${escapeHtml(record.additional_driver_charges || '')}"
                                data-deposit-waiver="${escapeHtml(record.deposit_waiver || '')}"
                                data-deposit-waiver-price="${escapeHtml(record.deposit_waiver_price || '')}"
                                data-delivery-location-price="${escapeHtml(record.delivery_location_price || '')}"
                                data-return-location-price="${escapeHtml(record.return_location_price || '')}"
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
                            <td colspan="6" class="px-6 py-12 text-center text-slate-500">
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
                                <div class="text-xs text-slate-700">Flow: ${record.payment_flow === 'now' ? 'Pay Now' : 'Pay Later'}</div>
                                <div class="text-xs text-slate-500">Vehicle Group ID: ${escapeHtml(record.vehicle_group_id || '-')}</div>
                                <div class="text-xs text-slate-500">Tariff Group ID: ${escapeHtml(record.tariff_group_id || '-')}</div>
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
                            <td colspan="6" class="px-6 py-12 text-center text-red-500">
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
                            sendBookingBtn.dataset.email || '',
                            sendBookingBtn.dataset.name || '',
                            sendBookingBtn.dataset.number || '',
                            sendBookingBtn.dataset.notes || '',
                            {
                                advance: sendBookingBtn.dataset.advance || '',
                                taxPercent: sendBookingBtn.dataset.taxPercent || '',
                                discount: sendBookingBtn.dataset.discount || '',
                                chargesTax: sendBookingBtn.dataset.chargesTax || '',
                                totalCharges: sendBookingBtn.dataset.totalCharges || '',
                                amount: sendBookingBtn.dataset.amount || '',
                                vehicleGroupId: sendBookingBtn.dataset.vehicleGroupId || '',
                                tariffGroupId: sendBookingBtn.dataset.tariffGroupId || '',
                                rentalType: sendBookingBtn.dataset.rentalType || '',
                                pickupLocationId: sendBookingBtn.dataset.pickupLocationId || '',
                                selfPickupLocationId: sendBookingBtn.dataset.selfPickupLocationId || '',
                                selfReturnLocationId: sendBookingBtn.dataset.selfReturnLocationId || '',
                                rentalPrice: sendBookingBtn.dataset.rentalPrice || '',
                                rentalDuration: sendBookingBtn.dataset.rentalDuration || '',
                                fullInsurance: sendBookingBtn.dataset.fullInsurance || '0',
                                fullInsurancePrice: sendBookingBtn.dataset.fullInsurancePrice || '',
                                babySeat: sendBookingBtn.dataset.babySeat || '0',
                                babySeatPrice: sendBookingBtn.dataset.babySeatPrice || '',
                                additionalDriver: sendBookingBtn.dataset.additionalDriver || '0',
                                additionalDriverCharges: sendBookingBtn.dataset.additionalDriverCharges || '',
                                depositWaiver: sendBookingBtn.dataset.depositWaiver || '',
                                depositWaiverPrice: sendBookingBtn.dataset.depositWaiverPrice || '',
                                deliveryLocationPrice: sendBookingBtn.dataset.deliveryLocationPrice || '',
                                returnLocationPrice: sendBookingBtn.dataset.returnLocationPrice || ''
                            }
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
        let currentBookingSelection = null;
        let bookingModalDataPromise = null;
        window.latestPayload = window.latestPayload || {};

        document.addEventListener('DOMContentLoaded', function () {
            preloadBookingModalData();

            $('#addCharge').on('click', function () {
                addChargeRow();
            });

            $(document).on('input', 'input[name="discount"], input[name="chargesTax"]', function () {
                calculateFinalAmount();
            });

            $(document).on('input change', '#sendForm input, #sendForm select, #sendForm textarea', function () {
                $(this).removeClass('border-red-500 ring-2 ring-red-200');
            });

            $(document).on('keydown paste drop cut input', '#sendForm textarea[name="notes"]', function (event) {
                event.preventDefault();
            });

            applyDecimalSteps();
            initCardNumberMask();

            $('#sendForm').on('submit', function (event) {
                event.preventDefault();

                const formData = new FormData(this);
                const bookingId = $('#booking_id').val(); // booking id is passed in this field for booking page
                const customerId = String($('#customerId').val() || '').trim();
                const submitBtn = $('#sendBookingSubmitBtn');
                const formValidation = validateSendForm();

                if (!formValidation.valid) {
                    showToast(`Please fill required fields: ${formValidation.missing.join(', ')}`, 'warning');
                    resetSubmitButton();
                    return;
                }

                formData.set('cardNumber', normalizeCardNumber($('#cardNumber').val()));
                formData.set('cardLastFourDigits', $('#cardLastFourDigits').val());
                submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Processing...');

                if (customerId) {
                    formData.set('customerId', customerId);
                    updateCustomerAndProceed(formData, bookingId);
                    return;
                }

                createCustomerAndProceed(formData, bookingId);
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

            syncChargeCardRateType(card);
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

        function firstSelectValue(selector) {
            const select = document.querySelector(selector);
            if (!select) {
                return '';
            }

            const firstOption = Array.from(select.options).find(option => String(option.value || '').trim() !== '');
            return firstOption ? String(firstOption.value) : '';
        }

        function selectFirstDataOption(selector) {
            const select = document.querySelector(selector);
            if (!select) {
                return '';
            }

            const firstOption = Array.from(select.options).find(option => {
                const value = String(option.value || '').trim();
                return value !== '' && value !== '0';
            });

            if (!firstOption) {
                return '';
            }

            $(select).val(firstOption.value).trigger('change');
            return String(firstOption.value);
        }

        function normalizeRentalTypeSelectionText(rentalType) {
            const normalized = String(rentalType || '').trim().toLowerCase();

            if (normalized === 'daily') return 'Daily';
            if (normalized === 'weekly') return 'Weekly';
            if (normalized === 'monthly') return 'Monthly';

            return '';
        }

        function getRentalRateTypeId(rentalType) {
            const normalized = String(rentalType || '').trim().toLowerCase();

            // Speed rate type IDs: Daily = 1, Weekly = 1, Monthly = 3
            if (normalized === 'monthly') {
                return 3;
            }

            return 1;
        }

        function selectOneTimeRateType(rateTypeSelect) {
            if (!rateTypeSelect) {
                return false;
            }

            if (selectOptionByText(rateTypeSelect, 'OneTime')) {
                return true;
            }

            $(rateTypeSelect).val('6').trigger('change');
            return String($(rateTypeSelect).val() || '') === '6';
        }

        function getStaticRateTypeOptions() {
            return `
                <option value="">Select Rate Type</option>
                <option value="1">Daily</option>
                <option value="1">Weekly</option>
                <option value="3">Monthly</option>
                <option value="6">OneTime</option>
            `;
        }

        function selectOptionByText(select, targetText) {
            if (!select || !targetText) {
                return false;
            }

            const option = Array.from(select.options).find(item => String(item.textContent || '').trim().toLowerCase() === String(targetText).trim().toLowerCase());

            if (!option) {
                return false;
            }

            Array.from(select.options).forEach(item => {
                item.selected = false;
            });

            option.selected = true;
            $(select).trigger('change');
            return true;
        }

        function selectBookingTypeByRentalType(rentalType) {
            const targetText = normalizeRentalTypeSelectionText(rentalType);
            const select = document.getElementById('bookingType');

            return selectOptionByText(select, targetText);
        }

        function getSelectedBookingTypeText() {
            const select = document.getElementById('bookingType');
            if (!select) {
                return '';
            }

            const option = select.options[select.selectedIndex];
            return option ? String(option.textContent || '').trim() : '';
        }

        function selectChargeCardRateTypeByRentalType(rateTypeSelect, rentalType) {
            if (!rateTypeSelect || !rentalType) {
                return false;
            }

            const type = String(rentalType).toLowerCase().trim();

            let matchText = '';

            if (type === 'daily') {
                matchText = 'Daily';
            } else if (type === 'weekly') {
                matchText = 'Weekly';
            } else if (type === 'monthly') {
                matchText = 'Monthly';
            }

            if (!matchText) {
                return false;
            }

            const option = Array.from(rateTypeSelect.options).find(function (opt) {
                return opt.text.trim().toLowerCase() === matchText.toLowerCase();
            });

            if (option) {
                rateTypeSelect.value = option.value;
                $(rateTypeSelect).trigger('change');

                return true;
            }

            return false;
        }

        function syncChargeCardRateType(card) {
            const rateTypeSelect = card && card.length ? card.find('.rateType').get(0) : null;
            if (!rateTypeSelect) {
                return false;
            }

            if (String(card.attr('data-rate-type-mode') || '').trim() === 'one_time') {
                return selectOneTimeRateType(rateTypeSelect);
            }

            const bookingTypeText = getSelectedBookingTypeText();
            return selectOptionByText(rateTypeSelect, bookingTypeText);
        }

        function syncAllChargeCardRateTypes() {
            $('.charge-card').each(function () {
                syncChargeCardRateType($(this));
            });
        }

        function selectVehicleByTariffGroupId(tariffGroupId) {
            const select = document.getElementById('vehicleSelect');
            const target = String(tariffGroupId || '').trim();

            if (!select || target === '') {
                return false;
            }

            const option = Array.from(select.options).find(item => {
                if (!item.value) {
                    return false;
                }

                try {
                    const vehicle = JSON.parse(item.dataset.vehicle || '{}');
                    return String(vehicle.tariffGroupId || '').trim() === target;
                } catch (error) {
                    return false;
                }
            });

            if (!option) {
                return false;
            }

            $(select).val(option.value).trigger('change');
            return true;
        }

        function applyCurrentBookingSelections() {
            if (!currentBookingSelection) {
                return;
            }

            const bookingStatusValue = firstSelectValue('#bookingStatus');
            if (bookingStatusValue !== '') {
                $('#bookingStatus').val(bookingStatusValue).trigger('change');
            }

            selectBookingTypeByRentalType(currentBookingSelection.rentalType);
            syncAllChargeCardRateTypes();

            const pickupLocationId = String(currentBookingSelection.pickupLocationId || currentBookingSelection.selfPickupLocationId || '').trim();
            if (pickupLocationId !== '' && Number(pickupLocationId) > 0) {
                $('#locationSelect').val(pickupLocationId).trigger('change');
            } else {
                selectFirstDataOption('#locationSelect');
            }

            if (String(currentBookingSelection.vehicleGroupId || '').trim() !== '') {
                $('#vehicleGroupSelect').val(String(currentBookingSelection.vehicleGroupId).trim()).trigger('change');
            }

            if (!selectVehicleByTariffGroupId(currentBookingSelection.tariffGroupId)) {
                if (String(currentBookingSelection.tariffGroupId || '').trim() !== '') {
                    $('#tariffGroupId').val(String(currentBookingSelection.tariffGroupId).trim());
                }
            }
        }

        function loadChargesSettings() {
            return fetch(@json(url('/api/speed/getChargesSettings')))
                .then(res => res.json())
                .then(data => {
                    chargesSettings = data.items || [];
                    if (!$('#chargesWrapper').children().length) {
                        addChargeRow();
                    }
                });
        }

        function applyDecimalSteps(container = document) {
            container.querySelectorAll('#sendForm input[type="number"], .charge-card input[type="number"]').forEach((input) => {
                input.setAttribute('step', 'any');
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
                                ${getStaticRateTypeOptions()}
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-600 mb-1.5">Rate</label>
                            <input type="number" step="any" class="rate border border-gray-300 p-2.5 rounded-xl w-full text-sm outline-none" placeholder="Rate">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-600 mb-1.5">Units</label>
                            <input type="number" step="any" class="units border border-gray-300 p-2.5 rounded-xl w-full text-sm outline-none" placeholder="Qty">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-600 mb-1.5">Total</label>
                            <input type="number" step="any" class="total border border-gray-200 p-2.5 rounded-xl bg-gray-100 text-gray-700 font-semibold w-full text-sm cursor-not-allowed outline-none" readonly placeholder="Calculated total">
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
            const newCard = $('#chargesWrapper').children('.charge-card').last();
            newCard.attr('data-rate-type-mode', 'rental_type');
            syncChargeCardRateType(newCard);
            applyDecimalSteps(document.getElementById('chargesWrapper'));
            applySelect2();
        }

        function applySelect2() {
            const selectors = '.chargeType, .rateType, #vehicleSelect, #vehicleGroupSelect, #locationSelect, #bookingStatus, #bookingType, #country, #nationality';

            $(selectors).each(function () {
                const $select = $(this);

                if ($select.hasClass('select2-hidden-accessible')) {
                    $select.select2('destroy');
                }

                $select.select2({
                    placeholder: $select.is('#country') ? 'Select Country' : ($select.is('#nationality') ? 'Select Nationality' : 'Select option...'),
                    width: '100%',
                    dropdownParent: $('#sendModal')
                });
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

        function calculateTotalCharges() {
            const totalChargesField = $('input[name="totalCharges"]');
            let total = 0;

            $('.charge-card').each(function () {
                total += parseFloat($(this).find('.total').val()) || 0;
            });

            totalChargesField.val(Math.round(total * 100) / 100);
            calculateFinalAmount();
        }

        function calculateFinalAmount() {
            const total = parseFloat($('input[name="totalCharges"]').val()) || 0;
            const chargesTax = parseFloat($('input[name="chargesTax"]').val()) || 0;
            const discount = parseFloat($('input[name="discount"]').val()) || 0;
            const finalAmount = Math.round((total + chargesTax - discount) * 100) / 100;
            $('input[name="amount"]').val(finalAmount);
        }

        function parseChargeUnits(rentalDuration) {
            const value = String(rentalDuration || '').trim();
            if (value === '') {
                return 0;
            }

            const numeric = parseFloat(value);
            if (!Number.isNaN(numeric) && numeric > 0) {
                return numeric;
            }

            const match = value.match(/(\d+(?:\.\d+)?)/);
            if (!match) {
                return 0;
            }

            const parsed = parseFloat(match[1]);
            return Number.isNaN(parsed) ? 0 : parsed;
        }

        function calculateChargeRate(totalPrice, units) {
            const total = parseFloat(totalPrice) || 0;
            const durationUnits = parseFloat(units) || 0;

            if (durationUnits <= 0) {
                return total;
            }

            return Math.round((total / durationUnits) * 100) / 100;
        }

        function setChargeCardValues(card, config) {
            if (!card || !config) {
                return;
            }

            const chargeType = card.find('.chargeType');
            const rateType = card.find('.rateType');
            const rate = card.find('.rate');
            const units = card.find('.units');
            const total = card.find('.total');
            const notes = card.find('.notes');

            chargeType.val(String(config.chargeTypeId)).trigger('change');

            const rateTypeMode = String(config.rateTypeMode || 'rental_type');
            card.attr('data-rate-type-mode', rateTypeMode);

            if (rateTypeMode === 'one_time') {
                if (!selectOneTimeRateType(rateType.get(0))) {
                    rateType.val(String(config.rateTypeId)).trigger('change');
                }
            } else if (!selectChargeCardRateTypeByRentalType(rateType.get(0), config.rentalType)) {
                rateType.val(String(config.rateTypeId)).trigger('change');
            }

            rate.val(config.rateValue);
            units.val(config.unitsValue);
            total.val(config.totalValue);
            notes.val(config.notes);
        }

        function buildBookingChargeConfigs(values = {}) {
            const rentalUnits = parseChargeUnits(values.rentalDuration);
            const rentalType = String(values.rentalType || '').trim().toLowerCase();
            const rentalRateTypeId = getRentalRateTypeId(rentalType);
            const configs = [];

            configs.push({
                chargeTypeId: 1,
                rateTypeId: rentalRateTypeId,
                rateTypeMode: 'rental_type',
                rentalType: values.rentalType,
                rateValue: calculateChargeRate(values.rentalPrice, rentalUnits),
                unitsValue: rentalUnits,
                totalValue: parseFloat(values.rentalPrice) || 0,
                notes: 'Rental charge'
            });

            if (String(values.fullInsurance || '0') === '1') {
                configs.push({
                    chargeTypeId: 2,
                    rateTypeId: rentalRateTypeId,
                    rateTypeMode: 'rental_type',
                    rentalType: values.rentalType,
                    rateValue: calculateChargeRate(values.fullInsurancePrice, rentalUnits),
                    unitsValue: rentalUnits,
                    totalValue: parseFloat(values.fullInsurancePrice) || 0,
                    notes: 'Full insurance charge'
                });
            }

            if (String(values.babySeat || '0') === '1') {
                configs.push({
                    chargeTypeId: 19,
                    rateTypeId: rentalRateTypeId,
                    rateTypeMode: 'rental_type',
                    rentalType: values.rentalType,
                    rateValue: calculateChargeRate(values.babySeatPrice, rentalUnits),
                    unitsValue: rentalUnits,
                    totalValue: parseFloat(values.babySeatPrice) || 0,
                    notes: 'Baby seat charge'
                });
            }

            if (String(values.additionalDriver || '0') === '1') {
                configs.push({
                    chargeTypeId: 23,
                    rateTypeId: 6,
                    rateTypeMode: 'one_time',
                    rentalType: values.rentalType,
                    rateValue: parseFloat(values.additionalDriverCharges),
                    unitsValue: 1,
                    totalValue: parseFloat(values.additionalDriverCharges) || 0,
                    notes: 'Additional driver charge'
                });
            }

            const depositWaiver = String(values.depositWaiver || '').trim().toLowerCase();

            if (depositWaiver === 'waiver' || depositWaiver === 'deposit') {
                let chargeTypeId = 54;

                if (depositWaiver === 'waiver') {
                    chargeTypeId = rentalType === 'monthly' ? 70 : 59;
                }

                configs.push({
                    chargeTypeId: chargeTypeId,
                    rateTypeId: rentalRateTypeId,
                    rateTypeMode: 'rental_type',
                    rentalType: values.rentalType,
                    rateValue: calculateChargeRate(values.depositWaiverPrice, rentalUnits),
                    unitsValue: rentalUnits,
                    totalValue: parseFloat(values.depositWaiverPrice) || 0,
                    notes: 'Deposit waiver charge'
                });
            }

            const deliveryLocationPrice = parseFloat(values.deliveryLocationPrice) || 0;
            const returnLocationPrice = parseFloat(values.returnLocationPrice) || 0;

            if (deliveryLocationPrice > 0) {
                const totalLocationPrice = deliveryLocationPrice;

                configs.push({
                    chargeTypeId: 26,
                    rateTypeId: 6,
                    rateTypeMode: 'one_time',
                    rentalType: values.rentalType,
                    rateValue: totalLocationPrice,
                    unitsValue: 1,
                    totalValue: totalLocationPrice,
                    notes: 'Drop-off location charge'
                });
            }

            if (returnLocationPrice > 0) {
                const totalLocationPrice = returnLocationPrice;

                configs.push({
                    chargeTypeId: 27,
                    rateTypeId: 6,
                    rateTypeMode: 'one_time',
                    rentalType: values.rentalType,
                    rateValue: totalLocationPrice,
                    unitsValue: 1,
                    totalValue: totalLocationPrice,
                    notes: 'Collection location charge'
                });
            }

            return configs;
        }

        function populateBookingChargeCards(values = {}) {
            const wrapper = $('#chargesWrapper');
            wrapper.html('');

            const configs = buildBookingChargeConfigs(values);

            configs.forEach(config => {
                addChargeRow();
                const card = wrapper.children('.charge-card').last();
                setChargeCardValues(card, config);
            });

            if (!configs.length) {
                addChargeRow();
            }

            calculateTotalCharges();
        }

        $(document).on('change', '#bookingType', function () {
            syncAllChargeCardRateTypes();
        });

        function preloadBookingModalData() {
            if (!bookingModalDataPromise) {
                bookingModalDataPromise = Promise.all([
                    loadVehicles(),
                    loadVehicleGroups(),
                    loadLocations(),
                    loadChargesSettings()
                ]);
            }

            return bookingModalDataPromise;
        }

        function loadVehicles() {
            const select = $('#vehicleSelect');

            select.prop('disabled', true)
                .html('<option value="">Loading vehicles...</option>');

            return fetch(@json(url('/api/speed/getVehicles')))
                .then(res => res.json())
                .then(res => {
                    select.empty().append('<option value="">Select Vehicle</option>');

                    vehiclesList = res.items || [];

                    const groupedVehicles = {};

                    vehiclesList.forEach(vehicle => {
                        const key = (vehicle.makeModelVariant || '').trim();

                        if (!key) return;

                        if (!groupedVehicles[key]) {
                            groupedVehicles[key] = {
                                vehicle: vehicle,
                                vehicles: []
                            };
                        }

                        groupedVehicles[key].vehicles.push(vehicle);
                    });

                    Object.values(groupedVehicles).forEach(item => {
                        const option = new Option(
                            item.vehicle.makeModelVariant,
                            item.vehicle.id,
                            false,
                            false
                        );

                        // First vehicle data
                        option.dataset.vehicle = JSON.stringify(item.vehicle);

                        // Same model ki tamam vehicles
                        option.dataset.vehicles = JSON.stringify(item.vehicles);

                        option.dataset.vehicleName = item.vehicle.makeModelVariant;

                        select.append(option);
                    });

                    select.prop('disabled', false).trigger('change');
                    applyCurrentBookingSelections();
                    applySelect2();
                })
                .catch(() => {
                    select.html('<option value="">Failed to load</option>')
                        .prop('disabled', false)
                        .trigger('change');
                });
        }

        function loadVehicleGroups() {
            const select = $('#vehicleGroupSelect');
            select.prop('disabled', true).html('<option>Loading vehicle groups...</option>');

            return fetch(@json(url('/api/speed/getVehicleGroups')))
                .then(res => res.json())
                .then(res => {
                    select.empty().append('<option value="">Select Vehicle Group</option>');
                    const items = res.items || [];
                    items.forEach(item => {
                        select.append(new Option(item.title || item.name, item.id, false, false));
                    });

                    select.prop('disabled', false).trigger('change');
                    applyCurrentBookingSelections();
                    applySelect2();
                })
                .catch(() => {
                    select.html('<option>Error loading</option>').prop('disabled', false).trigger('change');
                });
        }

        function loadLocations() {
            const select = $('#locationSelect');
            select.prop('disabled', true).html('<option>Loading locations...</option>');

            return fetch(@json(url('/api/speed/getLocations')))
                .then(res => res.json())
                .then(res => {
                    select.empty().append('<option value="">Select Location</option>');
                    const items = res.items || [];
                    items.forEach(item => {
                        select.append(new Option(item.name || item.locationName, item.id, false, false));
                    });

                    select.prop('disabled', false).trigger('change');
                    applyCurrentBookingSelections();
                    applySelect2();
                })
                .catch(() => {
                    select.html('<option>Error loading</option>').prop('disabled', false).trigger('change');
                });
        }

        function splitCustomerName(fullName) {
            const parts = String(fullName || '')
                .trim()
                .split(/\s+/)
                .filter(Boolean);

            return {
                firstName: parts[0] || '',
                lastName: parts.slice(1).join(' ')
            };
        }

        function formatBookingNotes(value, depth = 0) {
            if (value === null || value === undefined || value === '') {
                return '';
            }

            if (typeof value === 'string') {
                const trimmed = value.trim();

                if ((trimmed.startsWith('{') && trimmed.endsWith('}')) || (trimmed.startsWith('[') && trimmed.endsWith(']'))) {
                    try {
                        return formatBookingNotes(JSON.parse(trimmed), depth);
                    } catch (error) {
                        return trimmed;
                    }
                }

                return trimmed;
            }

            if (Array.isArray(value)) {
                return value
                    .map((item, index) => {
                        const formatted = formatBookingNotes(item, depth + 1);
                        const prefix = `${'  '.repeat(depth)}${Array.isArray(item) || (item && typeof item === 'object') ? `Item ${index + 1}` : '- '}`;

                        if (Array.isArray(item) || (item && typeof item === 'object')) {
                            return `${prefix}\n${formatted}`;
                        }

                        return `${prefix}${formatted}`;
                    })
                    .join('\n');
            }

            if (typeof value === 'object') {
                return Object.entries(value)
                    .map(([key, item]) => {
                        const label = key
                            .replace(/_/g, ' ')
                            .replace(/([a-z])([A-Z])/g, '$1 $2')
                            .replace(/\b\w/g, char => char.toUpperCase());
                        const formatted = formatBookingNotes(item, depth + 1);
                        const indent = '  '.repeat(depth);

                        if (Array.isArray(item) || (item && typeof item === 'object')) {
                            return `${indent}${label}:\n${formatted}`;
                        }

                        return `${indent}${label}: ${formatted}`;
                    })
                    .join('\n');
            }

            return String(value);
        }

        function setBookingNotesField(notes) {
            const notesField = document.querySelector('#sendForm textarea[name="notes"]');
            if (!notesField) {
                return;
            }

            const formattedNotes = formatBookingNotes(notes);
            notesField.value = formattedNotes || '';
        }

        function prefillBookingCustomerFields(name, number) {
            const splitName = splitCustomerName(name);

            document.getElementById('firstName').value = splitName.firstName;
            document.getElementById('lastName').value = splitName.lastName;
            document.getElementById('mobileNo').value = number || '';
            document.getElementById('customerFields').classList.remove('hidden');
        }

        function setBookingFinancialFields(values = {}) {
            const fieldMap = {
                advance: values.advance,
                taxPercent: values.taxPercent,
                discount: values.discount,
                chargesTax: values.chargesTax,
                totalCharges: values.totalCharges,
                amount: values.amount
            };

            Object.entries(fieldMap).forEach(([name, value]) => {
                const field = document.querySelector(`#sendForm [name="${name}"]`);
                if (field) {
                    field.value = value ?? '';
                }
            });

            $('input[name="totalCharges"]').data('baseSubtotal', parseFloat(values.totalCharges) || 0);
            $('input[name="amount"]').data('baseAmount', parseFloat(values.amount) || 0);
        }

        async function prepareSendModal(el, bookingId, email, bookingName = '', bookingNumber = '', bookingNotes = '', bookingFinancials = {}) {
            startBtnLoader(el);

            try {
                const emailField = document.getElementById('customerEmail');
                emailField.value = email || '';
                currentBookingSelection = {
                    vehicleGroupId: bookingFinancials.vehicleGroupId || '',
                    tariffGroupId: bookingFinancials.tariffGroupId || '',
                    rentalType: bookingFinancials.rentalType || '',
                    pickupLocationId: bookingFinancials.pickupLocationId || '',
                    selfPickupLocationId: bookingFinancials.selfPickupLocationId || '',
                    selfReturnLocationId: bookingFinancials.selfReturnLocationId || '',
                    rentalPrice: bookingFinancials.rentalPrice || '',
                    rentalDuration: bookingFinancials.rentalDuration || '',
                    fullInsurance: bookingFinancials.fullInsurance || '0',
                    fullInsurancePrice: bookingFinancials.fullInsurancePrice || '',
                    babySeat: bookingFinancials.babySeat || '0',
                    babySeatPrice: bookingFinancials.babySeatPrice || '',
                    additionalDriver: bookingFinancials.additionalDriver || '0',
                    additionalDriverCharges: bookingFinancials.additionalDriverCharges || '',
                    depositWaiver: bookingFinancials.depositWaiver || '',
                    depositWaiverPrice: bookingFinancials.depositWaiverPrice || '',
                    deliveryLocationPrice: bookingFinancials.deliveryLocationPrice || '',
                    returnLocationPrice: bookingFinancials.returnLocationPrice || ''
                };
                prefillBookingCustomerFields(bookingName, bookingNumber);
                setBookingNotesField(bookingNotes);
                setBookingFinancialFields(bookingFinancials);

                await preloadBookingModalData();
                populateBookingChargeCards(currentBookingSelection);
                await searchCustomerAsync(email);

                if (!document.getElementById('firstName').value.trim() && !document.getElementById('lastName').value.trim()) {
                    prefillBookingCustomerFields(bookingName, bookingNumber);
                }

                if (!document.getElementById('mobileNo').value.trim()) {
                    document.getElementById('mobileNo').value = bookingNumber || '';
                    document.getElementById('customerFields').classList.remove('hidden');
                }

                openSendModal(bookingId);
            } finally {
                stopBtnLoader(el);
            }
        }

        function openSendModal(id) {
            document.getElementById('booking_id').value = id;
            const modal = document.getElementById('sendModal');
            const modalBody = modal.querySelector('.overflow-y-auto.flex-1');
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            document.body.classList.add('overflow-hidden');
            modal.scrollTop = 0;
            if (modalBody) {
                modalBody.scrollTop = 0;
            }
            applyCurrentBookingSelections();
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
            currentBookingSelection = null;
            resetDefaultSelects();
            addChargeRow();
            resetSubmitButton();
        }

        function resetDefaultSelects() {
            $('#country').val('').trigger('change');
            $('#nationality').val('').trigger('change');
            $('input[name="totalCharges"]').data('baseSubtotal', 0);
            $('input[name="amount"]').data('baseAmount', 0);
            $('#cardNumber, #cardLastFourDigits').val('');
            $('#sendForm select').each(function () {
                $(this).val('').trigger('change');
            });
            const defaultBookingStatus = firstSelectValue('#bookingStatus');
            if (defaultBookingStatus !== '') {
                $('#bookingStatus').val(defaultBookingStatus).trigger('change');
            }
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

            const customerFieldsEl = document.getElementById('customerFields');

            if (!value || value.length < 3) {
                customerFieldsEl.classList.add('hidden');
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
                            $('#nationality').val(pickCustomerValue(customer, ['nationality', 'Nationality']) || pickCustomerValue(customer.customer, ['nationality', 'Nationality'])).trigger('change');
                            document.getElementById('gender').value = pickCustomerValue(customer, ['gender', 'Gender']) || pickCustomerValue(customer.customer, ['gender', 'Gender']);
                            document.getElementById('dateOfBirth').value = normalizeDateTimeLocal(
                                pickCustomerValue(customer, ['dateOfBirth', 'DateOfBirth']) || pickCustomerValue(customer.customer, ['dateOfBirth', 'DateOfBirth'])
                            );
                            document.getElementById('city').value = pickCustomerValue(address, ['city', 'City']);
                            $('#country').val(pickCustomerValue(address, ['country', 'Country'])).trigger('change');
                            document.getElementById('street').value = pickCustomerValue(address, ['street', 'Street', 'addressLine1', 'AddressLine1']);
                            document.getElementById('state').value = pickCustomerValue(address, ['state', 'State']);
                            document.getElementById('postalCode').value = pickCustomerValue(address, ['zipCode', 'ZipCode', 'postalCode', 'PostalCode']);

                            showCustomerFieldsReadonly();
                        } else {
                            makeCustomerFieldsEditable();
                        }

                        if (callback) callback();
                    })
                    .catch(() => {
                        customerFieldsEl.classList.add('hidden');
                        if (callback) callback();
                    });
            }, 500);
        }

        function searchCustomerAsync(value) {
            return new Promise((resolve) => {
                searchCustomer(value, resolve);
            });
        }

        function showCustomerFieldsReadonly() {
            const fields = document.querySelectorAll('#customerFields input');
            document.getElementById('customerFields').classList.remove('hidden');

            fields.forEach(el => {
                el.removeAttribute('readonly');
                el.classList.remove('bg-gray-100');
            });

            const nationalityField = document.getElementById('nationality');
            const countryField = document.getElementById('country');
            nationalityField.classList.remove('bg-gray-100');
            countryField.classList.remove('bg-gray-100');
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
            $('#gender').val('').trigger('change');
            $('#nationality').val('').trigger('change');
            $('#country').val('').trigger('change');
            document.getElementById('nationality').classList.remove('bg-gray-100');
            document.getElementById('country').classList.remove('bg-gray-100');
        }

        function showToast(message, type = 'success') {
            const variants = {
                success: 'bg-emerald-600 text-white shadow-emerald-200/80',
                error: 'bg-red-600 text-white shadow-red-200/80',
                warning: 'bg-[#fff7e3] text-[#7d6220] border border-[#e7cf8a] shadow-[#eadfbe]/90'
            };
            const classes = variants[type] || variants.success;
            const toast = $(`<div class="fixed top-5 right-5 z-[9999] max-w-md ${classes} px-5 py-3 rounded-xl shadow-lg">${message}</div>`);
            $('body').append(toast);
            setTimeout(() => {
                toast.fadeOut(300, function () {
                    $(this).remove();
                });
            }, 3000);
        }

        function normalizeCardNumber(value) {
            return String(value || '').replace(/\D/g, '').slice(0, 16);
        }

        function formatCardNumber(value) {
            return normalizeCardNumber(value).replace(/(\d{4})(?=\d)/g, '$1 ').trim();
        }

        function syncCardLastFourDigits() {
            const digits = normalizeCardNumber($('#cardNumber').val());
            $('#cardLastFourDigits').val(digits ? digits.slice(-4) : '');
        }

        function initCardNumberMask() {
            $(document).on('input', '#cardNumber', function () {
                this.value = formatCardNumber(this.value);
                syncCardLastFourDigits();
            });

            $(document).on('input', '#cardLastFourDigits', function () {
                this.value = String(this.value || '').replace(/\D/g, '').slice(0, 4);
            });
        }

        function validateSendForm() {
            const requiredFields = [
                { id: 'firstName', label: 'First Name' },
                { id: 'lastName', label: 'Last Name' },
                { id: 'mobileNo', label: 'Mobile No' },
                { id: 'nationality', label: 'Nationality' },
                { id: 'gender', label: 'Gender' },
                { id: 'dateOfBirth', label: 'Date of Birth' },
                { id: 'city', label: 'City' },
                { id: 'country', label: 'Country' },
                { id: 'street', label: 'Street' },
                { id: 'state', label: 'State' },
                { id: 'vehicleSelect', label: 'Vehicle' },                
                { id: 'bookingStatus', label: 'Booking Status', invalidValues: ['0'] },
                { id: 'bookingType', label: 'Booking Type', invalidValues: ['0'] },
                { id: 'locationSelect', label: 'Location' }
            ];

            const missing = [];

            requiredFields.forEach(field => {
                const input = field.id
                    ? document.getElementById(field.id)
                    : document.querySelector(`#sendForm [name="${field.name}"]`);
                const value = input ? String(input.value || '').trim() : '';
                const invalidValues = field.invalidValues || [''];
                const isInvalid = !input || invalidValues.includes(value);

                if (isInvalid) {
                    missing.push(field.label);
                    if (input) {
                        $(input).addClass('border-red-500 ring-2 ring-red-200');
                    }
                } else {
                    $(input).removeClass('border-red-500 ring-2 ring-red-200');
                }
            });

            const cardNumberField = document.getElementById('cardNumber');
            const normalizedCardNumber = normalizeCardNumber(cardNumberField?.value || '');
            if (cardNumberField && normalizedCardNumber.length > 0 && normalizedCardNumber.length !== 16) {
                missing.push('Card Number must be 16 digits');
                $(cardNumberField).addClass('border-red-500 ring-2 ring-red-200');
            }

            return {
                valid: missing.length === 0,
                missing
            };
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

        function normalizeDateTimeLocal(value) {
            if (!value) {
                return '';
            }

            const text = String(value).trim();
            if (!text) {
                return '';
            }

            if (/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}$/.test(text)) {
                return text;
            }

            const normalized = text.replace(' ', 'T');
            const date = new Date(normalized);

            if (Number.isNaN(date.getTime())) {
                return '';
            }

            const year = date.getFullYear();
            const month = String(date.getMonth() + 1).padStart(2, '0');
            const day = String(date.getDate()).padStart(2, '0');
            const hours = String(date.getHours()).padStart(2, '0');
            const minutes = String(date.getMinutes()).padStart(2, '0');

            return `${year}-${month}-${day}T${hours}:${minutes}`;
        }

        function customerAddress(customer) {
            return customer?.address || customer?.Address || customer?.customer?.address || customer?.customer?.Address || {};
        }

        function syncCustomerFieldsToFormData(formData) {
            ['customerId', 'customerEmail', 'firstName', 'lastName', 'mobileNo', 'gender', 'street', 'city', 'state', 'postalCode', 'country']
                .forEach((field) => {
                    const element = document.getElementById(field);
                    if (element) {
                        formData.set(field, element.value || '');
                    }
                });
        }

        function proceedBooking(formData, bookingId) {
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
            formData.set('booking_id', bookingId);
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
                        window.latestPayload[bookingId] = {
                            payload: res.result,
                            booking: res.result,
                            speed_response: res.result.speed_response || null,
                            send_booking_id: res.result.id || null
                        };
                    }

                    showToast('Booking sent successfully.', 'success');
                    closeSendModal();

                    const sendBtn = document.getElementById(`sendBtn-${bookingId}`);
                    if (sendBtn) {
                        sendBtn.outerHTML = `
                            <button
                                type="button"
                                id="speedBtn-${bookingId}"
                                class="speed-view-btn inline-flex h-9 w-9 items-center justify-center rounded-lg border border-purple-200 bg-purple-50 text-purple-600 transition hover:bg-purple-100"
                                data-id="${bookingId}"
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

        function createCustomerAndProceed(formData, bookingId) {
            $.ajax({
                url: @json(route('create.customer')),
                method: 'POST',
                data: {
                    _token: @json(csrf_token()),
                    firstName: $('#firstName').val(),
                    lastName: $('#lastName').val(),
                    email: $('#customerEmail').val(),
                    mobileNo: $('#mobileNo').val(),
                    nationality: $('#nationality').val(),
                    dateOfBirth: $('#dateOfBirth').val(),
                    gender: $('#gender').val(),
                    locationId: 1,
                    street: $('#street').val(),
                    city: $('#city').val(),
                    state: $('#state').val(),
                    postCode: $('#postalCode').val(),
                    country: $('#country').val()
                },
                success: function (res) {
                console.log('Create Customer Response:', res); // Debugging line
                    // if (res.success && res.result) {
                    //     formData.set('customerId', res.result);
                    //     proceedBooking(formData, bookingId);
                    //     return;
                    // }

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

        function updateCustomerAndProceed(formData, bookingId) {
            $.ajax({
                url: @json(route('update.customer')),
                method: 'POST',
                data: {
                    _token: @json(csrf_token()),
                    customerId: $('#customerId').val(),
                    firstName: $('#firstName').val(),
                    lastName: $('#lastName').val(),
                    email: $('#customerEmail').val(),
                    mobileNo: $('#mobileNo').val(),
                    nationality: $('#nationality').val(),
                    dateOfBirth: $('#dateOfBirth').val(),
                    gender: $('#gender').val(),
                    locationId: 1,
                    street: $('#street').val(),
                    city: $('#city').val(),
                    state: $('#state').val(),
                    postCode: $('#postalCode').val(),
                    country: $('#country').val()
                },
                success: function (res) {
                    if (res.success) {
                        proceedBooking(formData, bookingId);
                        return;
                    }

                    showToast(res.error || 'Customer update failed', 'error');
                    resetSubmitButton();
                },
                error: function (xhr) {
                    const message = xhr.responseJSON?.error || xhr.responseJSON?.message || 'Customer update failed';
                    showToast(message, 'error');
                    resetSubmitButton();
                }
            });
        }

        function detailRow(label, value) {
            return `<div><b>${label}:</b> ${value ?? '-'}</div>`;
        }

        function amountWithIcon(value, sizeClass = 'h-[1em] w-[1em]', textClass = '') {
            const icon = @json(asset('images/durham.png'));

            return `
                <span class="inline-flex items-center gap-1 ${textClass}">
                    <img src="${icon}" alt="AED" class="inline-block ${sizeClass} object-contain align-[-0.12em]">
                    <span>${formatAmount(value)}</span>
                </span>
            `;
        }

        function detailAmountRow(label, value) {
            return `<div><b>${label}:</b> ${amountWithIcon(value)}</div>`;
        }

        function renderPrettyJson(value) {
            if (value === null || value === undefined || value === '') {
                return '<div class="text-sm text-slate-500">-</div>';
            }

            let data = value;

            if (typeof value === 'string') {
                try {
                    data = JSON.parse(value);
                } catch (error) {
                    data = value;
                }
            }

            const raw = typeof data === 'string' ? data : JSON.stringify(data, null, 2);
            const escapeJsonHtml = (text) => String(text)
                .replaceAll('&', '&amp;')
                .replaceAll('<', '&lt;')
                .replaceAll('>', '&gt;')
                .replaceAll('"', '&quot;')
                .replaceAll("'", '&#039;');

            return `
                <pre class="max-h-[420px] overflow-auto rounded-xl border border-slate-200 bg-slate-950 p-4 text-[12px] leading-5 text-slate-100 whitespace-pre-wrap break-words">${escapeJsonHtml(raw)}</pre>
            `;
        }

        function formatAmount(value) {
            const amount = Number(value);

            if (Number.isNaN(amount)) {
                return value ?? '-';
            }

            return amount.toLocaleString(undefined, {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
        }

        function renderSpeedView(data) {
            const booking = data?.payload || data?.booking;

            if (!data || !booking) {
                return;
            }

            const speedResponse = data.speed_response || booking.speed_response || booking.speedResponse || booking.speed_response_json || booking.speedResponseJson || null;
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
                detailRow('Pickup Location', booking.pickupLocationId),
                detailRow('Pickup Location Address', booking.pickupLocationAddress),
                detailAmountRow('Advance', booking.advance || 0),
                detailRow('Tax Percent', booking.taxPercent || 0),
                detailAmountRow('Discount', booking.discount || 0),
                detailAmountRow('Tax', booking.tax || 0),
                detailAmountRow('Total Charges', booking.totalCharges || 0),
                detailRow('Booking Type', booking.bookingType || '-'),
                `
                <div class="col-span-2 overflow-hidden rounded-2xl border border-emerald-200 bg-gradient-to-r from-emerald-50 via-white to-teal-50 shadow-sm">
                    <div class="flex items-center justify-between gap-4 px-5 py-4">
                        <div>
                            <div class="text-xs font-semibold uppercase tracking-[0.2em] text-emerald-700">TotalAmount</div>
                            <div class="mt-1">
                                ${amountWithIcon(booking.amount || 0, 'h-[1em] w-[1em]', 'text-xl font-bold text-slate-900')}
                            </div>
                        </div>
                    </div>
                </div>
                `
            ].join('');

            document.getElementById('customerInfo').innerHTML = [
                detailRow('Name', `${customer.firstName || ''} ${customer.lastName || ''}`.trim() || '-'),
                detailRow('Email', customer.email),
                detailRow('Phone', customer.mobileNo),
                detailRow('Nationality', customer.nationality),
                detailRow('Date of Birth', customer.dateOfBirth),
                detailRow(
                    'Gender',
                    customer.gender == 1
                        ? 'Male'
                        : customer.gender == 2
                            ? 'Female'
                            : '-'
                ),
                detailRow('Country', address.country),
                detailRow('City', address.city),
                detailRow('Street', address.street || address.addressLine1),
                detailRow('State', address.state),
                detailRow('Zip Code', address.zipCode || address.postalCode)
            ].join('');

            document.getElementById('vehicleInfo').innerHTML = [
                detailRow('Plate No', vehicle.plateNo),
                detailRow('Model', tariff.title || tariff.Title),
                detailRow('Sub Title', tariff.subTitle || tariff.SubTitle),
                detailRow('Seats', tariff.passengerCapacity || tariff.PassengerCapacity),
                detailRow('Large Bags', tariff.largeBagsCapacity || tariff.LargeBagsCapacity),
                detailRow('Small Bags', tariff.smallBagsCapacity || tariff.SmallBagsCapacity)
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
                <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                    <div class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Card Details</div>
                    <div class="mt-3 grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-4">
                        <div><b>Card Holder:</b> ${creditCard.cardHolderName || creditCard.CardHolderName || contactCard.nameOnCard || contactCard.NameOnCard || '-'}</div>
                        <div><b>Card No:</b> ${contactCard.cardNo || contactCard.CardNo || '-'}</div>
                        <div><b>Last 4 Digits:</b> ${contactCard.cardNoLastFourDigits || contactCard.CardNoLastFourDigits || creditCard.cardNoLastDigits || creditCard.CardNoLastDigits || '-'}</div>
                        <div><b>Expiry:</b> ${contactCard.expiry || contactCard.Expiry || creditCard.expiryDate || creditCard.ExpiryDate || '-'}</div>
                        <div><b>CVV:</b> ${contactCard.cvv || contactCard.Cvv || '-'}</div>
                        <div><b>Bank:</b> ${contactCard.bankName || contactCard.BankName || '-'}</div>
                        <div><b>Default:</b> ${(contactCard.isDefault || contactCard.IsDefault) ? 'Yes' : 'No'}</div>
                        <div><b>Contact ID:</b> ${contactCard.contactId || contactCard.ContactId || '-'}</div>
                        <div><b>Transaction No:</b> ${creditCard.transactionNo || creditCard.TransactionNo || '-'}</div>
                        <div><b>Commission:</b> ${creditCard.commissionPercentage || creditCard.CommissionPercentage || 0}%</div>
                        <div><b>External Source:</b> ${contactCard.externalSource || contactCard.ExternalSource || '-'}</div>
                    </div>
                </div>
                <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                    <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                        <div class="min-w-0 flex-1">
                            <div class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">
                                Billing Notes
                            </div>

                            <div class="mt-2 rounded-xl border border-slate-200 bg-slate-50 p-4 max-h-[400px] overflow-y-auto">
                                ${billing.notes || billing.Notes || '-'}
                            </div>
                        </div>
                    </div>
                </div>
                <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                    <div class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Speed Response</div>
                    <div class="mt-3">
                        ${renderPrettyJson(speedResponse)}
                    </div>
                </div>
                <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                    <div class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Booking Notes</div>
                    <div class="mt-3 grid grid-cols-1 md:grid-cols-2 gap-4">
                        ${renderPrettyNotes(booking.notes)}
                    </div>
                </div>
            `;
        }

        function parseNotesToObject(notes) {
            const result = {};

            String(notes || '')
                .split(/\r?\n/)
                .forEach(line => {
                    const index = line.indexOf(':');
                    if (index === -1) return;

                    const key = line.slice(0, index).trim();
                    const value = line.slice(index + 1).trim();

                    if (key) result[key] = value || '-';
                });

            return result;
        }

        function renderPrettyNotes(notes) {
            const data = parseNotesToObject(notes);

            const sections = {
                'Car Details': ['Source', 'Car Id', 'Car Slug', 'Car Name'],
                'Customer Details': ['Name', 'Number', 'Email', 'Contact Preference'],
                'Booking Schedule': ['Start Date', 'End Date', 'Start Time', 'End Time', 'Rental Type', 'Rental Duration'],
                'Pickup & Return': [
                    'Pickup Branch',
                    'Dropoff Branch',
                    'Delivery Location',
                    'Delivery Custom Address',
                    'Return Location',
                    'Return Custom Address'
                ],
                'Add-ons': [
                    'Full Insurance',
                    'Full Insurance Price',
                    'Additional Driver',
                    'Additional Driver Charges',
                    'Baby Seat',
                    'Baby Seat Price',
                    'Deposit Waiver',
                    'Deposit Waiver Price'
                ],
                'Payment Summary': [
                    'Rental Price',
                    'Subtotal',
                    'Vat Percentage',
                    'Vat Amount',
                    'Discount Percentage',
                    'Pay Now Discount',
                    'Total Amount',
                    'Payment Flow',
                    'Pay Now 20% To Reserve',
                    'Pay At Pickup 80%'
                ],
                'Terms': [
                    'Term 22 Years',
                    'Term 6 Month Experience'
                ]
            };

            return Object.entries(sections).map(([title, keys]) => {
                const rows = keys
                    .filter(key => data[key] !== undefined && data[key] !== '')
                    .map(key => `
                        <div class="flex justify-between gap-4 border-b border-gray-100 py-2 last:border-b-0">
                            <span class="text-xs font-semibold text-gray-500">${key}</span>
                            <span class="text-xs font-bold text-gray-800 text-right">${data[key]}</span>
                        </div>
                    `).join('');

                if (!rows) return '';

                return `
                    <div class="rounded-xl border border-[#eadfbe] bg-white p-4 shadow-sm">
                        <h4 class="mb-3 text-[12px] font-extrabold uppercase tracking-wider text-[#8b6a1c]">
                            ${title}
                        </h4>
                        ${rows}
                    </div>
                `;
            }).join('');
        }

        function openSpeedViewModal(bookingId) {
            if (window.latestPayload[bookingId]?.booking) {
                renderSpeedView(window.latestPayload[bookingId]);
            } else {
                fetch(@json(route('admin.bookings.payload', ['id' => '__ID__'])).replace('__ID__', bookingId), {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                })
                    .then(res => res.json())
                    .then(res => {
                        const booking = res.payload || res.booking;

                        if (!res.status || !booking) {
                            showToast('Booking data not available', 'error');
                            return;
                        }

                        window.latestPayload[bookingId] = {
                            payload: res.payload || null,
                            booking: booking,
                            speed_response: (res.booking && res.booking.speed_response) || booking.speed_response || null,
                            send_booking_id: res.send_booking_id || null
                        };

                        renderSpeedView(window.latestPayload[bookingId]);
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
