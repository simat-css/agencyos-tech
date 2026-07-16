<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
   public function index()
{
    $query = auth()
        ->user()
        ->notifications()
        ->latest();

    if (request()->filled('search')) {

        $query->where(
            'data->message',
            'like',
            '%' . request('search') . '%'
        );

    }

    $notifications = $query->paginate(15);

    $totalNotifications = auth()
        ->user()
        ->notifications()
        ->count();

    $unreadNotifications = auth()
        ->user()
        ->unreadNotifications()
        ->count();

    $readNotifications = auth()
        ->user()
        ->notifications()
        ->whereNotNull('read_at')
        ->count();

    return view(
        'notifications.index',
        compact(
            'notifications',
            'totalNotifications',
            'unreadNotifications',
            'readNotifications'
        )
    );
}
    public function markAsRead($id)
{
    $notification = auth()
        ->user()
        ->notifications()
        ->findOrFail($id);

    $notification->markAsRead();

    return back()
        ->with(
            'success',
            'Notification marked as read.'
        );
}
public function markAllRead()
{
    auth()
        ->user()
        ->unreadNotifications()
        ->update([
            'read_at' => now()
        ]);

    return back()
        ->with(
            'success',
            'All notifications marked as read.'
        );
}
/**
 * Delete Notification
 */
public function destroy($id)
{
    $notification = auth()
        ->user()
        ->notifications()
        ->findOrFail($id);

    $notification->delete();

    return redirect()
        ->back()
        ->with(
            'success',
            'Notification deleted successfully.'
        );
}
public function bulkDelete(Request $request)
{

    $request->validate([

        'ids'=>'required|array'

    ]);


    auth()->user()
        ->notifications()
        ->whereIn('id',$request->ids)
        ->delete();


    return response()->json([

        'message'=>'Selected notifications deleted successfully.'

    ]);

}
}