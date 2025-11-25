<?php

namespace Database\Seeders;

use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Seeder;

class TaskSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::where('email', 'test@example.com')->first();

        if ($user) {
            Task::create([
                'user_id' => $user->id,
                'title' => 'Completar documentación de la API',
                'description' => 'Escribir la documentación completa de todos los endpoints de SimpleTasks API',
                'status' => 'in_progress',
                'priority' => 'high',
                'due_date' => now()->addDays(7),
            ]);

            Task::create([
                'user_id' => $user->id,
                'title' => 'Revisar código y aplicar mejores prácticas',
                'description' => 'Hacer una revisión completa del código para asegurar que sigue los estándares PSR',
                'status' => 'pending',
                'priority' => 'medium',
                'due_date' => now()->addDays(14),
            ]);

            Task::create([
                'user_id' => $user->id,
                'title' => 'Implementar tests unitarios',
                'description' => 'Crear tests para los controladores de autenticación y tareas',
                'status' => 'pending',
                'priority' => 'high',
                'due_date' => now()->addDays(10),
            ]);
        }
    }
}
