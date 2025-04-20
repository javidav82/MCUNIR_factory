<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\PrintJob;
use App\Models\PrintStatusTracking;
use App\Models\PrintDocument;
use App\Models\PrintDocumentFeedback;
use App\Models\PrintDocumentIssue;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class PrintJobController extends Controller
{
    /**
     * Display a listing of print jobs.
     */
    public function index()
    {
        $jobs = PrintJob::with(['batch', 'statusHistory', 'documentation'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return response()->json($jobs);
    }

    /**
     * Store a newly created print job.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), PrintJob::rules());

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            // Create the print job
            $job = PrintJob::create($request->all());

            // Create initial status tracking
            $job->statusHistory()->create([
                'status' => $job->status,
                'changed_by' => $request->user()->id ?? 'system',
                'comments' => 'Print job created'
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Print job created successfully',
                'data' => $job->load(['batch', 'statusHistory'])
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to create print job',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified print job.
     */
    public function show(PrintJob $printJob)
    {
        return response()->json($printJob->load(['batch', 'statusHistory', 'documentation']));
    }

    /**
     * Update the specified print job.
     */
    public function update(Request $request, PrintJob $printJob)
    {
        $validator = Validator::make($request->all(), PrintJob::rules());

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            // Update the print job
            $printJob->update($request->all());

            // Create status tracking if status changed
            if ($request->has('status') && $request->status !== $printJob->getOriginal('status')) {
                $printJob->statusHistory()->create([
                    'status' => $request->status,
                    'changed_by' => $request->user()->id ?? 'system',
                    'comments' => 'Status updated'
                ]);
            }

            DB::commit();

            return response()->json([
                'message' => 'Print job updated successfully',
                'data' => $printJob->load(['batch', 'statusHistory'])
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to update print job',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified print job.
     */
    public function destroy(PrintJob $printJob)
    {
        try {
            DB::beginTransaction();

            // Delete associated files if they exist
            if ($printJob->file_path && Storage::exists($printJob->file_path)) {
                Storage::delete($printJob->file_path);
            }

            $printJob->delete();

            DB::commit();

            return response()->json([
                'message' => 'Print job deleted successfully'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to delete print job',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Upload a print file and create a job.
     */
    public function uploadFile(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'batch_id' => 'required|exists:print_batches,id',
            'file' => 'required|file|max:10240', // Max 10MB
            'page_count' => 'required|integer|min:1'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            $file = $request->file('file');
            $path = $file->store('print_files');

            $job = PrintJob::create([
                'batch_id' => $request->batch_id,
                'file_name' => $file->getClientOriginalName(),
                'file_path' => $path,
                'file_type' => $file->getClientMimeType(),
                'file_size' => $file->getSize(),
                'page_count' => $request->page_count,
                'status' => 'pending'
            ]);

            // Create initial status tracking
            $job->statusHistory()->create([
                'status' => 'pending',
                'changed_by' => $request->user()->id ?? 'system',
                'comments' => 'File uploaded and job created'
            ]);

            DB::commit();

            return response()->json([
                'message' => 'File uploaded and print job created successfully',
                'data' => $job->load(['batch', 'statusHistory'])
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to upload file and create print job',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function getUserPrintJobs()
    {
        try {
            $user = Auth::user();
            
            $printJobs = PrintJob::with([
                'printDocuments' => function($query) {
                    $query->with([
                        'feedback' => function($query) {
                            $query->select('id', 'print_document_id', 'feedback', 'created_at');
                        },
                        'issues' => function($query) {
                            $query->select('id', 'print_document_id', 'issue', 'status', 'created_at');
                        }
                    ])
                    ->select('id', 'print_job_id', 'file_name', 'file_path', 'status', 'created_at');
                }
            ])
            ->where('user_id', $user->id)
            ->select('id', 'user_id', 'title', 'description', 'status', 'created_at')
            ->orderBy('created_at', 'desc')
            ->get();

            return response()->json([
                'status' => 'success',
                'data' => $printJobs
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch print jobs',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function getPrintJobDetails($id)
    {
        try {
            $user = Auth::user();
            
            $printJob = PrintJob::with([
                'printDocuments' => function($query) {
                    $query->with([
                        'feedback' => function($query) {
                            $query->select('id', 'print_document_id', 'feedback', 'created_at');
                        },
                        'issues' => function($query) {
                            $query->select('id', 'print_document_id', 'issue', 'status', 'created_at');
                        }
                    ])
                    ->select('id', 'print_job_id', 'file_name', 'file_path', 'status', 'created_at');
                }
            ])
            ->where('id', $id)
            ->where('user_id', $user->id)
            ->select('id', 'user_id', 'title', 'description', 'status', 'created_at')
            ->first();

            if (!$printJob) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Print job not found'
                ], 404);
            }

            return response()->json([
                'status' => 'success',
                'data' => $printJob
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch print job details',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function managePrintJobs(Request $request)
    {
        try {
            $user = Auth::user();
            
            // Validate request
            $validator = Validator::make($request->all(), [
                'status' => 'sometimes|required|string|in:pending,processing,completed,failed',
                'page' => 'sometimes|required|integer|min:1',
                'per_page' => 'sometimes|required|integer|min:1|max:100'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Build query
            $query = PrintJob::with([
                'printDocuments' => function($query) {
                    $query->with([
                        'feedback' => function($query) {
                            $query->select('id', 'print_document_id', 'feedback', 'created_at');
                        },
                        'issues' => function($query) {
                            $query->select('id', 'print_document_id', 'issue', 'status', 'created_at');
                        },
                        'statusHistory' => function($query) {
                            $query->select('id', 'print_document_id', 'status', 'changed_by', 'comments', 'created_at')
                                  ->orderBy('created_at', 'desc');
                        }
                    ])
                    ->select('id', 'print_job_id', 'file_name', 'file_path', 'status', 'created_at');
                },
                'statusHistory' => function($query) {
                    $query->select('id', 'print_job_id', 'status', 'changed_by', 'comments', 'created_at')
                          ->orderBy('created_at', 'desc');
                }
            ])
            ->where('user_id', $user->id);

            // Apply status filter if provided
            if ($request->has('status')) {
                $query->where('status', $request->status);
            }

            // Get paginated results
            $perPage = $request->input('per_page', 10);
            $printJobs = $query->orderBy('created_at', 'desc')
                             ->paginate($perPage);

            // Transform the response to include status history
            $transformedJobs = $printJobs->getCollection()->map(function ($job) {
                return [
                    'id' => $job->id,
                    'title' => $job->title,
                    'description' => $job->description,
                    'status' => $job->status,
                    'created_at' => $job->created_at,
                    'updated_at' => $job->updated_at,
                    'documents' => $job->printDocuments->map(function ($document) {
                        return [
                            'id' => $document->id,
                            'file_name' => $document->file_name,
                            'file_path' => $document->file_path,
                            'status' => $document->status,
                            'feedback' => $document->feedback,
                            'issues' => $document->issues,
                            'status_history' => $document->statusHistory
                        ];
                    }),
                    'status_history' => $job->statusHistory
                ];
            });

            return response()->json([
                'status' => 'success',
                'data' => [
                    'jobs' => $transformedJobs,
                    'pagination' => [
                        'current_page' => $printJobs->currentPage(),
                        'last_page' => $printJobs->lastPage(),
                        'per_page' => $printJobs->perPage(),
                        'total' => $printJobs->total()
                    ]
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch print jobs',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function updatePrintJobStatus(Request $request, $id)
    {
        try {
            $user = Auth::user();
            
            // Validate request
            $validator = Validator::make($request->all(), [
                'status' => 'required|string|in:pending,processing,completed,failed',
                'comments' => 'nullable|string|max:500'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            DB::beginTransaction();

            // Find the print job
            $printJob = PrintJob::where('id', $id)
                              ->where('user_id', $user->id)
                              ->firstOrFail();

            // Update status
            $printJob->status = $request->status;
            $printJob->save();

            // Create status history record
            PrintStatusTracking::create([
                'print_job_id' => $printJob->id,
                'status' => $request->status,
                'changed_by' => $user->id,
                'comments' => $request->comments ?? 'Status updated by user'
            ]);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Print job status updated successfully',
                'data' => $printJob->load(['statusHistory'])
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update print job status',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function manageDocumentIssues(Request $request, $documentId)
    {
        try {
            $user = Auth::user();
            
            // Validate request
            $validator = Validator::make($request->all(), [
                'issue' => 'required|string|max:1000',
                'status' => 'required|string|in:pending,resolved,rejected',
                'comments' => 'nullable|string|max:500'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            DB::beginTransaction();

            // Find the document and verify ownership
            $document = PrintDocument::where('id', $documentId)
                ->whereHas('printJob', function($query) use ($user) {
                    $query->where('user_id', $user->id);
                })
                ->firstOrFail();

            // Create or update the issue
            $issue = PrintDocumentIssue::updateOrCreate(
                [
                    'print_document_id' => $documentId,
                    'status' => 'pending'
                ],
                [
                    'issue' => $request->issue,
                    'status' => $request->status,
                    'resolved_by' => $request->status === 'resolved' ? $user->id : null,
                    'resolved_at' => $request->status === 'resolved' ? now() : null
                ]
            );

            // Update document status based on issue status
            if ($request->status === 'resolved') {
                $document->status = 'validated';
                $document->save();
            } elseif ($request->status === 'rejected') {
                $document->status = 'invalid';
                $document->save();
            }

            // Create status history record
            PrintStatusTracking::create([
                'print_document_id' => $documentId,
                'status' => $request->status,
                'changed_by' => $user->id,
                'comments' => $request->comments ?? 'Issue status updated'
            ]);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Document issue updated successfully',
                'data' => [
                    'issue' => $issue,
                    'document' => $document->load(['statusHistory', 'issues'])
                ]
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update document issue',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function validateDocument(Request $request, $documentId)
    {
        try {
            $user = Auth::user();
            
            // Validate request
            $validator = Validator::make($request->all(), [
                'is_valid' => 'required|boolean',
                'comments' => 'nullable|string|max:500',
                'issues' => 'nullable|array',
                'issues.*.description' => 'required|string|max:1000',
                'issues.*.severity' => 'required|string|in:low,medium,high,critical'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            DB::beginTransaction();

            // Find the document and verify ownership
            $document = PrintDocument::where('id', $documentId)
                ->whereHas('printJob', function($query) use ($user) {
                    $query->where('user_id', $user->id);
                })
                ->firstOrFail();

            // Update document status
            $document->status = $request->is_valid ? 'validated' : 'invalid';
            $document->validated_by = $user->id;
            $document->validated_at = now();
            $document->save();

            // Create issues if document is invalid
            if (!$request->is_valid && !empty($request->issues)) {
                foreach ($request->issues as $issueData) {
                    PrintDocumentIssue::create([
                        'print_document_id' => $documentId,
                        'issue' => $issueData['description'],
                        'severity' => $issueData['severity'],
                        'status' => 'pending',
                        'reported_by' => $user->id
                    ]);
                }
            }

            // Create status history record
            PrintStatusTracking::create([
                'print_document_id' => $documentId,
                'status' => $document->status,
                'changed_by' => $user->id,
                'comments' => $request->comments ?? ($request->is_valid ? 'Document validated' : 'Document marked as invalid')
            ]);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Document validation completed successfully',
                'data' => $document->load(['statusHistory', 'issues'])
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to validate document',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function getDocumentIssues($documentId)
    {
        try {
            $user = Auth::user();
            
            // Find the document and verify ownership
            $document = PrintDocument::where('id', $documentId)
                ->whereHas('printJob', function($query) use ($user) {
                    $query->where('user_id', $user->id);
                })
                ->with([
                    'issues' => function($query) {
                        $query->orderBy('created_at', 'desc');
                    },
                    'statusHistory' => function($query) {
                        $query->orderBy('created_at', 'desc');
                    }
                ])
                ->firstOrFail();

            return response()->json([
                'status' => 'success',
                'data' => $document
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch document issues',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
