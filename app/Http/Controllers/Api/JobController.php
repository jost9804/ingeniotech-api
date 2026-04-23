<?php

namespace App\Http\Controllers\Api;

use App\Models\Job;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class JobController extends Controller
{
    public function index(Request $request)
    {
        try {
            $query = Job::with('assignedTo');

            if ($request->has('status')) {
                $query->where('status', $request->status);
            }

            if ($request->has('assigned_to')) {
                $query->where('assigned_to', $request->assigned_to);
            }

            $jobs = $query->paginate(15);

            return response()->json($jobs);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'client_name' => 'required|string|max:120',
            'client_phone' => 'required|string|max:20',
            'device_type' => 'required|in:computador,celular,camara,otro',
            'problem_description' => 'required|string',
            'status' => 'required|in:recibido,en_diagnostico,en_reparacion,listo,entregado',
            'assigned_to' => 'required|exists:users,id',
            'progress' => 'nullable|integer|min:0|max:100',
            'price' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        $job = Job::create($validated);

        return response()->json($job, 201);
    }

    public function show(Job $job)
    {
        $job->load('assignedTo');

        return response()->json($job);
    }

    public function update(Request $request, Job $job)
    {
        $validated = $request->validate([
            'status' => 'nullable|in:recibido,en_diagnostico,en_reparacion,listo,entregado',
            'progress' => 'nullable|integer|min:0|max:100',
            'price' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
            'assigned_to' => 'nullable|exists:users,id',
        ]);

        $job->update($validated);

        return response()->json($job);
    }

    public function destroy(Job $job)
    {
        if (!$job->delete()) {
            return response()->json(['message' => 'Failed to delete'], 500);
        }

        return response()->json(['message' => 'Deleted successfully']);
    }
}
