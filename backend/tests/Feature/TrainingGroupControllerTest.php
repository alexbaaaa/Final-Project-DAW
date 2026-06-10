<?php

namespace Tests\Feature;

use App\Models\TrainingGroup;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class TrainingGroupControllerTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware();

        config(['app.url' => 'http://localhost']);
        URL::forceRootUrl('http://localhost');
    }

    public function test_stores_multiple_schedule_blocks_in_one_training_group(): void
    {
        $initialTrainingGroupCount = TrainingGroup::query()->count();

        $response = $this->post('/training-groups', [
            'name' => 'Absoluto',
            'categories' => ['Absoluto'],
            'schedule_entries' => json_encode([
                [
                    'days' => ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'],
                    'from' => '20:15',
                    'to' => '22:00',
                ],
                [
                    'days' => ['saturday'],
                    'from' => '08:30',
                    'to' => '10:30',
                ],
            ]),
        ]);

        $response->assertRedirect(route('admin.training_groups.index'));
        $this->assertSame($initialTrainingGroupCount + 1, TrainingGroup::query()->count());

        $trainingGroup = TrainingGroup::query()
            ->where('name', 'Absoluto')
            ->latest('id')
            ->firstOrFail();

        $this->assertSame('Absoluto', $trainingGroup->name);
        $this->assertSame('Absoluto', $trainingGroup->categories);
        $this->assertCount(2, $trainingGroup->schedule);
        $this->assertSame(
            'M-F (Lunes a viernes): 20:15 a 22:00',
            $trainingGroup->formattedSchedule()[0]
        );
        $this->assertSame(
            'S (Sabado): 8:30 a 10:30',
            $trainingGroup->formattedSchedule()[1]
        );
    }

    public function test_updates_multiple_schedule_blocks_without_creating_another_training_group(): void
    {
        $trainingGroup = TrainingGroup::query()->create([
            'name' => 'Junior',
            'categories' => 'Junior',
            'schedule' => [
                [
                    'days' => ['monday'],
                    'from' => '19:00',
                    'to' => '20:00',
                ],
            ],
        ]);
        $trainingGroupCount = TrainingGroup::query()->count();

        $response = $this->put("/training-groups/{$trainingGroup->id}", [
            'name' => 'Junior',
            'categories' => ['Junior'],
            'schedule_entries' => json_encode([
                [
                    'days' => ['monday', 'wednesday', 'friday'],
                    'from' => '19:00',
                    'to' => '20:30',
                ],
                [
                    'days' => ['saturday'],
                    'from' => '09:00',
                    'to' => '10:00',
                ],
            ]),
        ]);

        $response->assertRedirect(route('admin.training_groups.edit', $trainingGroup->id));
        $this->assertSame($trainingGroupCount, TrainingGroup::query()->count());

        $trainingGroup->refresh();

        $this->assertCount(2, $trainingGroup->schedule);
        $this->assertSame(
            'M,X,F (Lunes, Miercoles, Viernes): 19:00 a 20:30',
            $trainingGroup->formattedSchedule()[0]
        );
        $this->assertSame(
            'S (Sabado): 9:00 a 10:00',
            $trainingGroup->formattedSchedule()[1]
        );
    }
}
