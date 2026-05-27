<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class SwimmerController extends Controller
{
    public function index(): View
    {
        return view('swimmers.index', [
            'swimmers' => $this->swimmers(),
        ]);
    }

    public function create(): View
    {
        return view('swimmers.create');
    }

    public function store(): RedirectResponse
    {
        return redirect()
            ->route('admin.swimmers.index')
            ->with('status_success', 'Swimmer created successfully (visual demo mode).');
    }

    public function show(int $swimmer): View
    {
        return view('swimmers.show', [
            'swimmer' => $this->findSwimmer($swimmer),
        ]);
    }

    public function edit(int $swimmer): View
    {
        return view('swimmers.edit', [
            'swimmer' => $this->findSwimmer($swimmer),
        ]);
    }

    public function update(int $swimmer): RedirectResponse
    {
        return redirect()
            ->route('admin.swimmers.edit', $swimmer)
            ->with('status_success', 'Swimmer updated successfully (visual demo mode).');
    }

    public function destroy(int $swimmer): RedirectResponse
    {
        return redirect()
            ->route('admin.swimmers.index')
            ->with('status_success', "Swimmer #{$swimmer} deleted successfully (visual demo mode).");
    }

    /**
     * @return array<int, array<string, int|string>>
     */
    private function swimmers(): array
    {
        return [
            [
                'id' => 1,
                'first_name' => 'Lucas',
                'last_name' => 'Garcia Ruiz',
                'age' => 16,
                'category' => 'Junior',
                'gender' => 'Male',
            ],
            [
                'id' => 2,
                'first_name' => 'Sofia',
                'last_name' => 'Martinez Lopez',
                'age' => 14,
                'category' => 'Cadet',
                'gender' => 'Female',
            ],
            [
                'id' => 3,
                'first_name' => 'Daniel',
                'last_name' => 'Navarro Perez',
                'age' => 19,
                'category' => 'Senior',
                'gender' => 'Male',
            ],
        ];
    }

    /**
     * @return array<string, int|string>
     */
    private function findSwimmer(int $id): array
    {
        foreach ($this->swimmers() as $swimmer) {
            if ($swimmer['id'] === $id) {
                return $swimmer;
            }
        }

        return $this->swimmers()[0];
    }
}
