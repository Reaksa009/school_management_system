<?php

namespace Tests\Unit;

use App\Services\KHQRTuitionService;
use PHPUnit\Framework\TestCase;

class KHQRTuitionServiceTest extends TestCase
{
    public function test_it_accepts_khqr_link_completed_response(): void
    {
        $service = new KHQRTuitionService;

        $this->assertTrue($service->isPaidResponse([
            'responseCode' => '0',
            'status' => 'COMPLETED',
            'verified' => true,
        ]));
    }

    public function test_it_accepts_string_zero_response_code(): void
    {
        $service = new KHQRTuitionService;

        $this->assertTrue($service->isPaidResponse([
            'responseCode' => '0',
        ]));
    }

    public function test_it_rejects_pending_khqr_link_response(): void
    {
        $service = new KHQRTuitionService;

        $this->assertFalse($service->isPaidResponse([
            'responseCode' => 1,
            'status' => 'PENDING',
            'verified' => false,
        ]));
    }
}
