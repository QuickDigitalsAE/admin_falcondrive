<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\BookingRequest;
use App\Http\Resources\BookingResource;
use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Carbon;

class BookingController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:Booking_ViewAll', ['only' => ['index']]);
        $this->middleware('permission:Booking_ViewAll|Booking_View', ['only' => ['show']]);
        $this->middleware('permission:Booking_Add', ['only' => ['create', 'store']]);
        $this->middleware('permission:Booking_Edit', ['only' => ['edit', 'update']]);
        $this->middleware('permission:Booking_Delete', ['only' => ['destroy', 'restore']]);
    }

    public function index(Request $request)
    {
        $perPage = $request->query('per_page', 10);
        $search = trim((string) $request->query('search', ''));
        $isExport = $request->query('is_export');
        $isDeleted = (int) $request->query('is_deleted', 0);
        $supportsSoftDeletes = $this->bookingSupportsSoftDeletes();
        $hasDeletedAtColumn = $this->bookingHasDeletedAtColumn();

        $query = Booking::query();

        if ($supportsSoftDeletes) {
            if ($isDeleted === 1) {
                $query->onlyTrashed();
            }
        } elseif ($hasDeletedAtColumn) {
            if ($isDeleted === 1) {
                $query->whereNotNull('deleted_at');
            } else {
                $query->whereNull('deleted_at');
            }
        }

        if ($search !== '') {
            $query->where(function ($subQuery) use ($search) {
                $subQuery->where('name', 'LIKE', "%{$search}%")
                    ->orWhere('number', 'LIKE', "%{$search}%")
                    ->orWhere('email', 'LIKE', "%{$search}%")
                    ->orWhere('coupon_code', 'LIKE', "%{$search}%")
                    ->orWhere('paid_id', 'LIKE', "%{$search}%")
                    ->orWhere('pickup_location_id', 'LIKE', "%{$search}%")
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
                    'rental_duration' => $booking->rental_duration,
                    'resident_tourist' => $booking->resident_tourist,
                    'full_insurance' => (bool) $booking->full_insurance,
                    'full_insurance_price' => (string) $booking->full_insurance_price,
                    'additional_driver' => (bool) $booking->additional_driver,
                    'additional_driver_charges' => (string) $booking->additional_driver_charges,
                    'baby_seat' => (bool) $booking->baby_seat,
                    'baby_seat_price' => (string) $booking->baby_seat_price,
                    'deposit_waiver' => $booking->deposit_waiver,
                    'deposit_waiver_price' => (string) $booking->deposit_waiver_price,
                    'delivery_location_price' => (string) $booking->delivery_location_price,
                    'payment_flow' => $booking->payment_flow,
                    'vehicle_group_id' => $booking->vehicle_group_id,
                    'tariff_group_id' => $booking->tariff_group_id,
                    'total_amount' => (string) $booking->total_amount,
                    'advance' => (string) ($booking->{'pay_now_20%_to_Reserve'} ?? ''),
                    'tax_percent' => (string) ($booking->vat_percentage ?? ''),
                    'discount' => (string) ($booking->pay_now_discount ?? ''),
                    'charges_tax' => (string) ($booking->vat_amount ?? ''),
                    'total_charges' => (string) ($booking->subtotal ?? ''),
                    'paid_status' => $booking->paid_status,
                    'paid_via' => $booking->paid_via,
                    'notes' => $booking->notes,
                    'speed_response' => $booking->speed_response,
                    'start_date' => optional($booking->start_date)->format('Y-m-d'),
                    'end_date' => optional($booking->end_date)->format('Y-m-d'),
                    'start_time' => $booking->start_time,
                    'end_time' => $booking->end_time,
                    'send_booking_id' => $booking->send_booking_id,
                    'pickup_location_id' => $booking->pickup_location_id,
                    'self_pickup_location_id' => $booking->self_pickup_location_id,
                    'self_return_location_id' => $booking->self_return_location_id,
                    'return_location_price' => (string) $booking->return_location_price,
                    'deleted_at' => optional($booking->deleted_at)->format('Y-m-d H:i:s'),
                    'created_at_human' => optional($booking->created_at)->format('d M Y, h:i A'),
                    'show_url' => route('admin.bookings.show', $booking->id),
                    'edit_url' => route('admin.bookings.edit', $booking->id),
                    'delete_url' => route('admin.bookings.delete', $booking->id),
                    'restore_url' => route('admin.bookings.restore', $booking->id),
                    'permissions' => [
                        'can_view' => $authUser->can('Booking_ViewAll') || $authUser->can('Booking_View'),
                        'can_edit' => $authUser->can('Booking_Edit'),
                        'can_delete' => $authUser->can('Booking_Delete'),
                        'can_restore' => $authUser->can('Booking_Delete'),
                        'can_send_booking' => $authUser->can('Inquiry_SendBooking') || $authUser->can('Booking_SendBooking'),
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
                    'filters' => ['search' => $search, 'is_deleted' => $isDeleted],
                ],
            ]);
        }

        return view('admin.bookings.index', [
            'records' => $records,
            'search' => $search,
            'perPage' => $perPage,
            'isDeleted' => $isDeleted,
        ]);
    }

    public function create()
    {
        return view('admin.bookings.create');
    }

    public function store(Request $request)
    {
        $validated = $this->validateBooking($request);
        $booking = Booking::create($validated);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'status' => true,
                'message' => 'Booking added successfully.',
                'data' => $booking,
            ]);
        }

        return redirect()->route('admin.bookings')->with('success', 'Booking added successfully.');
    }

    public function show(int $id)
    {
        $booking = $this->findBookingIncludingTrash($id);
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
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['status' => false, 'message' => 'Booking not found.'], 404);
            }

            return back()->with('error', 'Booking not found.');
        }

        $validated = $this->validateBooking($request);
        $booking->update($validated);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'status' => true,
                'message' => 'Booking updated successfully.',
                'data' => $booking->fresh(),
            ]);
        }

        return redirect()->route('admin.bookings')->with('success', 'Booking updated successfully.');
    }

    public function destroy(Request $request, int $id)
    {
        $booking = Booking::find($id);

        if (!$booking) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['status' => false, 'message' => 'Booking not found.'], 404);
            }

            return back()->with('error', 'Booking not found.');
        }

        if ($this->bookingSupportsSoftDeletes()) {
            $booking->delete();
        } elseif ($this->bookingHasDeletedAtColumn()) {
            $booking->forceFill(['deleted_at' => Carbon::now()])->save();
        } else {
            $booking->delete();
        }

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'status' => true,
                'message' => 'Booking moved to trash successfully.',
            ]);
        }

        return redirect()->route('admin.bookings')->with('success', 'Booking moved to trash successfully.');
    }



    public function restore(int $id)
    {
        if ($this->bookingSupportsSoftDeletes()) {
            $booking = Booking::withTrashed()->find($id);

            if (!$booking) {
                return response()->json(['status' => false, 'message' => 'Booking not found.'], 404);
            }

            if (!$booking->trashed()) {
                return response()->json(['status' => true, 'message' => 'Booking is already active.']);
            }

            $booking->restore();

            return response()->json([
                'status' => true,
                'message' => 'Booking restored successfully.',
            ]);
        }

        if (!$this->bookingHasDeletedAtColumn()) {
            return response()->json([
                'status' => false,
                'message' => 'Trash is not enabled for bookings. Add deleted_at column or SoftDeletes first.',
            ], 422);
        }

        $booking = Booking::query()->where('id', $id)->whereNotNull('deleted_at')->first();

        if (!$booking) {
            return response()->json(['status' => false, 'message' => 'Booking not found in trash.'], 404);
        }

        $booking->forceFill(['deleted_at' => null])->save();

        return response()->json([
            'status' => true,
            'message' => 'Booking restored successfully.',
        ]);
    }

    public function payload(int $id)
    {
        $booking = $this->findBookingIncludingTrash($id);

        if (!$booking) {
            return response()->json([
                'status' => false,
                'message' => 'Booking not found.',
            ], 404);
        }

        $payload = $booking->speed_response ?? $booking->notes ?? null;

        if (is_string($payload)) {
            $decoded = json_decode($payload, true);
            $payload = json_last_error() === JSON_ERROR_NONE ? $decoded : $payload;
        }

        return response()->json([
            'status' => true,
            'message' => 'Booking payload fetched successfully.',
            'booking' => BookingResource::make($booking)->resolve(),
            'payload' => $payload,
            'send_booking_id' => $booking->send_booking_id,
        ]);
    }

    private function exportBookings($query)
    {
        $records = $query->get();

        return response()->stream(function () use ($records) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['ID', 'Name', 'Number', 'Email', 'Rental Type', 'Rental Price', 'Pickup Location ID', 'Self Pickup Location ID', 'Self Return Location ID', 'Vehicle Group ID', 'Tariff Group ID', 'Payment Flow', 'Total Amount', 'Paid Status', 'Paid Via', 'Created At']);

            foreach ($records as $record) {
                fputcsv($file, [
                    $record->id,
                    $record->name,
                    $record->number,
                    $record->email,
                    $record->rental_type,
                    $record->rental_price,
                    $record->pickup_location_id,
                    $record->self_pickup_location_id,
                    $record->self_return_location_id,
                    $record->vehicle_group_id,
                    $record->tariff_group_id,
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


    private function bookingHasDeletedAtColumn(): bool
    {
        return Schema::hasColumn((new Booking())->getTable(), 'deleted_at');
    }

    private function findBookingIncludingTrash(int $id): ?Booking
    {
        if ($this->bookingSupportsSoftDeletes()) {
            return Booking::withTrashed()->find($id);
        }

        return Booking::query()->find($id);
    }

    private function bookingSupportsSoftDeletes(): bool
    {
        return in_array(
            \Illuminate\Database\Eloquent\SoftDeletes::class,
            class_uses_recursive(Booking::class),
            true
        );
    }

}
