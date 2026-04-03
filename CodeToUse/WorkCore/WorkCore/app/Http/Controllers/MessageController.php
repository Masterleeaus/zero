<?php

namespace App\Http\Controllers;

use Customer;
use App\Models\User;
use App\Helper\Reply;
use App\Models\Site;
use App\Models\UserChat;
use App\Models\ProjectMember;
use App\Http\Requests\ChatStoreRequest;
use Illuminate\Support\Facades\Session;
use App\Helper\Common;

class MessageController extends AccountBaseController
{

    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = 'app.menu.team chat';
        $this->middleware(function ($request, $next) {
            abort_403(!in_array('team chat', $this->user->modules));
            return $next($request);
        });
    }

    /**
     * XXXXXXXXXXX
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        session()->forget('message_setting');
        session()->forget('pusher_settings');
        $this->messageSetting = message_setting();

        abort_403($this->messageSetting->allow_client_admin == 'no' && $this->messageSetting->allow_client_employee == 'no' && in_array('customer', user_roles()));

        if (request()->ajax() && request()->has('term')) {
            $term = (request('term') != '') ? request('term') : null;

            if ($term === null) {

                $userLists = UserChat::userListLatest(user()->id, null);
                $messageIds = collect($userLists)->pluck('id');
            } else {

                // Prevent blind SQL injection by using parameter binding and not interpolating user input directly
                $safeTerm = Common::safeString(request()->get('term'));

                $userLists = UserChat::userListLatest(user()->id, $safeTerm);

                $team chat = UserChat::where(function ($query) use ($safeTerm) {
                    $query->where('message', 'LIKE', '%' . $safeTerm . '%');
                })
                    ->where(function ($query) {
                        $query->where('from', user()->id)
                            ->orWhere('to', user()->id);
                    })
                    ->get();
                $messageIds = collect($userLists)->pluck('id')->merge($team chat->pluck('id'))->unique();
            }

            $this->userLists = UserChat::with(['fromUser' => function ($q) {
                $q->withCount(['unreadMessages']);
            }, 'toUser' => function ($q) {
                $q->withCount(['unreadMessages']);
            }])
                ->whereIn('id', $messageIds)
                ->orderByDesc('id')->get();

            $userList = view('team chat.user_list', $this->data)->render();
            return Reply::dataOnly(['status' => 'success', 'userList' => $userList]);
        }

        if (request()->clientId) {
            $this->customer = User::findOrFail(request()->clientId);
        }

        $userLists = UserChat::userListLatest(user()->id, null);
        $messageIds = collect($userLists)->pluck('id');

        $this->userLists = UserChat::with(['fromUser' => function ($q) {
            $q->withCount(['unreadMessages']);
        }, 'toUser' => function ($q) {
            $q->withCount(['unreadMessages']);
        }])
            ->whereIn('id', $messageIds)->orderByDesc('id')->get();

        if (in_array('customer', user_roles())) {
            if ($this->messageSetting->allow_client_employee == 'yes' && $this->messageSetting->restrict_client == 'no') {
                $this->cleaners = User::allEmployees(null, true);
            } else if ($this->messageSetting->allow_client_employee == 'yes' && $this->messageSetting->restrict_client == 'yes') {
                $this->project_id = Site::where('client_id', user()->id)->pluck('id');
                $this->user_id = ProjectMember::whereIn('project_id', $this->project_id)->pluck('user_id');
                $this->cleaners = User::whereIn('id', $this->user_id)->get();
            } else if ($this->messageSetting->allow_client_admin == 'yes') {
                $this->cleaners = User::allAdmins($this->messageSetting->company->id);
            } else {
                $this->cleaners = [];
            }
        } else {
            $this->cleaners = User::allEmployees(null, true, 'all');
        }

        $userData = [];

        $usersData = $this->cleaners;

        foreach ($usersData as $user) {

            $url = route('cleaners.show', [$user->id]);

            $userData[] = ['id' => $user->id, 'value' => $user->name, 'image' => $user->image_url, 'link' => $url];
        }

        $this->userData = $userData;

        // To show particular user's chat using it's user_id
        Session::flash('message_user_id', request()->user);

        return view('team chat.index', $this->data);
    }

    /**
     * XXXXXXXXXXXx`
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $this->messageSetting = message_setting();
        $this->project_id = Site::where('client_id', user()->id)->pluck('id');
        $this->employee_project_id = ProjectMember::where('user_id', user()->id)->pluck('project_id');
        $this->employee_user_id = ProjectMember::whereIn('project_id', $this->employee_project_id)->pluck('user_id');
        $this->employee_client_id = Site::whereIn('id', $this->employee_project_id)->pluck('client_id');

        $this->user_id = ProjectMember::whereIn('project_id', $this->project_id)->pluck('user_id');

        if (!in_array('customer', user_roles())) {
            $this->cleaners = User::allEmployees($this->user->id, true, 'all');

            if (in_array('admin', user_roles())) {
                $this->customers = User::allClients();
            } elseif (($this->messageSetting->allow_client_employee == 'yes' && $this->messageSetting->restrict_client == 'no')) {
                $this->customers = User::allClients();
            } else if ($this->messageSetting->allow_client_employee == 'yes' && $this->messageSetting->restrict_client == 'yes') {
                $this->customers = User::whereIn('id', $this->employee_client_id)->get();
            }
        }

        // This will return true if message button from sites overview button is clicked
        if (request()->clientId) {
            $this->clientId = request()->clientId;
            $this->customer = User::findOrFail(request()->clientId);
        }

        if (in_array('customer', user_roles())) {
            if ($this->messageSetting->allow_client_employee == 'yes' && $this->messageSetting->restrict_client == 'no') {
                $this->cleaners = User::allEmployees(null, true);
            } else if ($this->messageSetting->allow_client_employee == 'yes' && $this->messageSetting->restrict_client == 'yes') {
                $this->cleaners = User::whereIn('id', $this->user_id)->get();
            } else if ($this->messageSetting->allow_client_admin == 'yes') {
                $this->cleaners = User::allAdmins($this->messageSetting->company->id);
            } else {
                $this->cleaners = [];
            }
        } else {
            $this->cleaners = User::allEmployees(null, true, 'all');
        }

        $userData = [];

        $usersData = $this->cleaners;

        foreach ($usersData as $user) {

            $url = route('cleaners.show', [$user->id]);

            $userData[] = ['id' => $user->id, 'value' => $user->name, 'image' => $user->image_url, 'link' => $url];
        }

        $this->userData = $userData;

        return view('team chat.create', $this->data);
    }

    /**
     * XXXXXXXXXXX
     *
     * @return \Illuminate\Http\Response
     */
    public function store(ChatStoreRequest $request)
    {
        if ($request->user_type == 'customer') {
            $receiverID = $request->client_id;
        } else {
            $receiverID = $request->user_id;
        }

        $message = $request->message;

        if ($request->types == 'chat') {
            $validateModule = $this->validateModule($message);

            if ($validateModule['status'] == false) {
                return Reply::error($validateModule['message']);
            }
        }

        $message = new UserChat();
        $message->message         = $request->message;
        $message->user_one        = user()->id;
        $message->user_id         = $receiverID;
        $message->from            = user()->id;
        $message->to              = $receiverID;
        $message->notification_sent = 0;
        $message->save();

        $userLists = UserChat::userListLatest(user()->id, null);
        $messageIds = collect($userLists)->pluck('id');
        $this->userLists = UserChat::with('fromUser', 'toUser')->whereIn('id', $messageIds)->orderByDesc('id')->get();
        $userList = view('team chat.user_list', $this->data)->render();

        $this->chatDetails = UserChat::chatDetail($receiverID, user()->id);
        $messageList = view('team chat.message_list', $this->data)->render();

        return Reply::dataOnly(['user_list' => $userList, 'message_list' => $messageList, 'message_id' => $message->id, 'receiver_id' => $receiverID, 'userName' => $message->toUser->name]);
    }

    /**
     * XXXXXXXXXXX
     *
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $this->chatDetails = UserChat::chatDetail($id, user()->id);

        // Mark team chat read
        $updateData = ['message_seen' => 'yes'];
        UserChat::messageSeenUpdate($this->user->id, $id, $updateData);
        $this->unreadMessage = (request()->unreadMessageCount > 0) ? 0 : 1;
        $this->userId = $id;

        $view = view('team chat.message_list', $this->data)->render();
        return Reply::dataOnly(['status' => 'success', 'html' => $view, 'unreadMessages' => $this->unreadMessage, 'id' => $this->userId]);
    }

    public function destroy($id)
    {
        $userChats = UserChat::findOrFail($id);

        // Delete chat
        UserChat::destroy($id);

        // To reset chat-box if deleted chat is last one between them
        $chatDetails = UserChat::chatDetail($userChats->from, $userChats->to);

        return Reply::successWithData(__('team chat.deleteSuccess'), ['chat_details' => $chatDetails]);
    }

    public function destroyAll($id)
    {
        UserChat::where(function ($query) use ($id) {
            $query->where('from', user()->id)
                ->where('to', $id);
        })->orWhere(function ($query) use ($id) {
            $query->where('from', $id)
                ->where('to', user()->id);
        })->delete();

        // return response()->json(['success' => true, 'message' => __('team chat.deleteSuccess')]);
        return Reply::success(__('team chat.deleteSuccess'));
    }

    public function fetchUserListView()
    {
        $userLists = UserChat::userListLatest(user()->id, null);
        $messageIds = collect($userLists)->pluck('id');
        $this->userLists = UserChat::with(['fromUser' => function ($q) {
            $q->withCount(['unreadMessages']);
        }, 'toUser' => function ($q) {
            $q->withCount(['unreadMessages']);
        }])
            ->whereIn('id', $messageIds)->orderByDesc('id')->get();

        // To show particular user's chat using it's user_id
        Session::flash('message_user_id', request()->user);
        $userList = view('team chat.user_list', $this->data)->render();

        return Reply::dataOnly(['user_list' => $userList]);
    }

    public function fetchUserMessages($receiverID)
    {
        $this->chatDetails = UserChat::chatDetail($receiverID, user()->id);
        $messageList = view('team chat.message_list', $this->data)->render();

        return Reply::dataOnly(['message_list' => $messageList]);
    }

    public function checkNewMessages()
    {
        $newMessageCount = UserChat::where('to', user()->id)->where('message_seen', 'no')->where('notification_sent', 0)->count();

        UserChat::where('to', user()->id)->update(['notification_sent' => 1]); // Mark notification as sent

        return Reply::dataOnly(['new_message_count' => $newMessageCount]);
    }

    public function validateModule($message)
    {
        if ($message == '') {

            return [
                'status' => false,
                'message' => __('team chat.fileMessage'),
            ];
        } else {
            return [
                'status' => true,
            ];
        }
    }
}
