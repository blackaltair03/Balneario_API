<?php

namespace Database\Seeders;

use App\Models\Balneario;
use App\Models\Evento;
use App\Models\Ingreso;
use App\Models\Rol;
use App\Models\Servicio;
use App\Models\User;
use App\Models\Brazalete;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        // 1. Seed de roles básicos
        $this->crearRolesBasicos();

        // 2. Crear superadmin principal
        $superAdmin = $this->crearSuperAdmin();

        // 3. Crear balnearios con sus dependencias
        $this->crearBalneariosCompletos();

        // 4. Crear eventos de ejemplo
        $this->crearEventos();

        // 5. Crear brazaletes de prueba
        $this->crearBrazaletes();

        // 6. Crear ingresos/egresos de ejemplo
        $this->crearMovimientosFinancieros();
    }

    protected function crearRolesBasicos()
    {
        $roles = [
            [
                'nombre' => 'superadmin', 
                'permisos' => json_encode(['*']),
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'nombre' => 'admin', 
                'permisos' => json_encode([
                    'dashboard', 'balnearios', 'estadisticas', 'reportes',
                    'ingresos', 'egresos', 'servicios'
                ]),
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'nombre' => 'checador', 
                'permisos' => json_encode([
                    'verificar', 'buscar', 'estadisticas', 'perfil'
                ]),
                'created_at' => now(),
                'updated_at' => now()
            ],
        ];

        Rol::insert($roles);
    }

    protected function crearSuperAdmin()
    {
        return User::create([
            'name' => 'superadmin',
            'email' => 'superadmin@balnearios.com',
            'password' => Hash::make('Password123!'),
            'nombre_completo' => 'Super Administrador',
            'rol_id' => 1,
            'email_verified_at' => now(),
            'remember_token' => Str::random(10),
        ]);
    }

    protected function crearBalneariosCompletos()
    {
        $balnearios = [
            [
                'nombre' => 'Balneario Oasis',
                'capacidad' => 500,
                'aforo_actual' => 120,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'nombre' => 'Balneario Paraíso',
                'capacidad' => 300,
                'aforo_actual' => 85,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'nombre' => 'Balneario VIP',
                'capacidad' => 150,
                'aforo_actual' => 45,
                'created_at' => now(),
                'updated_at' => now()
            ],
        ];

        foreach ($balnearios as $balnearioData) {
            $balneario = Balneario::create($balnearioData);

            // Crear admin para el balneario
            $this->crearAdminParaBalneario($balneario);

            // Crear checadores para el balneario
            $this->crearChecadoresParaBalneario($balneario);

            // Crear servicios para el balneario
            $this->crearServiciosParaBalneario($balneario);
        }
    }

    protected function crearAdminParaBalneario($balneario)
    {
        $username = strtolower(str_replace(' ', '_', $balneario->nombre));
        
        return User::create([
            'name' => 'admin_' . $username,
            'email' => 'admin@' . $username . '.com',
            'password' => Hash::make('Admin' . $balneario->id . '!'),
            'nombre_completo' => 'Administrador ' . $balneario->nombre,
            'rol_id' => 2, // Rol admin
            'balneario_id' => $balneario->id,
            'email_verified_at' => now(),
            'remember_token' => Str::random(10),
        ]);
    }

    protected function crearChecadoresParaBalneario($balneario)
    {
        $username = strtolower(str_replace(' ', '_', $balneario->nombre));
        
        for ($i = 1; $i <= 3; $i++) {
            User::create([
                'name' => 'checador' . $i . '_' . $username,
                'email' => 'checador' . $i . '@' . $username . '.com',
                'password' => Hash::make('Checador' . $i . $balneario->id . '!'),
                'nombre_completo' => 'Checador ' . $i . ' ' . $balneario->nombre,
                'rol_id' => 3, // Rol checador
                'balneario_id' => $balneario->id,
                'email_verified_at' => now(),
                'remember_token' => Str::random(10),
            ]);
        }
    }

    protected function crearServiciosParaBalneario($balneario)
    {
        $servicios = [
            [
                'nombre' => 'Acceso General',
                'descripcion' => 'Incluye acceso a todas las áreas comunes y albercas principales',
                'costo' => 150.00,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'nombre' => 'Área VIP',
                'descripcion' => 'Acceso exclusivo a zona VIP con restaurante y barra libre',
                'costo' => 350.00,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'nombre' => 'Cabaña Familiar',
                'descripcion' => 'Renta de cabaña para 6 personas con área privada',
                'costo' => 800.00,
                'created_at' => now(),
                'updated_at' => now()
            ],
        ];

        foreach ($servicios as $servicio) {
            $servicio['balneario_id'] = $balneario->id;
            Servicio::create($servicio);
        }
    }

    protected function crearEventos()
    {
        $eventos = [
            [
                'nombre' => 'Festival del Agua 2023',
                'fecha_inicio' => now()->addDays(5),
                'fecha_fin' => now()->addDays(10),
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'nombre' => 'Verano Extremo',
                'fecha_inicio' => now()->addDays(15),
                'fecha_fin' => now()->addDays(20),
                'created_at' => now(),
                'updated_at' => now()
            ],
        ];

        Evento::insert($eventos);
    }

    protected function crearBrazaletes()
    {
        $balnearios = Balneario::all();
        $eventos = Evento::all();
        $checadores = User::where('rol_id', 3)->get();

        foreach ($balnearios as $balneario) {
            foreach ($eventos as $evento) {
                // Crear 50 brazaletes por balneario/evento
                for ($i = 1; $i <= 50; $i++) {
                    $status = $i <= 30 ? 'activo' : ($i <= 40 ? 'pendiente' : 'rechazado');
                    $checador = $status === 'activo' ? $checadores->random()->id : null;
                    $fechaVerificacion = $status === 'activo' ? now()->subDays(rand(1, 3)) : null;

                    Brazalete::create([
                        'codigo_qr' => Str::uuid(),
                        'status' => $status,
                        'evento_id' => $evento->id,
                        'balneario_id' => $balneario->id,
                        'checador_id' => $checador,
                        'fecha_verificacion' => $fechaVerificacion,
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
                }
            }
        }
    }

    protected function crearMovimientosFinancieros()
    {
        $balnearios = Balneario::all();
        $conceptosIngresos = ['Entradas', 'Venta alimentos', 'Venta bebidas', 'Renta lockers'];
        $conceptosEgresos = ['Mantenimiento', 'Personal', 'Limpieza', 'Servicios'];

        foreach ($balnearios as $balneario) {
            // Ingresos
            for ($i = 1; $i <= 10; $i++) {
                Ingreso::create([
                    'balneario_id' => $balneario->id,
                    'concepto' => $conceptosIngresos[array_rand($conceptosIngresos)],
                    'monto' => rand(5000, 20000),
                    'tipo' => 'ingreso',
                    'fecha' => now()->subDays(rand(1, 30)),
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }

            // Egresos
            for ($i = 1; $i <= 8; $i++) {
                Ingreso::create([
                    'balneario_id' => $balneario->id,
                    'concepto' => $conceptosEgresos[array_rand($conceptosEgresos)],
                    'monto' => rand(2000, 15000),
                    'tipo' => 'egreso',
                    'fecha' => now()->subDays(rand(1, 30)),
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }
        }
    }
}