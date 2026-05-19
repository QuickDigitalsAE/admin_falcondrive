<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\BookingRequest;
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
                    ->orWhere('paid_id', 'LIKE', "%{$search}%")
                    ->orWhere('send_booking_id', 'LIKE', "%{$search}%");
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
                    'rental_type' => $booking->rental_type,
                    'rental_price' => (string) $booking->rental_price,
                    'resident_tourist' => $booking->resident_tourist,
                    'payment_flow' => $booking->payment_flow,
                    'total_amount' => (string) $booking->total_amount,
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
            fputcsv($file, ['ID', 'Name', 'Number', 'Email', 'Rental Type', 'Rental Price', 'Payment Flow', 'Total Amount', 'Paid Status', 'Paid Via', 'Created At']);

            foreach ($records as $record) {
                fputcsv($file, [
                    $record->id,
                    $record->name,
                    $record->number,
                    $record->email,
                    $record->rental_type,
                    $record->rental_price,
                    $record->payment_flow,
                    $record->total_amount,
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
        $rules = (new BookingRequest())->rules();
        unset($rules['website'], $rules['g-recaptcha-response']);

        return $request->validate($rules);
    }
}
