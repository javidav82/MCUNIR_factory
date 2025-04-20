<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\PrintJob;
use App\Models\PrintDocumentation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class PrintDocumentationController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    /**
     * Generate documentation for a print job
     */
    public function generate(PrintJob $printJob)
    {
        try {
            $documentation = $printJob->documentation()->create([
                'document_type' => 'feedback',
                'title' => 'Print Job Documentation',
                'description' => 'Documentation for print job ' . $printJob->id,
                'status' => 'open',
                'created_by' => auth()->id()
            ]);

            return response()->json([
                'message' => 'Documentation generated successfully',
                'data' => $documentation
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to generate documentation',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Download documentation for a print job
     */
    public function download(PrintJob $printJob)
    {
        try {
            $documentation = $printJob->documentation()->firstOrFail();
            
            if (!$documentation->document_path) {
                return response()->json([
                    'message' => 'No documentation file available'
                ], 404);
            }

            return response()->download($documentation->document_path);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to download documentation',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Send documentation by email
     */
    public function sendByEmail(Request $request, PrintJob $printJob)
    {
        try {
            $validator = Validator::make($request->all(), [
                'email' => 'required|email',
                'subject' => 'required|string|max:255',
                'message' => 'nullable|string|max:1000'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }
    

             // For now, we'll just return a success message
            return response()->json([
                'message' => 'Documentation sent successfully',
                'data' => [
                    'email' => $request->email,
                    'subject' => $request->subject,
                    'message' => $request->message
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to send documentation by email',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
