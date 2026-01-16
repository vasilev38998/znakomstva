<?php
declare(strict_types=1);

class PaymentController
{
    private PaymentService $paymentService;

    public function __construct()
    {
        $this->paymentService = new PaymentService();
    }

    public function webhook(): void
    {
        $payload = json_decode(file_get_contents('php://input'), true);
        if (!is_array($payload)) {
            http_response_code(400);
            echo 'Invalid payload';
            return;
        }

        try {
            $this->paymentService->handleWebhook($payload);
        } catch (InvalidArgumentException $exception) {
            http_response_code(422);
            echo $exception->getMessage();
            return;
        }

        echo 'OK';
    }
}
