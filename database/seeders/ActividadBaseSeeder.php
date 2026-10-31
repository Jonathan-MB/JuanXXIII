<?php

namespace Database\Seeders;

use App\Models\ActividadBase;
use Illuminate\Database\Seeder;

class ActividadBaseSeeder extends Seeder
{

    //  Borrador tomado del "Desarrollo del retiro" de septiembre de 2026
    // (Formatos Rectorados.xlsx: retiro 90 hombres y 89 mujeres).
    // Formato: día | inicio | fin | nombre | C si es charla.
    private const HOMBRES = <<<'TEXTO'
        0|17:00|18:00|Celebración Santa Misa|
        0|19:15|19:45|Llamar candidatos|
        0|19:45|20:00|Normas y autopresentación|
        0|20:00|20:45|Toma de dormitorios|
        0|20:45|21:05|PRESENTACIÓN|C
        0|21:05|21:55|¿QUIÉN ERES?|C
        0|21:55|22:15|Merienda|
        0|22:15|23:05|LA CONQUISTA|C
        0|23:05|23:15|Dinámica Conócete|
        0|23:15|00:15|ME LEVANTARÉ|C
        0|00:15|00:30|Dinámica Hijo Pródigo|
        0|00:30|00:40|Oraciones finales|
        1|06:00|06:20|Levantarse y capilla|
        1|06:20|06:30|Oración de la mañana|
        1|06:30|07:30|TRES MIRADAS|C
        1|07:30|08:00|Desayuno|
        1|08:00|08:45|IDEAL|C
        1|08:45|08:55|Dinámica Ideal|
        1|08:55|09:40|DEBER|C
        1|09:40|10:40|VOLUNTAD|C
        1|10:40|10:50|Baños|
        1|10:50|11:50|CRISTO DIOS Y HOMBRE|C
        1|11:50|12:20|Previsto|
        1|12:20|12:50|Almuerzo|
        1|12:50|13:50|VIDA EN GRACIA|C
        1|13:50|14:35|SACRAMENTOS|C
        1|14:35|15:35|Baños|
        1|15:35|16:20|SER HOMBRE / CASTIDAD MATRIMONIAL|C
        1|16:20|17:10|PIEDAD|C
        1|17:10|17:40|Comida y baños|
        1|17:40|18:30|INVITADOS DESCORTESES|C
        1|18:30|19:35|AYUDAS DE DIOS|C
        1|19:35|20:05|Previsto|
        1|20:05|23:05|Confesión y plenaria|
        1|23:05|23:15|Oraciones de la noche|
        2|05:00|05:30|Previsto mañanitas|
        2|06:00|06:30|Levantarse y capilla|
        2|06:30|07:30|IGLESIA Y SUS FIGURAS|C
        2|07:30|07:40|Dinámica Iglesia|
        2|07:40|08:10|Desayuno|
        2|08:10|08:50|ESTUDIO|C
        2|08:50|09:30|LO QUE NOS RODEA|C
        2|09:30|09:45|Baños y donaciones|
        2|09:45|10:35|LÍDERES|C
        2|10:35|11:35|ACTUAR|C
        2|11:35|12:05|Previsto|
        2|12:05|12:50|Almuerzo|
        2|12:50|13:20|AYUDA SOCIAL|C
        2|13:20|14:05|APÓSTOLES|C
        2|14:05|14:35|Recomendaciones, murales y baños|
        2|15:05|15:35|Librería|
        2|16:00|17:00|Misa|
        2|17:00|19:00|Clausura|
        TEXTO;

    private const MUJERES = <<<'TEXTO'
        0|17:00|18:00|Celebración Santa Misa|
        0|18:30|19:15|Llamar candidatas|
        0|19:15|19:30|Normas y autopresentación|
        0|19:30|20:15|Toma de dormitorios|
        0|20:15|20:40|PRESENTACIÓN|C
        0|20:40|21:30|¿QUIÉN ERES?|C
        0|21:30|21:50|Merienda|
        0|21:50|22:40|LA CONQUISTA|C
        0|22:40|22:55|Dinámica Conócete|
        0|22:55|23:55|ME LEVANTARÉ|C
        0|23:55|00:10|Dinámica Hijo Pródigo|
        0|00:10|00:20|Oraciones finales|
        1|05:30|05:50|Levantarse y capilla|
        1|05:50|06:00|Oración de la mañana|
        1|06:00|07:00|TRES MIRADAS|C
        1|07:00|07:30|Desayuno|
        1|07:30|08:15|IDEAL|C
        1|08:15|08:25|Dinámica Ideal|
        1|08:25|09:10|DEBER|C
        1|09:10|10:10|VOLUNTAD|C
        1|10:10|10:25|Baños|
        1|10:25|11:25|CRISTO DIOS Y HOMBRE|C
        1|11:25|11:55|Previsto|
        1|11:55|12:25|Almuerzo|
        1|12:25|13:25|VIDA EN GRACIA|C
        1|13:25|14:10|SACRAMENTOS|C
        1|14:10|15:10|Baños|
        1|15:10|15:55|SER MUJER / CASTIDAD MATRIMONIAL|C
        1|15:55|16:45|PIEDAD|C
        1|16:45|17:15|Comida y baños|
        1|17:15|18:05|INVITADOS DESCORTESES|C
        1|18:05|19:10|AYUDAS DE DIOS|C
        1|19:10|19:40|Previsto|
        1|19:40|22:40|Confesión y plenaria|
        1|22:40|22:50|Oraciones de la noche|
        2|05:00|05:30|Previsto mañanitas|
        2|05:30|06:00|Levantarse y capilla|
        2|06:00|07:00|IGLESIA Y SUS FIGURAS|C
        2|07:00|07:10|Dinámica Iglesia|
        2|07:10|07:40|Desayuno|
        2|07:40|08:20|ESTUDIO|C
        2|08:20|09:00|LO QUE NOS RODEA|C
        2|09:00|09:15|Baños y donaciones|
        2|09:15|10:05|LÍDERES|C
        2|10:05|11:05|ACTUAR|C
        2|11:05|11:35|Previsto|
        2|12:05|12:50|Almuerzo|
        2|12:50|13:20|AYUDA SOCIAL|C
        2|13:20|14:05|APÓSTOLES|C
        2|14:05|14:35|Librería|
        2|14:35|15:35|Recomendaciones, murales y baños|
        2|16:00|17:00|Misa|
        2|17:00|19:00|Clausura|
        TEXTO;



    public function run(): void
    {
        //  La base la edita Secretaría: si ya existe la de un sexo no se
        // toca, para no devolver cambios ni revivir actividades quitadas.
        foreach (['Masculino' => self::HOMBRES, 'Femenino' => self::MUJERES] as $sexo => $texto) {
            if (ActividadBase::where('sexo', $sexo)->exists()) {
                continue;
            }

            foreach (preg_split('/\R/', trim($texto)) as $linea) {
                [$dia, $inicio, $fin, $nombre, $charla] = explode('|', trim($linea));

                ActividadBase::firstOrCreate(
                    ['sexo' => $sexo, 'dia_relativo' => (int) $dia, 'hora_inicio' => $inicio . ':00', 'nombre' => $nombre],
                    ['hora_fin' => $fin . ':00', 'es_charla' => $charla === 'C']
                );
            }
        }
    }
}
