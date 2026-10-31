<?php

namespace App\Http\Controllers;

use App\Actions\Payments\ApplyPaymentStatus;
use App\Actions\Payments\CreatePixCharge;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Payments\PaymentGatewayException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PixController extends Controller
{
    /**
     * Generates the Pix charge when checkout could not (provider down).
     */
    public function store(Request $request, Order $order, CreatePixCharge $createCharge): RedirectResponse
    {
        abort_unless($order->user_id === $request->user()->getKey(), 403);

        if ($order->status === OrderStatus::PendingPayment) {
            try {
                $createCharge->handle($order);
            } catch (PaymentGatewayException $exception) {
                report($exception);
                $this->toast('error', 'Não foi possível gerar o Pix agora. Tente novamente em instantes.');
            }
        }

        return back();
    }

    /**
     * Demo only (fake gateway): pays the charge through the same path a
     * provider webhook takes.
     */
    public function simulate(Request $request, Order $order, ApplyPaymentStatus $apply): RedirectResponse
    {
        abort_unless(self::canSimulate(), 404);
        abort_unless($order->user_id === $request->user()->getKey(), 403);

        $payment = $order->payment()->where('provider', 'fake')->firstOrFail();
        $apply->handle($payment, PaymentStatus::Approved);

        $this->toast('success', 'Pagamento simulado com sucesso.');

        return back();
    }

    public static function canSimulate(): bool
    {
        return config('payments.gateway') === 'fake';
    }
}
