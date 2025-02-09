<?php

namespace App\Http\Controllers\Api\User;

use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Payment\VerifyPaymentRequest;
use App\Http\Requests\Api\User\StorePaymentRequest;
use App\Http\Resources\Ecommerce\Order\UserOrderResource;
use App\Http\Resources\User\PaymentResource;
use App\Models\Payment;
use App\Models\User;
use App\Models\UserOrder;
use App\Services\PaymentService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Shetabit\Payment\Facade\Payment as ShetabitPayment;
use Symfony\Component\HttpFoundation\Response;

class PaymentController extends Controller
{
    protected $paymentService;

    public function __construct(PaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    public function index()
    {
        $paymentMethods = Auth::user()->payment_methods ?? PaymentMethod::names();
        $inactiveMethods = ['SEP', 'BEHPARDAKHT'];

        return response()->json(array_diff($paymentMethods, $inactiveMethods));
    }

    public function store(StorePaymentRequest $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if (! $user) {
            return response()->json(['error' => 'Unauthorized'], Response::HTTP_UNAUTHORIZED);
        }

        $user->load(['wallet']);
        $order = UserOrder::findOrFail($request->order);

        DB::beginTransaction();

        try {
            $response = $request->method === PaymentMethod::WALLET
                ? $this->handleWalletPayment($user, $order)
                : $this->handlePortalPayment($user, $order, $request->method, $request->withWallet);

            $order->update(['status' => OrderStatus::PENDING]);
            DB::commit();

            return $response;
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error('Payment processing error: '.$th->getMessage());

            return response()->json(['error' => 'Failed to process payment'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function verify(VerifyPaymentRequest $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $payment = Payment::where('bank_transaction_id', $request->track_id)->firstOrFail();

        try {
            $order = $payment->paymentable;
            $amount = $payment->amount;
            $driverName = PaymentMethod::getDriverName($request->method);

            $receipt = ShetabitPayment::amount($amount)->via($driverName)->transactionId($request->track_id)->verify();
            if ($receipt) {
                $this->paymentService->verified($user->wallet, $payment, $amount, PaymentStatus::COMPLETED);
                $this->handleWalletPayment($user, $order, $payment);
                $order->update([
                    'status' => OrderStatus::PAID,
                ]);
            }

            return (new UserOrderResource($order))->response()->setStatusCode(Response::HTTP_ACCEPTED);
        } catch (\Throwable $th) {
            Log::error('fail to verify transaction: '.$th);
            if ($th->getCode() === 201) {
                return (new UserOrderResource($order))->response()->setStatusCode(Response::HTTP_ACCEPTED);
            }
            $payment->update([
                'status' => PaymentStatus::FAILED,
            ]);
            $payment->save();
            abort(Response::HTTP_FORBIDDEN);
        }
    }

    private function handleWalletPayment(User $user, UserOrder $order, ?Payment $payment = null): \Illuminate\Http\JsonResponse
    {
        try {
            if ($user->wallet->total < $order->final_price) {
                return abort(Response::HTTP_PAYMENT_REQUIRED);
            }

            if (is_null($payment)) {
                $payment = $this->paymentService->createPayment($order, $order->final_price, PaymentMethod::WALLET);
            }

            $this->paymentService->complete($user->wallet, $payment, $order->final_price, PaymentStatus::COMPLETED);

            DB::commit();

            return (new PaymentResource($payment))->response()->setStatusCode(Response::HTTP_CREATED);
        } catch (\Throwable $th) {
            Log::error('fail to verify transaction: '.$th->getMessage());
            abort(Response::HTTP_FORBIDDEN);
        }
    }

    private function handlePortalPayment(User $user, UserOrder $order, int $method, ?bool $withWallet = false): \Illuminate\Http\JsonResponse
    {
        $driver = PaymentMethod::getDriverName($method);

        $amount = $withWallet ? $order->final_price - $user->wallet->balance : $order->final_price;
        $details = $this->paymentService->create($amount, $driver, $order);
        DB::commit();

        return response()->json(json_decode($details))->setStatusCode(Response::HTTP_CREATED);
    }
}
