<?php

namespace Tests\Unit;

use App\Models\TrainingGroup;
use PHPUnit\Framework\TestCase;

class TrainingGroupTest extends TestCase
{
    public function test_formats_training_group_schedule_entries(): void
    {
        $this->assertSame(
            'M-F (Lunes a viernes): 20:15 a 22:00',
            TrainingGroup::formatScheduleEntry([
                'days' => ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'],
                'from' => '20:15',
                'to' => '22:00',
            ])
        );

        $this->assertSame(
            'M,X,F (Lunes, Miercoles, Viernes): 20:00 a 22:00',
            TrainingGroup::formatScheduleEntry([
                'days' => ['monday', 'wednesday', 'friday'],
                'from' => '20:00',
                'to' => '22:00',
            ])
        );

        $this->assertSame(
            'S (Sabado): 8:30 a 10:30',
            TrainingGroup::formatScheduleEntry([
                'days' => ['saturday'],
                'from' => '08:30',
                'to' => '10:30',
            ])
        );
    }
}
