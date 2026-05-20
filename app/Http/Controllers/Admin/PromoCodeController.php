<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\PromoCodeRequest;
use App\Http\Resources\PromoCodeResource;
use App\Models\PromoCode;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class PromoCodeController extends Controller
{
    public function index(Request $request)
    {
        $query = PromoCode::query();

        if ((int) $request->get('is_deleted') === 1) {
            $query->onlyTrashed();
        }

        if ($request->filled('search')) {
            $search = trim((string) $request->search);

            $query->where(function ($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                    ->orWhere('title', 'like', "%{$search}%")
                    ->orWhere('discount_type', 'like', "%{$search}%");
            });
        }

        $query->latest('id');

        if ((int) $request->get('is_export') === 1) {
            return $this->exportCsv($query->get());
        }

        $records = $query->paginate(15)->withQueryString();

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'status' => true,
                'message' => 'Promo codes fetched successfully.',
                'data' => [
                    'items' => PromoCodeResource::collection($records->items()),
                    'pagination' => [
                        'current_page' => $records->currentPage(),
                        'last_page' => $records->lastPage(),
                        'per_page' => $records->perPage(),
                        'total' => $records->total(),
                        'from' => $records->firstItem(),
                        'to' => $records->lastItem(),
                    ],
                ],
            ]);
        }

        $search = $request->search;

        return view('admin.promo-codes.index', compact('records', 'search'));
    }

    public function create()
    {
        $promoCode = new PromoCode();

        return view('admin.promo-codes.create', compact('promoCode'));
    }

    public function store(PromoCodeRequest $request)
    {
        $data = $request->validated();
        $data['created_by'] = auth()->id();

        PromoCode::create($data);

        return redirect()
            ->route('admin.promo-codes')
            ->with('success', 'Promo code created successfully.');
    }

    public function show($id)
    {
        $promoCode = PromoCode::withTrashed()->findOrFail($id);

        return view('admin.promo-codes.show', compact('promoCode'));
    }

    public function edit($id)
    {
        $promoCode = PromoCode::findOrFail($id);

        return view('admin.promo-codes.edit', compact('promoCode'));
    }

    public function update(PromoCodeRequest $request, $id)
    {
        $promoCode = PromoCode::findOrFail($id);

        $data = $request->validated();
        $data['updated_by'] = auth()->id();

        $promoCode->update($data);

        return redirect()
            ->route('admin.promo-codes')
            ->with('success', 'Promo code updated successfully.');
    }

    public function destroy(Request $request, $id)
    {
        $promoCode = PromoCode::findOrFail($id);
        $promoCode->deleted_by = auth()->id();
        $promoCode->save();
        $promoCode->delete();

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'status' => true,
                'message' => 'Promo code deleted successfully.',
            ]);
        }

        return redirect()
            ->route('admin.promo-codes')
            ->with('success', 'Promo code deleted successfully.');
    }

    public function restore(Request $request, $id)
    {
        $promoCode = PromoCode::onlyTrashed()->findOrFail($id);
        $promoCode->restore();
        $promoCode->updated_by = auth()->id();
        $promoCode->deleted_by = null;
        $promoCode->save();

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'status' => true,
                'message' => 'Promo code restored successfully.',
            ]);
        }

        return redirect()
            ->route('admin.promo-codes')
            ->with('success', 'Promo code restored successfully.');
    }

    public function apply(Request $request): JsonResponse
    {
        $request->validate([
            'code' => ['required', 'string', 'max:60'],
            'amount' => ['required', 'numeric', 'min:0'],
        ]);

        $amount = round((float) $request->amount, 2);
        $code = strtoupper(trim((string) $request->code));

        $promoCode = PromoCode::where('code', $code)->first();

        if (!$promoCode) {
            return response()->json([
                'status' => false,
                'message' => 'Invalid promo code.',
            ], 404);
        }

        [$valid, $message] = $promoCode->isValidForAmount($amount);

        if (!$valid) {
            return response()->json([
                'status' => false,
                'message' => $message,
            ], 422);
        }

        $discountAmount = min($promoCode->calculateDiscount($amount), $amount);
        $finalAmount = round($amount - $discountAmount, 2);

        return response()->json([
            'status' => true,
            'message' => 'Promo code applied successfully.',
            'data' => [
                'code' => $promoCode->code,
                'discount_type' => $promoCode->discount_type,
                'discount_value' => (float) $promoCode->discount_value,
                'discount_amount' => $discountAmount,
                'original_amount' => $amount,
                'final_amount' => $finalAmount,
            ],
        ]);
    }

    private function exportCsv($records)
    {
        $filename = 'promo-codes-' . now()->format('Y-m-d-H-i-s') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($records) {
            $file = fopen('php://output', 'w');

            fputcsv($file, [
                'ID',
                'Code',
                'Title',
                'Discount Type',
                'Discount Value',
                'Minimum Amount',
                'Start Date',
                'Expiry Date',
                'Status',
                'Created At',
            ]);

            foreach ($records as $record) {
                fputcsv($file, [
                    $record->id,
                    $record->code,
                    $record->title,
                    $record->discount_type,
                    $record->discount_value,
                    $record->minimum_amount,
                    optional($record->start_date)->format('Y-m-d'),
                    optional($record->expiry_date)->format('Y-m-d'),
                    $record->status ? 'Active' : 'Inactive',
                    optional($record->created_at)->format('Y-m-d H:i:s'),
                ]);
            }

            fclose($file);
        };

        return Response::stream($callback, 200, $headers);
    }
}
