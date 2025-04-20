<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\PrintJob;
use App\Models\PrintBatch;
use App\Models\PrintStatusTracking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PrintStatusController extends Controller
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
     * Get all possible status values
     */
    public function getStatusOptions()
    {
        return response()->json([
            'job_statuses' => [
                'pending',
                'processing',
                'completed',
                'failed'
            ],
            'batch_statuses' => [
                'pending',
                'processing',
                'completed',
                'failed'
            ],
            'tracking_statuses' => [
                'pending',
                'queued',
                'processing',
                'paused',
                'resumed',
                'completed',
                'failed',
                'cancelled'
            ]
        ]);
    }

    /**
     * Get status history for a specific job
     */
    public function getJobStatusHistory(PrintJob $printJob)
    {
        $statusHistory = $printJob->statusHistory()
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'job' => $printJob->load('batch'),
            'status_history' => $statusHistory
        ]);
    }

    /**
     * Get status history for a specific batch
     */
    public function getBatchStatusHistory(PrintBatch $printBatch)
    {
        $statusHistory = $printBatch->statusHistory()
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'batch' => $printBatch->load('jobs'),
            'status_history' => $statusHistory
        ]);
    }

    /**
     * Get current status summary for all jobs
     */
    public function getJobsStatusSummary()
    {
        $summary = PrintJob::select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->get();

        $total = $summary->sum('count');

        return response()->json([
            'summary' => $summary,
            'total' => $total
        ]);
    }

    /**
     * Get current status summary for all batches
     */
    public function getBatchesStatusSummary()
    {
        $summary = PrintBatch::select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->get();

        $total = $summary->sum('count');

        return response()->json([
            'summary' => $summary,
            'total' => $total
        ]);
    }

    /**
     * Get jobs by status
     */
    public function getJobsByStatus(Request $request)
    {
        $request->validate([
            'status' => 'required|in:pending,processing,completed,failed'
        ]);

        $jobs = PrintJob::with(['batch', 'statusHistory'])
            ->where('status', $request->status)
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return response()->json($jobs);
    }

    /**
     * Get batches by status
     */
    public function getBatchesByStatus(Request $request)
    {
        $request->validate([
            'status' => 'required|in:pending,processing,completed,failed'
        ]);

        $batches = PrintBatch::with(['jobs', 'statusHistory'])
            ->where('status', $request->status)
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return response()->json($batches);
    }

    /**
     * Get recent status changes
     */
    public function getRecentStatusChanges()
    {
        $recentChanges = PrintStatusTracking::with(['trackable'])
            ->orderBy('created_at', 'desc')
            ->limit(50)
            ->get();

        return response()->json($recentChanges);
    }

    /**
     * Get status statistics
     */
    public function getStatusStatistics()
    {
        $jobStats = PrintJob::select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->get();

        $batchStats = PrintBatch::select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->get();

        $recentFailures = PrintJob::where('status', 'failed')
            ->orderBy('updated_at', 'desc')
            ->limit(5)
            ->get();

        return response()->json([
            'job_statistics' => $jobStats,
            'batch_statistics' => $batchStats,
            'recent_failures' => $recentFailures
        ]);
    }
}
