<?php

namespace App\Services;

use App\Repositories\PaymentRepository;

class PaymentService
{
    protected $PaymentRepository;

    public function __construct(PaymentRepository $PaymentRepository)
    {
        $this->PaymentRepository = $PaymentRepository;
    }

    public function getPaymentsByUserId($id, $search = null, $isAdmin = false)
    {
        return $this->PaymentRepository->getPaymentsByUserId($id, $search, $isAdmin);
    }

    public function getPaymentById($id)
    {
        return $this->PaymentRepository->getPaymentById($id);
    }
    public function createPayment($data)
    {
        return $this->PaymentRepository->createPayment($data);
    }
    public function updatePayment($id, $data)
    {
        return $this->PaymentRepository->updatePayment($id, $data);
    }
    public function deletePayment($id)
    {
        return $this->PaymentRepository->deletePayment($id);
    }
}
