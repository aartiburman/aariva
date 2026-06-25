<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\TicketMessage;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use App\Helpers\NotificationHelper;
use App\Helpers\ImageHelper;
use App\Helpers\EmailHelper;
use Illuminate\Support\Facades\Log; 
     



class TicketController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        // Auto-Escalate tickets sent to Vendors that are older than 48 hours and still Open
        // This handles "automatic transfer after 48 hours"
        Ticket::where('status', 'Open')
            ->where('created_at', '<', now()->subHours(48))
            ->whereHas('receiver', function($q) {
                $q->where('role', '2'); // Vendor
            })
            ->update(['status' => 'Escalated']);

        $query = Ticket::query();

        // Role-based visibility
        if ((string)$user->role === '1') {
            // Admin Logic
            $type = $request->get('type', 'vendor'); // Default to vendor if coming from sidebar link, or 'my' if manual?

            if ($type == 'escalated') {
                $query->where('status', 'Escalated');
            } elseif ($type == 'vendor') {
                // Show tickets created by Vendors (sent to Admin)
                $query->whereHas('user', function($u) { $u->where('role', '2'); });
            } elseif ($type == 'customer') {
                // Show tickets created by Customers (sent to Vendor or Admin)
                $query->whereHas('user', function($u) { $u->where('role', '3'); });
            } else {
                 // Default Admin View (My Tickets / Direct)
                 // Also include Escalated tickets here just in case
                $query->where(function($q) use ($user) {
                    $q->where('receiver_id', $user->id)
                      ->orWhere('status', 'Escalated')
                      ->orWhere('user_id', $user->id);
                });
            }

        } elseif ((string)$user->role === '2') {
            // Vendor sees tickets they sent to Admin or received
            /*$query->where(function($q) use ($user) {
                $q->where('user_id', $user->id)
                  ->orWhere('receiver_id', $user->id);
            });*/

             $type = $request->get('type', 'my'); 
            
            if ($type == 'customer') {
                 $query->where('receiver_id', $user->id);
            } else {
                 $query->where('user_id', $user->id);
            }

        } else {
            // Customer sees tickets they created
            $query->where('user_id', $user->id);
        }

        // Filtering by Vendor (only for Admin)
        if ((string)$user->role === '1' && $request->vendor_id) {
            $query->where(function($q) use ($request) {
                $q->where('receiver_id', $request->vendor_id)
                  ->orWhere('user_id', $request->vendor_id);
            });
        }

        // Filtering by Status
        if ($request->status) {
            $query->where('status', $request->status);
        }

        $tickets = $query->latest('updated_at')->paginate(10)->withQueryString();
        
        $vendors = [];
        $open_count = 0;
        $closed_count = 0;

        if ((string)$user->role === '1') {
            $vendors = User::where('role', '2')->select('id', 'name', 'store_name')->get();
        } elseif ((string)$user->role === '2') {
             $type = $request->get('type', 'my');
             if ($type == 'customer') {
                  $open_count = Ticket::where('receiver_id', $user->id)->where('status', '!=', 'Closed')->count();
                  $closed_count = Ticket::where('receiver_id', $user->id)->where('status', 'Closed')->count();
             } else {
                  $open_count = Ticket::where('user_id', $user->id)->where('status', '!=', 'Closed')->count();
                  $closed_count = Ticket::where('user_id', $user->id)->where('status', 'Closed')->count();
             }
        }

        return view('backend.tickets.index', compact('tickets', 'vendors', 'open_count', 'closed_count'));
    }

    public function create()
    {
        $user = Auth::user();
        $vendors = [];
        if ($user->role == '3') {
            // Customers can send to Vendors
            $vendors = User::where('role','2')->where('status', 1)->get();
        } elseif ($user->role == '2') {
            // Vendors can only send to Admin
            $admins = User::where('role', '1')->get();
            return view('backend.tickets.create', compact('admins'));
        }

        return view('backend.tickets.create', compact('vendors'));
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'subject' => 'required|string|max:255',
                'message' => 'nullable|required_without:attachment|string',
                'receiver_id' => 'nullable|exists:users,id',
                'priority' => 'required|in:Low,Medium,High',
                'attachment' => 'nullable|file|mimes:jpg,jpeg,png,pdf,doc,docx|max:2048',
            ]);

            $user = Auth::user();
            $receiver_id = $request->receiver_id;

            // If vendor, always send to admin
            if ($user->role == '2') {
                $admin = User::where('role', '1')->first();
                $receiver_id = $admin->id;
            }

            $ticket = Ticket::create([
                'ticket_id' => 'TKT-' . strtoupper(Str::random(8)),
                'user_id' => $user->id,
                'receiver_id' => $receiver_id,
                'subject' => $request->subject,
                'priority' => $request->priority,
                'status' => 'Open',
            ]);

            $attachmentPath = null;
            if ($request->hasFile('attachment')) {
                $attachmentPath = ImageHelper::uploadImage($request->file('attachment'), 'uploads/ticket');
            }   

            TicketMessage::create([
                'ticket_id' => $ticket->id,
                'user_id' => $user->id,
                'message' => $request->message,
                'attachment' => $attachmentPath,
            ]);

            // Send Notification to Receiver
            if ($ticket->receiver_id) {
                $notifyUsers = [];
                $receiver = User::find($ticket->receiver_id);
                if ($receiver) {
                    $notifyUsers[] = $receiver;
                }

                // If customer (role 3) creates a ticket for a vendor (role 2), also notify Admin
                if ($user->role == '3' && $receiver && $receiver->role == '2') {
                    $admin = User::where('role', '1')->first();
                    if ($admin && !in_array($admin->id, array_column($notifyUsers, 'id'))) {
                        $notifyUsers[] = $admin;
                    }
                }

                foreach ($notifyUsers as $notifyUser) {
                    try {
                        NotificationHelper::send($notifyUser, [
                            'title' => 'New Helpdesk Ticket: ' . $ticket->ticket_id,
                            'message' => 'A new ticket has been raised by ' . $user->name,
                            'type' => 'helpdesk',
                            'url' => route('tickets.show', $ticket->id),
                            'icon' => 'solar:help-bold-duotone',
                            'priority' => strtolower($ticket->priority) == 'high' ? 'critical' : 'medium'
                        ]);
                    } catch (\Exception $e) {
                        Log::error('Ticket Store Notification Error: ' . $e->getMessage());
                    }

                    try {
                        // Email Notification
                       EmailHelper::send(
                            $notifyUser->email,
                            'New Helpdesk Ticket: ' . $ticket->ticket_id,
                            'A new ticket has been raised by <b>' . $user->name . '</b>.<br><br><b>Subject:</b> ' . $ticket->subject . '<br><b>Message:</b><br>' . $request->message
                        );
                    } catch (\Exception $e) {
                        Log::error('Ticket Store Email Error: ' . $notifyUser->email . ' - ' . $e->getMessage());
                    }
                }
            }

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Ticket created successfully.',
                    'redirect_url' => route('tickets.index')
                ]);
            }

            return redirect()->route('tickets.index')->with('success', 'Ticket created successfully.');
        } catch (\Exception $e) {
            Log::error('Ticket Store Error: ' . $e->getMessage());
            
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error: ' . $e->getMessage()
                ], 500);
            }
            
            return back()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    public function show($id)
    {
        $ticket = Ticket::with(['messages.user:id,name,role,image,store_name', 'user:id,name,role,image,store_name', 'receiver:id,name,role,image,store_name'])->findOrFail($id);
        
        // Authorization check
        $user = Auth::user();
        if ($user->role != '1' && $ticket->user_id !== $user->id && $ticket->receiver_id !== $user->id) {
            abort(403);
        }

        return view('backend.tickets.show', compact('ticket'));
    }

    public function reply(Request $request, $id)
    {
        try {
            $request->validate([
                'message' => 'nullable|required_without:attachment|string',
                'attachment' => 'nullable|file|mimes:jpg,jpeg,png,pdf,doc,docx|max:2048',
            ]);

            $ticket = Ticket::findOrFail($id);
            $user = Auth::user();

            // Check for Escalation - Vendor cannot reply
            if ($ticket->status == 'Escalated' && $user->role == '2') {
                if ($request->ajax()) {
                    return response()->json(['success' => false, 'message' => 'This ticket has been escalated to Admin. You cannot reply.']);
                }
                return back()->with('error', 'This ticket has been escalated to Admin. You cannot reply.');
            }

            $attachmentPath = null;
            if ($request->hasFile('attachment')) {
                $attachmentPath = ImageHelper::uploadImage($request->file('attachment'), 'uploads/ticket');
            }

            $message = TicketMessage::create([
                'ticket_id' => $ticket->id,
                'user_id' => $user->id,
                'message' => $request->message,
                'attachment' => $attachmentPath,
            ]);

            // Update status
            if ($user->id === $ticket->receiver_id) {
                $ticket->status = 'In Progress';
            }
            
            $ticket->last_reply_at = now();
            $ticket->save();

            // Send Notification to the other party
            $notifyUserId = ($user->id === $ticket->user_id) ? $ticket->receiver_id : $ticket->user_id;
            if ($notifyUserId) {
                $notifyUsers = [];
                $mainNotifyUser = User::find($notifyUserId);
                if ($mainNotifyUser) {
                    $notifyUsers[] = $mainNotifyUser;
                }

                // If customer (role 3) replies, notify both Vendor and Admin
                if ($user->role == '3') {
                    $admin = User::where('role', '1')->first();
                    if ($admin && !in_array($admin->id, array_column($notifyUsers, 'id'))) {
                        $notifyUsers[] = $admin;
                    }
                }

                foreach ($notifyUsers as $notifyUser) {
                    try {
                        NotificationHelper::send($notifyUser, [
                            'title' => 'New Reply on Ticket: ' . $ticket->ticket_id,
                            'message' => $user->name . ' replied to a ticket.',
                            'type' => 'helpdesk',
                            'url' => route('tickets.show', $ticket->id),
                            'icon' => 'solar:chat-round-dots-bold-duotone',
                            'priority' => 'medium'
                        ]);
                    } catch (\Exception $e) {
                        Log::error('Ticket Reply Notification Error: ' . $e->getMessage());
                    }

                    try {
                        // Email Notification
                        \App\Helpers\EmailHelper::send(
                            $notifyUser->email,
                            'New Reply on Ticket: ' . $ticket->ticket_id,
                            '<b>' . $user->name . '</b> replied to a ticket (<b>' . $ticket->ticket_id . '</b>).<br><br><b>Message:</b><br>' . $request->message
                        );
                    } catch (\Exception $e) {
                        Log::error('Ticket Reply Email Error: ' . $notifyUser->email . ' - ' . $e->getMessage());
                    }
                }
            }

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Message sent successfully.',
                    'data' => $message->load('user')
                ]);
            }

            return back()->with('success', 'Message sent successfully.');
        } catch (\Exception $e) {
            Log::error('Ticket Reply Error: ' . $e->getMessage());
            
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error: ' . $e->getMessage()
                ], 500);
            }
            
            return back()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    public function fetchMessages($id, Request $request)
    {
        $ticket = Ticket::select('id', 'user_id', 'receiver_id')->findOrFail($id);
        $user = Auth::user();

        if ($user->role != '1' && $ticket->user_id !== $user->id && $ticket->receiver_id !== $user->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $query = $ticket->messages()->with('user:id,name,role,image,store_name')->orderBy('created_at', 'asc');

        if ($request->has('last_id')) {
            $query->where('id', '>', $request->last_id);
        }

        $messages = $query->get()->map(function($msg) {
            $msg->formatted_time = $msg->created_at->format('h:i A');
            $msg->human_time = $msg->created_at->diffForHumans();
            return $msg;
        });

        return response()->json([
            'messages' => $messages,
            'auth_id' => $user->id
        ]);
    }

    public function escalate($id, Request $request)
    {
        $ticket = Ticket::findOrFail($id);
        $user = Auth::user();

        // Allow Vendors to escalate tickets
        if ($user->role != '2' && $user->role != '1') {
             if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Unauthorized action.']);
             }
             return back()->with('error', 'Unauthorized action.');
        }

        $ticket->status = 'Escalated';
        $ticket->escalated_at = now();
        $ticket->save();

        // Notify Admin
        $admins = User::where('role', '1')->get();
        foreach($admins as $admin) {
             NotificationHelper::send($admin, [
                'title' => 'Ticket Escalated: ' . $ticket->ticket_id,
                'message' => 'Ticket has been escalated by ' . $user->name,
                'type' => 'helpdesk',
                'url' => route('tickets.show', $ticket->id),
                'icon' => 'solar:danger-circle-bold-duotone',
                'priority' => 'critical'
            ]);
        }

        if ($request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Ticket escalated to Admin successfully.']);
        }

        return back()->with('success', 'Ticket escalated to Admin successfully.');
    }

    public function close($id, Request $request)
    {
        $ticket = Ticket::findOrFail($id);
        
        // Only Admin can close tickets (as per request)
        if (Auth::user()->role != '1') {
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Only admin can close tickets.']);
            }
            return back()->with('error', 'Only admin can close tickets.');
        }

        $ticket->status = 'Closed';
        $ticket->save();

        // Notify user
        $user = User::find($ticket->user_id);
        if ($user) {
            try {
                EmailHelper::send(
                    $user->email,
                    'Ticket Closed: ' . $ticket->ticket_id,
                    'Your ticket (<b>' . $ticket->ticket_id . '</b>) has been marked as <b>Closed</b>.'
                );

                // Push Notification
                NotificationHelper::send($user, [
                    'title' => 'Ticket Closed: ' . $ticket->ticket_id,
                    'message' => 'Your ticket has been marked as Closed.',
                    'type' => 'helpdesk',
                    'url' => route('tickets.show', $ticket->id),
                    'icon' => 'solar:check-circle-bold-duotone',
                    'priority' => 'medium'
                ]);
            } catch (\Exception $e) {
                Log::error('Ticket Close Notification Error: ' . $e->getMessage());
            }
        }

        if ($request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Ticket closed successfully.']);
        }

        return back()->with('success', 'Ticket closed successfully.');
    }
}
