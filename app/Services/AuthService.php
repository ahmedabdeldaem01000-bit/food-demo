<?php
namespace App\Services;

use App\Models\Otp;
use App\Models\User;
use App\Mail\OtpMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Carbon\Carbon;

class AuthService
{
    /**
     * Summary of sendOtp
     * @param array<array,string> $data
     * @return void
     */
    public function sendOtp(array $data): void
    {
        $code = random_int(1000, 9999);

        Otp::updateOrCreate(
            ['email' => $data['email']],
            [
                'name' => $data['name'],
                'phone' => $data['phone'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'code' => $code,
                'expires_at' => Carbon::now()->addMinutes(10),
            ]
        );
        Mail::to($data['email'])->queue(new OtpMail((string)$code));

    }

    /**
     * Summary of verifyOtp
     * @param array<array,mixed> $data
     * @throws \Exception
     * @return User
     */
    public function verifyOtp(array $data): User
    {
        
     
        $data['code'] = implode('', $data['code']);

        $otp = Otp::where('code', $data['code'])
            ->where('expires_at', '>', now())
            ->first();

        if (!$otp) {
            throw new \Exception('Invalid or expired OTP');
        }

        return DB::transaction(function () use ($otp) {
            $user = User::create([
                'name' => $otp->name,
                'email' => $otp->email,
                'phone' => $otp->phone,
                'password' => $otp->password,
            ]);

            // إعطاء المستخدم الدور الافتراضي بمجرد إنشائه
            // يمكنك تغيير 'user' إلى اسم الدور الذي تستخدمه في نظامك مثل 'customer' أو 'client'
            $user->assignRole('user');

            $otp->delete();

            return $user;
        });
    }





    /**
     * Summary of sendOtpPassword
     * @param array<array,float> $data
     * @throws \Exception
     * @return void
     */
    public function sendOtpPassword(array $data): void
    {
      

        $code = random_int(1000, 9999);
        $user = User::where('email', $data['email'])->first();
        if (!$user) {
            throw new \Exception('User not found');
        }
        Otp::updateOrCreate(
            ['email' => $data['email']],
            [
                'phone' => $user['phone'],
                'email' => $user['email'],
                'name' => $user['name'],
                'password' => Hash::make($user['password']),

                'code' => $code,
                'expires_at' => Carbon::now()->addMinutes(10),
            ]
        );
        Mail::to($data['email'])->queue(new OtpMail((string)$code));

    }

 

/**
 * @param array<string, mixed> $data
 * @return User|null
 */
public function verifyOtpPassword(array $data): ?User
{
    // PHPStan ممكن يعترض لو عملنا implode على حاجة مش مأكدين إنها array
    /** @var array<int, string|int> $codeArray */
    $codeArray = $data['code'] ?? [];
    $data['code'] = implode('', $codeArray);

    $otp = Otp::where('code', $data['code'])
        ->where('expires_at', '>', now())
        ->first();

    if (!$otp) {
        throw new \Exception('Invalid or expired OTP');
    }

    /** @var User|null $user */
    $user = User::where('email', $otp->email)->first();

    return $user;
}
}