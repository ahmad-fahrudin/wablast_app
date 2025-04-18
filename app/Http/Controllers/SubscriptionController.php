<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Models\Payment;
use App\Models\Subscription;
use Illuminate\Http\Request;
use App\Services\DeviceService;
use App\Services\PaymentService;
use App\Services\SubscriptionService;
use App\Enums\StatusPaymentEnum;

class SubscriptionController extends Controller
{
    protected $deviceService;
    protected $subscriptionService;
    protected $paymentService;

    public function __construct(DeviceService $deviceService, SubscriptionService $subscriptionService, PaymentService $paymentService)
    {
        $this->subscriptionService = $subscriptionService;
        $this->deviceService = $deviceService;
        $this->paymentService = $paymentService;
    }

    public function index(Request $request)
    {
        $search = $request->input('search');

        $devices = $this->deviceService->getPaginatedSubscribedDevices(
            auth()->id(),
            $search,
            15
        );

        return Inertia::render('subscription/Index', [
            'devices' => $devices,
            'filters' => [
                'search' => $search,
            ],
        ]);
    }

    public function planList()
    {
        $plans = $this->subscriptionService->getPlan();

        $filteredPlans = collect($plans)->reject(function ($plan) {
            return $plan->name === 'Free';
        });

        return Inertia::render('subscription/PlanList', [
            'plans' => $filteredPlans,
        ]);
    }

    public function create()
    {
        return Inertia::render('subscription/Create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'limit' => 'required|integer|min:0',
            'is_active' => 'boolean',
        ]);

        Subscription::create($validated);

        return redirect()->route('subscriptions.index');
    }

    public function checkout($id)
    {
        $plan = $this->subscriptionService->getPlanById($id);

        if (!$plan) {
            return redirect()->route('subscriptions.plan')
                ->with('error', 'Subscription plan not found.');
        }
        $unsubscribedDevices = $this->deviceService->getExpiredDevices(auth()->id());

        return Inertia::render('subscription/Checkout', [
            'plan' => $plan,
            'devices' => $unsubscribedDevices,
        ]);
    }

    public function payment(Request $request)
    {
        $validated = $request->validate([
            'device_id' => 'required|exists:devices,id',
            'subscription_id' => 'required|exists:subscriptions,id',
            'price' => 'required|numeric',
            'payment_method' => 'required|string',
            'order_id' => 'required|string',
            'payment_proof' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
        ]);

        // Handle file upload if payment proof is provided
        $imagePath = null;
        if ($request->hasFile('payment_proof')) {
            $imagePath = $request->file('payment_proof')->store('payment_proofs', 'public');
        }

        // Create new payment record
        $payment = new Payment([
            'user_id' => auth()->id(),
            'device_id' => $validated['device_id'],
            'subscription_id' => $validated['subscription_id'],
            'price' => $validated['price'],
            'image' => $imagePath,
            'status' => 'pending',
        ]);

        $payment->save();

        return redirect()->route('subscriptions.invoice');
    }

    public function invoice(Request $request)
    {
        $search = $request->input('search');
        $isAdmin = auth()->user()->hasRole('Admin');
        $payments = $this->paymentService->getPaymentsByUserId(auth()->id(), $search, $isAdmin);

        return Inertia::render('subscription/Invoice', [
            'payments' => $payments,
            'filters' => [
                'search' => $search,
            ],
            'isAdmin' => $isAdmin,
        ]);
    }

    public function updateStatusInvoice(Request $request)
    {
        // Validate the request
        $validated = $request->validate([
            'payment_id' => 'required|exists:payments,id',
            'status' => 'required|string|in:completed,failed',
        ]);

        $payment = Payment::findOrFail($validated['payment_id']);

        // Update the payment status
        $result = $this->paymentService->updatePayment($payment->id, [
            'status' => $validated['status']
        ]);

        if ($validated['status'] === StatusPaymentEnum::COMPLETED->value && $result) {
            $this->deviceService->activateDeviceSubscription(
                $payment->device_id,
                $payment->subscription_id,
                30
            );
        }

        return redirect()->route('subscriptions.invoice');
    }
}
