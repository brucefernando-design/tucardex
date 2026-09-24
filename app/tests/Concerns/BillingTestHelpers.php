<?php

namespace Tests\Concerns;

use App\Models\ElectronicBillingSetting;
use App\Models\Payment;
use App\Models\School;
use App\Models\Student;
use App\Services\Facturacion\Drivers\GreenterDriver;
use App\Services\Tenancy;
use Tests\Support\FakeSunatDriver;

trait BillingTestHelpers
{
    protected FakeSunatDriver $fakeDriver;

    /** Crea un colegio, activa el tenant y devuelve su id. */
    protected function bootTenant(): int
    {
        $school = School::create(['name' => 'Colegio Test', 'slug' => 'colegio-test-'.uniqid()]);
        app(Tenancy::class)->set($school->id);

        return $school->id;
    }

    /** Configuración de facturación lista para emitir con el driver simulado. */
    protected function makeSettings(array $overrides = []): ElectronicBillingSetting
    {
        $settings = ElectronicBillingSetting::create(array_merge([
            'enabled' => true,
            'auto_emit' => true,
            'boletas_por_resumen' => false,
            'driver' => 'greenter',
            'environment' => 'beta',
            'ruc' => '20000000001',
            'razon_social' => 'COLEGIO TEST S.A.C.',
            'direccion_fiscal' => 'Av. Test 123',
            'ubigeo' => '150101',
            'sol_user' => 'MODDATOS',
            'sol_pass' => 'MODDATOS',
            'certificate_path' => 'storage/test/cert.pem',
            'serie_factura' => 'F001',
            'serie_boleta' => 'B001',
            'serie_nc_factura' => 'FC01',
            'serie_nc_boleta' => 'BC01',
            'igv_percent' => 18,
            'moneda' => 'PEN',
        ], $overrides));

        // Sustituye el driver real por el simulado.
        $this->fakeDriver = new FakeSunatDriver();
        $this->app->instance(GreenterDriver::class, $this->fakeDriver);

        return $settings;
    }

    protected function makeStudent(array $overrides = []): Student
    {
        return Student::create(array_merge([
            'code' => 'EST-'.uniqid(),
            'first_name' => 'Juan',
            'last_name' => 'Pérez',
            'dni' => '12345678',
            'guardian_name' => 'María Pérez',
            'status' => 'activo',
        ], $overrides));
    }

    protected function makePayment(Student $student, array $overrides = []): Payment
    {
        return Payment::create(array_merge([
            'student_id' => $student->id,
            'concept' => 'Pensión Marzo',
            'amount' => 118.00,
            'period' => 'Marzo 2026',
            'status' => 'pendiente',
        ], $overrides));
    }
}
