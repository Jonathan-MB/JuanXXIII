<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        //  El login es con nombre de usuario, no con correo.
        // El correo queda como dato de contacto opcional.
        Schema::table('usuarios', function (Blueprint $table) {
            $table->string('usuario',45)->nullable()->after('nombre');
            $table->string('correo',100)->nullable()->change();
        });

        // Las cuentas que ya existían toman como usuario lo que va antes de la @
        DB::table('usuarios')->whereNull('usuario')->orderBy('id')->each(function ($cuenta) {
            DB::table('usuarios')->where('id', $cuenta->id)->update([
                'usuario' => strstr($cuenta->correo, '@', true) ?: 'usuario' . $cuenta->id,
            ]);
        });

        Schema::table('usuarios', function (Blueprint $table) {
            $table->string('usuario',45)->nullable(false)->unique()->change();
        });
    }



    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('usuarios', function (Blueprint $table) {
            $table->dropUnique(['usuario']);
            $table->dropColumn('usuario');
        });
    }
};
