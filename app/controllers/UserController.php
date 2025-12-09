<?php

require_once __DIR__ . '/../models/User.php';

class UserController
{
    private User $model;

    public function __construct(User $model)
    {
        $this->model = $model;
    }

    public function index(): array
    {
        return $this->model->all();
    }

    public function show(int $id): ?array
    {
        return $this->model->find($id);
    }

    public function store(array $payload): array
    {
        $first = trim($payload['first_name'] ?? '');
        $last = trim($payload['last_name'] ?? '');
        $email = trim($payload['email'] ?? '');
        $password = (string) ($payload['password'] ?? '');
        $phone = trim($payload['phone_number'] ?? '');
        $address = trim($payload['address'] ?? '');
        $city = trim($payload['city'] ?? '');
        $profileImage = trim($payload['profile_image_path'] ?? '');

        if ($first === '' || $last === '' || $email === '' || $password === '') {
            return ['ok' => false, 'message' => 'First name, last name, email, and password are required.'];
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ['ok' => false, 'message' => 'Invalid email format.'];
        }

        if (strlen($password) < 8) {
            return ['ok' => false, 'message' => 'Password must be at least 8 characters.'];
        }

        $passwordHash = password_hash($password, PASSWORD_DEFAULT);

        try {
            $id = $this->model->create([
                'first_name'         => $first,
                'last_name'          => $last,
                'email'              => $email,
                'password_hash'      => $passwordHash,
                'phone_number'       => $phone ?: null,
                'address'            => $address ?: null,
                'city'               => $city ?: null,
                'profile_image_path' => $profileImage ?: null,
            ]);

            return ['ok' => true, 'id' => $id];
        } catch (\PDOException $e) {
            if ((int) $e->getCode() === 23000) { // duplicate
                return ['ok' => false, 'message' => 'Email already exists.'];
            }
            return ['ok' => false, 'message' => 'Database error.'];
        }
    }

    public function create(array $payload): int
    {
        return $this->model->create($payload);
    }

    public function destroy(int $id): bool
    {
        return $this->model->delete($id);
    }
}

