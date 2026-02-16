<?php

namespace App\Services\External;

use App\Services\External\Base\BaseApiClient;
use App\DTOs\External\AttendanceFilterDTO;
use Illuminate\Support\Collection;

class HrApiClient extends BaseApiClient
{
    /**
     * Set custom base URL (for multi-tenant support)
     */
    public function setBaseUrl(string $url): self
    {
        $this->baseUrl = rtrim($url, '/');
        return $this;
    }

    /**
     * Login and get access token
     */
    public function login(string $email, string $password): string
    {
        $response = $this->post('/login', [
            'email' => $email,
            'password' => $password,
        ]);

        $body = $response->json();

        if (!isset($body['token']) || empty($body['token'])) {
            throw new \Exception(
                $body['message'] ?? 'Token not found in login response'
            );
        }

        return $body['token'];
    }

    /**
     * Test connection (get user info)
     */
    public function testConnection()
    {
        $response = $this->get('/user/profile');

        $user = $response->json('user');

        if (!$user) {
            throw new \Exception('User data not found in response.');
        }

        return $user;
    }


    /**
     * Get attendance data
     */
    public function getAttendance(AttendanceFilterDTO $dto)
    {
        $response = $this->post('check-attendance', [
            'start_date' => $dto->startDate,
            'end_date' => $dto->endDate,
        ]);

        $data = $this->handleResponse($response);

        return collect($data)->map(fn($item) => [
            'employee_id' => $item['employee_id'] ?? null,
            'employee_name' => $item['employee_name'] ?? null,
            'date' => $item['date'] ?? null,
            'status' => $item['status'] ?? null,
            'check_in' => $item['check_in'] ?? null,
            'check_out' => $item['check_out'] ?? null,
            'hours' => $item['hours'] ?? 0,
        ]);
    }

    /**
     * Get attendance data
     */
    // public function getAttendance(array $params): Collection
    // {
    //     $response = $this->post('attendance/fetch', $params);

    //     $data = $this->handleResponse($response);

    //     return collect($data)->map(fn($item) => [
    //         'id' => $item['id'] ?? null,
    //         'employee_id' => $item['employee_id'] ?? null,
    //         'employee_name' => $item['employee_name'] ?? null,
    //         'date' => $item['date'] ?? null,
    //         'status' => $item['status'] ?? null,
    //         'check_in' => $item['check_in'] ?? null,
    //         'check_out' => $item['check_out'] ?? null,
    //         'hours' => $item['hours'] ?? 0,
    //         'overtime' => $item['overtime'] ?? 0,
    //     ]);
    // }

    /**
     * Get employee list
     */
    public function getEmployees(): Collection
    {
        $response = $this->get('employees');

        return collect($this->handleResponse($response));
    }

    /**
     * Sync single attendance record
     */
    public function syncAttendance(array $data): array
    {
        $response = $this->post('attendance/sync', $data);

        return $this->handleResponse($response);
    }
}
