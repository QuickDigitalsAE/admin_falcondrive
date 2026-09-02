<?php

namespace App\Http\Controllers\APIs;

use App\Http\Controllers\Controller;
use App\Models\CustomerDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CustomerDocumentController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'customer_id' => 'required|integer',
            'document' => 'required',
            'file_name' => 'required|string'
        ]);

        try {
            $base64File = $request->document;

            // Remove Base64 Header
            if (str_contains($base64File, 'base64,')) {
                $base64File = explode('base64,', $base64File)[1];
            }

            $fileData = base64_decode($base64File);

            if (!$fileData) {
                return response()->json([
                    'status' => false,
                    'message' => 'Invalid Base64 document'
                ], 422);
            }

            // Generate File Name
            $extension = pathinfo($request->file_name, PATHINFO_EXTENSION);

            $fileNameWithoutExtension = pathinfo(
                $request->file_name,
                PATHINFO_FILENAME
            );

            $fileName = time() . '_' .
                Str::slug($fileNameWithoutExtension) .
                '.' . $extension;

            // Upload File
            $path = 'customer_documents/' . $fileName;

            $encodedDocument = base64_encode($fileData);

            Storage::disk('public')->put(
                $path,
                $fileData
            );

            // Save Database
            $document = CustomerDocument::create([
                'customer_id' => $request->customer_id,

                'customer_details' => $request->customer_details,

                'document_no' => $request->document_no,

                'issue_date' => $request->issue_date,

                'expiry_date' => $request->expiry_date,

                'issued_by' => $request->issued_by,

                'identity_name' => $request->identity_name,

                'identity_document_id' => $request->identity_document_id,

                'description' => $request->description,

                'data' => $request->data,

                'document' => $encodedDocument,

                'path' => $path,

                'file_name' => $fileName,

                'file_name_without_extension' => $fileNameWithoutExtension,

                'status' => 'pending',

                'created_by' => $request->customer_id
            ]);

            return response()->json([
                'status' => true,
                'message' => 'Document uploaded successfully',
                'data' => $document,
                'document_url' => asset('storage/' . $path)
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }


    public function getDocuments($customer_id)
    {
        try {
            $documents = CustomerDocument::where(
                'customer_id',
                $customer_id
            )
            ->whereNull('deleted_at')
            ->get();


            if ($documents->isEmpty()) {
                return response()->json([
                    "success" => false,
                    "error" => "No documents found",
                    "result" => null
                ], 404);
            }


            $customerDetails = $documents->first()->customer_details;
            if (!is_array($customerDetails)) {
                $customerDetails = json_decode((string) $customerDetails, true) ?: [];
            }


            $identityDocuments = [];


            foreach ($documents as $document) {

                $identityDocuments[] = [

                    "id" => $document->id,

                    "identityDocumentType" =>
                        $document->identity_document_id,

                    "isInternational" => false,

                    "documentNo" =>
                        $document->document_no,

                    "issuedBy" =>
                        $document->issued_by,

                    "issueDate" =>
                        $document->issue_date,

                    "expiryDate" =>
                        $document->expiry_date,

                    "issueDateStr" => null,

                    "expiryDateStr" => null,

                    "description" =>
                        $document->description,

                    "images" => [

                        [
                            "id" => $document->id,

                            "identityDocumentId" =>
                                $document->identity_document_id,

                            "name" =>
                                $document->file_name_without_extension .
                                '.' .
                                pathinfo(
                                    $document->file_name,
                                    PATHINFO_EXTENSION
                                ),

                            "url" => $document->path
                                ? asset('storage/' . $document->path)
                                : (str_starts_with((string) $document->document, 'data:')
                                    ? $document->document
                                    : ($document->document && $document->data
                                        ? 'data:' . $document->data . ';base64,' . $document->document
                                        : asset('storage/' . $document->document))),

                            "path" => "",

                            "guid" => null,

                            "imageURL" => null,

                            "imagePath" => null,

                            "description" =>
                                $document->description
                        ]
                    ]
                ];
            }


            $customerDetails['identityDocuments'] = $identityDocuments;


            return response()->json([
                "success" => true,
                "error" => null,
                "result" => $customerDetails
            ], 200);


        } catch (\Exception $e) {

            return response()->json([
                "success" => false,
                "error" => $e->getMessage(),
                "result" => null
            ], 500);

        }
    }
}
