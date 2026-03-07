<?php

namespace Tests\Unit\Services;

use App\Services\ReportService;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class ReportServiceTest extends TestCase
{
    public function test_sanitize_removes_control_characters_and_preserves_utf8_strings(): void
    {
        $service = new ReportService;
        $method = (new ReflectionClass($service))->getMethod('sanitize');
        $method->setAccessible(true);

        $input = "Texto\x00\x1F limpio";
        $sanitized = $method->invoke($service, $input);

        $this->assertSame('Texto limpio', $sanitized);
    }

    public function test_sanitize_processes_nested_arrays(): void
    {
        $service = new ReportService;
        $method = (new ReflectionClass($service))->getMethod('sanitize');
        $method->setAccessible(true);

        $input = [
            'title' => "A\x00",
            'nested' => [
                'value' => "B\x1F",
            ],
        ];

        $sanitized = $method->invoke($service, $input);

        $this->assertSame('A', $sanitized['title']);
        $this->assertSame('B', $sanitized['nested']['value']);
    }
}
