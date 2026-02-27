<?php

namespace App\Http\Controllers;

use App\Models\Request;
use App\Models\User;
use Illuminate\Http\Request as HttpRequest;

class DispatcherController extends Controller
{
    /**
     * Показать дашборд диспетчера
     */
    public function dashboard()
    {
        $stats = [
            'new_requests' => Request::where('status', 'new')->count(),
            'assigned_requests' => Request::where('status', 'assigned')->count(),
            'in_progress_requests' => Request::where('status', 'in_progress')->count(),
            'total_requests' => Request::count(),
        ];

        $recentRequests = Request::with('master')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        $masters = User::where('role', 'master')->get();

        return view('dispatcher.dashboard', compact('stats', 'recentRequests', 'masters'));
    }

    /**
     * Назначить мастера на заявку
     */
    public function assignMaster(HttpRequest $request, $requestId)
    {
        $requestData = Request::findOrFail($requestId);
        $requestData->assignedTo = $request->master_id;
        $requestData->status = 'assigned';
        $requestData->save();

        return redirect()->back()->with('success', 'Мастер назначен на заявку');
    }
}
