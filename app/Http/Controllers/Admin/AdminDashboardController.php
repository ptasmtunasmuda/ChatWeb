<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\ChatRoom;
use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AdminDashboardController extends Controller
{
    public function index()
    {
        $stats = $this->getDashboardStats();
        $recentActivity = $this->getRecentActivity();
        $userGrowth = $this->getUserGrowthData();
        $messageStats = $this->getMessageStats();

        return response()->json([
            'stats' => $stats,
            'recent_activity' => $recentActivity,
            'user_growth' => $userGrowth,
            'message_stats' => $messageStats
        ]);
    }

    private function getDashboardStats()
    {
        $totalUsers = User::count();
        $activeUsers = User::where('last_seen_at', '>=', Carbon::now()->subDays(7))->count();
        $totalChatRooms = ChatRoom::count();
        $totalMessages = Message::count();
        $deletedMessages = Message::onlyTrashed()->count();

        $todayUsers = User::whereDate('created_at', Carbon::today())->count();
        $todayMessages = Message::whereDate('created_at', Carbon::today())->count();
        $todayChatRooms = ChatRoom::whereDate('created_at', Carbon::today())->count();

        return [
            'total_users' => $totalUsers,
            'active_users' => $activeUsers,
            'total_chat_rooms' => $totalChatRooms,
            'total_messages' => $totalMessages,
            'deleted_messages' => $deletedMessages,
            'today_users' => $todayUsers,
            'today_messages' => $todayMessages,
            'today_chat_rooms' => $todayChatRooms,
            'user_activity_rate' => $totalUsers > 0 ? round(($activeUsers / $totalUsers) * 100, 2) : 0,
        ];
    }

    private function getRecentActivity()
    {
        $recentUsers = User::latest()
            ->take(5)
            ->select('id', 'name', 'email', 'created_at', 'last_seen_at')
            ->get();

        $recentChatRooms = ChatRoom::with('creator:id,name')
            ->latest()
            ->take(5)
            ->select('id', 'name', 'type', 'created_by', 'created_at')
            ->get();

        $recentMessages = Message::with(['user:id,name', 'chatRoom:id,name'])
            ->latest()
            ->take(10)
            ->select('id', 'content', 'type', 'user_id', 'chat_room_id', 'created_at')
            ->get();

        return [
            'recent_users' => $recentUsers,
            'recent_chat_rooms' => $recentChatRooms,
            'recent_messages' => $recentMessages,
        ];
    }

    private function getUserGrowthData()
    {
        $last30Days = collect();
        for ($i = 29; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $count = User::whereDate('created_at', $date)->count();
            $last30Days->push([
                'date' => $date->format('Y-m-d'),
                'count' => $count,
                'label' => $date->format('M j')
            ]);
        }

        return $last30Days;
    }

    private function getMessageStats()
    {
        $last7Days = collect();
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $count = Message::whereDate('created_at', $date)->count();
            $last7Days->push([
                'date' => $date->format('Y-m-d'),
                'count' => $count,
                'label' => $date->format('D')
            ]);
        }

        return $last7Days;
    }

    public function systemHealth()
    {
        $dbConnection = $this->checkDatabaseConnection();
        $storageSpace = $this->getStorageInfo();
        $queueStatus = $this->getQueueStatus();

        return response()->json([
            'database' => $dbConnection,
            'storage' => $storageSpace,
            'queue' => $queueStatus,
            'timestamp' => Carbon::now()->toISOString()
        ]);
    }

    private function checkDatabaseConnection()
    {
        try {
            DB::connection()->getPdo();
            return [
                'status' => 'healthy',
                'message' => 'Database connection is working'
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Database connection failed: ' . $e->getMessage()
            ];
        }
    }

    private function getStorageInfo()
    {
        $storagePath = storage_path();
        $totalSpace = disk_total_space($storagePath);
        $freeSpace = disk_free_space($storagePath);
        $usedSpace = $totalSpace - $freeSpace;

        return [
            'total' => $this->formatBytes($totalSpace),
            'used' => $this->formatBytes($usedSpace),
            'free' => $this->formatBytes($freeSpace),
            'usage_percentage' => round(($usedSpace / $totalSpace) * 100, 2)
        ];
    }

    private function getQueueStatus()
    {
        // This is a basic implementation
        // You might want to implement more sophisticated queue monitoring
        return [
            'status' => 'running',
            'pending_jobs' => 0,
            'failed_jobs' => 0
        ];
    }

    private function formatBytes($bytes, $precision = 2)
    {
        $units = array('B', 'KB', 'MB', 'GB', 'TB');

        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, $precision) . ' ' . $units[$i];
    }

    public function exportData(Request $request)
    {
        $request->validate([
            'type' => 'required|in:users,messages,chat_rooms',
            'format' => 'required|in:csv,json',
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date|after_or_equal:date_from'
        ]);

        $type = $request->input('type');
        $format = $request->input('format');
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');

        $data = $this->getExportData($type, $dateFrom, $dateTo);

        if ($format === 'csv') {
            return $this->exportToCsv($data, $type);
        }

        return response()->json([
            'data' => $data,
            'count' => count($data),
            'exported_at' => Carbon::now()->toISOString()
        ]);
    }

    private function getExportData($type, $dateFrom = null, $dateTo = null)
    {
        $query = null;

        switch ($type) {
            case 'users':
                $query = User::select('id', 'name', 'email', 'role', 'created_at', 'last_seen_at');
                break;
            case 'messages':
                $query = Message::with(['user:id,name', 'chatRoom:id,name'])
                    ->select('id', 'content', 'type', 'user_id', 'chat_room_id', 'created_at');
                break;
            case 'chat_rooms':
                $query = ChatRoom::with(['creator:id,name'])
                    ->withCount('messages', 'participants')
                    ->select('id', 'name', 'type', 'description', 'created_by', 'created_at');
                break;
        }

        if ($dateFrom) {
            $query->whereDate('created_at', '>=', $dateFrom);
        }

        if ($dateTo) {
            $query->whereDate('created_at', '<=', $dateTo);
        }

        return $query->get()->toArray();
    }

    private function exportToCsv($data, $type)
    {
        $filename = $type . '_export_' . Carbon::now()->format('Y-m-d_H-i-s') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($data) {
            $file = fopen('php://output', 'w');

            if (!empty($data)) {
                // Write headers
                fputcsv($file, array_keys($data[0]));

                // Write data
                foreach ($data as $row) {
                    fputcsv($file, $row);
                }
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
