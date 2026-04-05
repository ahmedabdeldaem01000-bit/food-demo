<?php

namespace App\Http\Controllers\web\Profile;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\UserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserController extends Controller
{

    protected UserService $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    public function profilePage(): View
    {
        $data = $this->userService->getProfile();
        return view('web.profile.index', compact('data'));
    }


    public function personalEditShow():View
    {
        return view('web.profile.update');
    }
    public function showOrders():View
    {
        $orders = Order::with(['items.product', 'payment'])
            ->where('user_id', auth()->id())
            ->latest() // ترتيب من الأحدث للأقدم
            ->get();


        return view('web.orders.index', compact('orders'));
    }
    public function personalEdit(Request $request):RedirectResponse
    {
        $data = $request->validate([
            'name' => 'required|string',
            'email' => 'required|email',
            'phone' => 'required|string',
            'birth_date' => 'required|date',
            'address' => 'required|string',
            'photo' => 'nullable|image'
        ]);

        if ($request->hasFile('photo')) {

            $photo = $request->file('photo');

            $photoName = time() . '.' . $photo->getClientOriginalExtension();

            $photo->move(public_path('images/user'), $photoName);

            $data['photo'] = 'images/user/' . $photoName;
        }

        $user = $this->userService->updateProfile($data);

        return redirect()->back()->with('user');
    }

    public function userOrder():JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'data' => $this->userService->getUserOrders()
        ]);
    }

    public function notificationShow():View
    {
        return view('web.profile.notification');
    }
}
// 