<?php

namespace App\Repositories;

use App\Models\Payment;

class PaymentRepository
{
    public function getPaymentsByUserId($id, $search = null, $isAdmin = false)
    {
        $query = Payment::with(['device', 'subscription']);

        if (!$isAdmin) {
            $query->where('user_id', $id);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->whereHas('device', function ($subQuery) use ($search) {
                    $subQuery->where('name', 'like', "%{$search}%");
                })
                    ->orWhereHas('subscription', function ($subQuery) use ($search) {
                        $subQuery->where('name', 'like', "%{$search}%");
                    })
                    ->orWhere('status', 'like', "%{$search}%")
                    ->orWhere('price', 'like', "%{$search}%");
            });
        }

        return $query->latest()->paginate(10);
    }

    public function getPaymentById($id)
    {
        return Payment::find($id);
    }

    public function createPayment($data)
    {
        return Payment::create($data);
    }

    public function updatePayment($id, $data)
    {
        $payment = $this->getPaymentById($id);
        if ($payment) {
            $payment->update($data);
            return $payment;
        }
        return null;
    }

    public function deletePayment($id)
    {
        $payment = $this->getPaymentById($id);
        if ($payment) {
            $payment->delete();
            return true;
        }
        return false;
    }
}
