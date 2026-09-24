<?php

namespace Database\Seeders;

use App\Models\Announcement;
use App\Models\Assignment;
use App\Models\Attendance;
use App\Models\Book;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Event;
use App\Models\Grade;
use App\Models\Incident;
use App\Models\Loan;
use App\Models\Message;
use App\Models\Payment;
use App\Models\Schedule;
use App\Models\School;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\User;
use App\Services\Tenancy;
use Illuminate\Database\Seeder;

/**
 * Agrega ~10 registros por módulo con FECHAS REPARTIDAS en varios meses,
 * para que los paneles y gráficos del dashboard muestren buena referencia.
 *
 * Ejecutar:  php artisan db:seed --class=DemoSeeder
 */
class DemoSeeder extends Seeder
{
    public function run(): void
    {
        $school = School::where('slug', 'colegio-san-martin')->first() ?? School::first();

        if (! $school) {
            $this->command->warn('No hay colegios. Ejecuta primero: php artisan migrate:fresh --seed');

            return;
        }

        // Activar el colegio: todo lo creado se asigna a este tenant
        app(Tenancy::class)->set($school->id);

        $nombres = ['Mateo', 'Valentina', 'Santiago', 'Camila', 'Sebastián', 'Isabella', 'Diego', 'Luciana', 'Adriano', 'Gabriela', 'Nicolás', 'Daniela', 'Joaquín', 'Antonella', 'Benjamín', 'Renata', 'Emiliano', 'Mía', 'Thiago', 'Sofía', 'Alejandro', 'Fernanda', 'Rodrigo', 'Paula'];
        $apellidos = ['Mamani', 'Quispe', 'Choque', 'Condori', 'Flores', 'Vargas', 'Rojas', 'Cruz', 'Apaza', 'Torrez', 'Gutiérrez', 'Colque', 'Huanca', 'Aruquipa', 'Villca', 'Poma', 'Nina', 'Ticona'];

        // Helper: fijar fecha de creación de un registro (para reflejarlo en el tiempo)
        $stamp = function ($model, $date) {
            $model->timestamps = false;
            $model->created_at = $date;
            $model->updated_at = $date;
            $model->save();
            $model->timestamps = true;

            return $model;
        };

        // ---- Base existente ----
        $courses = Course::all();
        if ($courses->isEmpty()) {
            foreach ([['1ro de Secundaria', '1ro'], ['2do de Secundaria', '2do'], ['3ro de Secundaria', '3ro']] as $i => $c) {
                Course::create(['name' => $c[0], 'level' => 'Secundaria', 'grade' => $c[1], 'section' => 'A', 'shift' => 'Mañana', 'capacity' => 35, 'academic_year' => date('Y'), 'status' => 'activo']);
            }
            $courses = Course::all();
        }

        $admin = User::first();

        // ============ 1) DOCENTES (10) ============
        $specialties = ['Matemática', 'Lenguaje', 'Biología', 'Química', 'Física', 'Historia', 'Geografía', 'Inglés', 'Arte', 'Música'];
        $teachers = collect();
        foreach (range(1, 10) as $i) {
            $t = Teacher::create([
                'code' => 'DOCX-'.str_pad((string) $i, 4, '0', STR_PAD_LEFT),
                'first_name' => $nombres[array_rand($nombres)],
                'last_name' => $apellidos[array_rand($apellidos)].' '.$apellidos[array_rand($apellidos)],
                'dni' => (string) rand(3000000, 9999999),
                'email' => 'docente'.$i.'.'.rand(10, 99).'@colegio.test',
                'phone' => '7'.rand(1000000, 9999999),
                'specialty' => $specialties[$i - 1],
                'gender' => ['F', 'Masculino'][rand(0, 1)],
                'hire_date' => now()->subMonths(rand(1, 60)),
                'status' => 'activo',
            ]);
            $teachers->push($stamp($t, now()->subMonths(rand(0, 10))));
        }

        // ============ 2) MATERIAS (10) ============
        $subjectsData = [
            ['Robótica', 'ROB'], ['Filosofía', 'FIL'], ['Psicología', 'PSI'], ['Economía', 'ECO'],
            ['Contabilidad', 'CON'], ['Dibujo Técnico', 'DIB'], ['Danza', 'DAN'], ['Teatro', 'TEA'],
            ['Ajedrez', 'AJE'], ['Programación', 'PRG'],
        ];
        foreach ($subjectsData as $s) {
            Subject::firstOrCreate(['code' => $s[1]], ['name' => $s[0], 'area' => 'Complementaria', 'status' => 'activo']);
        }
        $subjects = Subject::all();

        // ============ 3) ESTUDIANTES (10) con fechas de matrícula repartidas ============
        $newStudents = collect();
        $baseCode = (int) (Student::max('id')) + 500;
        foreach (range(1, 10) as $i) {
            $course = $courses->random();
            $enrollDate = now()->subMonths(rand(0, 9))->subDays(rand(0, 28));
            $st = Student::create([
                'code' => 'ESTX-'.str_pad((string) ($baseCode + $i), 5, '0', STR_PAD_LEFT),
                'first_name' => $nombres[array_rand($nombres)],
                'last_name' => $apellidos[array_rand($apellidos)].' '.$apellidos[array_rand($apellidos)],
                'dni' => (string) rand(8000000, 14000000),
                'birth_date' => now()->subYears(rand(11, 17))->subDays(rand(0, 360)),
                'gender' => ['M', 'F'][rand(0, 1)],
                'phone' => '6'.rand(1000000, 9999999),
                'guardian_name' => $nombres[array_rand($nombres)].' '.$apellidos[array_rand($apellidos)],
                'guardian_phone' => '7'.rand(1000000, 9999999),
                'course_id' => $course->id,
                'enrollment_date' => $enrollDate,
                'status' => 'activo',
            ]);
            $newStudents->push($stamp($st, $enrollDate));

            // Matrícula correspondiente
            Enrollment::create([
                'student_id' => $st->id, 'course_id' => $course->id,
                'academic_year' => date('Y'), 'enrollment_date' => $enrollDate, 'status' => 'inscrito',
            ]);
        }

        $allStudents = Student::all();

        // ============ 4) PAGOS repartidos en los últimos 8 meses (clave para el gráfico) ============
        $invSeq = (int) Payment::max('id') + 1000;
        $meses = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
        for ($m = 7; $m >= 0; $m--) {
            $month = now()->subMonths($m);
            // 10 pagos por mes
            foreach ($allStudents->random(min(10, $allStudents->count())) as $st) {
                $paid = rand(0, 10) > 2; // ~80% pagados
                $paidDate = $paid ? $month->copy()->day(rand(1, 26)) : null;
                $p = Payment::create([
                    'student_id' => $st->id,
                    'invoice_number' => 'FACX-'.$month->format('Ym').'-'.str_pad((string) $invSeq++, 5, '0', STR_PAD_LEFT),
                    'concept' => 'Pensión '.$meses[$month->month - 1],
                    'amount' => rand(200, 350),
                    'period' => $month->translatedFormat('F Y'),
                    'due_date' => $month->copy()->endOfMonth(),
                    'paid_date' => $paidDate,
                    'method' => $paid ? ['efectivo', 'transferencia', 'qr'][rand(0, 2)] : null,
                    'status' => $paid ? 'pagado' : ($m > 1 ? 'vencido' : 'pendiente'),
                ]);
                $stamp($p, $month->copy()->day(rand(1, 26)));
            }
        }

        // ============ 5) CALIFICACIONES (10+) en distintos periodos ============
        $periods = ['1er Trimestre', '2do Trimestre', '3er Trimestre'];
        $types = ['examen', 'practica', 'tarea', 'proyecto'];
        foreach (range(1, 15) as $i) {
            $st = $allStudents->random();
            $g = Grade::create([
                'student_id' => $st->id,
                'subject_id' => $subjects->random()->id,
                'course_id' => $st->course_id,
                'period' => $periods[array_rand($periods)],
                'type' => $types[array_rand($types)],
                'score' => rand(40, 99),
            ]);
            $stamp($g, now()->subMonths(rand(0, 6))->subDays(rand(0, 20)));
        }

        // ============ 6) ASISTENCIA en distintas fechas (para estudiantes nuevos) ============
        foreach ($newStudents as $st) {
            foreach (range(1, 10) as $d) {
                Attendance::updateOrCreate(
                    ['student_id' => $st->id, 'date' => now()->subDays($d * 3 + rand(0, 2))->toDateString()],
                    ['course_id' => $st->course_id, 'status' => ['presente', 'presente', 'presente', 'tardanza', 'ausente', 'justificado'][rand(0, 5)]]
                );
            }
        }

        // ============ 7) HORARIOS (10) ============
        $days = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes'];
        foreach (range(1, 10) as $i) {
            $start = 8 + ($i % 5);
            Schedule::create([
                'course_id' => $courses->random()->id,
                'subject_id' => $subjects->random()->id,
                'teacher_id' => $teachers->random()->id,
                'day_of_week' => $days[array_rand($days)],
                'start_time' => sprintf('%02d:00', $start),
                'end_time' => sprintf('%02d:30', $start),
                'classroom' => 'Aula '.rand(1, 20),
            ]);
        }

        // ============ 8) TAREAS (10) con fechas repartidas ============
        foreach (range(1, 10) as $i) {
            $course = $courses->random();
            $assigned = now()->subMonths(rand(0, 5))->subDays(rand(0, 20));
            $a = Assignment::create([
                'title' => 'Tarea '.$i.': '.['Investigación', 'Práctica', 'Ensayo', 'Proyecto', 'Cuestionario'][rand(0, 4)],
                'description' => 'Actividad de refuerzo para la unidad correspondiente.',
                'course_id' => $course->id,
                'subject_id' => $subjects->random()->id,
                'teacher_id' => optional($course->tutor)->id,
                'assigned_date' => $assigned,
                'due_date' => (clone $assigned)->addDays(rand(3, 10)),
                'status' => rand(0, 1) ? 'activa' : 'cerrada',
                'created_by' => optional($admin)->id,
            ]);
            $stamp($a, $assigned);
        }

        // ============ 9) DISCIPLINA (10) en distintas fechas ============
        $incs = [
            ['merito', 'Participación', 'Buen desempeño en clase.', 5],
            ['demerito', 'Puntualidad', 'Llegó tarde.', -3],
            ['observacion', 'Conducta', 'Requiere atención.', 0],
            ['merito', 'Responsabilidad', 'Entregó todo a tiempo.', 5],
            ['demerito', 'Respeto', 'Interrumpió la clase.', -4],
        ];
        foreach (range(1, 10) as $i) {
            $x = $incs[array_rand($incs)];
            $d = now()->subMonths(rand(0, 5))->subDays(rand(0, 25));
            $inc = Incident::create([
                'student_id' => $allStudents->random()->id,
                'date' => $d, 'type' => $x[0], 'category' => $x[1],
                'description' => $x[2], 'points' => $x[3], 'reported_by' => optional($admin)->id,
            ]);
            $stamp($inc, $d);
        }

        // ============ 10) EVENTOS (10) repartidos en el año ============
        $evTypes = ['feriado', 'examen', 'reunion', 'actividad', 'civico'];
        foreach (range(1, 10) as $i) {
            $d = now()->startOfYear()->addDays(rand(0, 330));
            $ev = Event::create([
                'title' => ['Reunión', 'Examen', 'Feriado', 'Acto cívico', 'Feria', 'Taller', 'Excursión'][rand(0, 6)].' '.$i,
                'type' => $evTypes[array_rand($evTypes)],
                'date' => $d->toDateString(),
                'created_by' => optional($admin)->id,
                'description' => 'Evento del calendario escolar.',
            ]);
            $stamp($ev, now()->subMonths(rand(0, 6)));
        }

        // ============ 11) COMUNICADOS (10) con fechas repartidas ============
        $aud = ['todos', 'docentes', 'estudiantes', 'padres'];
        foreach (range(1, 10) as $i) {
            $d = now()->subDays(rand(1, 150));
            $an = Announcement::create([
                'title' => 'Comunicado '.$i.': '.['Reunión', 'Recordatorio', 'Aviso', 'Circular', 'Invitación'][rand(0, 4)],
                'body' => 'Estimada comunidad educativa, les informamos sobre las actividades programadas. Agradecemos su atención.',
                'author_id' => optional($admin)->id,
                'audience' => $aud[array_rand($aud)],
                'status' => 'publicado',
                'published_at' => $d,
            ]);
            $stamp($an, $d);
        }

        // ============ 12) BIBLIOTECA: libros (10) + préstamos (10) ============
        $bookTitles = [
            ['Matemáticas 1', 'Santillana', 'Texto'], ['Lenguaje 2', 'SM', 'Texto'], ['Atlas Universal', 'Larousse', 'Referencia'],
            ['Química Orgánica', 'McGraw', 'Ciencias'], ['Cuentos Andinos', 'Nacional', 'Literatura'], ['Historia Universal', 'Vicens', 'Sociales'],
            ['Inglés Básico', 'Oxford', 'Idiomas'], ['Biología Celular', 'Panamericana', 'Ciencias'], ['Geometría', 'Baldor', 'Matemática'], ['Poemas Selectos', 'Alfaguara', 'Literatura'],
        ];
        $books = collect();
        foreach ($bookTitles as $i => $b) {
            $book = Book::create([
                'title' => $b[0], 'author' => $apellidos[array_rand($apellidos)], 'editorial' => $b[1],
                'category' => $b[2], 'isbn' => '978-'.rand(1000000000, 9999999999),
                'location' => 'Estante '.chr(65 + ($i % 5)), 'quantity' => rand(3, 8),
            ]);
            $book->available = $book->quantity;
            $book->save();
            $books->push($book);
        }
        foreach (range(1, 10) as $i) {
            $book = $books->random();
            if ($book->available < 1) {
                continue;
            }
            $loanDate = now()->subDays(rand(2, 40));
            $returned = rand(0, 1);
            $l = Loan::create([
                'book_id' => $book->id,
                'student_id' => $allStudents->random()->id,
                'loan_date' => $loanDate,
                'due_date' => (clone $loanDate)->addDays(7),
                'return_date' => $returned ? (clone $loanDate)->addDays(rand(1, 7)) : null,
                'status' => $returned ? 'devuelto' : ((clone $loanDate)->addDays(7)->isPast() ? 'vencido' : 'prestado'),
            ]);
            if (! $returned) {
                $book->decrement('available');
            }
            $stamp($l, $loanDate);
        }

        // ============ 13) MENSAJES (10) ============
        $users = User::all();
        if ($users->count() >= 2) {
            foreach (range(1, 10) as $i) {
                $from = $users->random();
                $to = $users->where('id', '!=', $from->id)->random();
                $d = now()->subDays(rand(1, 60));
                $msg = Message::create([
                    'sender_id' => $from->id, 'recipient_id' => $to->id,
                    'subject' => ['Consulta', 'Reunión', 'Solicitud', 'Recordatorio', 'Coordinación'][rand(0, 4)].' #'.$i,
                    'body' => 'Mensaje de coordinación interna. Quedo atento a su respuesta.',
                    'read_at' => rand(0, 1) ? $d : null,
                ]);
                $stamp($msg, $d);
            }
        }

        app(Tenancy::class)->forget();

        $this->command->info('DemoSeeder: datos de demostración agregados a "'.$school->name.'" con fechas repartidas.');
    }
}
