<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BookingController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:Booking_ViewAll', ['only' => ['index']]);
        $this->middleware('permission:Booking_ViewAll|Booking_View', ['only' => ['show']]);
        $this->middleware('permission:Booking_Add', ['only' => ['create', 'store']]);
        $this->middleware('permission:Booking_Edit', ['only' => ['edit', 'update']]);
        $this->middleware('permission:Booking_Delete', ['only' => ['destroy']]);
    }

    public function index(Request $request)
    {
        $perPage = $request->query('per_page', 10);
        $search = trim((string) $request->query('search', ''));
        $isExport = $request->query('is_export');

        $query = Booking::query();

        if ($search !== '') {
            $query->where(function ($subQuery) use ($search) {
                $subQuery->where('name', 'LIKE', "%{$search}%")
                    ->orWhere('number', 'LIKE', "%{$search}%")
                    ->orWhere('email', 'LIKE', "%{$search}%")
                    ->orWhere('coupon_code', 'LIKE', "%{$search}%")
                    ->orWhere('paid_id', 'LIKE', "%{$search}%");
            });
        }

        $query->latest('id');

        if ($isExport) {
            return $this->exportBookings($query);
        }

        $records = $query->paginate($perPage)->withQueryString();

        if ($request->ajax() || $request->wantsJson()) {
            $authUser = Auth::user();

            $items = $records->getCollection()->map(function (Booking $booking) use ($authUser) {
                return [
                    'id' => $booking->id,
                    'name' => $booking->name,
                    'number' => $booking->number,
                    'email' => $booking->email,
                    'start_date' => optional($booking->start_date)->format('Y-m-d'),
                    'end_date' => optional($booking->end_date)->format('Y-m-d'),
                    'start_time' => $booking->start_time,
                    'end_time' => $booking->end_time,
                    'rental_type' => $booking->rental_type,
                    'resident_tourist' => $booking->resident_tourist,
                    'payment_flow' => $booking->payment_flow,
                    'paid_status' => $booking->paid_status,
                    'paid_via' => $booking->paid_via,
                    'created_at_human' => optional($booking->created_at)->format('d M Y, h:i A'),
                    'show_url' => route('admin.bookings.show', $booking->id),
                    'delete_url' => route('admin.bookings.delete', $booking->id),
                    'permissions' => [
                        'can_view' => $authUser->can('Booking_ViewAll') || $authUser->can('Booking_View'),
                        'can_delete' => $authUser->can('Booking_Delete'),
                    ],
                ];
            })->values();

            return response()->json([
                'status' => true,
                'message' => 'Bookings fetched successfully.',
                'data' => [
                    'items' => $items,
                    'pagination' => [
                        'current_page' => $records->currentPage(),
                        'last_page' => $records->lastPage(),
                        'per_page' => $records->perPage(),
                        'total' => $records->total(),
                        'from' => $records->firstItem(),
                        'to' => $records->lastItem(),
                        'has_more_pages' => $records->hasMorePages(),
                    ],
                    'filters' => ['search' => $search],
                ],
            ]);
        }

        return view('admin.bookings.index', [
            'records' => $records,
            'search' => $search,
            'perPage' => $perPage,
        ]);
    }

    public function create()
    {
        return view('admin.bookings.create');
    }

    public function store(Request $request)
    {
        $validated = $this->validateBooking($request);
        Booking::create($validated);

        return redirect()->route('admin.bookings')->with('success', 'Booking added successfully.');
    }

    public function show(int $id)
    {
        $booking = Booking::find($id);
        if (!$booking) {
            return redirect()->route('admin.bookings')->with('error', 'Booking not found.');
        }

        return view('admin.bookings.show', compact('booking'));
    }

    public function edit(int $id)
    {
        $booking = Booking::find($id);
        if (!$booking) {
            return back()->with('error', 'Booking not found.');
        }

        return view('admin.bookings.edit', compact('booking'));
    }

    public function update(Request $request, int $id)
    {
        $booking = Booking::find($id);
        if (!$booking) {
            return back()->with('error', 'Booking not found.');
        }

        $validated = $this->validateBooking($request);
        $booking->update($validated);

        return redirect()->route('admin.bookings')->with('success', 'Booking updated successfully.');
    }

    public function destroy(int $id)
    {
        $booking = Booking::find($id);
        if (!$booking) {
            return back()->with('error', 'Booking not found.');
        }

        $booking->delete();

        return redirect()->route('admin.bookings')->with('success', 'Booking deleted successfully.');
    }

    private function exportBookings($query)
    {
        $records = $query->get();

        return response()->stream(function () use ($records) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['ID', 'Name', 'Number', 'Email', 'Start Date', 'End Date', 'Rental Type', 'Payment Flow', 'Paid Status', 'Paid Via', 'Created At']);

            foreach ($records as $record) {
                fputcsv($file, [
                    $record->id,
                    $record->name,
                    $record->number,
                    $record->email,
                    optional($record->start_date)->format('Y-m-d'),
                    optional($record->end_date)->format('Y-m-d'),
                    $record->rental_type,
                    $record->payment_flow,
                    $record->paid_status,
                    $record->paid_via,
                    optional($record->created_at)->format('Y-m-d H:i:s'),
                ]);
            }

            fclose($file);
        }, 200, ['Content-Type' => 'text/csv', 'Content-Disposition' => 'attachment; filename=bookings.csv']);
    }

    private function validateBooking(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:191'],
            'number' => ['required', 'string', 'max:191'],
            'email' => ['nullable', 'email', 'max:191'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'start_time' => ['nullable', 'regex:/^([01]\\d|2[0-3]):[0-5]\\d(:[0-5]\\d)?$/'],
            'end_time' => ['nullable', 'regex:/^([01]\\d|2[0-3]):[0-5]\\d(:[0-5]\\d)?$/'],
            'rental_type' => ['nullable', 'in:daily,weekly,monthly'],
            'resident_tourist' => ['nullable', 'in:resident,tourist'],
            'full_insurance' => ['required', 'boolean'],
            'additional_driver' => ['required', 'boolean'],
            'baby_seat' => ['required', 'boolean'],
            'deposit_waiver' => ['nullable', 'in:Deposit,Waiver'],
            'delivery_address' => ['nullable', 'string'],
            'delivery_area' => ['nullable', 'string', 'max:191'],
            'pickup_address' => ['nullable', 'string'],
            'pickup_area' => ['nullable', 'string', 'max:191'],
            'delivery_price' => ['nullable', 'numeric', 'min:0'],
            'pickup_price' => ['nullable', 'numeric', 'min:0'],
            'coupon_code' => ['nullable', 'string', 'max:191'],
            'discount_percentage' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'payment_flow' => ['required', 'in:now,later'],
            'paid_id' => ['nullable', 'string', 'max:191'],
            'paid_date' => ['nullable', 'date'],
            'paid_status' => ['nullable', 'string', 'max:191'],
            'paid_via' => ['nullable', 'string', 'max:191'],
            'contact_preference' => ['nullable', 'in:whatsapp,phone'],
            'term_22_years' => ['required', 'boolean'],
            'term_6_month_experience' => ['required', 'boolean'],
            'description' => ['nullable', 'string'],
            'notes' => ['nullable', 'string'],
            'request_body' => ['nullable', 'string'],
        ]);
    }
}
