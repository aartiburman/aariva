<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use App\Models\TicketMessage;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use App\Helpers\EmailHelper;
use App\Helpers\ImageHelper;

class TicketApiController extends Controller
{
    /**
     * Get list of tickets for the authenticated user (Customer or Vendor)
     */
    public function ticket_index(Request $request)
    {
        $user_id = $request->user_id;
        if (!$user_id) {
            // Support for non-sanctum requests if user_id is passed (as seen in some other controllers)
            $user = $request->user();
        } else {
            $user = User::find($user_id);
        }

        if (!$user) {
            return response()->json(['status' => false, 'message' => 'Unauthorized'], 401);
        }

        $query = Ticket::with(['user', 'receiver', 'order']);

        // Role-based visibility
        if ((string)$user->role === '1') {
            $query->where(function($q) use ($user) {
                $q->where('receiver_id', $user->id)
                  ->orWhere('status', 'Escalated')
                  ->orWhere('user_id', $user->id);
            });
        } elseif ((string)$user->role === '2') {
            $query->where(function($q) use ($user) {
                $q->where('receiver_id', $user->id)
                  ->orWhere('user_id', $user->id);
            });
        } else {
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

        $tickets = $query->latest()->get();

        return response()->json([
            'status' => true,
            'data' => $tickets
        ]);
    }

    /**
     * Create a new ticket (Customer to Vendor or Admin)
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'subject' => 'required|string|max:255',
            'message' => 'nullable|required_without:attachment|string',
            'order_id' => 'nullable|exists:orders,id',
            'order_item_id' => 'nullable|exists:order_items,id',
            'priority' => 'nullable|in:Low,Medium,High',
            'attachment' => 'nullable|file|mimes:jpg,jpeg,png,pdf,doc,docx|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors()
            ], 422);
        }

        $user = User::find($request->user_id);
        if (!$user) {
            return response()->json(['status' => false, 'message' => 'User not found'], 404);
        }

        $receiver_id = null;
        $order_id = $request->order_id;

        // Logic for receiver based on Order Item
        if ($request->order_item_id) {
            $orderItem = OrderItem::find($request->order_item_id);
            if ($orderItem) {
                // If it's an order related ticket, send to the vendor of that order item
                $receiver_id = $orderItem->vendor_id;
                // Ensure order_id is correct
                $order_id = $orderItem->order_id;
            }
        }

        // Logic for receiver based on Order (if no item specified)
        // If order_id is present but no item, we might need to find the vendor(s). 
        // For simplicity, if multiple vendors, maybe default to Admin or primary vendor?
        // Let's stick to order_item_id being the primary driver for vendor selection.

        // If no receiver yet (not order related or vendor not found), and it's a customer, 
        // they might need to pick a vendor or it goes to admin
        if (!$receiver_id && $user->role == '3') {
             // If user manually selected a receiver (vendor)
             if ($request->receiver_id) {
                 $receiver_id = $request->receiver_id;
             } else {
                 // Default to Admin if no vendor specified and not order related
                 $admin = User::where('role', '1')->first();
                 $receiver_id = $admin ? $admin->id : null;
             }
        } elseif ($user->role == '2') {
            // Vendor creating ticket always goes to Admin
            $admin = User::where('role', '1')->first();
            $receiver_id = $admin ? $admin->id : null;
        }

        $ticket = Ticket::create([
            'ticket_id' => 'TKT-' . strtoupper(Str::random(8)),
            'user_id' => $user->id,
            'receiver_id' => $receiver_id,
            'order_id' => $order_id,
            'order_item_id' => $request->order_item_id,
            'subject' => $request->subject,
            'priority' => $request->priority ?? 'Medium',
            'status' => 'Open',
        ]);

        OrderItem::find($request->order_item_id)->update([
            'status' => 6,
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

        // Send Notifications
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
                if ($notifyUser && $notifyUser->email) {
                    EmailHelper::send(
                        $notifyUser->email,
                        'New Helpdesk Ticket: ' . $ticket->ticket_id,
                        'A new ticket has been raised by <b>' . $user->name . '</b>.<br><br><b>Subject:</b> ' . $ticket->subject . '<br><b>Message:</b><br>' . $request->message
                    );
                }

                // Push Notification
                try {
                    \App\Helpers\NotificationHelper::send($notifyUser, [
                        'title' => 'New Helpdesk Ticket: ' . $ticket->ticket_id,
                        'message' => 'A new ticket has been raised by ' . $user->name,
                        'type' => 'helpdesk',
                        'url' => route('tickets.show', $ticket->id),
                        'icon' => 'solar:help-bold-duotone',
                        'priority' => strtolower($ticket->priority) == 'high' ? 'critical' : 'medium'
                    ]);
                } catch (\Exception $e) {
                    \Illuminate\Support\Facades\Log::error('Ticket Store Notification Error: ' . $e->getMessage());
                }
            }
        }

        return response()->json([
            'status' => true,
            'message' => 'Ticket created successfully',
            'data' => $ticket->load('messages')
        ]);
    }

    /**
     * Show ticket details and conversation
     */
    public function show(Request $request)
    {
        $user_id = $request->user_id;
        $ticket_id = $request->ticket_id;
        $ticket = Ticket::with(['messages.user', 'user', 'receiver', 'order'])->find($ticket_id);

        if (!$ticket) {
            return response()->json(['status' => false, 'message' => 'Ticket not found'], 404);
        }

        // Auth check
        if ($user_id && $ticket->user_id != $user_id && $ticket->receiver_id != $user_id) {
            // Check if user is admin
            $user = User::find($user_id);
            if (!$user || $user->role != '1') {
                return response()->json(['status' => false, 'message' => 'Unauthorized'], 403);
            }
        }

        return response()->json([
            'status' => true,
            'data' => $ticket
        ]);
    }

    /**
     * Reply to a ticket
     */
    public function reply(Request $request)
    {
        $ticket_id = $request->ticket_id;
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'message' => 'nullable|required_without:attachment|string',
            'attachment' => 'nullable|file|mimes:jpg,jpeg,png,pdf,doc,docx|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }

        $ticket = Ticket::find($ticket_id);
        if (!$ticket) {
            return response()->json(['status' => false, 'message' => 'Ticket not found'], 404);
        }

        $user = User::find($request->user_id);

        if ($ticket->status == 'Escalated' && $user->role == '2') {
             return response()->json(['status' => false, 'message' => 'This ticket has been escalated to Admin. You cannot reply.']);
        }

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

        // Update status if receiver replies
        if ($user->id == $ticket->receiver_id) {
            $ticket->status = 'In Progress';
        }
        
        $ticket->last_reply_at = now();
        $ticket->save();

        // Send Notifications
        $notifyUserId = ($user->id == $ticket->user_id) ? $ticket->receiver_id : $ticket->user_id;
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
                if ($notifyUser && $notifyUser->email) {
                    EmailHelper::send(
                        $notifyUser->email,
                        'New Reply on Ticket: ' . $ticket->ticket_id,
                        '<b>' . $user->name . '</b> replied to a ticket (<b>' . $ticket->ticket_id . '</b>).<br><br><b>Message:</b><br>' . $request->message
                    );
                }

                // Push Notification
                try {
                    \App\Helpers\NotificationHelper::send($notifyUser, [
                        'title' => 'New Reply on Ticket: ' . $ticket->ticket_id,
                        'message' => $user->name . ' replied to a ticket.',
                        'type' => 'helpdesk',
                        'url' => route('tickets.show', $ticket->id),
                        'icon' => 'solar:chat-round-dots-bold-duotone',
                        'priority' => 'medium'
                    ]);
                } catch (\Exception $e) {
                    \Illuminate\Support\Facades\Log::error('Ticket Reply Notification Error: ' . $e->getMessage());
                }
            }
        }

        return response()->json([
            'status' => true,
            'message' => 'Reply sent successfully',
            'data' => $ticket->load('messages')
        ]);
    }
}
