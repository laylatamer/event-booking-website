<?php

require_once __DIR__ . '/../models/ContactMessage.php';
require_once __DIR__ . '/../services/EmailService.php';

class ContactController
{
    private ContactMessage $model;
    private EmailService $emailService;

    public function __construct(ContactMessage $model)
    {
        $this->model = $model;
        $this->emailService = new EmailService();
    }

    public function store(array $request): array
    {
        $payload = [
            'name'    => trim($request['name'] ?? ''),
            'email'   => trim($request['email'] ?? ''),
            'subject' => trim($request['subject'] ?? ''),
            'message' => trim($request['message'] ?? ''),
        ];

        if (in_array('', $payload, true)) {
            return ['ok' => false, 'message' => 'All fields are required.'];
        }

        if (!filter_var($payload['email'], FILTER_VALIDATE_EMAIL)) {
            return ['ok' => false, 'message' => 'Please enter a valid email.'];
        }

        $id = $this->model->create($payload);

        // Send confirmation email to the user
        try {
            $this->emailService->sendContactConfirmationEmail(
                $payload['name'],
                $payload['email'],
                $payload['subject']
            );
        } catch (Exception $e) {
            // Log error but don't fail the request if email fails
            error_log("Failed to send contact confirmation email: " . $e->getMessage());
        }

        return ['ok' => true, 'id' => $id];
    }

    public function index(): array
    {
        return $this->model->all();
    }

    public function show(int $id): ?array
    {
        return $this->model->find($id);
    }

    public function update(int $id, string $status): bool
    {
        return $this->model->updateStatus($id, $status);
    }

    public function destroy(int $id): bool
    {
        return $this->model->delete($id);
    }
}


