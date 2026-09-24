<?php

namespace Database\Seeders;

use App\Models\Announcement;
use App\Models\Assignment;
use App\Models\Attendance;
use App\Models\Book;
use App\Models\Loan;
use App\Models\Message;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Event;
use App\Models\Grade;
use App\Models\Incident;
use App\Models\Payment;
use App\Models\Role;
use App\Models\Schedule;
use App\Models\School;
use App\Models\Setting;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\User;
use App\Services\Tenancy;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ---- Roles globales (incluye super-administrador de la plataforma) ----
        $roles = [
            ['name' => 'Super Administrador', 'slug' => 'superadmin', 'description' => 'Administra la plataforma y todos los colegios'],
            ['name' => 'Administrador', 'slug' => 'admin', 'description' => 'Acceso total al sistema'],
            ['name' => 'Docente', 'slug' => 'docente', 'description' => 'Gestión de notas, asistencia y horarios'],
            ['name' => 'Secretaría', 'slug' => 'secretaria', 'description' => 'Matrículas, pagos y estudiantes'],
            ['name' => 'Estudiante / Padre', 'slug' => 'estudiante', 'description' => 'Consulta de notas y comunicados'],
        ];
        foreach ($roles as $r) {
            Role::firstOrCreate(['slug' => $r['slug']], $r);
        }

        $adminRole = Role::where('slug', 'admin')->first();
        $teacherRole = Role::where('slug', 'docente')->first();
        $secRole = Role::where('slug', 'secretaria')->first();

        // ---- Super-administrador de la plataforma (sin colegio) ----
        User::firstOrCreate(['email' => 'super@saas.test'], [
            'name' => 'Super Administrador',
            'password' => Hash::make('password'),
            'role_id' => Role::where('slug', 'superadmin')->first()->id,
            'is_active' => true,
        ]);

        // ---- Colegio demo (tenant principal) ----
        $school = School::firstOrCreate(['slug' => 'colegio-san-martin'], [
            'name' => 'Colegio San Martín',
            'plan' => 'pro',
            'status' => 'activo',
            'trial_ends_at' => now()->addDays(30),
            'email' => 'admin@colegio.test',
            'address' => 'Av. Educación #123',
            'phone' => '(2) 222-3344',
        ]);

        // Activar el colegio: a partir de aquí todo se asigna a este tenant
        app(Tenancy::class)->set($school->id);

        // ---- Configuración del colegio ----
        Setting::firstOrCreate([], [
            'school_name' => 'Colegio San Martín',
            'academic_year' => date('Y'),
            'active_period' => '1er Trimestre',
            'currency' => 'Bs',
            'address' => 'Av. Educación #123',
            'phone' => '(2) 222-3344',
            'director' => 'Lic. Roberto Salazar',
            'tuition_amount' => 250,
        ]);

        // ---- Usuarios del colegio ----
        User::firstOrCreate(['email' => 'admin@colegio.test'], [
            'name' => 'Director General',
            'password' => Hash::make('password'),
            'role_id' => $adminRole->id,
            'phone' => '70000000',
            'is_active' => true,
        ]);
        $docenteUser = User::firstOrCreate(['email' => 'docente@colegio.test'], [
            'name' => 'Prof. Ana Quispe',
            'password' => Hash::make('password'),
            'role_id' => $teacherRole->id,
            'is_active' => true,
        ]);
        User::firstOrCreate(['email' => 'secretaria@colegio.test'], [
            'name' => 'Secretaría Académica',
            'password' => Hash::make('password'),
            'role_id' => $secRole->id,
            'is_active' => true,
        ]);

        // ---- Docentes ----
        $teachersData = [
            ['Ana', 'Quispe Mamani', 'Matemática', 'F'],
            ['Carlos', 'Rojas Vargas', 'Lenguaje y Literatura', 'Masculino'],
            ['María', 'Flores Condori', 'Ciencias Naturales', 'F'],
            ['Jorge', 'Mendoza Cruz', 'Ciencias Sociales', 'Masculino'],
            ['Lucía', 'Apaza Torrez', 'Inglés', 'F'],
            ['Pedro', 'Gutiérrez Lima', 'Educación Física', 'Masculino'],
        ];
        $teachers = collect($teachersData)->map(function ($t, $i) use ($docenteUser) {
            return Teacher::create([
                // El primer docente se vincula al usuario docente@colegio.test
                'user_id' => $i === 0 ? $docenteUser->id : null,
                'code' => 'DOC-'.str_pad((string) ($i + 1), 4, '0', STR_PAD_LEFT),
                'first_name' => $t[0],
                'last_name' => $t[1],
                'dni' => (string) rand(3000000, 9999999),
                'email' => strtolower(str_replace(' ', '.', $t[0])).'@colegio.test',
                'phone' => '7'.rand(1000000, 9999999),
                'specialty' => $t[2],
                'gender' => $t[3],
                'hire_date' => now()->subYears(rand(1, 8)),
                'status' => 'activo',
            ]);
        });

        // ---- Cursos ----
        $coursesData = [
            ['1ro de Secundaria', 'Secundaria', '1ro', 'A'],
            ['2do de Secundaria', 'Secundaria', '2do', 'A'],
            ['3ro de Secundaria', 'Secundaria', '3ro', 'B'],
            ['5to de Primaria', 'Primaria', '5to', 'A'],
            ['6to de Primaria', 'Primaria', '6to', 'A'],
        ];
        $courses = collect($coursesData)->map(function ($c, $i) use ($teachers) {
            return Course::create([
                'name' => $c[0],
                'level' => $c[1],
                'grade' => $c[2],
                'section' => $c[3],
                'shift' => 'Mañana',
                'capacity' => 35,
                'tutor_id' => $teachers[$i % $teachers->count()]->id,
                'academic_year' => date('Y'),
                'status' => 'activo',
            ]);
        });

        // ---- Materias ----
        $subjectsData = [
            ['Matemática', 'MAT', 'Ciencias exactas'],
            ['Lenguaje y Literatura', 'LEN', 'Comunicación'],
            ['Ciencias Naturales', 'CNA', 'Ciencias'],
            ['Ciencias Sociales', 'CSO', 'Sociales'],
            ['Inglés', 'ING', 'Lenguas'],
            ['Educación Física', 'EDF', 'Deportes'],
            ['Religión, Ética y Valores', 'REL', 'Valores'],
            ['Computación', 'COM', 'Tecnología'],
        ];
        $subjects = collect($subjectsData)->map(fn ($s) => Subject::create([
            'name' => $s[0], 'code' => $s[1], 'area' => $s[2], 'status' => 'activo',
        ]));

        // ---- Carga académica (course_subject) ----
        foreach ($courses as $course) {
            foreach ($subjects->take(6) as $k => $subject) {
                $course->subjects()->attach($subject->id, [
                    'teacher_id' => $teachers[$k % $teachers->count()]->id,
                    'hours_per_week' => rand(2, 5),
                ]);
            }
        }

        // ---- Estudiantes ----
        $nombres = ['Mateo', 'Valentina', 'Santiago', 'Camila', 'Sebastián', 'Isabella', 'Diego', 'Luciana', 'Adriano', 'Gabriela', 'Nicolás', 'Daniela', 'Joaquín', 'Antonella', 'Benjamín', 'Renata', 'Emiliano', 'Mía', 'Thiago', 'Sofía'];
        $apellidos = ['Mamani', 'Quispe', 'Choque', 'Condori', 'Flores', 'Vargas', 'Rojas', 'Cruz', 'Apaza', 'Torrez', 'Gutiérrez', 'Colque', 'Huanca', 'Aruquipa'];

        $counter = 1;
        foreach ($courses as $course) {
            for ($i = 0; $i < 12; $i++) {
                $gender = ['M', 'F'][rand(0, 1)];
                $student = Student::create([
                    'code' => 'EST-'.str_pad((string) $counter, 5, '0', STR_PAD_LEFT),
                    'first_name' => $nombres[array_rand($nombres)],
                    'last_name' => $apellidos[array_rand($apellidos)].' '.$apellidos[array_rand($apellidos)],
                    'dni' => (string) rand(8000000, 14000000),
                    'birth_date' => now()->subYears(rand(11, 17))->subDays(rand(0, 360)),
                    'gender' => $gender,
                    'phone' => '6'.rand(1000000, 9999999),
                    'guardian_name' => $nombres[array_rand($nombres)].' '.$apellidos[array_rand($apellidos)],
                    'guardian_phone' => '7'.rand(1000000, 9999999),
                    'course_id' => $course->id,
                    'enrollment_date' => now()->startOfYear()->addDays(rand(0, 30)),
                    'status' => 'activo',
                ]);

                Enrollment::create([
                    'student_id' => $student->id,
                    'course_id' => $course->id,
                    'academic_year' => date('Y'),
                    'enrollment_date' => $student->enrollment_date,
                    'status' => 'inscrito',
                ]);

                // Pagos (matrícula + 3 pensiones)
                Payment::create([
                    'student_id' => $student->id,
                    'invoice_number' => 'FAC-'.now()->format('Y').'-'.str_pad((string) ($counter * 10), 5, '0', STR_PAD_LEFT),
                    'concept' => 'Matrícula '.date('Y'),
                    'amount' => 350,
                    'period' => 'Anual',
                    'due_date' => now()->startOfYear()->addDays(15),
                    'paid_date' => now()->startOfYear()->addDays(rand(1, 14)),
                    'method' => 'efectivo',
                    'status' => 'pagado',
                ]);
                foreach (['Marzo', 'Abril', 'Mayo'] as $m => $mes) {
                    $paid = rand(0, 10) > 3;
                    Payment::create([
                        'student_id' => $student->id,
                        'invoice_number' => 'FAC-'.now()->format('Y').'-'.str_pad((string) ($counter * 10 + $m + 1), 5, '0', STR_PAD_LEFT),
                        'concept' => 'Pensión '.$mes,
                        'amount' => 250,
                        'period' => $mes.' '.date('Y'),
                        'due_date' => now()->subMonths(2 - $m),
                        'paid_date' => $paid ? now()->subMonths(2 - $m)->addDays(rand(1, 10)) : null,
                        'method' => $paid ? 'transferencia' : null,
                        'status' => $paid ? 'pagado' : (rand(0, 1) ? 'pendiente' : 'vencido'),
                    ]);
                }

                // Notas (2 materias x 1 periodo)
                foreach ($subjects->take(4) as $subject) {
                    Grade::create([
                        'student_id' => $student->id,
                        'subject_id' => $subject->id,
                        'course_id' => $course->id,
                        'period' => '1er Trimestre',
                        'type' => 'examen',
                        'score' => rand(45, 98),
                    ]);
                }

                // Asistencia últimos 5 días hábiles
                foreach (range(1, 5) as $d) {
                    Attendance::create([
                        'student_id' => $student->id,
                        'course_id' => $course->id,
                        'date' => now()->subDays($d)->toDateString(),
                        'status' => ['presente', 'presente', 'presente', 'tardanza', 'ausente'][rand(0, 4)],
                    ]);
                }

                $counter++;
            }
        }

        // ---- Usuario estudiante de prueba (vinculado al primer estudiante) ----
        $studentRole = Role::where('slug', 'estudiante')->first();
        $firstStudent = Student::orderBy('id')->first();
        if ($firstStudent) {
            $estUser = User::firstOrCreate(['email' => 'estudiante@colegio.test'], [
                'name' => $firstStudent->full_name,
                'password' => Hash::make('password'),
                'role_id' => $studentRole->id,
                'is_active' => true,
            ]);
            $firstStudent->update(['user_id' => $estUser->id, 'email' => 'estudiante@colegio.test']);
        }

        // ---- Horarios para el primer curso ----
        $firstCourse = $courses->first();
        $days = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes'];
        foreach ($days as $di => $day) {
            foreach (range(0, 2) as $block) {
                $subject = $subjects[($di + $block) % $subjects->count()];
                $start = 8 + $block * 2;
                Schedule::create([
                    'course_id' => $firstCourse->id,
                    'subject_id' => $subject->id,
                    'teacher_id' => $teachers[($di + $block) % $teachers->count()]->id,
                    'day_of_week' => $day,
                    'start_time' => sprintf('%02d:00', $start),
                    'end_time' => sprintf('%02d:30', $start + 1),
                    'classroom' => 'Aula '.(10 + $di),
                ]);
            }
        }

        // ---- Tareas / asignaciones ----
        $admin = User::where('email', 'admin@colegio.test')->first();
        $taskTitles = [
            ['Resolver guía de ejercicios', 'Matemática'],
            ['Ensayo sobre lectura asignada', 'Lenguaje y Literatura'],
            ['Informe de laboratorio', 'Ciencias Naturales'],
            ['Línea de tiempo histórica', 'Ciencias Sociales'],
            ['Vocabulary worksheet', 'Inglés'],
        ];
        foreach ($courses as $course) {
            foreach (array_slice($taskTitles, 0, 3) as $idx => $t) {
                $subject = $subjects->firstWhere('name', $t[1]) ?? $subjects->first();
                Assignment::create([
                    'title' => $t[0],
                    'description' => 'Trabajo asignado para reforzar los contenidos de la unidad. Entregar en hoja o de forma digital.',
                    'course_id' => $course->id,
                    'subject_id' => $subject->id,
                    'teacher_id' => optional($course->tutor)->id,
                    'assigned_date' => now()->subDays(3),
                    'due_date' => now()->addDays(($idx + 1) * 3),
                    'status' => 'activa',
                    'created_by' => $admin->id ?? null,
                ]);
            }
        }

        // ---- Biblioteca ----
        $booksData = [
            ['Cien años de soledad', 'Gabriel García Márquez', 'Literatura', 4],
            ['El Quijote de la Mancha', 'Miguel de Cervantes', 'Literatura', 3],
            ['Álgebra de Baldor', 'Aurelio Baldor', 'Matemática', 6],
            ['Física General', 'Héctor Pérez Montiel', 'Ciencias', 5],
            ['Historia de Bolivia', 'Carlos Mesa', 'Sociales', 4],
            ['Biología y Geología', 'Varios autores', 'Ciencias', 5],
            ['Diccionario de la Lengua Española', 'RAE', 'Referencia', 8],
            ['El Principito', 'Antoine de Saint-Exupéry', 'Literatura', 6],
        ];
        $books = collect($booksData)->map(fn ($b, $i) => Book::create([
            'title' => $b[0], 'author' => $b[1], 'category' => $b[2],
            'editorial' => 'Editorial Educativa', 'isbn' => '978-'.rand(1000000000, 9999999999),
            'location' => 'Estante '.chr(65 + ($i % 5)).'-'.($i + 1),
            'quantity' => $b[3], 'available' => $b[3],
        ]));

        // Algunos préstamos de ejemplo
        $someStudents = Student::inRandomOrder()->limit(5)->get();
        foreach ($someStudents as $k => $st) {
            $book = $books[$k % $books->count()];
            if ($book->available > 0) {
                $overdue = $k === 0;
                Loan::create([
                    'book_id' => $book->id,
                    'student_id' => $st->id,
                    'loan_date' => now()->subDays($overdue ? 20 : 3),
                    'due_date' => now()->{$overdue ? 'subDays' : 'addDays'}($overdue ? 6 : 4),
                    'status' => $overdue ? 'vencido' : 'prestado',
                ]);
                $book->decrement('available');
            }
        }

        // ---- Disciplina / incidencias ----
        $incidentSamples = [
            ['merito', 'Participación', 'Excelente participación en clase y apoyo a sus compañeros.', 5],
            ['demerito', 'Puntualidad', 'Llegó tarde reiteradamente a la primera hora.', -5],
            ['observacion', 'Conducta', 'Se distrae con facilidad durante las clases.', 0],
            ['merito', 'Responsabilidad', 'Entregó todos sus trabajos en fecha y con buena calidad.', 5],
            ['demerito', 'Respeto', 'Interrumpió la clase en varias ocasiones.', -3],
        ];
        foreach (Student::inRandomOrder()->limit(10)->get() as $k => $st) {
            $s = $incidentSamples[$k % count($incidentSamples)];
            Incident::create([
                'student_id' => $st->id,
                'date' => now()->subDays(rand(1, 25)),
                'type' => $s[0],
                'category' => $s[1],
                'description' => $s[2],
                'points' => $s[3],
                'reported_by' => $admin->id ?? null,
            ]);
        }

        // ---- Eventos del calendario ----
        $eventsData = [
            ['Inicio de clases', 'actividad', now()->startOfYear()->addDays(45)],
            ['Feriado: Día del Trabajo', 'feriado', now()->setMonth(5)->setDay(1)],
            ['Reunión de padres de familia', 'reunion', now()->addDays(7)],
            ['Exámenes 1er Trimestre', 'examen', now()->addDays(20), now()->addDays(24)],
            ['Acto cívico', 'civico', now()->addDays(3)],
            ['Día del Estudiante', 'actividad', now()->setMonth(9)->setDay(21)],
            ['Feria de ciencias', 'actividad', now()->addDays(35)],
        ];
        foreach ($eventsData as $e) {
            Event::create([
                'title' => $e[0],
                'type' => $e[1],
                'date' => $e[2]->toDateString(),
                'end_date' => isset($e[3]) ? $e[3]->toDateString() : null,
                'created_by' => $admin->id ?? null,
                'description' => 'Evento de demostración.',
            ]);
        }

        // ---- Mensajería interna (demo) ----
        $secUser = User::where('email', 'secretaria@colegio.test')->first();
        if ($admin && $docenteUser) {
            Message::create([
                'sender_id' => $docenteUser->id, 'recipient_id' => $admin->id,
                'subject' => 'Solicitud de material didáctico',
                'body' => "Estimada Dirección:\n\nSolicito la compra de material de laboratorio para las prácticas del trimestre. Quedo atenta.\n\nSaludos cordiales.",
                'created_at' => now()->subDays(2),
            ]);
            Message::create([
                'sender_id' => $admin->id, 'recipient_id' => $docenteUser->id,
                'subject' => 'RE: Solicitud de material didáctico',
                'body' => "Buenas tardes:\n\nRecibido. Coordinaremos la compra con administración esta semana.\n\nGracias.",
                'read_at' => now()->subDay(),
                'created_at' => now()->subDays(1),
            ]);
        }
        if ($secUser && $admin) {
            Message::create([
                'sender_id' => $secUser->id, 'recipient_id' => $admin->id,
                'subject' => 'Reporte de cobranzas del mes',
                'body' => 'Adjunto resumen: la cobranza del mes alcanzó el 78% de lo proyectado.',
                'created_at' => now()->subHours(5),
            ]);
        }

        // ---- Comunicados ----
        $announcements = [
            ['Inicio de gestión escolar '.date('Y'), 'Damos la bienvenida a toda la comunidad educativa al nuevo año escolar. Las clases inician con normalidad en ambos turnos.', 'todos'],
            ['Reunión de padres de familia', 'Se convoca a los padres de familia a la primera reunión general el próximo viernes a horas 18:00 en el salón auditorio.', 'padres'],
            ['Entrega de notas del primer trimestre', 'Los boletines del primer trimestre estarán disponibles a partir de la próxima semana. Agradecemos su puntualidad.', 'estudiantes'],
            ['Capacitación docente', 'Recordamos a todo el plantel docente la jornada de capacitación pedagógica programada para el sábado.', 'docentes'],
        ];
        foreach ($announcements as $a) {
            Announcement::create([
                'title' => $a[0],
                'body' => $a[1],
                'author_id' => $admin->id,
                'audience' => $a[2],
                'status' => 'publicado',
                'published_at' => now()->subDays(rand(1, 20)),
            ]);
        }

        // ============================================================
        // Segundo colegio demo (más pequeño) para mostrar el aislamiento
        // ============================================================
        $school2 = School::firstOrCreate(['slug' => 'colegio-andino'], [
            'name' => 'Colegio Andino',
            'plan' => 'basico',
            'status' => 'activo',
            'trial_ends_at' => now()->addDays(30),
            'email' => 'admin@andino.test',
            'address' => 'Calle Los Pinos #45',
            'phone' => '(2) 244-5566',
        ]);

        app(Tenancy::class)->set($school2->id);

        Setting::firstOrCreate([], [
            'school_name' => 'Colegio Andino',
            'academic_year' => date('Y'),
            'active_period' => '1er Trimestre',
            'currency' => 'Bs',
            'director' => 'Lic. Carmen Vega',
            'tuition_amount' => 300,
        ]);

        User::firstOrCreate(['email' => 'admin@andino.test'], [
            'name' => 'Dirección Andino',
            'password' => Hash::make('password'),
            'role_id' => $adminRole->id,
            'is_active' => true,
        ]);

        $courseA = Course::create([
            'name' => '1ro de Secundaria', 'level' => 'Secundaria', 'grade' => '1ro',
            'section' => 'A', 'shift' => 'Mañana', 'capacity' => 30,
            'academic_year' => date('Y'), 'status' => 'activo',
        ]);
        foreach (range(1, 8) as $i) {
            Student::create([
                'code' => 'AND-'.str_pad((string) $i, 4, '0', STR_PAD_LEFT),
                'first_name' => $nombres[array_rand($nombres)],
                'last_name' => $apellidos[array_rand($apellidos)].' '.$apellidos[array_rand($apellidos)],
                'gender' => ['M', 'F'][rand(0, 1)],
                'course_id' => $courseA->id,
                'enrollment_date' => now(),
                'status' => 'activo',
            ]);
        }

        // Liberar el tenant al finalizar el seeding
        app(Tenancy::class)->forget();
    }
}
