<?php

namespace App\Http\Controllers\web\Profile;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\UserService;
use Illuminate\Http\Request;

class UserController extends Controller
{

    protected $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    public function profilePage()
    {
        $data= $this->userService->getProfile();
        return view('web.profile.index',compact('data'));
    }


    public function personalEditShow(){
        return view('web.profile.update');
    }
public function showOrders()
{
   $orders = Order::with(['items.product', 'payment'])
        ->where('user_id', auth()->id())
        ->latest() // ترتيب من الأحدث للأقدم
        ->get();
 
 
        return view('web.orders.index', compact('orders'));
}
   public function personalEdit(Request $request)
{
    $data = $request->validate([
        'name'       => 'required|string',
        'email'      => 'required|email',
        'phone'      => 'required|string',
        'birth_date' => 'required|date',
        'address'    => 'required|string',
        'photo'      => 'nullable|image'
    ]);

    if ($request->hasFile('photo')) {

        $photo = $request->file('photo');

        $photoName = time().'.'.$photo->getClientOriginalExtension();

        $photo->move(public_path('images/user'), $photoName);

        $data['photo'] = 'images/user/'.$photoName;
    }

    $user = $this->userService->updateProfile($data);

    return redirect()->back()->with('user');
}

    public function userOrder()
    {
        return response()->json([
            'status' => 'success',
            'data' => $this->userService->getUserOrders()
        ]);
    }

    public function notificationShow(){
        return view('web.profile.notification');
    }
}
// 